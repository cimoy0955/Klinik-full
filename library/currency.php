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

function Eja($x,$curr) {
     $abil = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
     
     if(!$x && !$curr) return "";
     elseif(!$x && $curr) return "";
     elseif ($x < 12)
          return " " . $abil[$x];
     elseif ($x < 20)
          return Eja($x - 10,$curr) . " belas";
     elseif ($x < 100)
          return Eja($x / 10,$curr) . " puluh" . Eja($x % 10,$curr);
     elseif ($x < 200)
          return " seratus" . Eja($x - 100,$curr);
     elseif ($x < 1000)
          return Eja($x / 100,$curr) . " ratus" . Eja($x % 100,$curr);
     elseif ($x < 2000)
          return " seribu" . Eja($x - 1000,$curr);
     elseif ($x < 1000000)
          return Eja($x / 1000,$curr) . " ribu" . Eja($x % 1000,$curr);
     elseif ($x < 1000000000)
          return Eja($x / 1000000,$curr) . " juta" . Eja($x % 1000000,$curr);
     elseif ($x < 1000000000000)
          return Eja($x / 1000000000,$curr) . " milyar" . Eja(fmod($x,1000000000),$curr);
     elseif ($x < 1000000000000000)
          return Eja($x / 1000000000000,$curr) . " trilyun" . Eja(fmod($x,1000000000000),$curr);
}

function Terbilang($nilai,$curr=false,  $style=3) {

     if($nilai > 99999999999999) return "Overflow";
     
     $tempNilai = explode(".",$nilai);
          
     if(count($tempNilai)>2) return "Not Format Well..";
     
     $str[0] = Eja($tempNilai[0],$curr);

     for($i=0,$n=strlen($tempNilai[1]);$i<$n;$i++) $koma[$i] = Eja($tempNilai[1]{$i},$curr);
     
     if($koma) $str[1] = implode(" ",$koma);
     $hasil = ($str[1]) ? implode(" koma ",$str) : $str[0];
    
    switch ($style) {
  
              case 1:
  
                  $hasil = strtoupper($hasil);
  
                  break;
  
              case 2:
  
                  $hasil = strtolower($hasil);
  
                  break;
  
              case 3:
 
                  $hasil = ucwords($hasil);
  
                  break;
              
  
              default:
                  $hasil = ucfirst($hasil);
  
                  break;
  
          }
     
     return trim($hasil);
}

?>
