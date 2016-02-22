<?php
function currency_format($nilai,$def_comma=0) {
  return number_format($nilai, $def_comma, '.', ',');
}

function StripCurrency($nilai){
	return str_replace(",","",$nilai);
}

function Pangkat($base, $pangkat) {
	$jum = 1;
	for($i = 1;$i <= $pangkat;$i++) {
		$jum = $jum * $base;
	}

	return $jum;
}

function HasilHuruf($nilai) 
{
	$arnilai = array(12=>"trilyun", 11=>"", 10=>"", 9=>"milyar", 8=>"", 7=>"", 6=>"juta", 5=>"", 4=>"", 3=>"ribu", 2=>"", 1=>"", 0=>"");
	$arsatuan = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh");
	$stringer = "";

	for( $k = 12; $k >= 0; $k-- ) {
		if(($nilai / pangkat(10, $k) ) >= 1) {
			$sisa = floor($nilai / pangkat(10, $k));
			$sisa_k_ini = $sisa;

			if(($sisa / 100) >= 1) {	
				if(floor($sisa / 100) == 1) {
					$stringer = $stringer." ".seratus;
				} else {
					$stringer = $stringer." ".$arsatuan[floor($sisa / 100)]." ratus ";
				}
				$sisa = $sisa - (floor($sisa / 100) * 100);
			}

			if(($sisa / 10) >= 1) {	
				if(($sisa > 10) && ($sisa < 20)) {
					if($sisa == 11) {
						$stringer = $stringer." "."sebelas ";
					} else {			
						$stringer = $stringer." ".$arsatuan[floor(($sisa)-10)]." belas ";
					}
					$sisa = 0;
				} else if(floor($sisa / 10) == 1) {
					$stringer = $stringer." "."sepuluh ";
					$sisa = $sisa - (floor($sisa / 10) * 10);
				} else {
					$stringer = $stringer." ".$arsatuan[floor($sisa / 10)]." puluh ";
					$sisa = $sisa - (floor($sisa / 10) * 10);
				}
			}

			if(($sisa / 1) >= 1) {	
				if (($k==3) && ($sisa == 1) && ($sisa_k_ini == 1)) {
					$stringer = $stringer." se";
				} else {
					$stringer = $stringer." ".$arsatuan[floor($sisa / 1)]." ";
				}
				
				$sisa = $sisa - (floor($sisa / 1) * 1);
			}

			$stringer .= $arnilai[$k];
			$nilai = $nilai - (floor($nilai / pangkat(10, $k)) * pangkat(10, $k));
		}
		
		$k -= 2;
	} 

	$stringer.= " rupiah";
	$arstringer = explode(" ", $stringer);
	$stringer = "";
	
	for($i = 0; $i < count($arstringer); $i++) {
		$arstringer[$i] = trim($arstringer[$i]);
		if(strlen($arstringer[$i]) >= 1) 
			$stringer .= $arstringer[$i]." ";
	}

	$stringer[0] = strtoupper($stringer[0]);

	return $stringer;
}

?>
