<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
     require_once($ROOT."library/dateFunc.lib.php");
     require_once($ROOT."library/tree.cls.php");
     require_once($ROOT."library/inoLiveX.php");
     require_once($APLICATION_ROOT."library/view.cls.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     

 	if(!$auth->IsAllowed("report_operasi",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("report_operasi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

	if($_GET["id_cust_usr"]) $_POST["id_cust_usr"] = $_GET["id_cust_usr"];
	if($_GET["id_reg"]) $_POST["id_reg"] = $_GET["id_reg"];
	
	
	$conj[1] = "Fornix Base"; 
	$conj[2] = "Limbal Base"; 
     
     $cauter[1] = "Minimal"; 
	$cauter[2] = "Moderate"; 
	$cauter[3] = "Severe"; 

     $indirectomy[1] = "No"; 
	$indirectomy[1] = "Yes"; 

     $nucleus[1] = "Irigasi"; 
	$nucleus[2] = "Expresi"; 
	$nucleus[3] = "Lain-lain"; 

     $cortex[1] = "manual I/A"; 
	$cortex[2] = "Vitreus"; 
	$cortex[3] = "lain-lain"; 

     $corneal[1] = "Vicryl"; 
	$corneal[2] = "Zeide"; 
	$corneal[3] = "Dexon"; 
	$corneal[4] = "Lain-lain"; 

     $typeSuture[1] = "Interupt"; 
	$typeSuture[2] = "Continous Type"; 

     $COA[1] = "NSS"; 
	$COA[2] = "AIR"; 
	$COA[3] = "Lain-lain"; 

     $obat[1] = "Healon"; 
	$obat[2] = "Myostat"; 
	$obat[3] = "Atropin"; 
	$obat[4] = "Pantocain"; 
	$obat[5] = "Efrisel"; 
	$obat[6] = "Genta Inj"; 
	$obat[7] = "Cortison Inj"; 
	$obat[8] = "Metilen Blue"; 
	$obat[9] = "Lain-lain";


	if($_POST["id_cust_usr"]) {
		
          $sql = "select cust_usr_nama,cust_usr_kode, b.cust_usr_jenis_kelamin,  
                    ((current_date - cust_usr_tanggal_lahir)/365) as umur 
                    from global.global_customer_user b 
                    where b.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
          $dataPasien = $dtaccess->Fetch($sql);
          
          
          // --- data refraksi ---
               $sql = "select a.*, b.visus_nama as visus_nonkoreksi_od, c.visus_nama as visus_koreksi_od,
                         d.visus_nama as visus_nonkoreksi_os, e.visus_nama as visus_koreksi_os  
                         from klinik.klinik_refraksi a
                         join klinik.klinik_visus b on a.id_visus_nonkoreksi_od = b.visus_id  
                         join klinik.klinik_visus c on a.id_visus_koreksi_od = c.visus_id
                         join klinik.klinik_visus d on a.id_visus_nonkoreksi_os = d.visus_id
                         join klinik.klinik_visus e on a.id_visus_koreksi_os = e.visus_id
                         where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataRefraksi = $dtaccess->Fetch($sql);
               
               
               $sql = "select b.pgw_nama 
                         from klinik.klinik_refraksi_suster a
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                         where a.id_ref = ".QuoteValue(DPE_CHAR,$dataRefraksi["ref_id"]);
               $dataRefraksionist = $dtaccess->FetchAll($sql);
                    
          
          // --- data pemeriksaan ---
               $sql = "select a.*, c.pgw_nama as dokter_nama, d.anes_jenis_nama, e.op_jenis_nama, f.op_paket_nama   
                         from klinik.klinik_perawatan a
                         left join klinik.klinik_perawatan_dokter b on a.rawat_id = b.id_rawat 
                         left join hris.hris_pegawai c on c.pgw_id = b.id_pgw 
                         left join klinik.klinik_anestesis_jenis d on a.rawat_anestesis_jenis = d.anes_jenis_id
                         left join klinik.klinik_operasi_jenis e on e.op_jenis_id = a.rawat_operasi_jenis
                         left join klinik.klinik_operasi_paket f on f.op_paket_id = a.rawat_operasi_paket 
                         where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataPemeriksaan = $dtaccess->Fetch($sql);
               
               $sql = "select b.pgw_nama 
                         from klinik.klinik_perawatan_suster a
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id
                         where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"]);
               $dataPemeriksaanSuster = $dtaccess->FetchAll($sql);
               
               $sql = "select item_nama, item_fisik, dosis_nama  
                         from klinik.klinik_perawatan_terapi a 
                         left join inventori.inv_item b on a.id_item = b.item_id 
                         join inventori.inv_dosis c on c.dosis_id = a.id_dosis 
                         where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])." 
                         order by rawat_item_urut";
               $dataPemeriksaanTerapi = $dtaccess->FetchAll($sql);
               
               $sql = "select icd_nama,icd_nomor 
                         from klinik.klinik_perawatan_icd a
                         join klinik.klinik_icd b on a.id_icd = b.icd_id 
                         where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])." 
                         and rawat_icd_odos = 'OD'
                         order by rawat_icd_urut";
               $dataPemeriksaanIcdOd = $dtaccess->FetchAll($sql); 
               
               $sql = "select icd_nama, icd_nomor 
                         from klinik.klinik_perawatan_icd a
                         join klinik.klinik_icd b on a.id_icd = b.icd_id 
                         where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])." 
                         and rawat_icd_odos = 'OS'
                         order by rawat_icd_urut";
               $dataPemeriksaanIcdOs = $dtaccess->FetchAll($sql);
               
               $sql = "select ina_nama, ina_kode  
                         from klinik.klinik_perawatan_ina a
                         join klinik.klinik_ina b on a.id_ina = b.ina_id 
                         where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])." 
                         and rawat_ina_odos = 'OD'
                         order by rawat_ina_urut";
               $dataPemeriksaanInaOd = $dtaccess->FetchAll($sql);
                    
               $sql = "select ina_nama, ina_kode 
                         from klinik.klinik_perawatan_ina a
                         join klinik.klinik_ina b on a.id_ina = b.ina_id 
                         where id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])." 
                         and rawat_ina_odos = 'OS'
                         order by rawat_ina_urut";
               $dataPemeriksaanInaOs = $dtaccess->FetchAll($sql);
               
          
          // --- data diagnostik ---
               $sql = "select a.*, b.bio_rumus_nama, c.bio_av_nama 
                         from klinik.klinik_diagnostik a
                         left join klinik.klinik_biometri_rumus b on a.diag_rumus = b.bio_rumus_id 
                         left join klinik.klinik_biometri_av c on a.diag_av_constant = c.bio_av_id  
                         where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataDiagnostik = $dtaccess->Fetch($sql);
               
               $sql = "select pgw_nama from klinik.klinik_diagnostik_suster a
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id  
                         where id_diag = ".QuoteValue(DPE_CHAR,$dataDiagnostik["diag_id"]);
               $dataDiagnostikSuster = $dtaccess->FetchAll($sql);

               $sql = "select pgw_nama from klinik.klinik_diagnostik_dokter a
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                         where id_diag = ".QuoteValue(DPE_CHAR,$dataDiagnostik["diag_id"]);
               $dataDiagnostikDokter = $dtaccess->Fetch($sql);
               
               
          // --- data Penjadwalan & Preoperasi ---
               $sql = "select a.*, b.bio_av_nama, c.iol_merk_nama, d.iol_jenis_nama, e.anes_jenis_nama, 
                         f.anes_komp_nama, g.anes_pre_nama, h.item_nama, i.dosis_nama      
                         from klinik.klinik_preop a
                         left join klinik.klinik_biometri_av b on a.preop_av_constant = b.bio_av_id 
                         left join klinik.klinik_iol_merk c on c.iol_merk_id = a.preop_iol_merk 
                         left join klinik.klinik_iol_jenis d on d.iol_jenis_id = a.preop_iol_jenis
                         left join klinik.klinik_anestesis_jenis e on e.anes_jenis_id = a.preop_anestesis_jenis 
                         left join klinik.klinik_anestesis_komplikasi f on f.anes_komp_id = a.preop_anestesis_komp
                         left join klinik.klinik_anestesis_premedikasi g on g.anes_pre_id = a.preop_anestesis_pre
                         left join inventori.inv_item h on h.item_id = a.preop_anestesis_obat
                         left join inventori.inv_dosis i on i.dosis_id = a.preop_anestesis_dosis 
                         where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataPenjadwalan = $dtaccess->Fetch($sql);
               
               
          // --- data Preoperasi ---
               $sql = "select b.pgw_nama 
                         from klinik.klinik_preop_dokter a
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                         where a.id_preop = ".QuoteValue(DPE_CHAR,$dataPenjadwalan["preop_id"]);
               $dataPreopDokter = $dtaccess->Fetch($sql);
               
               $sql = "select b.pgw_nama 
                         from klinik.klinik_preop_suster a 
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                         where a.id_preop = ".QuoteValue(DPE_CHAR,$dataPenjadwalan["preop_id"]);
               $dataPreopSuster = $dtaccess->FetchAll($sql);
               
               
          // --- data Bedah Minor ---
               $sql = "select a.*, b.pgw_nama as op_dokter_nama, c.pgw_nama as op_suster_nama, 
                         d.op_jenis_nama, e.icd_nama, e.icd_nomor, f.ina_kode, f.ina_nama  
                         from klinik.klinik_perawatan_operasi a
                         left join hris.hris_pegawai b on a.id_dokter = b.pgw_id
                         left join hris.hris_pegawai c on c.pgw_id = a.id_suster 
                         left join klinik.klinik_operasi_jenis d on d.op_jenis_id = a.id_op_jenis 
                         left join klinik.klinik_icd e on e.icd_id = a.id_icd
                         left join klinik.klinik_ina f on f.ina_id = a.id_ina 
                         where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataBedahMinor = $dtaccess->Fetch($sql);
               
               $sql = "select b.injeksi_nama, c.item_nama, d.dosis_nama 
                         from klinik.klinik_perawatan_injeksi a
                         left join klinik.klinik_injeksi b on a.id_injeksi = b.injeksi_id
                         left join inventori.inv_item c on c.item_id = a.id_item
                         left join inventori.inv_dosis d on d.dosis_id = a.id_dosis
                         where a.id_op = ".QuoteValue(DPE_CHAR,$dataBedahMinor["op_id"]);
               $dataInjeksi = $dtaccess->FetchAll($sql);
               
               $sql = "select b.durop_komp_nama
                         from klinik.klinik_perawatan_duranteop a
                         join klinik.klinik_duranteop_komplikasi b on a.id_durop_komp = b.durop_komp_id
                         where a.id_op = ".QuoteValue(DPE_CHAR,$dataOperasi["op_id"]);
               $dataBedahMinorDuranteop = $dtaccess->FetchAll($sql);
               
               
          // --- data Premedikasi ---
               $sql = "select a.*, b.iol_merk_nama, c.iol_jenis_nama, d.anes_jenis_nama, e.anes_komp_nama, 
                         f.anes_pre_nama, g.item_nama, h.dosis_nama   
                         from klinik.klinik_premedikasi a 
                         left join klinik.klinik_iol_merk b on a.preme_iol_merk = b.iol_merk_id
                         left join klinik.klinik_iol_jenis c on a.preme_iol_jenis = c.iol_jenis_id
                         left join klinik.klinik_anestesis_jenis d on d.anes_jenis_id = a.preme_anestesis_jenis
                         left join klinik.klinik_anestesis_komplikasi e on e.anes_komp_id = a.preme_anestesis_komp
                         left join klinik.klinik_anestesis_premedikasi f on f.anes_pre_id = a.preme_anestesis_pre
                         left join inventori.inv_item g on g.item_id = a.preme_anestesis_obat
                         left join inventori.inv_dosis h on h.dosis_id = a.preme_anestesis_dosis  
                         where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataPremedikasi = $dtaccess->Fetch($sql);
               
               $sql = "select b.pgw_nama 
                         from klinik.klinik_premedikasi_dokter a
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id
                         where a.id_preme = ".QuoteValue(DPE_CHAR,$dataPremedikasi["preme_id"]);
               $dataPremedikasiDokter = $dtaccess->Fetch($sql);
               
               $sql = "select b.pgw_nama 
                         from klinik.klinik_premedikasi_suster a
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id
                         where a.id_preme = ".QuoteValue(DPE_CHAR,$dataPremedikasi["preme_id"]);
               $dataPremedikasiSuster = $dtaccess->FetchAll($sql);
               
               
          // --- data Laporan Operasi ---
               $sql = "select a.*, b.op_jenis_nama, c.icd_nama, c.icd_nomor, d.ina_nama, d.ina_kode,
                         f.iol_jenis_nama, g.iol_merk_nama, h.iol_pos_nama       
                         from klinik.klinik_operasi a 
                         left join klinik.klinik_operasi_jenis b on a.op_jenis = op_jenis_id 
                         left join klinik.klinik_icd c on c.icd_id = a.id_icd
                         left join klinik.klinik_ina d on d.ina_id = a.id_ina 
                         left join klinik.klinik_iol_jenis f on f.iol_jenis_id = a.op_iol_jenis
                         left join klinik.klinik_iol_merk g on g.iol_merk_id = a.op_iol_merk
                         left join klinik.klinik_iol_posisi h on h.iol_pos_id = a.op_iol_posisi 
                         where a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dataOperasi = $dtaccess->Fetch($sql);
               
               $sql = "select b.pgw_nama 
                         from klinik.klinik_operasi_dokter a 
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id
                         where a.id_op = ".QuoteValue(DPE_CHAR,$dataOperasi["op_id"]);
               $dataOperasiDokter = $dtaccess->Fetch($sql);
               
               $sql = "select b.pgw_nama
                         from klinik.klinik_operasi_suster a
                         join hris.hris_pegawai b on a.id_pgw = b.pgw_id 
                         where a.id_op = ".QuoteValue(DPE_CHAR,$dataOperasi["op_id"]);
               $dataOperasiSuster = $dtaccess->FetchAll($sql);
               
               $sql = "select b.durop_komp_nama
                         from klinik.klinik_operasi_duranteop a
                         join klinik.klinik_duranteop_komplikasi b on a.id_durop_komp = b.durop_komp_id
                         where a.id_op = ".QuoteValue(DPE_CHAR,$dataOperasi["op_id"]);
               $dataOperasiDuranteop = $dtaccess->FetchAll($sql);
          
	}

?>

<?php echo $view->RenderBody("inosoft.css",true); ?>



<table width="100%" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<td align="left" colspan=2 class="tableheader">Report Operasi Pasien</td>
	</tr>
</table> 


<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data" onSubmit="return CheckData(this)">
<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="60%">

     <legend><strong>Data Pasien</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td width= "30%" align="left" class="tablecontent">Kode Pasien<?php if(readbit($err_code,11)||readbit($err_code,12)) {?>&nbsp;<font color="red">(*)</font><?}?></td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_kode"]; ?></label></td>
          </tr>	
          <tr>
               <td width= "30%" align="left" class="tablecontent">Nama Lengkap</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_nama"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Umur</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["umur"]; ?></label></td>
          </tr>
          <tr>
               <td width= "30%" align="left" class="tablecontent">Jenis Kelamin</td>
               <td width= "70%" align="left" class="tablecontent-odd"><label><?php echo $dataPasien["cust_usr_jenis_kelamin"]; ?></label></td>
          </tr>
	</table>
	
	
	<?php if($dataRefraksi) {?>
     	<fieldset style="border-color:red">
          <legend><strong>Refraksi</strong></legend>
     
               <legend><strong>Petugas</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>                    
                         <td width="20%" class="tablecontent" align="left" rowspan="<?php echo count($dataRefraksionist)?>">Refraksionist</td>                                         
                         <td align="left" class="tablecontent-odd" width="80%"> <?php echo $dataRefraksionist[0]["pgw_nama"];?></td>
                    </tr>
                    
                    <?php for($i=1,$n=count($dataRefraksionist);$i<$n;$i++) {?>
                         <tr>               
                              <td align="left" class="tablecontent-odd" width="80%"> <?php echo $dataRefraksionist[$i]["pgw_nama"];?></td>
                         </tr>
                    <?php }?>
          	</table>
          	
          	<legend><strong>Pemeriksaan Refraksi</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td width="5%" rowspan=2 align="center">Mata</td>
                         <td width="30%" rowspan=2 align="center">Visus Tanpa Koreksi</td>
                         <td width="35%" colspan=3 align="center">Koreksi</td>
                         <td width="30%" rowspan=2 align="center">Visus Dengan Koreksi</td>
                    </tr>	
                    <tr class="subheader">
                         <td width="15%" align="center">Spheris</td>
                         <td width="15%" align="center">Cylinder</td>
                         <td width="15%" align="center">Sudut</td>
                    </tr>	
                    <tr>
                         <td align="center" class="tablecontent">OD</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["visus_nonkoreksi_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_spheris"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_cylinder"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_sudut"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["visus_koreksi_od"];?></td>
                    </tr>
                    <tr>
                         <td align="center" class="tablecontent">OS</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["visus_nonkoreksi_os"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_spheris"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_cylinder"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_sudut"];?></td>
                         <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["visus_koreksi_os"];?></td>
                    </tr>
               </table>
               
               <legend><strong>Pinhole</strong></legend>
               <table width="50%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" width="20%" class="tablecontent">OD</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataRefraksi["ref_pinhole_od"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">OS</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataRefraksi["ref_pinhole_os"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Streak Retinoscopy</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td width="100%" colspan=4 align="center">Koreksi</td>
                    </tr>	
                    <tr class="subheader">
                         <td width="10%" align="center">&nbsp;</td>
                         <td width="30%" align="center">Spheris</td>
                         <td width="30%" align="center">Cylinder</td>
                         <td width="30%" align="center">Sudut</td>
                    </tr>	
                    <tr>
                         <td align="center" class="tablecontent">OD</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_streak_koreksi_spheris_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_streak_koreksi_cylinder_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_streak_koreksi_sudut_od"];?></td>
                    </tr>
                    <tr>
                         <td align="center" class="tablecontent">OS</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_streak_koreksi_spheris_os"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_streak_koreksi_cylinder_os"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_streak_koreksi_sudut_os"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Lensometri</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td width="100%" colspan=4 align="center">Koreksi</td>
                    </tr>	
                    <tr class="subheader">
                         <td width="10%" align="center">&nbsp;</td>
                         <td width="30%" align="center">Spheris</td>
                         <td width="30%" align="center">Cylinder</td>
                         <td width="30%" align="center">Sudut</td>
                    </tr>	
                    <tr>
                         <td align="center" class="tablecontent">OD</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_lenso_koreksi_spheris_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_lenso_koreksi_cylinder_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_lenso_koreksi_sudut_od"];?></td>
                    </tr>
                    <tr>
                         <td align="center" class="tablecontent">OS</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_lenso_koreksi_spheris_os"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_lenso_koreksi_cylinder_os"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_lenso_koreksi_sudut_os"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>ARK</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td width="100%" colspan=4 align="center">Koreksi</td>
                    </tr>	
                    <tr class="subheader">
                         <td width="10%" align="center">&nbsp;</td>
                         <td width="30%" align="center">Spheris</td>
                         <td width="30%" align="center">Cylinder</td>
                         <td width="30%" align="center">Sudut</td>
                    </tr>	
                    <tr>
                         <td align="center" class="tablecontent">OD</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_ark_koreksi_spheris_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_ark_koreksi_cylinder_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_ark_koreksi_sudut_od"];?></td>
                    </tr>
                    <tr>
                         <td align="center" class="tablecontent">OS</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_ark_koreksi_spheris_os"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_ark_koreksi_cylinder_os"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_ark_koreksi_sudut_os"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Koreksi Prisma</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td width="30%" align="center">Dioptri</td>
                         <td width="30%" align="center">Base Up/Down</td>
                         <td width="30%" align="center">Base Up/Down</td>
                    </tr>	
                    <tr>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_prisma_koreksi_dioptri"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_prisma_koreksi_base1"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_prisma_koreksi_base2"];?></td>
                    </tr>
          	</table>
          </fieldset>
     <?php }?>
     	
     
     <?php if($dataPemeriksaan) {?>     	
          <fieldset style="border-color:red">
          <legend><strong>Pemeriksaan</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td width= "30%" align="left" class="tablecontent">Keluhan Pasien</td>
                         <td width= "40%" align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_keluhan"];?></td>
                    </tr>
                    <tr>
                         <td width= "30%" align="left" class="tablecontent">Keadaan Umum</td>
                         <td width= "40%" align="left" class="tablecontent-odd"><?php echo $rawatKeadaan[$dataPemeriksaan["rawat_keadaan_umum"]];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Tensi</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_lab_tensi"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Nadi</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_lab_nadi"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Pernafasan</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_lab_nafas"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Alergi</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_lab_alergi"];?></td>
                    </tr>
               </table>
               
               <legend><strong>Petugas</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td width="20%" class="tablecontent" align="left">Dokter</td>
                         <td align="left" class="tablecontent-odd" width="80%"><?php echo $dataPemeriksaan["dokter_nama"];?></td>
                    </tr>
                    <tr>
                         <td width="20%" class="tablecontent" align="left" rowspan="<?php echo count($dataPemeriksaanSuster);?>">Perawat</td>
                         <td align="left" class="tablecontent-odd" width="80%"><?php echo $dataPemeriksaanSuster[0]["pgw_nama"];?></td>
                    </tr>
                    <?php for($i=1,$n=count($dataPemeriksaanSuster);$i<$n;$i++){?>
                         <tr>                         
                              <td align="left" class="tablecontent-odd" width="80%"><?php echo $dataPemeriksaanSuster[$i]["pgw_nama"];?></td>
                         </tr>
                    <?php }?>
               </table>
               
               <legend><strong>Pemeriksaan Mata</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td width="30%" align="center">Pemeriksaan</td>
                         <td width="30%" align="center">OD</td>
                         <td width="30%" align="center">OS</td>
                    </tr>	
                    <tr>
                         <td align="left" class="tablecontent">Palpebra</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_palpebra"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_palpebra"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Conjunctiva</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_conjunctiva"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_conjunctiva"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Cornea</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_cornea"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_cornea"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">COA</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_coa"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_coa"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Iris</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_iris"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_iris"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Pupil</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_pupil"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_pupil"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Lensa</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_lensa"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_lensa"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Ocular Movement</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_ocular"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_ocular"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Funduscopy (Retina)</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_retina"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_retina"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Tindakan Pemeriksaan</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" width="30%" class="tablecontent">Tonometri OD</td>
                         <td align="left" class="tablecontent-odd">
                              <?php echo $dataPemeriksaan["rawat_tonometri_scale_od"];?> / 
                              <?php echo $dataPemeriksaan["rawat_tonometri_weight_od"];?> g = 
                              <?php echo $dataPemeriksaan["rawat_tonometri_pressure_od"];?> mmHG
                         </td>
                    </tr>
                    <tr>
                         <td align="left" width="30%" class="tablecontent">Tonometri OS</td>
                         <td align="left" class="tablecontent-odd">
                              <?php echo $dataPemeriksaan["rawat_tonometri_scale_os"];?> / 
                              <?php echo $dataPemeriksaan["rawat_tonometri_weight_os"];?> g = 
                              <?php echo $dataPemeriksaan["rawat_tonometri_pressure_os"];?> mmHG
                         </td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Anel Test</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_anel"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Schimer Test</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_schimer"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Irigasi Bola Mata</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_irigasi"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Epilasi</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_epilasi"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Probing</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_probing"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Flouorecsin Test</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_flouorecsin"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Uji Kesehatan Mata</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_kesehatan"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Color Blindness</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_color_blindness"];?></td>
                    </tr>
          	</table>     	
                    
               <?php if($dataPemeriksaanTerapi) {?>
                    <legend><strong>Terapi</strong></legend>
                    <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tb_terapi"> 
                         <tr class="subheader">
                              <td width="30%" align="center">Nama Obat</td>
                              <td width="30%" align="center">Dosis</td>
                         </tr>
                         <?php for($i=0,$n=count($dataPemeriksaanTerapi);$i<$n;$i++) { ?>
                              <tr>
                                   <td align="left" class="tablecontent-odd" width="70%"><?php echo $dataPemeriksaanTerapi[$i]["item_nama"];?></td>                
                                   <td align="left" class="tablecontent-odd" width="70%"><?php echo $dataPemeriksaanTerapi[$i]["dosis_nama"];?></td>
                              </tr>
                         <?php } ?>
                    </table>
               <?php }?>
               
               <legend><strong>Terapi Kacamata</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
                    <tr class="subheader">
                         <td width="5%" align="center">Mata</td>
                         <td width="15%" align="center">Spheris</td>
                         <td width="15%" align="center">Cylinder</td>
                         <td width="15%" align="center">Sudut</td>
                    </tr>	
                    <tr>
                         <td align="center" class="tablecontent">OD</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_koreksi_spheris"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_koreksi_cylinder"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_od_koreksi_sudut"];?></td>
                    </tr>
                    <tr>
                         <td align="center" class="tablecontent">OS</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_koreksi_spheris"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_koreksi_cylinder"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataPemeriksaan["rawat_mata_os_koreksi_sudut"];?></td>
                    </tr>
               </table>
               
               <legend><strong>Diagnosis - ICD - OD</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="center" class="subheader" width="5%"></td>
                         <td align="center" class="subheader" width="25%">ICD</td>
                         <td align="center" class="subheader">Keterangan</td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">1</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanIcdOd[0]["icd_nomor"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanIcdOd[0]["icd_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">2</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanIcdOd[1]["icd_nomor"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanIcdOd[1]["icd_nama"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Diagnosis - ICD - OS</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="center" class="subheader" width="5%"></td>
                         <td align="center" class="subheader" width="25%">ICD</td>
                         <td align="center" class="subheader">Keterangan</td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">1</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanIcdOs[0]["icd_nomor"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanIcdOs[0]["icd_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">2</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanIcdOs[1]["icd_nomor"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanIcdOs[1]["icd_nama"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Diagnosis - INA DRG - OD</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="center" class="subheader" width="5%"></td>
                         <td align="center" class="subheader" width="25%">INA DRG</td>
                         <td align="center" class="subheader">Keterangan</td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">1</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanInaOd[0]["ina_kode"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanInaOd[0]["ina_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">2</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanInaOd[1]["ina_kode"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanInaOd[1]["ina_nama"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Diagnosis - INA DRG - OS</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="center" class="subheader" width="5%"></td>
                         <td align="center" class="subheader" width="25%">INA DRG</td>
                         <td align="center" class="subheader">Keterangan</td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">1</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanInaOs[0]["ina_kode"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanInaOs[0]["ina_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">2</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanInaOs[1]["ina_kode"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPemeriksaanInaOs[1]["ina_nama"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Rencana Tindakan Operasi</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Jenis Operasi</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPemeriksaan["op_jenis_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Paket Biaya</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPemeriksaan["op_paket_nama"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Rencana Anestesis</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPemeriksaan["anes_jenis_nama"];?></td>
                    </tr>
          	</table>
          </fieldset>
     <?php }?>
     
     
     <?php if($dataDiagnostik) {?>
          <fieldset style="border-color:red">
          <legend><strong>Diagnostik</strong></legend>
               <legend><strong>Petugas</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td width="20%"  class="tablecontent" align="left">Dokter</td>
                         <td align="left" class="tablecontent-odd" width="80%"><?php echo $dataDiagnostikDokter["pgw_nama"];?></td>
                    </tr>
                    <tr>
                         <td width="20%"  class="tablecontent" align="left">Perawat</td>
                         <td align="left" class="tablecontent-odd" width="80%">
          				<table width="100%" border="0" cellpadding="1" cellspacing="1">                              
                                   <?php for($i=0,$n=count($dataDiagnostikSuster);$i<$n;$i++) { ?>
                                        <tr>
                                             <td align="left" class="tablecontent-odd" width="70%"><?php echo $dataDiagnostikSuster[$i]["pgw_nama"];?></td>                                        
                                        </tr>
                                   <?php } ?>
          				</table>
                         </td>
                    </tr>
          	</table>
          	
          	<legend><strong>Keratometri</strong></legend>
               <table width="80%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td width="5%" align="center">&nbsp;</td>
                         <td width="30%" align="center">OD</td>
                         <td width="30%" align="center">OS</td>
                    </tr>	
                    <tr>
                         <td align="left" class="tablecontent">K1</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_k1_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_k1_os"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">K2</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_k2_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_k2_os"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Biometri</strong></legend>
               <table width="70%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td width="30%" align="center" width="20%">&nbsp;</td>
                         <td align="center">OD</td>
                         <td align="center">OS</td>
                    </tr>	
                    <tr>
                         <td align="left" class="tablecontent">Acial Length</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_acial_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_acial_os"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Power IOL</td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_iol_od"];?></td>
                         <td align="center" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_iol_os"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">AV Constant</td>
                         <td align="left" class="tablecontent-odd" colspan=2><?php echo $dataDiagnostik["bio_av_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Standart Deviasi</td>
                         <td align="left" class="tablecontent-odd" colspan=2><?php echo $dataDiagnostik["diag_deviasi"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Rumus yang dipakai</td>
                         <td align="left" class="tablecontent-odd" colspan=2><?php echo $dataDiagnostik["bio_rumus_nama"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>LAB</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" class="tablecontent" width="20%">Gula Darah Acak</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_lab_gula_darah"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Darah Lengkap</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_lab_darah_lengkap"];?><td>
                    </tr>
          	</table>
          	
          	<legend><strong>USG</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" class="tablecontent" width="20%">COA</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_coa"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Lensa</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_lensa"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Retina</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_retina"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Kesimpulan</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_kesimpulan"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Tindakan Medik di R. Diagnostik</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" class="tablecontent" width="20%">EKG</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_ekg"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Fundus Angiografi</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_fundus"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Indirect Opthalmoscopy</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_opthalmoscop"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Optical Coherence Tomographi</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_oct"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Yag Laser</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_yag"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Argon Laser</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_argon"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Laser Glaukoma</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_glaukoma"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Humprey</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataDiagnostik["diag_humpre"];?></td>
                    </tr>
          	</table>
          </fieldset>
     <?php }?>
     
     
     <?php if($dataPenjadwalan) {?>
          <fieldset>
          <legend><strong><?php echo ($dataPenjadwalan["tanggal_jadwal"])?"Penjadwalan":"Pre Operasi";?></strong></legend>
               <?php if(!$dataPenjadwalan["tanggal_jadwal"]) {?>
                    <legend><strong>Petugas</strong></legend>
                    <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
                         <tr>
                              <td align="left" width="20%" class="tablecontent">Dokter</td>
                              <td align="left" class="tablecontent-odd" width="30%" colspan="2"><?php echo $dataPreopDokter["pgw_nama"];?></td>
               		</tr>               
                         <tr>
                              <td width="20%"  class="tablecontent" align="left">Perawat</td>
                              <td align="left" class="tablecontent-odd" width="80%">
               				<table width="100%" border="0" cellpadding="1" cellspacing="1">                                   
                                        <?php for($i=0,$n=count($dataPreopSuster);$i<$n;$i++) {?>
                                             <tr>
                                                  <td align="left" class="tablecontent-odd" width="70%"><?php echo $dataPreopSuster[$i]["pgw_nama"];?></td>
                                             </tr>
                                        <?php } ?>
                                   </table>
                              </td>
                         </tr> 
               	</table>
               <?php }?>
               
               <legend><strong>Keratometri</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td align="center" width="20%">&nbsp;</td>
                         <td align="center" width="40%">OD</td>
                         <td align="center" width="40%">OS</td>
                    </tr>	
                    <tr>
                         <td align="left" class="tablecontent">K1</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_k1_od"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_k1_os"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">K2</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_k2_od"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_k2_os"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Biometri</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr class="subheader">
                         <td align="center" width="20%">&nbsp;</td>
                         <td align="center" width="40%">OD</td>
                         <td align="center" width="40%">OS</td>
                    </tr>	
                    <tr>
                         <td align="left" class="tablecontent">Acial Length</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_acial_od"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_acial_os"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Power IOL</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_iol_od"];?></td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_iol_os"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">A.Constan</td>
                         <td align="left" class="tablecontent-odd" colspan=2><?php echo $dataPenjadwalan["bio_av_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Standart Deviasi</td>
                         <td align="left" class="tablecontent-odd" colspan=2><?php echo $dataPenjadwalan["preop_deviasi"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Data Pasien Hari Ini </strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td width="20%" align="left" class="tablecontent">Keluhan Pasien</td>
                         <td width="80%" align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_keluhan"];?></td>
                    </tr>
                    <tr>
                         <td width="20%" align="left" class="tablecontent">Keadaan Umum</td>
                         <td width="80%" align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_keadaan_umum"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Tensimeter</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_lab_tensi"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Nadi</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_lab_nadi"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Pernafasan</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_lab_nafas"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Status Lokal Mata</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_mata_lokal"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent" width="20%">Gula Darah Acak</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_lab_gula_darah"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Darah Lengkap</td>
                         <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_lab_darah_lengkap"];?><td>
                    </tr>
                    <tr>
                         <td align="left" width="20%" class="tablecontent">Tonometri OD</td>
                         <td align="left" class="tablecontent-odd">
                              <?php echo $dataPenjadwalan["preop_tonometri_scale_od"];?> / 
                              <?php echo $dataPenjadwalan["preop_tonometri_weight_od"];?> g = 
                              <?php echo $dataPenjadwalan["preop_tonometri_pressure_od"];?> mmHG
                         </td>
                    </tr>
                    <tr>
                         <td align="left" width="20%" class="tablecontent">Tonometri OS</td>
                         <td align="left" class="tablecontent-odd">
                              <?php echo $dataPenjadwalan["preop_tonometri_scale_os"];?> / 
                              <?php echo $dataPenjadwalan["preop_tonometri_weight_os"];?> g = 
                              <?php echo $dataPenjadwalan["preop_tonometri_pressure_os"];?> mmHG
                         </td>
                    </tr>
          	</table>
          	
          	<legend><strong>Rencana Pemakaian IOL</strong></legend>
               <table width="40%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Jenis IOL</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPenjadwalan["iol_jenis_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Merk</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPenjadwalan["iol_merk_nama"]?></td>
                    </tr>
               </table>
               
               <?php if(!$dataPenjadwalan["tanggal_jadwal"]) {?>
                    <legend><strong>Anestesis</strong></legend>
                    <table width="100%" border="1" cellpadding="4" cellspacing="1">
                         <tr>
                              <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
                              <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPenjadwalan["anes_jenis_nama"];?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
                              <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPenjadwalan["item_nama"];?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent" width="35%">Dosis</td>
               			<td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPenjadwalan["dosis_nama"];?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
                              <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPenjadwalan["anes_komp_nama"];?></td>
                         </tr>
                         <tr>
                              <td align="left" class="tablecontent" width="35%">Premedikasi</td>
                              <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPenjadwalan["anes_pre_nama"];?></td>
                         </tr>
               	</table>
               <?php }?>
               
               
               <?php if($dataPenjadwalan["preop_regulasi"]=='y') {?>
                    <legend><strong>Regulasi</strong></legend>               
                    <table width="100%" border="1" cellpadding="4" cellspacing="1" id="tbRegulasi">
                         <tr class="subheader">
                              <td width="10%" align="center">No</td>
                              <td width="30%" align="center">Jenis Regulasi</td>
                              <td width="30%" align="center">Dengan Obat</td>
                              <td width="30%" align="center">Hasil Regulasi</td>
                         </tr>	
                         <tr>
                              <td align="center" class="tablecontent">1</td>
                              <td align="left" class="tablecontent">Gula Darah</td>
                              <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_regulasi_gula_obat"];?></td>
                              <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_regulasi_gula_hasil"];?></td>
                         </tr>
                         <tr>
                              <td align="center" class="tablecontent">2</td>
                              <td align="left" class="tablecontent">Tonometri</td>
                              <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_regulasi_tono_obat"];?></td>
                              <td align="left" class="tablecontent-odd"><?php echo $dataPenjadwalan["preop_regulasi_tono_hasil"];?></td>
                         </tr>                    
               	</table>
               <?php }?>
          
          </fieldset>
     <?php }?>
     
     
     <?php if($dataBedahMinor) {?>
          <fieldset style="border-color:red">
          <legend><strong>Bedah Minor</strong></legend>
               <?php if($dataInjeksi) {?>
                    <legend><strong>Inputan Injeksi</strong></legend>
                    <table width="100%" border="1" cellpadding="2" cellspacing="1">
                         <?php for($i=0,$n=count($dataInjeksi);$i<$n;$i++) { ?>
                              <tr>
                                   <td width="10%" class="tablecontent"><?php echo ($i==0)?"Nama Obat":"&nbsp;"?></td>
                                   <td width="20%" class="tablecontent-odd"><?php echo $dataInjeksi[$i]["item_nama"];?></td>
                                   <td width="10%" class="tablecontent"><?php echo ($i==0)?"Dosis":"&nbsp;"?></td>
                                   <td width="20%" class="tablecontent-odd"><?php echo $dataInjeksi[$i]["dosis_nama"];?></td>                         
                                   <td width="10%" class="tablecontent"><?php echo ($i==0)?"Teknik Injeksi":"&nbsp;"?></td>
                                   <td width="15%" class="tablecontent-odd"><?php echo $dataInjeksi[$i]["injeksi_nama"];?></td>                              
                              </tr>
                         <?php } ?>
               	</table>
          	<?php }?>
          	
          	<legend><strong>Data Laporan Operasi</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" width="20%" class="tablecontent">Dokter</td>
                         <td align="left" class="tablecontent-odd" width="30%"><?php echo $dataBedahMinor["op_dokter_nama"];?></td>
          
                         <td align="left" width="20%" class="tablecontent">Asisten Perawat</td>
                         <td align="left" class="tablecontent-odd" width="30%"><?php echo $dataBedahMinor["op_suster_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Jam</td>
                         <td align="left" class="tablecontent-odd" colspan=3>
                              <?php echo $dataBedahMinor["op_mulai_jam"]." - ".$dataBedahMinor["op_selesai_jam"]?>
          			</td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Jenis Operasi</td>
                         <td align="left" class="tablecontent-odd" colspan=3><?php echo $dataBedahMinor["op_jenis_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" width="20%" class="tablecontent">Kode ICDM</td>
                         <td align="left" class="tablecontent-odd" width="20%"><?php echo $dataBedahMinor["icd_nomor"];?></td>
          
                         <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
                         <td align="left" class="tablecontent-odd" width="30%"> 
                              <?php echo $dataBedahMinor["icd_nama"];?>               
                         </td>
                    </tr>
                    <tr>
                         <td align="left" width="20%" class="tablecontent">INA DRG</td>
                         <td align="left" class="tablecontent-odd" width="20%"><?php echo $dataBedahMinor["ina_kode"];?></td>
          
                         <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
                         <td align="left" class="tablecontent-odd" width="30%"><?php echo $dataBedahMinor["ina_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Komplikasi Durante OP</td>
                         <td align="left" class="tablecontent-odd"  colspan=3>
                              <table width="100%" border="1" cellpadding="1" cellspacing="1">
                                   <?php for($i=0,$n=count($dataBedahMinorDuranteop);$i<$n;$i++) {?>
                                        <tr>
                                             <td align="left" class="tablecontent-odd">
                                                  <?php echo $dataBedahMinorDuranteop[$i]["durop_komp_nama"];?>
                                             </td>
                                        </tr>
                                   <?php }?>
                              </table>
                         </td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Pesan Khusus Dari Operator</td>
                         <td align="left" class="tablecontent-odd"  colspan=3><?php echo $dataBedahMinor["op_pesan"];?></td>
                    </tr>
          	</table>          
          </fieldset>
     <?php }?>
     
     
     <?php if($dataPremedikasi) {?>
          <fieldset style="border-color:red">
          <legend><strong>Premedikasi</strong></legend>
               <legend><strong>Petugas</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1"> 
                    <tr>
                         <td align="left" width="20%" class="tablecontent">Dokter</td>
                         <td align="left" class="tablecontent-odd" width="30%" colspan="2"><?php echo $dataPremedikasiDokter["pgw_nama"];?></td>
          		</tr> 
                    <tr>
                         <td width="20%"  class="tablecontent" align="left">Perawat</td>
                         <td align="left" class="tablecontent-odd" width="80%">
          				<table width="100%" border="0" cellpadding="1" cellspacing="1" id="tb_suster">
                                   <?php for($i=0,$n=count($dataPremedikasiSuster);$i<$n;$i++) { ?>
                                        <tr>
                                             <td align="left" class="tablecontent-odd" width="70%">
                                                  <?php echo $dataPremedikasiSuster[$i]["pgw_nama"];?>
                                             </td>
                                        </tr>
                                   <?php } ?>
          				</table>
                         </td>
                    </tr> 
          	</table>
          	
               <legend><strong>Pemeriksaan Ulang</strong></legend>
     		<table width="100%" border="1" cellpadding="4" cellspacing="1"> 
     			<tr>
     				<td align="left" class="tablecontent">Tensimeter</td>
     				<td align="left" class="tablecontent-odd"><?php echo $dataPremedikasi["preme_lab_tensi"];?></td>
     			</tr>
     			<tr>
     				<td align="left" class="tablecontent">Nadi</td>
     				<td align="left" class="tablecontent-odd"><?php echo $dataPremedikasi["preme_lab_nadi"];?></td>
     			</tr>
     			<tr>
     				<td align="left" class="tablecontent">Pernafasan</td>
     				<td align="left" class="tablecontent-odd"><?php echo $dataPremedikasi["preme_lab_nafas"];?></td>
     			</tr>  
     			<tr>
     				<td align="left" width="20%" class="tablecontent">Tonometri OD</td>
     				<td align="left" class="tablecontent-odd">
     					<?php echo $dataPremedikasi["preme_tonometri_scale_od"];?> / 
     					<?php echo $dataPremedikasi["preme_tonometri_weight_od"];?> g = 
     					<?php echo $dataPremedikasi["preme_tonometri_pressure_od"];?> mmHG
     				</td>
     			</tr>
     			<tr>
     				<td align="left" width="20%" class="tablecontent">Tonometri OS</td>
     				<td align="left" class="tablecontent-odd">
     					<?php echo $dataPremedikasi["preme_tonometri_scale_os"];?> / 
     					<?php echo $dataPremedikasi["preme_tonometri_weight_os"];?> g = 
     					<?php echo $dataPremedikasi["preme_tonometri_pressure_os"];?> mmHG
     				</td>
     			</tr>
     		</table>
     		
     		<legend><strong>Rencana Pemakaian IOL</strong></legend>
               <table width="40%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Jenis IOL</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPremedikasi["iol_jenis_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Merk</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPremedikasi["iol_merk_nama"];?></td>
                    </tr>
          	</table>
          	
          	<legend><strong>Anestesis</strong></legend>
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Jenis Anestesis</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPremedikasi["anes_jenis_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Jenis Obat Anestesis</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPremedikasi["item_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Dosis</td>
          			<td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPremedikasi["dosis_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Komplikasi Anestesis</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPremedikasi["anes_komp_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent" width="35%">Premedikasi</td>
                         <td align="left" class="tablecontent-odd" width="65%"><?php echo $dataPremedikasi["anes_pre_nama"];?></td>
                    </tr>
          	</table>
          </fieldset>
     <?php }?>
     	
     	
     <?php if($dataOperasi) {?>
     	<fieldset>
          <legend><strong>Data Laporan Operasi</strong></legend>
          
               <table width="100%" border="1" cellpadding="4" cellspacing="1">
                    <tr>
                         <td width="30%"  class="tablecontent" align="left">Operator</td>
                         <td align="left" class="tablecontent-odd" width="80%" colspan=3><?php echo $dataOperasiDokter["pgw_nama"];?></td>
                    </tr>
               
                    <tr>
                         <td width="30%"  class="tablecontent" align="left">Asisten Perawat</td>
                         <td align="left" class="tablecontent-odd" width="80%" colspan=3>
          				<table width="100%" border="0" cellpadding="1" cellspacing="1">                              
                                   <?php for($i=0,$n=count($dataOperasiSuster);$i<$n;$i++) { ?>
                                        <tr>
                                             <td align="left" class="tablecontent-odd" width="70%">
                                                  <?php echo $dataOperasiSuster[$i]["pgw_nama"];?>
                                             </td>
                                        </tr>
                                   <?php } ?>
          				</table>
                         </td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Jam</td>
                         <td align="left" class="tablecontent-odd" colspan=3>
                              <?php echo $dataOperasi["op_jam_mulai"]." - ".$dataOperasi["op_jam_selesai"];?>
                         </td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Jenis Operasi</td>
                         <td align="left" class="tablecontent-odd" colspan=3> 
                              <?php echo $dataOperasi["op_jenis_nama"];?>               
                         </td>
                    </tr>
                    <tr>
                         <td align="left" width="20%" class="tablecontent">Kode ICDM</td>
                         <td align="left" class="tablecontent-odd" width="20%"><?php echo $dataOperasi["icd_nomor"];?></td>
          
                         <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
                         <td align="left" class="tablecontent-odd" width="30%"><?php echo $dataOperasi["icd_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" width="20%" class="tablecontent">INA DRG</td>
                         <td align="left" class="tablecontent-odd" width="20%"><?php echo $dataOperasi["ina_nomor"];?></td>
          
                         <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
                         <td align="left" class="tablecontent-odd" width="30%"><?php echo $dataOperasi["ina_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" width="20%" class="tablecontent">Tindakan Operasi</td>
                         <td align="left" class="tablecontent-odd" width="20%"><?php echo $dataOperasi["op_tindakan"];?></td>
          
                         <td align="left" width="20%" class="tablecontent">Paket Biaya</td>
                         <td align="left" class="tablecontent-odd" width="30%"><?php echo $dataOperasi["op_paket_biaya"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Prosedur Operasi</td>
                         <td align="left" class="tablecontent-odd"  colspan=3>
                              <table width="100%" border="1" cellpadding="1" cellspacing="1">
                                   <tr>
                                        <td align="left" class="tablecontent" width="20%">Conj. Flap</td>
                                        <td align="left" class="tablecontent-odd"><?php echo $conj[$dataOperasi["op_conj"]];?></td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">Cauter</td>
                                        <td align="left" class="tablecontent-odd"><?php echo $cauter[$dataOperasi["op_cauter"]];?></td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">Corneal Enter</td>
                                        <td align="left" class="tablecontent-odd">
                                             Jam <?php echo $dataOperasi["op_corneal_enter_jam"];?>
                                             Diperluas <?php echo $dataOperasi["op_corneal_enter_diperluas"];?>
                                             Jam <?php echo $dataOperasi["op_corneal_enter_jam1"];?> - 
                                             <?php echo $dataOperasi["op_corneal_enter_jam2"];?>
                                        </td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">Indirectomy</td>
                                        <td align="left" class="tablecontent-odd">
                                             <?php echo $indirectomy[$dataOperasi["op_indirectomy_tipe"]];?>
                                             Tipe <?php echo $dataOperasi["op_indirectomy_tipe"];?>
                                             Jam <?php echo $dataOperasi["op_indirectomy_jam"];?> 
                                        </td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">Nucleus Removal</td>
                                        <td align="left" class="tablecontent-odd"><?php echo $nucleus[$dataOperasi["op_nucleus_removal"]];?></td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">Cortex Removal</td>
                                        <td align="left" class="tablecontent-odd"><?php echo $cortex[$dataOperasi["op_cortex_removal"]];?></td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">Corneal Suture</td>
                                        <td align="left" class="tablecontent-odd">
                                             <?php echo $corneal[$dataOperasi["op_corneal_suture"]];?>
                                             Ukuran <?php echo $dataOperasi["op_corneal_suture_ukuran"];?> 
                                        </td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">Type Suture</td>
                                        <td align="left" class="tablecontent-odd"><?php echo $typeSuture[$dataOperasi["op_suture_tipe"]];?></td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">COA Form With</td>
                                        <td align="left" class="tablecontent-odd"><?php echo $COA[$dataOperasi["op_coa"]];?></td>
                                   </tr>
                                   <tr>
                                        <td align="left" class="tablecontent">Obat/Bahan</td>
                                        <td align="left" class="tablecontent-odd"><?php echo $obat[$dataOperasi["op_obat"]];?></td>
                                   </tr>
                              </table>
                         </td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Komplikasi Durante OP</td>
                         <td align="left" class="tablecontent-odd"  colspan=3>
                              <table width="100%" border="1" cellpadding="1" cellspacing="1">
                                   <?php for($i=0,$n=count($dataOperasiDuranteop);$i<$n;$i++) {?>
                                        <tr>
                                             <td align="left" class="tablecontent-odd">
                                                  <?php echo $dataOperasiDuranteop[$i]["durop_komp_nama"];?>
                                             </td>
                                        </tr>
                                   <?php }?>
                              </table>
                         </td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Manajemen Komplikasi</td>
                         <td align="left" class="tablecontent-odd"  colspan=3><?php echo $dataOperasi["op_komplikasi_manajemen"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Jenis IOL</td>
                         <td align="left" class="tablecontent-odd" colspan=3><?php echo $dataOperasi["iol_jenis_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Merk</td>
                         <td align="left" class="tablecontent-odd" colspan=3><?php echo $dataOperasi["iol_merk_nama"];?></td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Power</td>
                         <td align="left" class="tablecontent-odd" colspan=3><?php echo $dataOperasi["op_iol_power"];?> Dioptri</td>
                    </tr>
                    <tr>
                         <td align="left" class="tablecontent">Posisi IOL Terpasang</td>
                         <td align="left" class="tablecontent-odd" colspan=3><?php echo $dataOperasi["iol_pos_nama"];?></td>
                    </tr>
          	</table>
          
          </fieldset>
     <?php }?>
     
     </td>
</tr>	
</table>

<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>

</form>
<?php echo $view->RenderBodyEnd(); ?>