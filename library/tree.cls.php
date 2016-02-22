<?php
require_once("root.inc.php");
require_once($ROOT."library/crack.cls.php");
require_once("dataaccess.cls.php");


class CTree
{
    var $_dataAccess;
    var $_treeIdLen;
    var $_treeTable;
    var $_treeFieldId;


    function CTree($ref_table,$ref_fieldId, $in_treeIdLen=2)
    {
        $this->_treeTable = & $ref_table;
        $this->_treeFieldId = & $ref_fieldId;
        $this->_treeIdLen= $in_treeIdLen;
        $this->_dataAccess=new DataAccess();
    }

    function GetNodeList($parent_id=null)
    {
        $sql = "select * from ".$this->_treeTable." where ".$this->_treeFieldId." like '".$parent_id."%'";
        $rs = $this->_dataAccess->Execute($sql);
        return $this->_dataAccess->FetchAll($rs);
    }

    function GetChildList($parent_id=null)
    {
        $sql = "select * from ".$this->_treeTable." where ".$this->_treeFieldId." like '".$parent_id."%' and length(".$this->_treeFieldId.")=".(strlen($parent_id)+$this->_treeIdLen);
        $rs = $this->_dataAccess->Execute($sql);
        return $this->_dataAccess->FetchAll($rs);
    }

    function GetParentId($node_id)
    {
        return substr($node_id,0,($this->_treeIdLen*-1));
    }

    function AddSibling($node_id)
    {
        $parentId=substr($node_id,0,strlen($node_id)-$this->_treeIdLen);
        $this->AddChild($parentId);
    }

    function AddChild($parent_id=null)
    {
        $sql = "select max(".$this->_treeFieldId.") as besar from ".$this->_treeTable." where length(".$this->_treeFieldId.") = ".(strlen($parent_id) + $this->_treeIdLen)." and ".$this->_treeFieldId." like '".$parent_id."%'";
        $rs = $this->_dataAccess->Execute($sql);
        $max = $this->_dataAccess->Fetch($rs);
        $newId = substr($max["besar"],($this->_treeIdLen*-1)) + 1;
        $newId = str_pad($newId,$this->_treeIdLen,"0",STR_PAD_LEFT);

        return $parent_id.$newId;
    }

    function DelNode($node_id)
    {
        $sql = "delete from ".$this->_treeTable." where ".$this->_treeFieldId." like '".$node_id."%'";
        $this->_dataAccess->Execute($sql);
    }
}

?>
