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
 
     $thisPage = "report_operasi.php";

     if(!$auth->IsAllowed("operasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("operasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y"); 
      
     $sql_where[] = "cast(op_waktu as date) >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     $sql_where[] = "cast(op_waktu as date) <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));

     $sql = "select op_id, reg_id,cast(c.op_waktu as date) as tanggal, b.cust_usr_id, b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, op_jenis_nama, op_paket_nama, 
               b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin, a.reg_jenis_pasien, a.reg_status_pasien, a.reg_id, icd_nama, f.pgw_nama as dokter_nama,
               iol_jenis_nama, iol_merk_nama, op_iol_power, ina_kode, reg_jenis_pasien, op_metode_nama
               from klinik.klinik_registrasi a 
               join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               join klinik.klinik_operasi c on c.id_reg = a.reg_id
               left join klinik.klinik_icd d on d.icd_id = c.id_icd
               left join klinik.klinik_operasi_dokter e on id_op = c.op_id
               left join hris.hris_pegawai f on f.pgw_id = e.id_pgw
               left join klinik.klinik_operasi_jenis g on g.op_jenis_id = c.op_jenis
               left join klinik.klinik_operasi_paket h on h.op_paket_id = c.op_paket_biaya
               left join klinik.klinik_iol_merk i on i.iol_merk_id = c.op_iol_merk
               left join klinik.klinik_iol_jenis j on j.iol_jenis_id = c.op_iol_jenis 
               left join klinik.klinik_ina k on k.ina_id = c.id_ina
               left join klinik.klinik_operasi_metode l on l.op_metode_id = c.id_op_metode ";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " order by c.op_waktu, a.reg_status_pasien, b.cust_usr_nama"; 
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
      
     $sql = "select b.icd_nomor as op_icd_kode, b.icd_nama as op_icd_nama, a.id_icd, op_id 
               from klinik.klinik_operasi c
               join klinik.klinik_operasi_icd a on c.op_id = a.id_op 
               join klinik.klinik_icd b on a.id_icd = b.icd_id ";
     $sql.= " where ".implode(" and ",$sql_where); 
     $dataIcd = $dtaccess->FetchAll($sql);
     
     for($i=0,$n=count($dataIcd);$i<$n;$i++) {
          $totIcd[$dataIcd[$i]["op_id"]]++;
          if($totIcd[$dataIcd[$i]["op_id"]]==1) {
               $icd[$dataIcd[$i]["op_id"]] = "-".$dataIcd[$i]["op_icd_kode"];       
          } else {
               $icd[$dataIcd[$i]["op_id"]] .= "<br>-".$dataIcd[$i]["op_icd_kode"]; 
          } 
     }
     
     $sql = "select pgw_nama, pgw_id, op_id 
               from klinik.klinik_operasi c
               join klinik.klinik_operasi_suster a on a.id_op = c.op_id 
               join hris.hris_pegawai b on a.id_pgw = b.pgw_id ";
     $sql.= " where ".implode(" and ",$sql_where); 
     $rs = $dtaccess->Execute($sql);
     $dataSuster = $dtaccess->FetchAll($rs);
     
     for($i=0,$n=count($dataSuster);$i<$n;$i++) {
          $tot[$dataSuster[$i]["op_id"]]++;
          
          if($tot[$dataSuster[$i]["op_id"]]>1)
               $suster[$dataSuster[$i]["op_id"]] .= "<br>".$dataSuster[$i]["pgw_nama"];
          else
               $suster[$dataSuster[$i]["op_id"]] = $dataSuster[$i]["pgw_nama"]; 
     }
     
     //--- komplikasi operasi ----
     $sql = "select b.durop_komp_nama, id_op 
               from klinik.klinik_operasi c
               join klinik.klinik_operasi_duranteop a on a.id_op = c.op_id 
               join klinik.klinik_duranteop_komplikasi b on a.id_durop_komp = b.durop_komp_id ";
     $sql.= " where ".implode(" and ",$sql_where); 
     $dataOperasiDuranteop = $dtaccess->FetchAll($sql);
     
     for($i=0,$n=count($dataOperasiDuranteop);$i<$n;$i++) {
          $totkomp[$dataOperasiDuranteop[$i]["id_op"]]++;
          
          if($totkomp[$dataOperasiDuranteop[$i]["id_op"]]==1) {  
               $komplikasi[$dataOperasiDuranteop[$i]["id_op"]] = "- ".$dataOperasiDuranteop[$i]["durop_komp_nama"];    
          } else {
               $komplikasi[$dataOperasiDuranteop[$i]["id_op"]] .= "<br>- ".$dataOperasiDuranteop[$i]["durop_komp_nama"];
          } 
     }
     //--- di tutup sementara ---
     /*
     // --- data refraksi ---
     $sql = "select a.*, b.visus_nama as visus_nonkoreksi_od, c.visus_nama as visus_koreksi_od,
               d.visus_nama as visus_nonkoreksi_os, e.visus_nama as visus_koreksi_os  
               from klinik.klinik_refraksi a
               left join klinik.klinik_visus b on a.id_visus_nonkoreksi_od = b.visus_id  
               left join klinik.klinik_visus c on a.id_visus_koreksi_od = c.visus_id
               left join klinik.klinik_visus d on a.id_visus_nonkoreksi_os = d.visus_id
               left join klinik.klinik_visus e on a.id_visus_koreksi_os = e.visus_id "; 
     $dataRefraksi = $dtaccess->FetchAll($sql);
     
     for($i=0,$n=count($dataRefraksi);$i<$n;$i++) {
          
          $dataVisusOd[$dataRefraksi[$i]["id_reg"]] = $dataRefraksi[$i]["visus_koreksi_od"];
          $dataVisusOs[$dataRefraksi[$i]["id_reg"]] = $dataRefraksi[$i]["visus_koreksi_os"]; 
          
     }*/
     
     // --- data pemeriksaan ---
     $sql = "select id_reg, d.anes_jenis_nama  
               from klinik.klinik_perawatan a 
               join klinik.klinik_anestesis_jenis d on a.rawat_anestesis_jenis = d.anes_jenis_id  ";
     $dataPemeriksaan = $dtaccess->FetchAll($sql);
     
     for($i=0,$n=count($dataPemeriksaan);$i<$n;$i++) { 
          $anestesis[$dataPemeriksaan[$i]["id_reg"]] = $dataPemeriksaan[$i]["anes_jenis_nama"]; 
     } 
     // --- data diagnostik ---
     $sql = "select a.*, b.bio_rumus_nama, c.bio_av_nama 
               from klinik.klinik_diagnostik a
               left join klinik.klinik_biometri_rumus b on a.diag_rumus = b.bio_rumus_id 
               left join klinik.klinik_biometri_av c on a.diag_av_constant = c.bio_av_id"; 
     $dataDiagnostik = $dtaccess->FetchAll($sql);
     
     for($i=0,$n=count($dataDiagnostik);$i<$n;$i++) {
          $dataK1Od[$dataDiagnostik[$i]["id_reg"]] = $dataDiagnostik[$i]["diag_k1_od"];
          $dataK1Os[$dataDiagnostik[$i]["id_reg"]] = $dataDiagnostik[$i]["diag_k1_os"];
          
          $dataK2Od[$dataDiagnostik[$i]["id_reg"]] = $dataDiagnostik[$i]["diag_k2_od"];
          $dataK2Os[$dataDiagnostik[$i]["id_reg"]] = $dataDiagnostik[$i]["diag_k2_os"];
          
          $dataAlOd[$dataDiagnostik[$i]["id_reg"]] = $dataDiagnostik[$i]["diag_acial_od"];
          $dataAlOs[$dataDiagnostik[$i]["id_reg"]] = $dataDiagnostik[$i]["diag_acial_od"]; 
     }
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Report Pasien Operasi";
     
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;
     /*
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";  
     $counterHeader++;*/
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";  
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";   
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";  
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";  
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";  
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";    
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "L/P";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";  
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";   
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode ICD 10";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2";
     $counterHeader++;
     
     $counHeader = 0;
     $tbHeader[1][$counHeader][TABLE_ISI] = "Visus OD";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "Visus OS";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Biometri";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "6";    
     $counterHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "K1 OD";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "K1 OS";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "K2 OD";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "K2 OS";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "AL OD";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "AL OS";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Lensa";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";        
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "3";  
     $counterHeader++; 
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "Dioptri";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "Merk";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "Jenis";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";    
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2"; 
     $counterHeader++; 
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "Anestesis";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "Tindakan";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";    
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2"; 
     $counterHeader++; 
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "ICD 9 CM";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "Ina DRG";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Metode Operasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";    
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Komplikasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";    
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Pelaksana";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";    
     $tbHeader[0][$counterHeader][TABLE_COLSPAN] = "2"; 
     $counterHeader++; 
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "OPRT";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++;
     
     $tbHeader[1][$counHeader][TABLE_ISI] = "ASIST";
     $tbHeader[1][$counHeader][TABLE_WIDTH] = "10%";     
     $counHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";    
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2"; 
     $counterHeader++;
     
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){ 
          $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right"; 
          $counter++;
          /*
          $tbContent[$i][$counter][TABLE_ISI] = "<img src='".$APLICATION_ROOT."images/b_lampiran2.gif' border='0' align='middle' width='18' height='20' style='cursor:pointer' title='Detail' alt='Detail' onClick='BukaWindow(\"report_operasi_detail.php?id_reg=".$dataTable[$i]["reg_id"]."&id_cust_usr=".$dataTable[$i]["cust_usr_id"]."\")'/>";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_WRAP] = "nowrap";
          $counter++;*/
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = HitungUmur($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";           
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataVisusOd[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataVisusOs[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataK1Od[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataK1Os[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataK2Od[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataK2Os[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataAlOd[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataAlOs[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["op_iol_power"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["iol_merk_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["iol_jenis_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $anestesis[$dataTable[$i]["reg_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["op_jenis_nama"];;
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $icd[$dataTable[$i]["op_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["ina_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["op_metode_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $komplikasi[$dataTable[$i]["op_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";         
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $suster[$dataTable[$i]["op_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["op_paket_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
     }
     
     $colspan = count($tbHeader[0])+count($tbHeader[1]);
     
     if(!$_POST["btnExcel"]){
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="submit" name="btnExcel" value="Export Excel" class="button" onClick="document.location.href=\''.$editPage.'\'">&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
          $tbBottom[0][0][TABLE_ALIGN] = "center";
     }
     
	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_operasi_'.$_POST["tgl_awal"].'.xls');
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


var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
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
          <td width="15%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="35%" class="tablecontent-odd">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               -
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>images/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          </td> 
          <td class="tablecontent" width="50%">
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
</script>

<?php echo $view->RenderBodyEnd(); ?>
