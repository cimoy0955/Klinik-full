<?php
require_once("root.inc.php");
require_once($ROOT."library/dataaccess.cls.php");
require_once($ROOT."library/registry.cls.php");
require_once($ROOT."library/datamodel.cls.php");
require_once($ROOT."library/dateFunc.lib.php");

/**
 * Business Role for user model
 *
 * @author bugsbunny
 */
class CAuth
{
     var $_usrId;
     var $_usrNama;
     var $_dataAccess;
     var $globalData;
     var $_usrData;

     function CAuth()
     {
          global $globalData;
          $this->_dataAccess=new DataAccess();

          $this->globalData=new Registry(APP_ID);
          $this->globalData->Restore();
          $loginData=$this->globalData->GetEntry("Login");
        
          $this->_usrId = $loginData["id"];
          $this->_usrName = $loginData["name"];
          $this->_usrData = $loginData;
     }

     function IsLoginOk($in_username,$in_password) 
     {
          $sql = "select a.* from vglobal_auth_user a                      
                    where upper(a.usr_loginname) = ".QuoteValue(DPE_CHAR,strtoupper($in_username))."  
                    and a.usr_password = ".QuoteValue(DPE_CHAR,md5(strtoupper($in_password)))." 
                    and a.usr_tipe <> ".QuoteValue(DPE_CHAR,USR_TIPE_MEMBER);
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataUser = $this->_dataAccess->Fetch($rs);
          if($dataUser){

               $data["loginname"] =  $dataUser["usr_loginname"];
               $data["name"] =  $dataUser["usr_name"];
               $data["id"] = $dataUser["usr_id"];            
               $data["tipe"] = $dataUser["usr_tipe"];
               $data["rol"] = $dataUser["id_rol"];
               $data["dep"] = $dataUser["id_dep"];
               $data["usr_app_def"] = $dataUsrDef["usr_app_def"];        
               $data["id_member"] = $dataMember["member_id"];
                                     
               $this->globalData->SetEntry("Login",$data);
               $this->globalData->Save();
               
               $sql = "delete from global.global_auth_user where usr_name = 'GUEST' AND usr_expire <= '".DateAdd(getdateToday(),-1)."'";
               $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
               return $data;
          } else return 2;
     }


     function IsLoginMPOk($in_username,$in_password) 
     {
          $sql = "select a.* from vglobal_auth_user a                      
                    where upper(a.usr_loginname) = ".QuoteValue(DPE_CHAR,strtoupper($in_username))."  
                    and a.usr_password = ".QuoteValue(DPE_CHAR,md5(strtoupper($in_password)))." 
                    and (a.usr_tipe = 'M' or a.usr_tipe = 'E')";
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataUser = $this->_dataAccess->Fetch($rs);
          if($dataUser){
           
               $sql = "select member_id, member_nama, member_aktif,member_tipe,member_jam_awal,member_jam_akhir,member_expire,member_expire_akhir,member_hari from multiplayer.mp_member where id_usr = ".$dataUser["usr_id"];
               $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA);
               $dataMember = $this->_dataAccess->Fetch($rs);
               
               if ($dataMember["member_nama"]=="GUEST")
               {   
               //cek jam
               $waktuSkarang=date("H:i:s");
               $selisihAwal=TimeDiff($waktuSkarang,$dataMember["member_jam_awal"]);
               $selisihAkhir=TimeDiff($dataMember["member_jam_akhir"],$waktuSkarang);
               if ($selisihAwal>0 && $selisihAkhir>0) return 3; //jam ngga tepat
               
               //cek tanggal
               if ($dataMember["member_expire"]||$dataMember["member_expire_akhir"])
               {
               $tanggalSkarang=getdateToday();
               $selisihTglAwal=DateDiff($tanggalSkarang,$dataMember["member_expire"]);
               $selisihTglAkhir=DateDiff($dataMember["member_expire_akhir"],$tanggalSkarang); 
               if ($selisihTglAwal>0 || $selisihTglAkhir>0) return 4;
               }
               
               //cek hari
               if ($dataMember["member_hari"]!="09090909090909") {
               
                $tanggalSkarang=getdateToday();
                $hariPaket  = explode("9",$dataMember["member_hari"]);
                $hariSkarang=GetDay($tanggalSkarang);
                //return $dataMember["member_hari"];
                if ($hariPaket[$hariSkarang]==0) return 5; 
               }
               }
               
               if($dataMember["member_aktif"]=="y") return 1;//user sedang aktif
               
               $sql = "delete from global.global_auth_user where usr_name = 'GUEST' AND usr_expire <= '".DateAdd(getdateToday(),-1)."'";
               $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
               
               $data["loginname"] =  $dataUser["usr_loginname"];
               $data["name"] =  $dataUser["usr_name"];
               $data["id"] = $dataUser["usr_id"];            
               $data["tipe"] = $dataUser["usr_tipe"];
               $data["rol"] = $dataUser["id_rol"];
               $data["usr_app_def"] = $dataUsrDef["usr_app_def"];        
               $data["id_member"] = $dataMember["member_id"];
               $data["member_tipe"] = $dataMember["member_tipe"];
                                     
               $this->globalData->SetEntry("Login",$data);
               $this->globalData->Save();
               
               $this->SetUserLog($dataMember["member_id"]);
               return $data;
          } else return 2;
     }

     function SetWarnetLogin($in_username,$in_password) {
          
          $sql = "select a.usr_id,a.usr_tipe from vglobal_auth_user a                      
                    where upper(a.usr_loginname) = ".QuoteValue(DPE_CHAR,strtoupper($in_username))."  
                    and a.usr_password = ".QuoteValue(DPE_CHAR,md5(strtoupper($in_password)))." 
                    and a.usr_tipe = 'E'";
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataUser = $this->_dataAccess->Fetch($rs);

          if (!$dataUser) {
          $dbTable = "global.global_auth_user";
          
          $dbField[0] = "usr_id";   // PK
          $dbField[1] = "usr_loginname";
          $dbField[2] = "usr_name";
          $dbField[3] = "id_rol";
          $dbField[4] = "usr_status";
          $dbField[5] = "usr_when_create";
          $dbField[6] = "usr_password";
          
          $usrId = $this->_dataAccess->GetNewID("global_auth_user","usr_id",DB_SCHEMA_GLOBAL);
          $dbValue[0] = QuoteValue(DPE_NUMERIC,$usrId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$in_username);
          $dbValue[2] = QuoteValue(DPE_CHAR,"GUEST");
          $dbValue[3] = QuoteValue(DPE_CHAR,ROLE_TIPE_MEMBER);
          $dbValue[4] = QuoteValue(DPE_CHAR,"y");
          $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[6] = QuoteValue(DPE_CHAR,md5(""));
     
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
          $dtmodel->Insert() or die("insert  error");	
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
     
     
          $dbTable = "mp_member";
          
          $dbField[0]  = "member_id";   // PK
          $dbField[1]  = "member_nama";  
          $dbField[2]  = "member_tipe";
          $dbField[3]  = "id_usr";
          $dbField[4]  = "member_aktif";
     
          $memberId = $this->_dataAccess->GetTransID();
     
          $dbValue[0] = QuoteValue(DPE_CHAR,$memberId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$in_username);
          $dbValue[2] = QuoteValue(DPE_CHAR,MEMBER_TIPE_GUEST);
          $dbValue[3] = QuoteValue(DPE_NUMERICKEY,$usrId);
          $dbValue[4] = QuoteValue(DPE_CHAR,"y");
     
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
          $dtmodel->Insert() or die("insert  error");	

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);


          $dbTable = "multiplayer.mp_member_trans";
          
          $dbField[0]  = "trans_id";   // PK
          $dbField[1]  = "id_member";  
          $dbField[2]  = "id_dep";
          $dbField[3]  = "trans_time_flag";
          $dbField[4]  = "trans_create";
          $dbField[5]  = "trans_time_start";
          $dbField[6]  = "trans_harga_satuan";
          $dbField[7]  = "trans_harga_total";
          $dbField[8]  = "trans_nama";
          $dbField[9]  = "trans_jenis";

          $transId = $this->_dataAccess->GetTransID();
          $skr = date("Y-m-d H:i:s");
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$memberId);
          $dbValue[2] = QuoteValue(DPE_CHAR,APP_OUTLET);
          $dbValue[3] = QuoteValue(DPE_CHAR,"y");
          $dbValue[4] = QuoteValue(DPE_DATE,$skr);
          $dbValue[5] = QuoteValue(DPE_DATE,$skr);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,0);
          $dbValue[7] = QuoteValue(DPE_NUMERIC,0);
          $dbValue[8] = QuoteValue(DPE_CHAR,$in_username);
          $dbValue[9] = QuoteValue(DPE_CHAR,"W");

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          }
          
          if ($dataUser) $memberId='1';
          $data["loginname"] =  $in_username;
          $data["name"] =  $in_username;
          if ($dataUser) $data["id"] = $dataUser["usr_id"]; else $data["id"] = $usrId;            
          if ($dataUser) $data["tipe"]='E'; else $data["tipe"] = USR_TIPE_MEMBER;
          $data["rol"] = ROLE_TIPE_MEMBER;
          $data["usr_app_def"] = $dataUsrDef["usr_app_def"];        
          $data["id_member"] = $memberId;
          $data["member_tipe"] = MEMBER_TIPE_GUEST;
                                
          $this->globalData->SetEntry("Login",$data);
          $this->globalData->Save();
          
          $this->SetUserLog($memberId);


     }

     function IsAllowed($in_modul=null,$in_akses=null)
     {
          if(!isset($this->_usrId)) return 1;
          else { 
               if($in_modul){
                    $sql = "select b.* 
                            from global_auth_user a 
                            join global_auth_role_priv b 
                            on a.id_rol = b.id_rol 
                            join global_auth_privilege c 
                            on b.id_priv = c.priv_id
                            where a.usr_id = ".$this->_usrId." 
                            and c.priv_code = ".QuoteValue(DPE_CHAR,$in_modul);
                    $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
                    $dataPriv = $this->_dataAccess->Fetch($rs);

                    if($dataPriv["rol_priv_access"]{$in_akses}=="1") return true;
                    else return false;
                }
                return true;
          }
     }
     
     function IsMenuAllowed($menu)
     {
          if(!isset($this->_usrId)) return 1;          
          for($i=0,$n=count($menu);$i<$n;$i++){
               for($j=0,$k=count($menu[$i]["sub"]);$j<$k;$j++){
                    $sql[] = "select c.priv_code 
                            from global_auth_user a 
                            join global_auth_role_priv b 
                            on a.id_rol = b.id_rol 
                            join global_auth_privilege c 
                            on b.id_priv = c.priv_id 
                            and c.priv_code = ".QuoteValue(DPE_CHAR,$menu[$i]["sub"][$j]["priv"])."   
                            and substring(rol_priv_access from 2 for 1) = '1' 
                            where a.usr_id = ".$this->_usrId;                    
               }
               
               if(count($menu[$i]["sub"])==0){
                    $sql[] = "select c.priv_code 
                            from global_auth_user a 
                            join global_auth_role_priv b 
                            on a.id_rol = b.id_rol 
                            join global_auth_privilege c 
                            on b.id_priv = c.priv_id 
                            and c.priv_code = ".QuoteValue(DPE_CHAR,$menu[$i]["priv"])."   
                            and substring(rol_priv_access from 2 for 1) = '1' 
                            where a.usr_id = ".$this->_usrId;
               }
          }
          
          $sql = implode(" union all ", $sql);
          
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          while($dataPriv = $this->_dataAccess->Fetch($rs)) {
               $status[$dataPriv["priv_code"]] = true;
          }
          
          return $status;
     }



     function Logout()
     {
          if($this->_usrData["id_member"]){
               $sql = "update multiplayer.mp_member set member_aktif = 'n' where member_id = ".QuoteValue(DPE_CHAR,$this->_usrData["id_member"]);
               $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          }
          
          $this->globalData->DelEntry("Login");
          $this->globalData->Free();
     }
     
     function LogoutWarnet()
     {
          $this->globalData->DelEntry("Login");
          $this->globalData->Free();
     }

     function CleanIdle()
     {
          $sql = "update global_user_log set usr_log_aktif = 'n' where id_usr in (select id_usr from vglobal_user_idle where online_status = ".QuoteValue(DPE_CHAR,USER_IDLE).") and usr_log_aktif = 'y'";
          $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          return;
     }


     function GetUserData()
     {
          return $this->_usrData;
     }
     
     
     function GetUserId()
     {
          return $this->_usrId;
     }
     
     function GetUserName()
     {
          return $this->_usrName;
     }
          
          
 
     function SetUserLog($in_id)
     {
          $sql = "update multiplayer.mp_member  set member_aktif = 'y' where member_id =  ".QuoteValue(DPE_CHAR,$in_id);
          $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
     }
}

?>
