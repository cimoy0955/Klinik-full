<?php
     require_once("root.inc.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
 
     $thisPage = "report_pasien.php";

     if(!$auth->IsAllowed("poli_anak",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("poli_anak",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y");
     $sql_where[] = "a.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]))." and a.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     $sql_where[] = "a.reg_tipe_umur='A'";
     if($_POST["cust_usr_jenis"]) $sql_where[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
     if($_POST["cust_usr_nama"]) $sql_where[] = "upper(b.cust_usr_nama) like '%".strtoupper($_POST["cust_usr_nama"])."%'";
     if($_POST["cust_usr_ortu"]) $sql_where[] = "upper(b.cust_usr_ortu) like '%".strtoupper($_POST["cust_usr_ortu"])."%'";
     if($_POST["cust_usr_propinsi"] && $_POST["cust_usr_propinsi"]!="-") $sql_where[] = "b.cust_usr_propinsi = ".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_propinsi"]);
     if($_POST["cust_usr_kota"] && $_POST["cust_usr_kota"]!="-") $sql_where[] = "b.cust_usr_kota = ".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_kota"]);
     if($_POST["cust_usr_jenis_kelamin"] && $_POST["cust_usr_jenis_kelamin"]!="-") $sql_where[] = "upper(b.cust_usr_jenis_kelamin) = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis_kelamin"]);
     if($_POST["rawat_icd_od_id"] <> "-") $sql_where[] = "g.id_icd=".QuoteValue(DPE_CHAR,$_POST["rawat_icd_od_id"])." and g.rawat_icd_odos=".QuoteValue(DPE_CHAR,"OD");
     if($_POST["rawat_icd_os_id"] <> "-") $sql_where[] = "h.id_icd=".QuoteValue(DPE_CHAR,$_POST["rawat_icd_os_id"])." and g.rawat_icd_odos=".QuoteValue(DPE_CHAR,"OS");
     
     $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin, a.reg_tipe_rawat,
               a.reg_jenis_pasien, a.reg_status_pasien, a.reg_keterangan, a.reg_waktu, c.prop_nama, d.kota_nama, e.*, f.*,
               i.icd_nomor as icd_nomor_od, j.icd_nomor as icd_nomor_os
               from klinik.klinik_registrasi a 
               join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               join global.global_propinsi c on b.cust_usr_propinsi = c.prop_id
               join global.global_kota d on b.cust_usr_kota = d.kota_id
               left join klinik.klinik_perawatan e on a.reg_id = e.id_reg
               left join klinik.klinik_refraksi f on a.reg_id = f.id_reg
               join klinik.klinik_perawatan_icd g on e.rawat_id = g.id_rawat
               join klinik.klinik_perawatan_icd h on e.rawat_id = h.id_rawat
               join klinik.klinik_icd i on g.id_icd = i.icd_nomor
               join klinik.klinik_icd j on h.id_icd = j.icd_nomor";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " order by a.reg_status_pasien, b.cust_usr_nama, a.reg_waktu";
     //echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs); 
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Harian";

     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Keterangan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Keluhan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Keadaan Umum";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Anel";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Schimer";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Gula Darah";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Darah Lengkap";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tensi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nadi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nafas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alergi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tonometri Scale OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tonometri Scale OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tonometri Weight OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tonometri Weight OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tonometri Pressure OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tonometri Pressure OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tonometri OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tonometri OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
    
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Anastesis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Obat Anastesis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dosis Anastesis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Anastesis Komp.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Anestesis Pre";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Operasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
    
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Paket Operasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Color Blindness";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Koreksi Spheris OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Koreksi Spheris OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Koreksi Cylinder OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Koreksi Cylinder OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
    
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Koreksi Sudut OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Koreksi Sudut OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "ICD OD";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "ICD OS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     /*
  
     */
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = HitungUmur($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $bayarPasien[$dataTable[$i]["reg_jenis_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["reg_keterangan"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_keluhan"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_keadaan_umum"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_anel"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_schimer"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_lab_gula_darah"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_lab_darah_lengkap"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_lab_tensi"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_lab_nadi"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_lab_nafas"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_lab_alergi"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_tonometri_scale_od"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_tonometri_scale_os"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_tonometri_weight_od"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_tonometri_weight_os"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_tonometri_pressure_od"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_tonometri_pressure_os"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_tonometri_od"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_tonometri_os"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
         
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_anestesis_jenis"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_anestesis_obat"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
     
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_anestesis_dosis"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_anestesis_komp"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_anestesis_pre"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_operasi_jenis"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_operasi_paket"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_color_blindness"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_mata_od_koreksi_spheris"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_mata_os_koreksi_spheris"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
         
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_mata_od_koreksi_cylinder"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_mata_os_koreksi_cylinder"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_mata_od_koreksi_sudut"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_mata_os_koreksi_sudut"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
     
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nomor_od"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nomor_os"];
          $tbContent[$i][$counter][TABLE_WIDTH] = "10%";     
          $counter++;
     }
     
     $colspan = count($tbHeader[0]);
     
     if(!$_POST["btnExcel"]){
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
          $tbBottom[0][0][TABLE_ALIGN] = "center";
     }
     
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pasien_'.$_POST["tgl_awal"].'_'.$_POST["tgl_akhir"].'.xls');
     }
     
     
     $sql_prop = "SELECT * FROM global.global_propinsi ORDER BY prop_id";
     $rs_prop = $dtaccess->Execute($sql_prop);
     $optProp[] = $view->RenderOption("-","--Pilih Propinsi--",null,null);
     while($data_prop = $dtaccess->Fetch($rs_prop)){
        $optProp[] = $view->RenderOption($data_prop["prop_id"],$data_prop["prop_nama"],($data_prop["prop_id"]==$_POST["cust_usr_propinsi"])?"selected":"",null);
     }
     
     if($_POST["cust_usr_propinsi"] && $_POST["cust_usr_propinsi"]!="-") {
        $sql_kota_user = "SELECT * FROM global.global_kota WHERE id_prop = ".QuoteValue(DPE_NUMERIC,$_POST["cust_usr_propinsi"])." ORDER BY kota_id";
        $rs_kota_user = $dtaccess->Execute($sql_kota_user);
        $optKotaUser[] = $view->RenderOption("-","--Pilih Kota--",null,null);
        while($data_kota_user = $dtaccess->Fetch($rs_kota_user)){
          $optKotaUser[] = $view->RenderOption($data_kota_user["kota_id"],$data_kota_user["kota_nama"],($data_kota_user["kota_id"]==$_POST["cust_usr_kota"])?"selected":"",null);
        }   
     }else{
       $optKotaUser[] = $view->RenderOption("-","--Pilih Propinsi Terlebih Dulu--",null,null);
     }
     
     $sql_icd = "select icd_id, icd_nama, icd_nomor from klinik.klinik_icd order by icd_nomor";
     $rs_icd = $dtaccess->Execute($sql_icd);
     $optICD_od[] = $view->RenderOption("-","--Pilih ICD OD--",null,null);
     $optICD_os[] = $view->RenderOption("-","--Pilih ICD OS--",null,null);
     while($dataICD = $dtaccess->Fetch($rs_icd)){
          $optICD_od[] = $view->RenderOption($dataICD["icd_id"],$dataICD["icd_nomor"],($dataICD["icd_id"]==$_POST["rawat_icd_od_id"])?"selected":"",null,null);
          $optICD_os[] = $view->RenderOption($dataICD["icd_id"],$dataICD["icd_nomor"],($dataICD["icd_id"]==$_POST["rawat_icd_os_id"])?"selected":"",null,null);
     }

?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("inosoft.css",true); ?>
<?php } ?>
<script language="JavaScript">
function CheckSimpan(frm) {
     
     if(!frm.tgl_awal.value) {
          alert("Tanggal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }
}

</script>

<table width="100%" border="1" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
</table>

<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
<?php if(!$_POST["btnExcel"]) { ?>
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
     <tr>
          <td width="15%" class="tablecontent">&nbsp;Tanggal Masuk Mulai</td>
          <td width="20%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td>
          <td width="10%" class="tablecontent-odd">&nbsp;Sampai</td>
          <td width="20%" class="tablecontent-odd">
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td>
     </tr>
     <tr>
          <td width="15%" class="tablecontent">&nbsp;Tanggal Lahir Mulai</td>
          <td width="20%" class="tablecontent-odd">
               <input type="text"  id="tgl_lahir_awal" name="tgl_lahir_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_lahir_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lahir_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td>
          <td width="10%" class="tablecontent-odd">&nbsp;Sampai</td>
          <td width="20%" class="tablecontent-odd">
               <input type="text"  id="tgl_lahir_akhir" name="tgl_lahir_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_lahir_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lahir_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td>
     </tr>
     <tr> 
          <td width="15%" class="tablecontent" align="left">&nbsp;Nama Pasien</td>
          <td width="20%" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama","40","255",$_POST["cust_usr_nama"],"inputField",null,false);?></td>
     </tr>
     <tr> 
          <td width="15%" class="tablecontent" align="left">&nbsp;Nama Orang Tua</td>
          <td width="20%" class="tablecontent-odd" colspan="3"><?php echo $view->RenderTextBox("cust_usr_ortu","cust_usr_ortu","40","255",$_POST["cust_usr_ortu"],"inputField",null,false);?></td>
     </tr>
     <tr>
		<td width= "20%" align="left" class="tablecontent">&nbsp;Propinsi</td>
		<td width= "50%" align="left" class="tablecontent-odd" colspan=3>
      <?php echo $view->RenderComboBox("cust_usr_propinsi","cust_usr_propinsi",$optProp,"inputfield",null,"onchange=submit();");?>
		</td>
	</tr>
	<tr>
		<td width= "20%" align="left" class="tablecontent">&nbsp;Kota Asal</td>
		<td width= "50%" align="left" class="tablecontent-odd" colspan=3>
      <?php echo $view->RenderComboBox("cust_usr_kota","cust_usr_kota",$optKotaUser,"inputfield");?>
		</td>
	</tr>
        <tr>
		<td class="tablecontent">&nbsp;Jenis Kelamin</td>
		<td colspan="3" class="tablecontent-odd">
			<select name="cust_usr_jenis_kelamin" onKeyDown="return tabOnEnter(this, event);">
                              <option value="-" <?php if(!$_POST["cust_usr_jenis_kelamin"] || $_POST["cust_usr_jenis_kelamin"]=="-") echo "selected";?>>--Pilih Jenis Kelamin--</option>
                              <option value="L" <?php if($_POST["cust_usr_jenis_kelamin"]=="L") echo "selected";?>>Laki-laki</option>
                              <option value="P" <?php if($_POST["cust_usr_jenis_kelamin"]=="P") echo "selected";?>>Perempuan</option>
			</select>
          </td>
	</tr>
	<tr>
          <td align="left" class="tablecontent">&nbsp;ICD OD</td>
          <td align="left" class="tablecontent-odd">
             <?php echo $view->RenderComboBox("rawat_icd_od_id","rawat_icd_od_id",$optICD_od,"inputfield");?>
          </td>
          <td align="left" class="tablecontent">&nbsp;ICD OS</td>
          <td align="left" class="tablecontent-odd">
             <?php echo $view->RenderComboBox("rawat_icd_os_id","rawat_icd_os_id",$optICD_os,"inputfield");?>
          </td>
	</tr>
     <tr>
     <td class="tablecontent-odd" colspan="4" align="center">
      <input type="submit" name="btnLanjut" value="Lanjut" class="button">
     </td>
     </tr>
</table>
<?php } ?>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    Calendar.setup({
        inputField     :    "tgl_lahir_awal",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_lahir_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    Calendar.setup({
        inputField     :    "tgl_lahir_akhir",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_lahir_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>

<?php echo $view->RenderBodyEnd(); ?>
