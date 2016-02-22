<?php
require_once("root.inc.php");
require_once($ROOT."library/dataaccess.cls.php");

/*

*/
class Registry {
    var $_cache_stack;
    var $_sess_id;
    var $_session; 
     
    /**
    * @return put return description here..
    * @param param :  parameter passed to function
    * @desc encode :  put function description here ...
    */
    
     function Registry($in_sess_id = "BUGSBUNNY") {
          $this->_sess_id = $in_sess_id;
          $this->_cache_stack = array(array());
          $this->_session = new Session();
          session_set_save_handler(array(&$this->_session,"Open"),
                                   array(&$this->_session,"Close"),
                                   array(&$this->_session,"Read"),
                                   array(&$this->_session,"Write"),
                                   array(&$this->_session,"Destroy"),
                                   array(&$this->_session,"Gc"));

     }

     function SetEntry($key, &$item) {
          $this->_cache_stack[0][$key] = &$item;
     }
    
     //function DelEntry($key, &$item) {
     function DelEntry($key) {
          unset($this->_cache_stack[0][$key]);
     }
     
     function &GetEntry($key) {
          return $this->_cache_stack[0][$key];
     }
     
     function IsEntry($key) {
          return ($this->getEntry($key) !== null);
     }
     
     function &Instance($in_sess_id = "bugsbunny") {
          static $registry = false;
          if (!$registry) {
               $registry = new Registry($in_sess_id);
          }
          return $registry;
     }
     
     function Save($is_tofile=true) {
//          array_unshift($this->_cache_stack, array());
          if (!count($this->_cache_stack)) {
               trigger_error('Registry lost');
          }
          
          if ($is_tofile) {
               session_start();
               $_SESSION[$this->_sess_id] = serialize($this->_cache_stack);
          }
     }
     
     function Restore($is_tofile=true) {
          if ($is_tofile) {
               session_start();
               $tmp = unserialize($_SESSION[$this->_sess_id]);
               if ($tmp !== false) $this->_cache_stack = $tmp;
          }
//          array_shift($this->_cache_stack);
     }
     
     function Free($is_tofile=true) {
          if ($is_tofile) {
               session_start();
               unset($_SESSION[$this->_sess_id]);
          }
          unset($this->_cache_stack);
          $this->_cache_stack = array(array());
     }
     
     function IsAlive($sessID){
          return $this->_session->IsSessionAlive($sessID);
     }
}


class Session {
     var $_lifeTime;
     var $_dataAccess;
    
    
     function Open($savePath, $sessName)
     {
          $this->_lifeTime = get_cfg_var("session.gc_maxlifetime");
          $this->_dataAccess=new DataAccess();
          return true;
     }
    
     function Close() {
          $this->gc(ini_get('session.gc_max_lifeTime'));
          return @$this->_dataAccess->Close();
     }
    
     function Read($sessID) {
          // fetch session-data
          $sql = "SELECT sess_data FROM global.global_session WHERE
                    sess_id = ".QuoteValue(DPE_CHAR,$sessID)."
                    and sess_expires > ".time();
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataSession = $this->_dataAccess->Fetch($rs);

          // return data or an empty string at failure
          return $dataSession['sess_data'];
     }
    
     function Write($sessID,$sessData)
     {
          // new session-expire-time
          $newExp = time() + $this->_lifeTime;

          $sql = "select sess_id from global.global_session where sess_id = ".QuoteValue(DPE_CHAR,$sessID);
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $dataSession = $this->_dataAccess->Fetch($rs);
          
          $isSave = ($dataSession["sess_id"]) ? false : true;
          if($isSave){
                $sql = "insert into global.global_session(sess_id, sess_expires, sess_data) values (".
                        QuoteValue(DPE_CHAR,$sessID).",".
                        QuoteValue(DPE_NUMERIC,$newExp).",".
                        QuoteValue(DPE_CHAR,$sessData).")";
          } else {
                $sql = "update global.global_session set ".
                        " sess_expires = ".QuoteValue(DPE_NUMERIC,$newExp).",".
                        " sess_data = ".QuoteValue(DPE_CHAR,$sessData).
                        " where sess_id = ".QuoteValue(DPE_CHAR,$sessID);
          }
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          return ($rs) ? true:false;
     }
    
     function Destroy($sessID) {

          $sql = "delete from global.global_session where sess_id = ".QuoteValue(DPE_CHAR,$sessID);
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);

          return ($rs) ? true:false;
     }
     
     function Gc($sessMax_lifeTime) {
          $sql = "delete from global.global_session where sess_expires < ".time();
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);

          return ($rs) ? true:false;
     }
     
     function IsSessionAlive($sessID){
          $sql = "select sess_id from global.global_session where sess_id = ".QuoteValue(DPE_CHAR,$sessID);
          $rs = $this->_dataAccess->Execute($sql,DB_SCHEMA_GLOBAL);
          return ($this->_dataAccess->Count($rs)) ? true : false;
     }
} 


$globalData=new Registry(APP_ID);
$globalData->Restore();

?>
