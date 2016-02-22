<?php
     $icdPage = "op_icd_find.php?";
     $inaPage = "op_ina_find.php?";
     $dokterPage = "op_dokter_find.php?";
     $susterPage = "op_suster_find.php?";

     if($_POST["btnSave"] || $_POST["btnUpdate"]) {
          $dbTable = "klinik.klinik_perawatan_operasi";

          $dbField[0] = "op_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "op_jam_mulai";
          $dbField[3] = "op_jam_selesai";
          $dbField[4] = "op_tanggal";
          $dbField[5] = "id_op_jenis";
          $dbField[6] = "id_icd";
          $dbField[7] = "id_ina";
          $dbField[8] = "op_pesan";
          $dbField[9] = "op_tipe";
                    
          if(!$_POST["op_id"]) $_POST["op_id"] = $dtaccess->GetTransID();
          $_POST["op_jam_mulai"] = $_POST["op_mulai_jam"].":".$_POST["op_mulai_menit"].":00";
          $_POST["op_jam_selesai"] = $_POST["op_selesai_jam"].":".$_POST["op_selesai_menit"].":00";
          $_POST["op_tanggal"] = date("Y-m-d");
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["op_id"]);   // PK
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          $dbValue[2] = QuoteValue(DPE_DATETIME,$_POST["op_jam_mulai"]);
          $dbValue[3] = QuoteValue(DPE_DATETIME,$_POST["op_jam_selesai"]);
          $dbValue[4] = QuoteValue(DPE_DATE,$_POST["op_tanggal"]);
          $dbValue[5] = QuoteValue(DPE_CHARKEY,$_POST["id_op_jenis"]);
          $dbValue[6] = QuoteValue(DPE_CHARKEY,$_POST["id_icd"]);
          $dbValue[7] = QuoteValue(DPE_CHARKEY,$_POST["id_ina"]);
          $dbValue[8] = QuoteValue(DPE_CHARKEY,$_POST["op_pesan"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,"B");
          

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          $dtmodel->Insert() or die("insert  error");	


          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
          unset($dtmodel);
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          
          // -- ini insert ke tabel durante OP
		$sql = "delete from klinik.klinik_perawatan_duranteop where id_op = ".QuoteValue(DPE_CHAR,$_POST["op_id"]);
		$dtaccess->Execute($sql); 

          $dbTable = "klinik.klinik_perawatan_duranteop";
          $dbField[0] = "id_op";
          $dbField[1] = "id_durop_komp";
          
          foreach($_POST["id_durop_komp"] as $key=>$value) {
               
               $dbValue[0] = QuoteValue(DPE_CHARKEY,$_POST["op_id"]);
               $dbValue[1] = QuoteValue(DPE_CHARKEY,$key);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dbKey[1] = 1; 
               
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

               $dtmodel->Insert() or die("insert  error");
               
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          }
          
          
		$sql = "update klinik.klinik_registrasi set reg_status = '".STATUS_SELESAI."' where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dtaccess->Execute($sql); 
          
          echo "<script>document.location.href='".$thisPage."';</script>";
          exit();   
     }

     $sql = "select ref_mata_od_nonkoreksi_visus, ref_mata_od_koreksi_visus,ref_mata_od_koreksi_spheris,ref_mata_od_koreksi_cylinder,ref_mata_od_koreksi_sudut, 
               ref_mata_os_nonkoreksi_visus, ref_mata_os_koreksi_visus,ref_mata_os_koreksi_spheris,ref_mata_os_koreksi_cylinder,ref_mata_os_koreksi_sudut
               from klinik.klinik_refraksi where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     $dataRefraksi = $dtaccess->Fetch($sql); 

     $sql = "select rawat_id, rawat_tonometri_scale_od, rawat_tonometri_weight_od, rawat_tonometri_pressure_od, 
               rawat_anel, rawat_schimer, rawat_operasi_jenis  
               from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     $dataPemeriksaan = $dtaccess->Fetch($sql);
     
     $sql = "select b.icd_nomor, b.icd_nama
               from klinik.klinik_perawatan_icd a join klinik.klinik_icd b on a.id_icd = b.icd_id
               where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
               order by a.rawat_icd_urut";
     $dataDiagIcd = $dtaccess->FetchAll($sql);

     $sql = "select b.ina_kode, b.ina_nama
               from klinik.klinik_perawatan_ina a join klinik.klinik_ina b on a.id_ina = b.ina_id
               where a.id_rawat = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_id"])."
               order by a.rawat_ina_urut";
     $dataDiagIna = $dtaccess->FetchAll($sql);
     
     $sql = "select op_jenis_nama from klinik.klinik_operasi_jenis where op_jenis_id = ".QuoteValue(DPE_CHAR,$dataPemeriksaan["rawat_operasi_jenis"]);
     $dataJenisTindakan= $dtaccess->Fetch($sql);

     // --- nyari datanya operasi jenis + harganya---
     $sql = "select op_jenis_id, op_jenis_nama from klinik.klinik_operasi_jenis";
     $dataOperasiJenis = $dtaccess->FetchAll($sql);

     // -- bikin combonya operasi Jenis
     $optOperasiJenis[0] = $view->RenderOption("","[Pilih Jenis Operasi]",$show); 
     for($i=0,$n=count($dataOperasiJenis);$i<$n;$i++) {
          $show = ($_POST["rawat_operasi_jenis"]==$dataOperasiJenis[$i]["op_jenis_id"]) ? "selected":"";
          $optOperasiJenis[$i+1] = $view->RenderOption($dataOperasiJenis[$i]["op_jenis_id"],$dataOperasiJenis[$i]["op_jenis_nama"],$show); 
     }


     // --- nyari datanya komplikasi durante ---
     $sql = "select durop_komp_id, durop_komp_nama from klinik.klinik_duranteop_komplikasi";
     $dataDurop = $dtaccess->FetchAll($sql);

     
?>

<table width="100%" border="1" cellpadding="4" cellspacing="1">
<tr>
     <td width="100%">

     <fieldset>
     <legend><strong>Data Refraksi</strong></legend>
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
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_od_nonkoreksi_visus"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_od_koreksi_sudut"];?></td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_od_koreksi_visus"];?></td>
          </tr>
          <tr>
               <td align="center" class="tablecontent">OS</td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_os_nonkoreksi_visus"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_spheris"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_cylinder"];?></td>
               <td align="center" class="tablecontent-odd"><?php echo $dataRefraksi["ref_mata_os_koreksi_sudut"];?></td>
               <td align="center" class="tablecontent-odd" nowrap><?php echo $dataRefraksi["ref_mata_os_koreksi_visus"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - ICD</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcd[0]["icd_nomor"]." ".$dataDiagIcd[0]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcd[1]["icd_nomor"]." ".$dataDiagIcd[1]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcd[2]["icd_nomor"]." ".$dataDiagIcd[2]["icd_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 4</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIcd[3]["icd_nomor"]." ".$dataDiagIcd[3]["icd_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Diagnose - INA</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 1</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIna[0]["ina_kode"]." ".$dataDiagIna[0]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 2</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIna[1]["ina_kode"]." ".$dataDiagIna[1]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 3</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIna[2]["ina_kode"]." ".$dataDiagIna[2]["ina_nama"];?></td>
          </tr>
          <tr>
               <td align="left" class="tablecontent" width="20%">Diagnosis 4</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataDiagIna[3]["ina_kode"]." ".$dataDiagIna[3]["ina_nama"];?></td>
          </tr>
	</table>
     </fieldset>

     <fieldset>
     <legend><strong>Rencana Tindakan</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" class="tablecontent" width="20%">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd"><?php echo $dataJenisTindakan["op_jenis_nama"];?></td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong>Data Laporan Operasi</strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
          <tr>
               <td align="left" width="20%" class="tablecontent">Operator</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_dokter_nama","op_dokter_nama_0","20","100",$_POST["op_dokter_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $dokterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Dokter"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Dokter" alt="Cari Dokter" /></a>
                    <input type="hidden" id="id_dokter_0" name="id_dokter" value="<?php echo $_POST["id_dokter"];?>"/>
               </td>

               <td align="left" width="20%" class="tablecontent">Asisten Perawat</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_suster_nama","op_suster_nama_0","20","100",$_POST["op_suster_nama"],"inputField", "readonly",false);?>
                    <a href="<?php echo $susterPage;?>&el=0&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari Suster"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari Suster" alt="Cari Suster" /></a>
                    <input type="hidden" id="id_suster_0" name="id_suster" value="<?php echo $_POST["id_suster"];?>"/> <BR>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Jam</td>
               <td align="left" class="tablecontent-odd" colspan=3>
				<select name="op_mulai_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_mulai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
					</select>:
					<select name="op_mulai_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_mulai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				s/d
				<select name="op_selesai_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
					</select>:
					<select name="op_selesai_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Jenis Operasi</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderComboBox("id_op_jenis","id_op_jenis",$optOperasiJenis,null,null,null);?>               
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">Kode ICDM</td>
               <td align="left" class="tablecontent-odd" width="20%"> 
                    <?php echo $view->RenderTextBox("op_icd_kode","op_icd_kode","10","100",$_POST["op_icd_kode"],"inputField", "readonly",false);?>
                    <a href="<?php echo $icdPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" id="id_icd" name="id_icd" value="<?php echo $_POST["id_icd"];?>"/>
               </td>

               <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_icd_nama","op_icd_nama","50","100",$_POST["op_icd_nama"],"inputField", "readonly",false);?>               
               </td>
          </tr>
          <tr>
               <td align="left" width="20%" class="tablecontent">INA DRG</td>
               <td align="left" class="tablecontent-odd" width="20%"> 
                    <?php echo $view->RenderTextBox("op_ina_kode","op_ina_kode","10","100",$_POST["op_ina_kode"],"inputField", "readonly",false);?>
                    <a href="<?php echo $inaPage;?>&TB_iframe=true&height=400&width=450&modal=true" class="thickbox" title="Cari ICD"><img src="<?php echo($APLICATION_ROOT);?>images/bd_insrow.png" border="0" align="middle" width="18" height="20" style="cursor:pointer" title="Cari ICD" alt="Cari ICD" /></a>
                    <input type="hidden" id="id_ina" name="id_ina" value="<?php echo $_POST["id_ina"];?>"/>
               </td>

               <td align="left" width="20%" class="tablecontent">Jenis Procedure</td>
               <td align="left" class="tablecontent-odd" width="30%"> 
                    <?php echo $view->RenderTextBox("op_ina_nama","op_ina_nama","50","100",$_POST["op_ina_nama"],"inputField", "readonly",false);?>               
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Komplikasi Durante OP</td>
               <td align="left" class="tablecontent-odd"  colspan=3>
                    <?php for($i=0,$n=count($dataDurop);$i<$n;$i++) { ?>                    
                         <?php echo $view->RenderCheckBox("id_durop_komp[".$dataDurop[$i]["durop_komp_id"]."]","id_durop_komp_".$dataDurop[$i]["durop_komp_id"],"y","null");?>
                         <label for="id_durop_komp_<?php echo $dataDurop[$i]["durop_komp_id"];?>"><?php echo $dataDurop[$i]["durop_komp_nama"];?></label><BR>
                    <?php } ?>
               </td>
          </tr>
          <tr>
               <td align="left" class="tablecontent">Pesan Khusus Dari Operator</td>
               <td align="left" class="tablecontent-odd"  colspan=3> 
                    <?php echo $view->RenderTextArea("op_pesan","op_pesan","3","40",$_POST["op_pesan"]);?>               
               </td>
          </tr>
	</table>
     </fieldset>


     <fieldset>
     <legend><strong></strong></legend>
     <table width="100%" border="1" cellpadding="4" cellspacing="1">
		<tr>
			<td align="center"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit") ? "btnUpdate" : "btnSave","btnSave","Simpan","button",false,null);?></td>
		</tr>
	</table>
     </fieldset>
     </td>
</tr>	
</table>
