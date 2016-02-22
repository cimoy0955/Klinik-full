<?php
require_once("root.inc.php");
require_once($ROOT."library/textEncrypt.cls.php");
require_once($ROOT."library/config/db.cfg.php");
require_once($ROOT."library/adodb/adodb.inc.php");
require_once($ROOT."library/adodb/adodb-errorhandler.inc.php");

$G_Connection = & ADONewConnection(DB_DRIVER);
$G_Connection->debug = DB_DEBUGGING;

function QuoteTable($in_tableName)
{
    return "`".$in_tableName."`";
}

function QuoteField($in_fieldName)
{
    return "`".$in_fieldName."`";
}

function _QuoteChar($in_value)
{
    global $G_Connection;
    return $G_Connection->qstr($in_value,get_magic_quotes_gpc());
}

function _QuoteExecDate($in_date)
{
    global $G_Connection;
    return $G_Connection->DBDate($in_date);
}

function _QuoteSelectDate($in_format, $in_field)
{
    global $G_Connection;
    return $G_Connection->SqlDate($in_format, $in_field);
}

function _QuoteExecDateTime($in_dateTime)
{
    global $G_Connection;
    return $G_Connection->DBTimeStamp($in_dateTime);
}

function QuoteValue($in_type,$val,$in_format=null)
{
    switch ($in_type) {
        case  DPE_CHAR: return _QuoteChar(trim($val));
        break;
        case  DPE_DATE: return _QuoteExecDate(trim($val));
        break; 
        case  DPE_DATETIME: return _QuoteExecDateTime(trim($val));
        break;
        case  DPE_TIMESTAMP: return _QuoteExecDateTime(trim($val));
        break;
        case  DPE_NUMERIC: return ($val && is_numeric($val)) ? $val : "0";
        break;
        case  DPE_NUMERICKEY: return ($val) ? $val : "null";
        break;
        case  DPE_CHARKEY: return ($val) ? _QuoteChar(trim($val)) : "null";
        break;
        default: return $val;
     }
}



class DataAccess
{
    var $db;
    var $_usrData;

     /**
    * Constucts a new DataAccess object
    * @param $host string hostname for dbserver
    * @param $user string dbserver user
    * @param $pass string dbserver user password
    * @param $db string database name
    * usage $somevar = new DataAccess()
    * setup files can be configured in config/db.cfg.php
    */

    function DataAccess($in_post=null)
    {
        global $G_Connection;
        $encrypt = new TextEncrypt();
         
        $G_Connection->PConnect(DB_SERVER,$encrypt->Decode(DB_USER),$encrypt->Decode(DB_PASSWORD),DB_NAME);
        
        $this->db = & $G_Connection;
    }

    function Reconnect($in_dbName)
    {
        $this->db->PConnect(DB_SERVER,$encrypt->Decode(DB_USER),$encrypt->Decode(DB_PASSWORD),$encrypt->Decode($in_dbName));
    }

    function CloseDb()
    {
        $this->db->close;
    }

   function Execute($in_sql,$in_schema=DB_SCHEMA_GLOBAL)
    {
        global $globalData;
 
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);

        $loginData=$globalData->GetEntry("Login");

        // --- mekanisme log ---
        if(isset($loginData["id"])){
            if (preg_match ("/global.user_log/i",$in_sql)) $tipe = 'N';
            elseif (preg_match ("/global.session/i",$in_sql)) $tipe = 'N';
            elseif (preg_match ("/\b^insert\b/i",$in_sql)) $tipe = 'I';
            elseif (preg_match ("/\b^update\b/i",$in_sql)) $tipe = 'U';
            elseif (preg_match ("/\b^delete\b/i",$in_sql)) $tipe = 'D';
        
            if($tipe == 'I' || $tipe == 'U' || $tipe == 'D') {
              
               $in_sql_pio = str_replace("'","",$in_sql);
            
               $sql = "insert into global.global_dblog(log_data,log_who,log_tipe,log_ip,log_when) values ( ".
                        QuoteValue(DPE_CHAR,$in_sql_pio).",". 
                        QuoteValue(DPE_CHAR,$loginData["loginname"]).",". 
                        QuoteValue(DPE_CHAR,$tipe).",". 
                        QuoteValue(DPE_CHAR,$_SERVER["REMOTE_ADDR"]).",". 
                        QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s")).")";
                $this->db->Execute($sql);
               
                //$sql = "insert into global.global_dblog(log_data,log_who,log_tipe,log_ip,log_when) values ( ".
                       // QuoteValue(DPE_CHAR,$in_sql).",". 
                       // QuoteValue(DPE_CHAR,$loginData["loginname"]).",". 
                       // QuoteValue(DPE_CHAR,$tipe).",". 
                      ////  QuoteValue(DPE_CHAR,$_SERVER["REMOTE_ADDR"]).",". 
                       // QuoteValue(DPE_DATETIME,date("Y-m-d H:i:s")).")";
               // $this->db->Execute($sql);
            }
        }
        
        return $this->db->Execute($in_sql);
    }

    function Query($in_sql,$numrows=-1,$offset=-1,$in_schema=DB_SCHEMA_GLOBAL)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->db->SelectLimit($in_sql, $numrows, $offset);
    }

    function & Fetch(& $in_rs)
    {
          if(is_object($in_rs)) return $in_rs->FetchRow();
          else {
               $rs = $this->db->Execute($in_rs);
               return $rs->FetchRow();
          }
    }

    function & FetchAll(& $in_rs)
    {
        $res=array();
        
        $rs = (is_object($in_rs)) ? $in_rs :  $this->db->Execute($in_rs);
        $this->MoveFirst($rs);
        while  ($col = $this->Fetch($rs))
        {
            $res[]=$col;
        }
        return $res;
    }


    function MoveFirst(& $in_rs)
    {
        $in_rs->MoveFirst();
    }

    function MoveNext(& $in_rs)
    {
        $in_rs->MoveNext();
    }

    function MovePrev(& $in_rs)
    {
        $in_rs->Move($in_rs->CurrentRow()-1);
    }

    function MoveLast(& $in_rs)
    {
        $in_rs->MoveLast();
    }

    function RowCount(& $in_rs)
    {
        return $in_rs->RecordCount();
    }

    function GetLastID($in_tabel, $in_field,$in_schema=DB_SCHEMA_GLOBAL)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $query_rsLastID = sprintf("SELECT MAX($in_field) as last_id FROM $in_tabel");
        $rsLastID = $this->db->Execute($query_rsLastID);
        $row_rsLastID = $this->Fetch($rsLastID);

        if (!$row_rsLastID["last_id"])
            return 0;
        else
            return $row_rsLastID["last_id"];
    }

    function & GetNewID($in_tabel, $in_field,$in_schema=DB_SCHEMA_GLOBAL)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $row_rsMaxID=$this->GetLastID($in_tabel, $in_field,$in_schema);
        return $row_rsMaxID+1;
    }
    
    function GetTransID()
    {
        $r = rand();
        $u = uniqid(getmypid() . $r . (double)microtime()*1000000,true);
        $m = md5(session_id().$u);
        return($m);  
    }

    //--- G Add Code Here ---//
    //---- Clear Method ----//
    function Clear(& $in_rs) {
       if ($in_rs) {
            $in_rs->Close();
       } 
    }
    
    //---- Close Method ----//
    function Close() {
        $this->db->Close();       
    }

    //---- Count Method ----//
    function Count(& $in_rs) {
        if($in_rs) {
            return $in_rs->RecordCount();
        } else {
            return (-1);
        }
    }

    function GetLastID_W($in_tabel, $in_field, $in_where = "", $in_schema=DB_SCHEMA_GLOBAL)
    {   
        
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $_whereSQL = "";
        if ($in_where != "")
            $_whereSQL = "WHERE ".$in_where;
        $query_rsLastID = "SELECT MAX(".$in_field.") as last_id FROM ".$in_tabel." ".$_whereSQL;
        $rsLastID = $this->db->Execute($query_rsLastID);
        $row_rsLastID = $this->Fetch($rsLastID);

        if (!$row_rsLastID["last_id"])
            return 0;
        else
            return $row_rsLastID["last_id"];
    }

    function & GetNewID_W($in_tabel, $in_field, $in_where = "", $in_schema=DB_SCHEMA_GLOBAL)
    {
        if($in_schema) $this->db->Execute("set search_path to ".$in_schema);
        $row_rsMaxID=$this->GetLastID_W($in_tabel, $in_field, $in_where, $in_schema);
        return $row_rsMaxID+1;
    }
    //--- End of G Code --//
}

?>
