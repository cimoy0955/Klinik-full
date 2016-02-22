<?php

$monthName = array("","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September",
				"Oktober","Nopember","Desember");

$monthDay = array("","31","28","31","30","31","30","31","31","30",
				"31","30","31");

$monthNameShort = array("","Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agt","Sep",
				"Okt","Nop","Des");

$dayName = array("Minggu","Senin","Selasa","Rabu","Kamis", "Jumat", "Sabtu");

$formatCal = "%d-%m-%Y";

$monthRomawi = array("","I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");


function TimeDiff($start_time, $end_time)
{
	$start = explode(":",$start_time);
	$end = explode(":",$end_time);

	$timeStart = mktime($start[0],$start[1],$start[2]);
	$timeEnd = mktime($end[0],$end[1],$end[2]);
	return ($timeEnd-$timeStart);
}


function getdateToday() 
{
    $_today = getdate();
    return($_today["year"]."-".str_pad($_today["mon"], 2, "0", STR_PAD_LEFT)."-".str_pad($_today["mday"], 2, "0", STR_PAD_LEFT));
}

function getMonth($_next=0,$_type="text") {
    global $monthName;
    $_today = getdate();
    $_mon = $_today["mon"] + $_next;
    if ($_mon > 12) {
        $_mon -= 12;
    }
    if ($_type == "text")
        return $monthName[$_mon];
    else if ($_type == "value")
        return $_mon; 
}

function getYear($_next=0) {
    $_today = getdate(); 
    $_mon = $_today["mon"] + $_next;
 
    if ($_mon > 12) {
        return $_today["year"]+1;    
    } else {
        return $_today["year"];
    }    
}

/*
 * @param _date_ string format yyyy-mm-dd
 */
function format_date($_date_) {
    if ($_date_) {
        list ($_year_, $_month_, $_day_,) = split ('-', $_date_);
        return $_day_."-".$_month_."-".$_year_;
    } else {
        return "";
    }
}


/*
 * @param _date_ string format yyyy-mm-dd
 * @return date long 7 juni 2006
 */
function format_date_long($_date_) {
    global $monthName;
    if ($_date_) {
        list ($_year_, $_month_, $_day_,) = split ('-', $_date_);
        return $_day_." ".$monthName[intval($_month_)]." ".$_year_;
    } else {
        return "";
    }
}

/*
 * @param _date_ string format yyyy-mm-dd
 */
function view_date($_date_){
    global $monthName;
    if($_date_){
        list ($_year_, $_month_, $_day_,) = split ('-', $_date_);
        $tmpDate = $_day_." ".$monthName[intval($_month_)]." ".$_year_;
        return $tmpDate;
    }
}

/*
 * @param _date_ string format mm-dd-yyyy
 * @return format yyyy-mm-dd
 */
function date_db($_date_) {
    if ($_date_) {
        list ($_day_, $_month_, $_year_,) = split ('-', $_date_);
        return $_year_."-".$_month_."-".$_day_;
    } else {
        return "";
    }
}

/*
 * @param date(yyyy-mm-dd)
 * @return bool check date is valid
 */
function check_date($_date_) {
    if ($_date_) {
        list($_year_,$_month_,$_day_) = split('-',$_date_);
		if(!$_year_ || (strlen($_year_)!=4)) return false;
		if(!$_month_ || (strlen($_month_)>2)) return false;
		if(!$_day_ || (strlen($_day_)>2)) return false;

        return checkdate($_month_,$_day_,$_year_);     
    } else {
        return false;
    }
}

function checkDateRange($_begin, $_end) {
    if ($_begin && $_end) {
        list($_from["month"],$_from["day"],$_from["year"],) = split('-',$_begin);
        list($_to["month"],$_to["day"],$_to["year"],) = split('-',$_end);
        
        if (checkdate(settype($_from["month"],"int"), settype($_from["day"],"int"), settype($_from["year"],"int")) && 
            checkdate(settype($_to["month"],"int"), settype($_to["day"],"int"), settype($_to["year"],"int"))) {
            $_from_stamp = mktime(0,0,0,$_from["month"],$_from["day"],$_from["year"]);
            $_to_stamp = mktime(0,0,0,$_to["month"],$_to["day"],$_to["year"]);
            if ($_from_stamp < $_to_stamp) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function checkMasterDateRange($_mbegin,$_mend,$_begin,$_end) {
    list($_mfrom["month"],$_mfrom["day"],$_mfrom["year"],) = split('-',$_mbegin);
    list($_mto["month"],$_mto["day"],$_mto["year"],) = split('-',$_mend);
    list($_from["month"],$_from["day"],$_from["year"],) = split('-',$_begin);
    list($_to["month"],$_to["day"],$_to["year"],) = split('-',$_end);
    if (checkdate(settype($_mfrom["month"],"int"), settype($_mfrom["day"],"int"), settype($_mfrom["year"],"int")) && 
        checkdate(settype($_mto["month"],"int"), settype($_mto["day"],"int"), settype($_mto["year"],"int")) && 
        checkdate(settype($_from["month"],"int"), settype($_from["day"],"int"), settype($_from["year"],"int")) && 
        checkdate(settype($_to["month"],"int"), settype($_to["day"],"int"), settype($_to["year"],"int"))) {
        
        $_mfrom_stamp = mktime(0,0,0,$_mfrom["month"],$_mfrom["day"],$_mfrom["year"]);
        $_mto_stamp = mktime(0,0,0,$_mto["month"],$_mto["day"],$_mto["year"]);
        $_from_stamp = mktime(0,0,0,$_from["month"],$_from["day"],$_from["year"]);
        $_to_stamp = mktime(0,0,0,$_to["month"],$_to["day"],$_to["year"]);
        
        if (($_from_stamp >= $_mfrom_stamp) && ($_from_stamp < $_mto_stamp) 
            && ($_to_stamp > $_mfrom_stamp) && ($_to_stamp <= $_mto_stamp)) {
            return true;     
        } else {
            return false; 
        }                  
    } else {
        return false;
    }
}

// fungsi perbandingan waktu skr =>  input(dd,mm,yyy) < finput(dd,mm,yyy)
// usage IsDateLessThen(dd, mm, yyyy,dd, mm, yyyy)
function IsDateMoreThen($in_day,$in_month,$in_year,$f_day,$f_month,$f_year)
{
    $tanggal=date("U",mktime(0,0,0,$in_month,$in_day,$in_year));
    $future=date("U",mktime(0,0,0,$f_month,$f_day,$f_year));

    if ($future > $tanggal)
        return true;
    else
        return false;
}

function TimestampDiff($in_start, $in_end)
{
    $in_start = explode(" ",$in_start);
    $start_time = explode(":",$in_start[0]);
    $start_date = explode("-",$in_start[1]);


    $in_end = explode(" ",$in_end);
    $end_time = explode(":",$in_end[0]);
    $end_date = explode("-",$in_end[1]);

    $tanggal=date("U",mktime($start_time[0],$start_time[1],$start_time[2],$start_date[1],$start_date[2],$start_date[0]));
    $future=date("U",mktime($end_time[0],$end_time[1],$end_time[2],$end_date[1],$end_date[2],$end_date[0]));

    return ($future - $tanggal);
}

// -- param date yyyy-mm-dd
function DateDiff($start_date, $end_date)
{
   return floor((strtotime($end_date) - strtotime($start_date))/86400);
}

// -- param date yyyy-mm-dd
function HitungUmur($tgllahir)
{
   return floor((strtotime(date("Y-m-d")) - strtotime($tgllahir))/86400/365);
}

// -- param date yyyy-mm-dd
function DateDiffYear($start_date, $end_date)
{
   return floor((strtotime($end_date) - strtotime($start_date))/86400/365);
}

function GetDayName($in_tgl)
{
	global $dayName;
	$tanggal = explode("-",$in_tgl);
	$hari =  date("w",mktime(0,0,0,$tanggal[1],$tanggal[2],$tanggal[0]));
	return $dayName[$hari];
}

function GetDay($in_tgl)
{
	$tanggal = explode("-",$in_tgl);
	$hari =  date("w",mktime(0,0,0,$tanggal[1],$tanggal[2],$tanggal[0]));
	return $hari;
}


function DateAdd($in_tgl,$jumlah)
{
	$tanggal = explode("-",$in_tgl);
	return date("Y-m-d",mktime(0,0,0,$tanggal[1],$tanggal[2]+$jumlah,$tanggal[0]));
}

// -- param timestamp(Y-m-d H:i:s)
// -- output date(d-m-Y)
function FormatFromTimeStamp($in_tgl){
	$ts = explode(" ",$in_tgl);
	list ($_year_, $_month_, $_day_,) = split ('-', $ts[0]);
        return $_day_."-".$_month_."-".$_year_;
}
?>
