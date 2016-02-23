<?php     require_once("root.inc.php");     require_once($ROOT."library/bitFunc.lib.php");     require_once($ROOT."library/auth.cls.php");     require_once($ROOT."library/textEncrypt.cls.php");     require_once($ROOT."library/datamodel.cls.php");     require_once($ROOT."library/dateFunc.lib.php");     require_once($ROOT."library/currFunc.lib.php");     require_once($APLICATION_ROOT."library/view.cls.php");               $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);	   $dtaccess = new DataAccess();     $enc = new textEncrypt();     $auth = new CAuth();     $err_code = 0;     $userData = $auth->GetUserData();     $skr = getDateToday();      	    if(!$auth->IsAllowed("laboratorium",PRIV_CREATE)){          die("access_denied");          exit(1);     } else if($auth->IsAllowed("laboratorium",PRIV_CREATE)===1){          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";          exit(1);     }  $pemeriksaanId = $_GET["pemeriksaan_id"];  $_POST['pemeriksaan_id'] = $_GET["pemeriksaan_id"];    $sql = "select a.*,f.kategori_nama,cast(a.pemeriksaan_create as date) as tanggal_periksa, a.pemeriksaan_create, c.pgw_nama, d.usr_name,b.pemeriksaan_hasil, b.periksa_det_total,b.periksa_det_id,b.id_kegiatan, b.id_pemeriksaan, b.pemeriksaan_nilai_normal,e.*, g.cust_usr_nama, g.cust_usr_jenis_kelamin, g.cust_usr_alamat, g.cust_usr_kode, g.cust_usr_tanggal_lahir, h.reg_jenis_pasien          from lab_pemeriksaan a          left join lab_pemeriksaan_detail b on b.id_pemeriksaan = a.pemeriksaan_id          left join hris.hris_pegawai c on c.pgw_id = a.id_dokter          left join global.global_auth_user d on d.usr_id=a.who_update          left join lab_kegiatan e on e.kegiatan_id = b.id_kegiatan          left join lab_kategori f on f.kategori_id = e.id_kategori          left join klinik.klinik_registrasi h on h.reg_id = a.id_reg          left join global.global_customer_user g on g.cust_usr_id = h.id_cust_usr          where pemeriksaan_id = ".QuoteValue(DPE_CHAR,$pemeriksaanId)."          order by f.kategori_nama";         //echo $sql;  $rs = $dtaccess->Execute($sql,DB_SCHEMA_LAB);  $data_pemeriksaan = $dtaccess->FetchAll($rs);       $totalItem = 0;  $totalService = 0;  $GrandTotal = 0;  if(!$_POST["id_reg"]) $_POST["id_reg"] = $data_pemeriksaan[0]["id_reg"];if($_POST["btnSave"] || $_POST["btnUpdate"]) {     if(!$_POST["pemeriksaan_kwt"] && $_POST["btnUpdate"]) {          $sql = "select max(CAST(substring(pemeriksaan_kwt from 5 for 7) as BIGINT)) as kode from laboratorium.lab_pemeriksaan";          $lastKode = $dtaccess->Fetch($sql);          $_POST["pemeriksaan_kwt"] = str_pad($lastKode["kode"]+1,6,"0",STR_PAD_LEFT);         }$jumlahdata = $_POST['jumlah'] ;$pemeriksaanId = $_POST['pemeriksaanThok'];              for($i=0,$n=$jumlahdata;$i<$n;$i++){            		$sql = "update laboratorium.lab_pemeriksaan_detail set pemeriksaan_hasil = ".QuoteValue(DPE_CHAR,$_POST['hasil'][$i]). " , pemeriksaan_nilai_normal = ".QuoteValue(DPE_CHAR,$_POST['nilai'][$i]).                "where id_kegiatan = ".QuoteValue(DPE_CHAR,$_POST['kegiatan'][$i])."and id_pemeriksaan = ".QuoteValue(DPE_CHAR,$pemeriksaanId) ;                                        $dtaccess->Execute($sql);              }              if($_POST["btnUpdate"])              {                $sql = "update laboratorium.lab_pemeriksaan set pemeriksaan_kwt = ".QuoteValue(DPE_CHAR,$_POST["pemeriksaan_kwt"]).                "where pemeriksaan_id = ".QuoteValue(DPE_CHAR,$pemeriksaanId) ;                                        $dtaccess->Execute($sql);                $cetakPage = "pemeriksaan_cetak.php?pemeriksaan_id=".$pemeriksaanId;                header ("location:".$cetakPage);              }              if($_POST["btnSave"]){                // insert ke tabel klinik_history_pasien                $dbSchema = "klinik";                $dbTable = "klinik_history_pasien";                $dbField[0] = "history_id";                $dbField[1] = "id_reg";                $dbField[2] = "history_status_pasien";                $dbField[3] = "history_when_out";                $history_id = $dtaccess->GetTransID();                $dbValue[0] = QuoteValue(DPE_CHAR,$history_id);                $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);                $dbValue[2] = QuoteValue(DPE_CHAR,STATUS_LABORATORIUM);                $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));                $dbKey[0] = 0;                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,$dbSchema);                $dtmodel->Insert() or die("insert error");                unset($dtmodel);                unset($dbField);                unset($dbValue);                unset($dbKey);                // end insert                                 header("location:pemeriksaan_edit.php");              }              $sql = "update klinik.klinik_registrasi set reg_status = ".QuoteValue(DPE_CHAR,$_POST["cmbNext"].STATUS_ANTRI)." where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);              $dtaccess->Execute($sql);        }        $count = 0;        $optionsNext[$count] = $view->RenderOption(STATUS_REFRAKSI,$rawatStatus[STATUS_REFRAKSI],$show); $count++;        $optionsNext[$count] = $view->RenderOption(STATUS_PEMERIKSAAN,$rawatStatus[STATUS_PEMERIKSAAN],$show); $count++;        $optionsNext[$count] = $view->RenderOption(STATUS_BEDAH,$rawatStatus[STATUS_BEDAH],$show); $count++;        $optionsNext[$count] = $view->RenderOption(STATUS_DIAGNOSTIK_TIPE,$rawatStatus[STATUS_DIAGNOSTIK_TIPE],$show); $count++;        $optionsNext[$count] = $view->RenderOption(STATUS_SELESAI,$rawatStatus[STATUS_SELESAI],$show); $count++;     ?><?php echo $view->RenderBody("inosoft.css",false); ?>  <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">  <table border="0" cellpadding="2" cellspacing="0" style="align:left" width="80%">    <tr>      <td rowspan="3" width="25%" class="tablecontent"><img src="<?echo $APLICATION_ROOT;?>/images/logo_bkmm1.gif" width="160" height="80"/></td>      <td style="text-align:left;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LABORATORIUM RSMM</td>    </tr>    <tr>      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Rumah Sakit Mata Masyarakat</td>    </tr>    <!--<tr>      <td style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jl. Gayung Kebonsari Timur 49, Surabaya</td>    </tr>-->  </table>  <br />  <br />  <table border="0" cellpadding="2" cellspacing="0" style="align:left" width="80%">    <tr>      <td class="tablecontent">Kode Pasien</td>      <td class="tablecontent-odd">:&nbsp;<?php echo $data_pemeriksaan[0]["cust_usr_kode"];?></td>      <td class="tablecontent">Jenis Kelamin/Umur</td>      <td class="tablecontent-odd">:&nbsp;<?php echo ($data_pemeriksaan[0]["cust_usr_jenis_kelamin"]);?>/<?php echo HitungUmur($data_pemeriksaan[0]["cust_usr_tanggal_lahir"]);?></td>    </tr>    <tr>      <td class="tablecontent">Nama</td>      <td class="tablecontent-odd">:&nbsp;<?php echo $data_pemeriksaan[0]["cust_usr_nama"];?></td>      <td class="tablecontent">Tanggal Terima</td>      <td class="tablecontent-odd">:&nbsp;<?php echo format_date_long($data_pemeriksaan[0]["tanggal_periksa"]);?></td>    </tr>    <tr>      <td class="tablecontent">Alamat</td>      <td class="tablecontent-odd">:&nbsp;<?php echo ($data_pemeriksaan[0]["cust_usr_alamat"]);?></td>      <td class="tablecontent">Waktu Terima</td>      <td class="tablecontent-odd">:&nbsp;<?php       $waktu = explode(" ",$data_pemeriksaan[0]["pemeriksaan_create"]);      echo $waktu[1];?></td>    </tr>    <tr>      <td class="tablecontent">Dokter</td>      <td class="tablecontent-odd">:&nbsp;<?php echo ($data_pemeriksaan[0]["pgw_nama"])?$data_pemeriksaan[0]["pgw_nama"]:"Permintaan Sendiri";?></td>      <td class="tablecontent">Tanggal Selesai</td>      <td class="tablecontent-odd">:&nbsp;<?php echo format_date_long($data_pemeriksaan[0]["tanggal_periksa"]);?></td>    </tr>    <tr>      <td class="tablecontent">Jenis Pasien</td>      <td class="tablecontent-odd">:&nbsp;<?php echo $bayarPasien[$data_pemeriksaan[0]["reg_jenis_pasien"]];?></td>      <td class="tablecontent">Waktu Selesai</td>      <td class="tablecontent-odd">:&nbsp;<?php       $waktu = explode(" ",$data_pemeriksaan[0]["pemeriksaan_create"]);      echo $waktu[1];?></td>    </tr>  </table>  <br />  <table border="1" cellpadding="1" cellspacing="0" style="align:left" width="80%" style="border-style:double;">    <?php for($i=0;$i<count($data_pemeriksaan);$i++){             if($data_pemeriksaan[$i]["kategori_nama"]!=$data_pemeriksaan[$i-1]["kategori_nama"]){        ?>      <tr>        <td class="tablecontent" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $data_pemeriksaan[$i]["kategori_nama"] ?></td>        <td class="tablecontent" style="width:35%;" align="center">Hasil</td>        <td class="tablecontent" style="width:30%;" align="center">Nilai Normal</td>      </tr>    <?php }?>    <tr>      <td class="tablecontent" width="25%">&nbsp;<?php echo $data_pemeriksaan[$i]["kegiatan_nama"]; ?></td>      <td class="tablecontent-odd" style="text-align:right;width:10%">Rp.&nbsp;<?php echo currency_format($data_pemeriksaan[$i]["periksa_det_total"]);?>&nbsp;</td>      <td class="tablecontent-odd" align="left">&nbsp;      <input type="text" size="20" maxlength="255"  name="hasil[<?php echo $i ?>]" id="hasil_<?php echo $i ;?>" >&nbsp;<?php echo $data_pemeriksaan[$i]["kegiatan_satuan"] ?>      </td>      <td class="tablecontent-odd" align="right">      &nbsp;<label><?php echo $data_pemeriksaan[$i]["kegiatan_nilai_normal"]."&nbsp;".$data_pemeriksaan[$i]["kegiatan_satuan"]; ?></label>      </td>    </tr>    <input type="hidden" name="kegiatan[<?php echo $i ?>]" value="<?php echo $data_pemeriksaan[$i]["id_kegiatan"]?>"  class="button">    <?php }?>  </table>  <br />  <input type="hidden" name="pemeriksaanThok" value="<?php echo $_POST['pemeriksaan_id']?>"  class="button">  <input type="hidden" name="jumlah" class="button" value="<?php echo count($data_pemeriksaan) ; ?>"  class="button">  <table border="1" cellpadding="1" cellspacing="0" style="align:left" width="80%" style="border-style:double;">    <tr>      <td class="tablecontent" width="30%">&nbsp;Jumlah</td>      <td class="tablecontent-odd" style="text-align:right">&nbsp;Rp.&nbsp;<?php echo currency_format($data_pemeriksaan[0]["pemeriksaan_total"]); ?></td>    </tr>    <tr>      <td align="left" width="30%" class="tablecontent">Tahap Berikutnya</td>      <td align="left" width="50%"><?php echo $view->RenderComboBox("cmbNext","cmbNext",$optionsNext,null,null,null);?></td>      <?php echo $view->RenderHidden("id_reg","id_reg",$_POST["id_reg"]);?>    </tr>    <tr>      <td class="" width="30%">&nbsp;</td>      <td class="" style="text-align:right">      <input type="submit" name="btnSave" value=" Simpan " class="button">&nbsp;&nbsp;      <input type="submit" name="btnUpdate" value=" Simpan & Cetak Hasil " class="button"></td>    </tr>          </table>  <br />  <table border="0" align="left" cellpadding="1" cellspacing="1" width="80%">    <tr>      <td height="120" style="font-family:sans-serif;text-align:right;text-decoration:underline;" class="tablecontent"><?php echo $data_pemeriksaan[0]["usr_name"];?></td>    </tr>  </form>  </body></html>