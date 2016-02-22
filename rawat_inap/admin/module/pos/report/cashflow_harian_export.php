
<?php
     require_once("root.inc.php");
     require_once($ROOT."library/bitFunc.lib.php");
     require_once($ROOT."library/auth.cls.php");
     require_once($ROOT."library/textEncrypt.cls.php");
     require_once($ROOT."library/datamodel.cls.php");
	   require_once($ROOT."library/currFunc.lib.php");
     require_once($ROOT."library/dateFunc.lib.php");
    
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $err_code = 0;
     $auth = new CAuth();

     if($_GET["export"]=="excel"){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=pendapatan_global.xls');
     }
     
    
          
    // if($_GET["id_petugas"]) $idPetugas = $_GET["id_petugas"];
     if($_GET["tanggal_awal"]) $tglAwal = format_date($_GET["tanggal_awal"]);
     if($_GET["tanggal_akhir"]) $tglAkhir = $_GET["tanggal_akhir"];
   //  if($_GET["id_dep"]) $idDep = $_GET["id_dep"];
    // if($_GET["penjualan_tipe"]) $penjualanTipe = $_GET["penjualan_tipe"];
     
     $sql_where[] = "a.penjualan_create >= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));
     $sql_where[] = "a.penjualan_create <= ".QuoteValue(DPE_DATE,DateAdd(date_db($_GET["tanggal_akhir"]),1));
     if ($_GET["id_petugas"]<> "--") $sql_where[] = "a.id_petugas = ".QuoteValue(DPE_NUMERIC,$_GET["id_petugas"]);
     if ($_GET["id_dep"]<> "--") $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$_GET["id_dep"]);
     if ($_GET["penjualan_tipe"]<> "--") $sql_where[] = "a.penjualan_tipe = ".QuoteValue(DPE_CHAR,$_GET["penjualan_tipe"]);
     
     $penjualanTipe=$_GET["penjualan_tipe"];
     $penjualanPetugas=$_GET["id_petugas"];
     $penjualanOutlet=$_GET["id_dep"];
     
     $sql_where = implode(" and ",$sql_where);
       
     $sql = "select a.penjualan_create, a.penjualan_nomer,a.penjualan_petugas,
             a.penjualan_tipe, a.penjualan_total, a.penjualan_ppn,a.penjualan_customer,a.id_petugas,b.dep_nama    
               from pos.pos_penjualan a left join global.global_departemen b on a.id_dep=b.dep_id";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.penjualan_create asc";
     $rs = $dtaccess->Execute($sql);
     $dataCashflow = $dtaccess->FetchAll($rs);
     
     
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<TITLE> New Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
</head>

<body>
<table  width="100%" border="0" cellpadding="0" cellspacing="0">     
	<tr>    
		<td width="15%">&nbsp;Tanggal Awal</td>
          <td width="35%"><?php echo $tglAwal?></td>
    <td width="15%">&nbsp;Tanggal Akhir</td>
          <td width="35%"><?php echo $tglAkhir?></td>
	</tr>
     <tr> 
          <td colspan="4">&nbsp;</td>
	</tr>
</table>

<table  width="100%" border="1" cellpadding="0" cellspacing="1" id="tabReport" style="border-style:solid">
	<tr>
		<td width="2%" align="center"><strong>NO</strong></td>
		<td width="8%" align="center"><strong>Waktu</strong></td>
    <td width="15%" align="center"><strong>No. Nota</strong></td>
    <td width="15%" align="center"><strong>Customer</strong></td>
    <td width="20%" align="center"><strong>Total Pendapatan</strong></td>
    <td width="20%" align="center"><strong>Total Tax</strong></td>
    <td width="20%" align="center"><strong>Outlet</strong></td>
	</tr>	
	    
	<?php for($i=0,$n=count($dataCashflow);$i<$n;$i++){
    $total += $dataCashflow[$i]["penjualan_total"];
    $totalTax += $dataCashflow[$i]["penjualan_ppn"];
  ?>
  <tr>
		<td width="2%" align="center"><?php echo $i+1;?></td>
		<td width="8%" align="center"><?php echo $dataCashflow[$i]["penjualan_create"];?></td>
    <td width="15%" align="center"><?php echo $dataCashflow[$i]["penjualan_nomer"];?></td>
    <td width="15%" align="center"><?php echo $dataCashflow[$i]["penjualan_customer"];?></td>
    <td width="20%" align="right"><?php echo currency_format($dataCashflow[$i]["penjualan_total"]);?></td>
	  <td width="20%" align="right"><?php echo currency_format($dataCashflow[$i]["penjualan_ppn"]);?></td>
	  <td width="20%" align="center"><?php echo $dataCashflow[$i]["dep_nama"];?></td>
  </tr>	      
 <?php } ?>
  <tr>
    <td width="20%" colspan="5" align="right"><strong><?php echo currency_format($total);?></strong></td>
	  <td width="20%" align="right"><strong><?php echo currency_format($totalTax);?></strong></td>
	  <td width="20%" align="right">&nbsp;</td>
  </tr>	      
  <tr>
    <td width="20%" colspan="4" align="right"><strong>TOTAL</strong></td>
	  <td width="20%" align="right"><strong><?php echo currency_format($total+$totalTax);?></strong></td>
	  <td width="20%" align="right">&nbsp;</td>
	  <td width="20%" align="right">&nbsp;</td>
  </tr>	
          
         
    </table>
</body>
</html>
