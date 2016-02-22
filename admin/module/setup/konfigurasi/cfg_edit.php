<?php
    require_once("root.inc.php");
    require_once($APLICATION_ROOT."library/config/global.cfg.php");
    require_once($APLICATION_ROOT."library/view.cls.php");
    require_once($ROOT."library/bitFunc.lib.php");
    require_once($ROOT."library/auth.cls.php");
    require_once($ROOT."library/textEncrypt.cls.php");
    require_once($ROOT."library/datamodel.cls.php");
    
    $dtaccess = new DataAccess();
    $enc = new textEncrypt();
    $auth = new CAuth();
    $err_code = 0;    
    $userConfig = $auth->GetUserConfig();
      
    // --- buat privilege nya ---
    if(!$auth->IsAllowed('setup_akad_config',PRIV_CREATE)){    
        die("access_denied");
        exit(1);
    }
    
    $cfgId = "1";    
  
     if ($_POST["btnUpdate"]) {
     
          $err_code = 0;
          
          if ($err_code == 0) {
               $dbTable = "akad_config";
               
               $dbField[0] = "cfg_id";   // PK
               $dbField[1] = "cfg_tahun_angkatan";
               $dbField[2] = "cfg_semester";
               $dbField[3] = "cfg_max_dosen";
               $dbField[4] = "cfg_kurikulum";
               $dbField[5] = "cfg_mk";
               
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$cfgId);
               $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["tahun_ajaran"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["semester"]);
               $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["max_cuti_dosen"]);
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["kurikulum"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["matakuliah"]);
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_AKAD);
               
               $dtmodel->Update() or die("update  error");	
               unset($dbTable); unset($dtmodel); unset($dbField); unset($dbValue); unset($dbKey);
               
               $sql = "select a.akt_tahun_ajaran, b.sem_nama 
                         from univ_tahun_angkatan a  
                         left join univ_semester b on 1=1 
                         where a.akt_id = ".$_POST["tahun_ajaran"]." and b.sem_id = ".$_POST["semester"];
               $rs = $dtaccess->Execute($sql,DB_SCHEMA_UNIV);
               $infoAjaran = $dtaccess->Fetch($rs);
               
               $userConfig["akt_id"] = $_POST["tahun_ajaran"];
               $userConfig["akt_tahun"] = $infoAjaran["akt_tahun_ajaran"];
               $userConfig["sem_id"] = $_POST["semester"];
               $userConfig["sem_nama"] = $infoAjaran["sem_nama"];
               $userConfig["kuri_id"] = $_POST["kurikulum"];
               $userConfig["mk"] = $_POST["matakuliah"];
               
               $auth->SetConfig($userConfig);
               unset($auth);
               $auth = new CAuth();          
          }
     }
     
	 
	$sql = "select * from akad_config where cfg_id = ".$cfgId;
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_AKAD);
	$dataConfig = $dtaccess->Fetch($rs);
	
     $_POST["tahun_ajaran"] = $dataConfig["cfg_tahun_angkatan"];
	$_POST["semester"] = $dataConfig["cfg_semester"];
	$_POST["max_cuti_dosen"] = $dataConfig["cfg_max_dosen"];
	$_POST["kurikulum"] = $dataConfig["cfg_kurikulum"];
     $_POST["matakuliah"] = $dataConfig["cfg_mk"];

     // --- cari tahun angkatan ---
     $sql = "select * from univ_tahun_angkatan order by akt_tahun_ajaran";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_UNIV);
     $dataTahunAjaran = $dtaccess->FetchAll($rs);
     
     // --- cari semester ---
     $sql = "select * from univ_semester order by sem_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_UNIV);
     $dataSemester = $dtaccess->FetchAll($rs);
     
     // --- cari kurikulum ---
     $sql = "select * from akad_kurikulum order by kuri_tahun";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_AKAD);
     $dataKurikulum = $dtaccess->FetchAll($rs);	
	
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<TITLE>.:: <?php echo APP_TITLE;?> ::.</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>library/css/inosoft.css">
<script language="JavaScript" type="text/javascript" src="<?php echo $APLICATION_ROOT;?>library/script/elements.js"></script>
</head>

<body>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="50%" border="0" cellpadding="3" cellspacing="1" class="tblForm">
   <tr>
        <td align="left" colspan=2 class="tblHeader">&nbsp;Konfigurasi</td>
    </tr>
    <tr><td class="tblMainCol" width="50%" nowrap>&nbsp;Tahun Akademik</td>
        <td class="tblCol">
        <select name="tahun_ajaran" class="inputField" onKeyDown="return tabOnEnter_select(this, event);">
            <?php for($i=0,$n=count($dataTahunAjaran);$i<$n;$i++){ ?>
            <option class="inputField" value="<?php echo $dataTahunAjaran[$i]["akt_id"];?>" <?php if($dataTahunAjaran[$i]["akt_id"]==$_POST["tahun_ajaran"]) echo "selected"; ?>><?php echo $dataTahunAjaran[$i]["akt_tahun_ajaran"];?></option>
            <?php } ?>
        </select>
        </td>
    </tr>
    <tr><td class="tblMainCol" width="50%" nowrap>&nbsp;Semester</td>
        <td class="tblCol">
        <select name="semester" class="inputField" onKeyDown="return tabOnEnter_select(this, event);">
            <?php for($i=0,$n=count($dataSemester);$i<$n;$i++){ ?>
            <option class="inputField" value="<?php echo $dataSemester[$i]["sem_id"];?>" <?php if($dataSemester[$i]["sem_id"]==$_POST["semester"]) echo "selected"; ?>><?php echo $dataSemester[$i]["sem_nama"];?></option>
            <?php } ?>
        </select>
        </td>
    </tr>
    <tr><td class="tblMainCol" width="50%" nowrap>&nbsp;Kurikulum</td>
        <td class="tblCol">
        <select name="kurikulum" class="inputField" onKeyDown="return tabOnEnter_select(this, event);">
            <?php for($i=0,$n=count($dataKurikulum);$i<$n;$i++){ ?>
            <option class="inputField" value="<?php echo $dataKurikulum[$i]["kuri_id"];?>" <?php if($dataKurikulum[$i]["kuri_id"]==$_POST["kurikulum"]) echo "selected"; ?>><?php echo $dataKurikulum[$i]["kuri_tahun"];?></option>
            <?php } ?>
        </select>
        </td>
    </tr>
    <tr><td class="tblMainCol" width="50%" nowrap>&nbsp;Mata Kuliah</td>
        <td class="tblCol">
        <select name="matakuliah" class="inputField" onKeyDown="return tabOnEnter_select(this, event);">            
            <option value="<?php echo MK_EKUIVALENSI;?>" <?php if($_POST["matakuliah"]==MK_EKUIVALENSI) echo "selected"; ?>>Ekuivalensi</option>
            <option value="<?php echo MK_REFERENSI?>" <?php if($_POST["matakuliah"]==MK_REFERENSI) echo "selected"; ?>>Referensi</option>            
        </select>
        </td>
    </tr>    
    <tr><td class="tblMainCol" width="50%" nowrap>&nbsp;Maksimal Hari Ganti Dosen</td>
        <td class="tblCol">
			<input type="text" name="max_cuti_dosen" value="<?php echo $_POST["max_cuti_dosen"];?>" size="5" maxlength="3" class="inputField" onKeyDown="return tabOnEnter_select(this, event);">
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center" class="tblMainCol">
            <input type="submit" name="btnUpdate" value="Simpan" class="inputField">
        </td>
    </tr>
</table>

</form>
</body>
</html>
<?
    $dtaccess->Close();
?>
