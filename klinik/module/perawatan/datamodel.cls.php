<?
require_once("root.inc.php");
require_once($ROOT."library/dataaccess.cls.php");

/**
 * Simple DAO class for encapsulate simple query process
 */

class DataModel
{
	var $_dbTable;
	var $_dbField;
	var $_dbValue;
	var $_dbKey;
	var $_dtaccess;
	var $_dbSchema;

/**
 * Constructor DataModel Class
 * @param in_dbTable string table name
 * @param in_dbField array of string stored names of field in table name
 * @param in_dbValue array of string stored values of each field in dbField
 * @param in_dbKey array of integer stored array keys of in_dbFields which represent key used in where clause
 */
	function DataModel (& $in_dbTable, & $in_dbField, & $in_dbValue, $in_dbKey=null, $in_dbSchema=DB_SCHEMA_GLOBAL)
	{
    	$this->_dtaccess = new DataAccess();
		$this->_dbTable = $in_dbTable;
		$this->_dbField = $in_dbField;
		$this->_dbValue = $in_dbValue;
		$this->_dbKey = $in_dbKey;
		$this->_dbSchema = $in_dbSchema;
	}

/**
 * Insert Function
 * @return bool
 */
	function Insert()
	{
		$sql = "insert into ".$this->_dbTable."(";

		for($i=0,$n=count($this->_dbField); $i<$n; $i++){
			if(isset($this->_dbValue[$i])) $sql.= $this->_dbField[$i].","; 
		}
		$sql = substr($sql,0,-1);

		$sql.= ") values (";

		for($i=0,$n=count($this->_dbValue);$i<$n;$i++){
			if(isset($this->_dbValue[$i]))  $sql.= $this->_dbValue[$i].",";
		}
		$sql = substr($sql,0,-1);

		$sql.= ")";

		return $this->_dtaccess->Execute($sql,$this->_dbSchema)?true:false;
		//return $sql;
	}

/**
 * Update Function
 * @return bool
 */
	function Update()
	{
		if(!$this->_dbKey) return false;
			
		$sql = "update ".$this->_dbTable." set ";

		for($i=0,$n=count($this->_dbField); $i<$n; $i++)
			$sql.= $this->_dbField[$i]." = ". $this->_dbValue[$i]. ",";

        $sql = substr($sql,0,-1);

        $sql.= " where ";

        for($i=0,$n=count($this->_dbKey); $i<$n; $i++)
			$sql.= $this->_dbField[$this->_dbKey[$i]]." = ".$this->_dbValue[$this->_dbKey[$i]]." and ";
        $sql = substr($sql,0,-5);

        return $this->_dtaccess->Execute($sql,$this->_dbSchema)?true:false;
        //return $sql;
	}
	
/**
 * Delete Function
 * @return bool
 */
	function Delete()
	{
		if(!$this->_dbKey) return false;

		$sql = "delete from ".$this->_dbTable." where ";

		for($i=0,$n=count($this->_dbKey); $i<$n-1; $i++)
			$sql.= $this->_dbField[$this->_dbKey[$i]]." = ".$this->_dbValue[$this->_dbKey[$i]]." and ";
		
		$sql.= $this->_dbField[$this->_dbKey[$i]]." = ".$this->_dbValue[$this->_dbKey[$i]];
		
		return $this->_dtaccess->Execute($sql,$this->_dbSchema)?true:false;
	}


/**
 * GetSequence Function
 * dipanggil sebelum event insert / update / delete
 * @return bool
 */
    function SetSequence($in_seq,$in_keySeq,$in_del=false,$in_sqlWhere=null) 
    {
        // -- reset sequence biz delete ---
        if($in_del){
            $sql = "update ".$this->_dbTable." set ".$this->_dbField[$in_keySeq]." = ".$this->_dbField[$in_keySeq]."-1 ";
            $sql.= "where ".$this->_dbField[$in_keySeq]." > ";
            $sql.= "(select ".$this->_dbField[$in_keySeq]." from ".$this->_dbTable." where ";
            for($i=0,$n=count($this->_dbKey); $i<$n-1; $i++)
                $sql.= $this->_dbField[$this->_dbKey[$i]]." = ".$this->_dbValue[$this->_dbKey[$i]]." and ";
		    $sql.= $this->_dbField[$this->_dbKey[$i]]." = ".$this->_dbValue[$this->_dbKey[$i]];
            $sql.= ")";
            if($in_sqlWhere) $sql = $sql." and ".$in_sqlWhere;
		    return $this->_dtaccess->Execute($sql,$this->_dbSchema)?true:false;
        }
        
        if($in_seq){
            $sql = "update ".$this->_dbTable." set ".$this->_dbField[$in_keySeq]." = ".$this->_dbField[$in_keySeq]."+1 ";
            $sql.= "where ".$this->_dbField[$in_keySeq]." >= ".$in_seq;
            if($in_sqlWhere) $sql = $sql." and ".$in_sqlWhere;
		    return $this->_dtaccess->Execute($sql,$this->_dbSchema)?true:false;
        }
        
    }

/**
 * GetSequenceData Function
 * @return data combo sequence
 */
    function GetSequenceData($in_keySeq,$in_keyShow,$in_edit=null,$in_sqlWhere=null)
    {
        $sql = "select ".$this->_dbField[$in_keySeq].",".$this->_dbField[$in_keyShow]." from ".$this->_dbTable;
        if($in_sqlWhere) $sql.= " where ".$in_sqlWhere;
        $sql.= " order by ".$this->_dbField[$in_keySeq];
        $rs = $this->_dtaccess->Execute($sql,$this->_dbSchema);
        $dataSeq = $this->_dtaccess->FetchAll($rs);
		//echo $sql;
        if($in_edit) $cmbSeq["no"] = "No Change";
        $cmbSeq[1] = "TOP";
        for($i=1,$n=count($dataSeq);$i<$n;$i++){
            $cmbSeq[$dataSeq[$i][$this->_dbField[$in_keySeq]]] = "AFTER ".$dataSeq[$i-1][$this->_dbField[$in_keyShow]];
        }
        $cmbSeq[($dataSeq[$i-1][$this->_dbField[$in_keySeq]]+1)] = "BOTTOM";
        return $cmbSeq;
    }
}


?>
