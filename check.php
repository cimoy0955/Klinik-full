<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");

	$enc = new TextEncrypt();
	$auth = new CAuth();
  $dtaccess = new DataAccess();     
    
     $login = $auth->IsLoginOk($_POST["user"],$_POST["passwd"]);
     
      $sql = "select b.id_app from global.global_auth_user_app b
             where id_usr=".QuoteValue(DPE_NUMERIC,$login["id"])."
             and id_app=".QuoteValue(DPE_NUMERIC,$_POST["cmbSystem"]);
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
      if($login && $dataTable){
       if($_POST["cmbSystem"]=="10"){
          header("location:./klinik/index.php");
       }elseif($_POST["cmbSystem"]=="11"){
          header("location:./optik/index.php");
       }elseif($_POST["cmbSystem"]=="12"){
          header("location:./logistik/index.php");
       }elseif($_POST["cmbSystem"]=="13"){
          header("location:./admin/index.php");
       }elseif($_POST["cmbSystem"]=="14"){
          header("location:./management/index.php");
       }elseif($_POST["cmbSystem"]=="15"){
          header("location:./accounting/index.php");
       }elseif($_POST["cmbSystem"]=="16"){
          header("location:./rawat_inap/index.php");
       }elseif($_POST["cmbSystem"]=="17"){
          header("location:./laboratorium/index.php");
       }elseif($_POST["cmbSystem"]=="18"){
          header("location:./apotek/u0");
       }elseif($_POST["cmbSystem"]=="19"){
          header("location:./ugd/index.php");
       }elseif($_POST["cmbSystem"]=="20"){
          header("location:./apotik_swadaya/index.php");
       }elseif($_POST["cmbSystem"]=="21"){
          header("location:./refraksi/index.php");   
       }elseif($_POST["cmbSystem"]=="22"){
          header("location:./diagnostik/index.php"); 
       }elseif($_POST["cmbSystem"]=="23"){
          header("location:./front_office/index.php");
       }elseif($_POST["cmbSystem"]=="24"){
          header("location:./kasir/index.php");  
       }elseif($_POST["cmbSystem"]=="25"){
          header("location:./dinas_luar/index.php");  
       }elseif($_POST["cmbSystem"]=="27"){
	        header("location:./poli_anak/index.php");
       }
       
     }  
     elseif(!$login)
     {
       header("Location:login.php?msg=kode_eror01&user=".$_POST["user"]);
     } 
     elseif(!$dataTable)
     {
       header("Location:login.php?msg=kode_eror02&user=".$_POST["user"]);
     }
     
     /*if($login==1){
         header("Location:login.php?msg=User Online");         
     } elseif($login==2) {
         header("Location:login.php?msg=Login Failed");
     } else {
          header("location:./klinik/index.php");
     }*/
?>
