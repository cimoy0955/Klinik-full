<?php
/**
* kumpulan class untuk keperluan view
* @author Agustinus Winata <winata@inosoftweb.com>
* @copyright Inosoft Trans Sistem 2007
* @package ViewClass
*/
require_once("root.inc.php");
require_once($ROOT."library/dateFunc.lib.php");
require_once($APLICATION_ROOT."library/config/global.cfg.php");


DEFINE("BTN_SUBMIT", "1");
DEFINE("BTN_BUTTON", "2");
DEFINE("BTN_RESET", "3");

DEFINE("INP_CURRENCY", "1");
DEFINE("INP_NUMERIC", "2");
DEFINE("INP_NONE", false);

DEFINE("TABLE_ISI","ISI");
DEFINE("TABLE_WIDTH","WIDTH");
DEFINE("TABLE_COLSPAN","COLSPAN");
DEFINE("TABLE_ROWSPAN","ROWSPAN");
DEFINE("TABLE_NOWRAP","NOWRAP");
DEFINE("TABLE_ALIGN","ALIGN");
DEFINE("TABLE_CLASS","CLASS");
DEFINE("TABLE_VALIGN","VALIGN");
DEFINE("TABLE_ID","ID");

$pagingMax = 10;


/**
* Constucts a new View object
* @param string $in_page default isikan dengan $_SERVER['PHP_SELF']
* @param string $in_qstring default isikan dengan $_SERVER['QUERY_STRING']
*/

class CView
{
    var $_page;
    var $_queryString;
    var $_datamodel;
	var $_newQString;

    /**
     * class constructor
     */
    function CView(& $in_page, $in_qstring = null)
    {
        $this->_page = & $in_page;
        $this->_queryString = $in_qstring;

		$newParams = array();
        if($in_qstring){
            $params = explode("&", $in_qstring);
            foreach ($params as $param) {
                if (stristr($param, "currentPage") == false && stristr($param, "recPerPage") == false) {
                    array_push($newParams, $param);
                }
            }
        }

        if (count($newParams) != 0)
            $this->_newQString = "&" . implode("&", $newParams);
    }

    /**
     * function to display paging
     * @access public
     */
    function RenderPaging($in_totRecord,$in_recPerPage, $in_currPage = 1)
    {
        global $pagingMax;
        $selisih = 3;

       
        if ($in_currPage == 1) $strPaging .= " First Prev ";
        else {
            $strPaging .= "<A HREF=\"".$this->_page."?currentPage="."1"."&recPerPage=".$in_recPerPage.$this->_newQString."\">First</A>\n";
            $strPaging .=  "<A HREF=\"".$this->_page."?currentPage=".($in_currPage - 1)."&recPerPage=".$in_recPerPage.$this->_newQString."\">Prev</A>\n";
        }
        $strPaging .= " | \n";
    
        if (strtoupper($in_recPerPage) != "ALL") $pagingNum = ceil($in_totRecord/$in_recPerPage);
        else  $pagingNum = 1;

        if($pagingNum <= $pagingMax) $atas = 1;
        else{
            $sisa = $in_currPage + $selisih; 
            $sisa2 = $sisa - $pagingMax;
            if($sisa2 <= 0) $atas = 1;
            elseif($sisa<=$pagingNum) $atas = $sisa2 + 1;   
            else $atas = $pagingNum - $pagingMax + 1;
        }

        if($pagingNum <= $pagingMax)  $bawah = $pagingNum;
        else $bawah=($atas+$pagingMax-1);
    
        for ($i=$atas; $i<=$bawah; $i++) {
            if ($i == $in_currPage) $strPaging .= $i." \n"; 
            else $strPaging .= "<A HREF=\"".$this->_page."?currentPage=".$i."&recPerPage=".$in_recPerPage.$this->_newQString."\">$i</A> \n" ;
        }
        $strPaging .= " | \n";
        
        if ($in_currPage == $pagingNum || $in_totRecord == 0) $strPaging .= "Next Last\n"; 
        else {
            $strPaging .= "<A HREF=\"".$this->_page."?currentPage=".($in_currPage + 1)."&recPerPage=".$in_recPerPage.$this->_newQString."\">Next</A>\n";
            $strPaging .= "<A HREF=\"".$this->_page."?currentPage=".$pagingNum."&recPerPage=".$in_recPerPage.$this->_newQString."\">Last</A>\n";
        }

        return $strPaging;
    }

    /**
     * function to display search widgets
	 * @param in_type enum {SCR_EXACT_PHRASE,SCR_ALL_WORDS,SCR_ANY_WORDS)
	 * @return html table 
     * @access public
     */
    function RenderSearch($in_type = null)
    {
        // --- penggunaan widget buat ngegenerate form search --- //

		if($in_type)
			$strHidSearch = '<input type="hidden" name="hidSearch" value="'.$in_type.'">';


        $strCmdSearch = '<input type="submit" name="btnSearch" class="button" value="Search(*)">';

        $strTxtSearch = '<input type="text" name="txtSearch"';
        if($_GET["txtSearch"]) $strTxtSearch .= ' value="'.$_GET["txtSearch"].'"';
        $strTxtSearch .= '>';
         
        $strRadSearch = '<input type="radio" name="rdSearch" id="rd1" value="'.SCR_EXACT_PHRASE.'" onClick="this.form.submit();"';
        if($_GET["rdSearch"]=="Exact Phase") $strRadSearch .= ' checked';
        $strRadSearch .= '><label for="rd1">Exact Phase</label>&nbsp';

        $strRadSearch .= '<input type="radio" name="rdSearch" id="rd2" value="'.SCR_ALL_WORDS.'" onClick="this.form.submit();"';
        if($_GET["rdSearch"]=="All Words") $strRadSearch .= ' checked';
        $strRadSearch .= '><label for="rd2">All Words</label>&nbsp';
        
        $strRadSearch .= '<input type="radio" name="rdSearch" id="rd3" value="'.SCR_ANY_WORDS.'" onClick="this.form.submit();"';
        if($_GET["rdSearch"]=="Any Words" || !$_GET["rdSearch"]) $strRadSearch .= ' checked';
        $strRadSearch .= '><label for="rd3">Any Words</label>&nbsp';
        
        $linkResetSearch = "<a href=\"".$this->_page."?".$in_type."\">Show All</a>";

        $strSearch  = "<form action=\"".$this->_page."?".$this->_newQString."\" method=\"get\" name=\"frm_search\">\n"; 
        $strSearch .= "<table width=\"75%\" border=0 cellspacing=0 cellpadding=0 class=\"table_master\">\n";
        $strSearch .= "<tr><td>\n&nbsp;";
        $strSearch .= $strTxtSearch;
        $strSearch .= "&nbsp;&nbsp;";
        $strSearch .= $strCmdSearch;
        $strSearch .= "&nbsp;&nbsp;";
        $strSearch .= $linkResetSearch;
        $strSearch .= "\n";
        $strSearch .= "</td></tr>\n";
        $strSearch .= "<tr><td>\n&nbsp;";
        $strSearch .= $strRadSearch;
        $strSearch .= "\n";
        $strSearch .= "</td></tr>\n";
        if($in_type)  $strSearch .= $strHidSearch;
        $strSearch .= "</form>\n";

        return $strSearch;
    }

    /**
     * function to display calendar
	 * @param objEvent array contain event information
	 * return calendar 
     * @access public
     */
	function RenderCalendar($in_month,$in_year,$objEvent=null,$in_href=null)
	{
		global $monthName,$APLICATION_ROOT;


		// bagian fungsi2 ---
		$day_of_wk = date(w, mktime(0, 0, 0, $in_month, 1, $in_year));
		$day_in_mth = date(t, mktime(0, 0, 0, $in_month, 1, $in_year)) ;
		$day_text = date(D, mktime(0, 0, 0, $in_month, 1, $in_year));

		$tglSkr = date("d");
		$blnSkr = date("n");
		$thnSkr = date("Y");

		$yb=$in_year;
		$yf=$in_year;
		$mb=$in_month-1;
		if ($mb<1) {
			$mb=12; 
			$yb=$yb-1;
		}
		$mf=$in_month+1;
		if ($mf>12) {
			$mf=1; 
			$yf=$yf+1;
		}
//		if ($in_month<10) $in_month = "0".$in_month;


		// --- buat headernya calendar bentuknya << NamaBulan Tahun >> ----
		$strCal  = '<table  border="0" cellpadding="0" cellspacing="1" width="100%">';
        $strCal .= "\n";
		$strCal .= '<tr><td colspan=7 class="tableheader" align="center">';				
        $strCal .= "\n";
		$strCal .= '<a title="prev month" href="'.$this->_page.'?'.$this->_newQString.'&bulan='.($in_month-1).'&tahun'.($in_year-1).'">&lt;&lt;</a>';				
        $strCal .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font size=1>'.$monthName[$in_month].' '.$in_year.'</font>';
        $strCal .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a title="next month" href="'.$this->_page.'?'.$this->_newQString.'&bulan='.($in_month+1).'&tahun'.($in_year+1).'">&gt;&gt;</a>';
        $strCal .= "\n";
		$strCal .= '</td></tr>';				
        $strCal .= "\n";

		// --- buat harinya gt ----
		$strCal .= '<tr class="subheader">';			
        $strCal .= "\n";
		$strCal .= '<td width="24" align="center"><font size="1" color="red">Sun</font></td>';			
		$strCal .= '<td width="24" align="center"><font size="1">Mon</font></td>';			
		$strCal .= '<td width="24" align="center"><font size="1">Tue</font></td>';			
		$strCal .= '<td width="24" align="center"><font size="1">Wed</font></td>';			
		$strCal .= '<td width="24" align="center"><font size="1">Thu</font></td>';			
		$strCal .= '<td width="24" align="center"><font size="1">Fri</font></td>';			
		$strCal .= '<td width="24" align="center"><font size="1">Sat</font></td>';			
        $strCal .= "\n";
		$strCal .= '</tr>';			
        $strCal .= "\n";
			
		
		// --- buat isine gt ----
		$strCal .= '<tr>';			
		if ($day_of_wk <> 0) {
			for ($i=0; $i<$day_of_wk; $i++) 
				$strCal .= '<td class=tablecontent>&nbsp;</td>';			
		}

		for ($date_of_mth = 1,$countEvent=0; $date_of_mth <= $day_in_mth; $date_of_mth++) {
			
			if ($day_of_wk = 0) {
				for ($i=0; $i<$day_of_wk; $i++); {
					$strCal .= '</tr>';			
					$strCal .= "\n";
				}
			}

			$day_text = date(D, mktime(0, 0, 0, $in_month, $date_of_mth, $in_year));
			$date_no = date(j, mktime(0, 0, 0, $in_month, $date_of_mth, $in_year));

			if ($date_no<10) $date_no = "0".$date_no;
			$day_of_wk = date(w, mktime(0, 0, 0, $in_month, $date_of_mth, $in_year));

			if($date_no == $objEvent[$countEvent]["tanggal"]){
				$warna = $objEvent[$countEvent]["color"];
				$class = "tablecontent-odd";
				$countEvent++;
			} elseif($day_of_wk==0){
				$warna = "red";
				$class = "tablecontent";
			} elseif($date_no==$tglSkr && $in_month==$blnSkr && $in_year==$thnSkr){
				$warna = "blue";
				$class = "tablecontent-odd";
			} else{
				$warna = "#717E0E";
				$class = "tablecontent";
			}
			

			$strCal .= '<td class="'.$class.'" align=center><a title='.$date_no.'-'.$in_month.'-'.$in_year;
			if($in_href) $strCal .= ' href="'.$in_href.'?'.$this->_newQString.'&cal_tgl='.$in_year.'-'.$in_month.'-'.$date_no.'"';
			$strCal .= '><font color="'.$warna.'" size=1>'.$date_no.'</font></a></td>';			

			if ($day_of_wk == 6)  $strCal .= '</tr>';	
							
			if ( $day_of_wk < 6 && $date_of_mth == $day_in_mth ) {
				for ( $i = $day_of_wk ; $i < 6; $i++ ) {
					$strCal .= '<td class=tablecontent>&nbsp;</td>';	
				}
				$strCal .= '</tr>';			
				$strCal .= "\n";
			}
		}
		$strCal .= '<tr>';			
        $strCal .= "\n";
		$strCal .= '<td width="24" align="center"><img src="'.$APLICATION_ROOT.'images/spacer.gif" width="24" height="1"></td>';			
		$strCal .= '<td width="24" align="center"><img src="'.$APLICATION_ROOT.'images/spacer.gif" width="24" height="1"></td>';			
		$strCal .= '<td width="24" align="center"><img src="'.$APLICATION_ROOT.'images/spacer.gif" width="24" height="1"></td>';			
		$strCal .= '<td width="24" align="center"><img src="'.$APLICATION_ROOT.'images/spacer.gif" width="24" height="1"></td>';			
		$strCal .= '<td width="24" align="center"><img src="'.$APLICATION_ROOT.'images/spacer.gif" width="24" height="1"></td>';			
		$strCal .= '<td width="24" align="center"><img src="'.$APLICATION_ROOT.'images/spacer.gif" width="24" height="1"></td>';			
		$strCal .= '<td width="24" align="center"><img src="'.$APLICATION_ROOT.'images/spacer.gif" width="24" height="1"></td>';			
        $strCal .= "\n";
		$strCal .= '</tr>';			
        $strCal .= "\n";
		$strCal .= "</table>";
		return $strCal;
	}
	
    /**
     * function buat bikin tag body
	 * setelah html selese bagian bawahnya harus dipanggil RenderBodyEnd lagi buat nutup
	 * @param string $in_css isinya nama css yang digunakan
	 * @param bolean $isCal isi true klo ada calendar yang digunakan
	 * @return string 
     * @access public
     */
	function RenderBody($in_css,$isCal=false,$in_event=null,$isGrid=false,$in_cssPrint=null)
	{
        global $APLICATION_ROOT,$ROOT;
        $strBody = '<html>'."\n";
        $strBody.= '<head>'."\n";
        $strBody.= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'."\n";
        $strBody.= '<title>.:: '.APP_TITLE.' ::.</title>'."\n";
        $strBody.= '<link href="'.$APLICATION_ROOT.'images/inosoft-icon.ico" rel="Shortcut Icon">'."\n";
        $strBody.= '<link href="'.$APLICATION_ROOT.'library/css/'.$in_css.'" media="screen"  rel="stylesheet" type="text/css">'."\n";

          if($in_cssPrint){
             $strBody.= '<link href="'.$APLICATION_ROOT.'library/css/'.$in_cssPrint.'" media="print"  rel="stylesheet" type="text/css">'."\n";
          }

        $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/elements.js"></script>'."\n";
        $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/ew.js"></script>'."\n";
        $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/func_curr.js"></script>'."\n";
        $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/mask.js"></script>'."\n";
        $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/func_date.js"></script>'."\n";
     

     
        if($isCal){
            $strBody.= '<link rel="stylesheet" type="text/css" media="all" href="'.$ROOT.'library/script/jscalendar/css/calendar-system.css" title="calendar-system" />'."\n";
            $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jscalendar/calendar.js"></script>'."\n";
            $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jscalendar/calendar-setup.js"></script>'."\n";
            $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jscalendar/lang/calendar-en.js"></script>'."\n";
        }
        
        
        if($isGrid){
            $strBody.= '<link rel="stylesheet" type="text/css" media="all" href="'.$ROOT.'library/dhtmlxGrid/css/dhtmlXGrid.css"/>'."\n";
            $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/dhtmlxGrid/js/dhtmlXCommon.js"></script>'."\n";
            $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/dhtmlxGrid/js/dhtmlXGrid.js"></script>'."\n";
            $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/dhtmlxGrid/js/dhtmlXGridCell.js"></script>'."\n";
        }
        
        $strBody.= '</head>'."\n";
        $strBody.= '<body';
        if($in_event) $strBody .= ' '.$in_event;
		$strBody .= '>'."\n";
        return $strBody;
	}
     
     function InitGraph()
     {
          global $ROOT;
          
          $strBody = '<link rel="stylesheet" type="text/css" media="all" href="'.$ROOT.'library/script/jquery/jqchart/canvaschart.css"/>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqchart/jquery.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqchart/json.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqchart/chartplugin.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqchart/excanvas.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqchart/wz_jsgraphics.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqchart/chart.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqchart/canvaschartpainter.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqchart/jgchartpainter.js"></script>'."\n";
          
          return $strBody;
     }
     
     function InitJqueryStd(){
	global $ROOT;
	
	return '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'"library/script/jquery/jquery_std/jquery-1.11.0.min.js></script>'."\n";
     }

     function InitTab()
     {
          global $ROOT;
          
          $strBody = '<link rel="stylesheet" type="text/css" media="all" href="'.$ROOT.'library/script/jquery/jqtab/jquery.tabs.css"/>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqtab/jquery-1.1.3.1.pack.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqtab/jquery.history_remote.pack.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqtab/jquery.tabs.pack.js"></script>'."\n";
          
          return $strBody;
     }

     function InitDom()
     {
          global $ROOT;
          
          $strBody = '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/flydom/jquery-1.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/flydom/jquery.flydom-3.1.1.js"></script>'."\n";
          
          return $strBody;
     }


     function InitUpload()
     {
          global $ROOT;
          
          $strBody = '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/ajaxupload/jquery.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/ajaxupload/ajaxfileupload.js"></script>'."\n";
          
          return $strBody;
     }

     
     function InitTreeTable()
     {
          global $ROOT;
          
          $strBody = '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqtreetable/jquery1131.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/jqtreetable/jQTreeTable.js"></script>'."\n";
          
          return $strBody;
     }
	
     function InitThickBox()
     {
          global $ROOT;
		
          echo '<script language="JavaScript"> var tb_pathToImage = "'.$ROOT.'library/script/jquery/thickbox/loadingAnimation.gif"; </script>'."\n";
          $strBody = '<link rel="stylesheet" type="text/css" media="all" href="'.$ROOT.'library/script/jquery/thickbox/thickbox.css"/>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/thickbox/jquery.js"></script>'."\n";
          $strBody.= '<script language="JavaScript" type="text/javascript" src="'.$ROOT.'library/script/jquery/thickbox/thickbox.js"></script>'."\n";
          
          return $strBody;
     }
	
    /**
     * function buat bikin nutup body
	 * harus dipanggil
     * @access public
     */
	function RenderBodyEnd()
	{
        $strBody = "</body>\n";
        $strBody.= "</html>\n";
        return $strBody;
	}

    
    /**
     * function buat bikin textbox
	 * @param string $in_name nama text box
	 * @param string $in_id id text box sekarang harus diisi
	 * @param string $in_size sizes text box
	 * @param string $in_maxlength maxlength isinya jangan ngawur
	 * @param string $in_value value(isinya textbox) boleh kosong
	 * @param string $in_class class klo ada
	 * @param string $in_show pilihan[null,readonly,disabled]
	 * @param bolean $is_currency isi true klo mo bikin duit jadi koma
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @access public
     */
    function RenderTextBox($in_name,$in_id,$in_size,$in_maxlength,$in_value=null,$in_class=null, $in_show=null,$is_currency=false,$in_event=null)
    {
	    if(!$in_name) return '<font color="red">Missing Name</font>';
	    if(!$in_id) return '<font color="red">Missing Id</font>';

	    if(!$in_size) return '<font color="red">Missing Size</font>';
	    if(!$in_maxlength) return '<font color="red">Missing Maxlength</font>';

        $strInput = '<input name="'.$in_name.'" id="'.$in_id.'" type="text" ';
        $strInput.= ' size="'.$in_size.'" maxlength="'.$in_maxlength.'" value="'.htmlspecialchars($in_value).'" ';
        if($in_class) $strInput .= ' class="'.$in_class.'" ';
        if($in_show) $strInput .= ' '.$in_show;
        if($in_event) $strInput .= ' '.$in_event;
        if($is_currency==INP_CURRENCY) $strInput .= ' onKeyUp="this.value=formatCurrency(this.value);" ';
		elseif($is_currency==INP_NUMERIC) $strInput .= ' OnKeyPress="return anyMask(event, \'#####\',this.value)" ';
        $strInput.= ' onKeyDown="return tabOnEnter(this, event);">'."\n";
        
        return $strInput;
    }

    /**
     * function buat bikin textpassword
	 * @param string $in_name nama text box
	 * @param string $in_id id text box sekarang harus diisi
	 * @param string $in_size sizes text box
	 * @param string $in_maxlength maxlength isinya jangan ngawur
	 * @param string $in_value value(isinya textbox) boleh kosong
	 * @param string $in_class class klo ada
	 * @param string $in_show pilihan[null,readonly,disabled]
	 * @param bolean $is_currency isi true klo mo bikin duit jadi koma
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @access public
     */
    function RenderPassword($in_name,$in_id,$in_size,$in_maxlength,$in_value=null,$in_class=null, $in_show=null,$is_currency=false,$in_event=null)
    {
	    if(!$in_name) return '<font color="red">Missing Name</font>';
	    if(!$in_id) return '<font color="red">Missing Id</font>';

	    if(!$in_size) return '<font color="red">Missing Size</font>';
	    if(!$in_maxlength) return '<font color="red">Missing Maxlength</font>';

        $strInput = '<input name="'.$in_name.'" id="'.$in_id.'" type="password" ';
        $strInput.= ' size="'.$in_size.'" maxlength="'.$in_maxlength.'" value="'.$in_value.'" ';
        if($in_class) $strInput .= ' class="'.$in_class.'" ';
        if($in_show=="readonly" || $in_show=="disabled") $strInput .= ' '.$in_show;
        if($in_event) $strInput .= ' '.$in_event;
        if($is_currency==INP_CURRENCY) $strInput .= ' onKeyUp="this.value=formatCurrency(this.value);" ';
		elseif($is_currency==INP_NUMERIC) $strInput .= ' OnKeyPress="return anyMask(event, \'#####\',this.value)" ';
        $strInput.= ' onKeyDown="return tabOnEnter(this, event);">'."\n";
        
        return $strInput;
    }

    /**
     * function buat bikin textarea
	 * @param string $in_name nama text area
	 * @param string $in_id id text area sekarang harus diisi
	 * @param string $in_rows lebar 
	 * @param string $in_cols tinggi
	 * @param string $in_value value(isinya ) boleh kosong
	 * @param string $in_class class klo ada
	 * @param string $in_show pilihan[null,readonly,disabled]
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @access public
     */
    function RenderTextArea($in_name,$in_id,$in_rows,$in_cols,$in_value=null,$in_class=null,$in_show=null, $in_event=null)
    {
	    if(!$in_name) return '<font color="red">Missing Name</font>';
	    if(!$in_id) return '<font color="red">Missing Id</font>';

 	    if(!$in_cols) return '<font color="red">Missing Cols</font>';
	    if(!$in_rows) return '<font color="red">Missing Rows</font>';

        $strInput = '<textarea name="'.$in_name.'" id="'.$in_id.'"';
        $strInput.= ' rows="'.$in_rows.'" cols="'.$in_cols.'"';
        if($in_class) $strInput .= ' class="'.$in_class.'" ';
        if($in_show=="readonly" || $in_show=="disabled") $strInput .= ' '.$in_show;
        if($in_event) $strInput .= ' '.$in_event;
        $strInput.= '>'.htmlspecialchars($in_value).'</textarea>'."\n";
        return $strInput;
    }

    /**
     * function buat bikin combo box yang select gt
	 * @param string $in_name namanya
	 * @param string $in_id idnya
	 * @param object $in_options isine hasil dari renderoption
	 * @param string $in_class class klo ada
	 * @param string $in_show pilihan[null,readonly,disabled]
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @access public
     */
    function RenderComboBox($in_name,$in_id,$in_options,$in_class=null,$in_show=null,$in_event=null)
    {
	    if(!$in_name) return '<font color="red">Missing Name</font>';
	    if(!$in_id) return '<font color="red">Missing Id</font>';

 	    if(!$in_options) return '<font color="red">Missing Options</font>';

        $strInput = '<select name="'.$in_name.'" id="'.$in_id.'"';
        if($in_class) $strInput .= ' class="'.$in_class.'" ';
        /*if($in_show=="readonly" || $in_show=="disabled" || $in_show=="multiple")*/ $strInput .= ' '.$in_show;
        if($in_event) $strInput .= ' '.$in_event;
        $strInput.= ' onKeyDown="return tabOnEnter(this, event);">'."\n";
        
        for($i=0,$n=count($in_options);$i<$n;$i++){
            $strInput.= $in_options[$i]."\n";
        }
        
        $strInput.= '</select>'."\n";
        return $strInput;
    }
    
    /**
     * function buat bikin isine combo box
	 * @param string $in_id memberikan value dari dari property id pada element option supaya dapat berinteraksi dgn javascript DOM
	 * @param string $in_value nilainya bagian value=""
	 * @param string $in_view yang ditampilkan tulisannya
	 * @param string $in_show pilihan[null,selected]
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @access public
     */
    function RenderOption($in_value,$in_view,$in_show=null,$in_event=null,$in_id=null)
    {
        $strInput = '<option ';
	if($in_id) $strInput .= ' id="'.$in_id.'" ';
	$strInput .= ' value="'.$in_value.'" '.$in_event.' '.$in_show.'>'.htmlspecialchars($in_view).'</option>'."\n";
        return $strInput;
    }
    
    /**
     * function buat bikin checkbox 
	 * @param string $in_name namanya
	 * @param string $in_id idnya
	 * @param string $in_value nilainya bagian value=""
	 * @param string $in_class class klo ada
	 * @param string $in_show pilihan[null,checked,disabled]
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @access public
     */
    function RenderCheckBox($in_name,$in_id,$in_value=null,$in_class=null,$in_show=null, $in_event=null)
    {
	    if(!$in_name) return '<font color="red">Missing Name</font>';
	    if(!$in_id) return '<font color="red">Missing Id</font>';

        $strInput = '<input name="'.$in_name.'" id="'.$in_id.'" type="checkbox" value="'.$in_value.'" ';
        if($in_class) $strInput .= ' class="'.$in_class.'" ';
        if($in_show=="checked" || $in_show=="disabled") $strInput .= ' '.$in_show;
        if($in_event) $strInput .= ' '.$in_event;
        $strInput.= ' onKeyDown="return tabOnEnter(this, event);">'."\n";
        
        return $strInput;
    }

    /**
     * function buat bikin radio 
	 * @param string $in_name namanya
	 * @param string $in_id idnya
	 * @param string $in_value nilainya bagian value=""
	 * @param string $in_class class klo ada
	 * @param string $in_show pilihan[null,checked,disabled]
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @access public
     */
    function RenderRadio($in_name,$in_id,$in_value=null,$in_class=null,$in_show=null, $in_event=null)
    {
	    if(!$in_name) return '<font color="red">Missing Name</font>';
	    if(!$in_id) return '<font color="red">Missing Id</font>';

        $strInput = '<input name="'.$in_name.'" id="'.$in_id.'" type="radio" value="'.$in_value.'" ';
        if($in_class) $strInput .= ' class="'.$in_class.'" ';
        if($in_show=="checked" || $in_show=="disabled") $strInput .= ' '.$in_show;
        if($in_event) $strInput .= ' '.$in_event;
        $strInput.= ' onKeyDown="return tabOnEnter(this, event);">'."\n";
        
        return $strInput;
    }
    
    /**
     * function buat bikin tombol 
	 * @param string $in_tipe pilihan[BTN_SUBMIT,BTN_BUTTON,BTN_RESET]
	 * @param string $in_name namanya
	 * @param string $in_id idnya
	 * @param string $in_value nilainya bagian value=""
	 * @param string $in_class class klo ada
	 * @param string $in_show pilihan[null,readonly,disabled]
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @param string $in_accessKey isi dengan huruf besar semua antara A-Z
	 * @access public
     */
    function RenderButton($in_tipe,$in_name,$in_id,$in_value=null,$in_class=null,$in_show=null, $in_event=null, $in_accessKey=null)
    {
	    if(!$in_name) return '<font color="red">Missing Name</font>';
	    if(!$in_id) return '<font color="red">Missing Id</font>';

        $strInput = '<input name="'.$in_name.'" id="'.$in_id.'"';
        switch($in_tipe){
            case BTN_SUBMIT:
                $strInput .= ' type="submit"';
                break;
            case BTN_BUTTON:
                $strInput .= ' type="button"';
                break;
            case BTN_RESET:
                $strInput .= ' type="reset"';
                break;
        }
        
        if($in_class) $strInput .= ' class="'.$in_class.'" ';
        if($in_show=="readonly" || $in_show=="disabled") $strInput .= ' '.$in_show;
        if($in_event) $strInput .= ' '.$in_event;
        if($in_accessKey) $strInput .= ' accesskey="'.$in_accessKey.'" ';
        $strInput.= ' value="'.$in_value.'">'."\n";

        return $strInput;
    }
    
    /**
     * function buat bikin label 
     * label ini fungsinya klo di klik maka akan focus ke elemen yang ditunjuk di for 
	 * @param string $in_id idnya
	 * @param string $in_for id element yang ditunjuk
	 * @param string $in_value nilainya bagian value=""
	 * @param string $in_class class klo ada
	 * @param string $in_event isi dengan event lengkap mis:onkeyup='refresh'
	 * @param string $in_accessKey isi dengan huruf besar semua antara A-Z
	 * @access public
     */
    function RenderLabel($in_id,$in_for,$in_value=null,$in_class=null, $in_event=null, $in_accessKey=null)
    {
	    if(!$in_id) return '<font color="red">Missing Id</font>';
        $strInput = '<label id="'.$in_id.'" for="'.$in_for.'"';
        if($in_class) $strInput .= ' class="'.$in_class.'" ';
        if($in_event) $strInput .= ' '.$in_event;
        if($in_accessKey) $strInput .= ' accesskey="'.$in_accessKey.'" ';
        $strInput .= '>'.htmlspecialchars($in_value).'</label>'."\n";
        return $strInput;
    }

    /**
     * function buat bikin hidden field 
	 * @param string $in_name namanya
	 * @param string $in_id idnya
	 * @param string $in_value nilainya bagian value=""
	 * @access public
     */
    function RenderHidden($in_name,$in_id,$in_value=null)
    {
	    if(!$in_name) return '<font color="red">Missing Name</font>';
        if(!$in_id) return '<font color="red">Missing Id</font>';
        $strInput = '<input type="hidden" name="'.$in_name.'" id="'.$in_id.'" value="'.$in_value.'">'."\n";
        return $strInput;
    }
    
    /**
     * function buat kasi focus ke suatu elemen 
	 * @param string $in_id idnya elemen yang pengen di set focusnya
	 * @access public
     */
    function SetFocus($in_id)
    {
        $strBody = '<script>document.getElementById("'.$in_id.'").focus();</script>'."\n";
        return $strBody;
    }
    
     function CreatePost($in_data) {
          if($in_data) {
               foreach($in_data as $key=>$value) {
                    $_POST[$key] = $value;
               }
          }
     }
}


class ITableBlock
{
    var $_component = array();
    var $_id;
    var $_width;
    var $_height;
    var $_style;
    var $_class;
    var $_event;

    function Push($in_component)
    {
        for($i=0,$n=count($in_component);$i<$n;$i++)
            $this->_component[] = $in_component[$i];
    }
}

class ITable extends ITableBlock
{
    var $_cellpadding;
    var $_cellspacing;
    var $_align;
    var $_border;
    
    function ITable($in_id,$in_width="100%",$in_height="100%",$in_border=0,$in_cellpadding=0,$in_cellspacing=0,$in_align="center",$in_class=null,$in_style=null,$in_event=null)
    {
        $this->_id = $in_id;
        $this->_border = $in_border;
        $this->_cellpadding = $in_cellpadding;
        $this->_cellspacing = $in_cellspacing;
        $this->_width = $in_width;
        $this->_height= $in_height;
        $this->_align= $in_align;
        $this->_style = $in_style;
        $this->_class = $in_class;
        $this->_event = $in_event;
    }
    
    function Render($depth=0)
    {
        if(!$this->_id) return "Missing Id for TR";
        
        $tab = str_repeat("\t",$depth);
        $str = $tab.'<table id="'.$this->_id.'" width="'.$this->_width.'" border="'.$this->_border.'" align="'.$this->_align.'" cellpadding="'.$this->_cellpadding.'" cellspacing="'.$this->_cellspacing.'"';
        if($this->_height) $str.= ' height="'.$this->_height.'"';
        if($this->_style) $str .= ' style="'.$this->_style.'"';
        if($this->_class) $str .= ' class="'.$this->_class.'"';
        if($this->_event) $str .= ' "'.$this->_event.'"';
        $str.= '>'."\n";

        for($i=0,$n=count($this->_component);$i<$n;$i++){
            $str.= $this->_component[$i]->Render($depth+1)."\n";
        }
        
        $str.= $tab.'</table>';
        return $str;
    }
}

class ITableTR extends ITableBlock
{
    function ITableTR($in_id,$in_class=null,$in_style=null,$in_event=null)
    {
        $this->_id = $in_id;
        $this->_style = $in_style;
        $this->_class = $in_class;
        $this->_event = $in_event;
    }
    
    function Render($depth=0)
    {
        if(!$this->_id) return "Missing Id for TR";
        
        $tab = str_repeat("\t",$depth);
        $str = $tab.'<tr id="'.$this->_id.'"';
        if($this->_style) $str .= ' style="'.$this->_style.'"';
        if($this->_class) $str .= ' class="'.$this->_class.'"';
        if($this->_event) $str .= ' "'.$this->_event.'"';
        $str.= '>'."\n";

        for($i=0,$n=count($this->_component);$i<$n;$i++){
            $str.= $this->_component[$i]->Render($depth+1)."\n";
        }
        
        $str.= $tab.'</tr>';
        return $str;
    }
}

class ITableTD extends ITableBlock
{
    var $_element;
    var $_colspan;
    var $_rowspan;
    var $_align;
    var $_nowrap;
    
    function ITableTD($in_id,$in_width="100%",$in_height=null,$in_colspan=1,$in_rowspan=1,$in_align="left",$in_class=null,$in_nowrap=false,$in_style=null,$in_event=null)
    {
        $this->_id = $in_id;
        $this->_style = $in_style;
        $this->_class = $in_class;
        $this->_event = $in_event;
        $this->_width = $in_width;
        $this->_height = $in_height;
        $this->_colspan = $in_colspan;
        $this->_rowspan = $in_rowspan;
        $this->_align = $in_align;
        $this->_nowrap= $in_nowrap;
    }
    
    function Push($in_component)
    {
        if(is_string($in_component)) $this->_element .= (string)$in_component;
        else $this->_component[] = $in_component;
    }
    
    function Render($depth=0)
    {
        if(!$this->_id) return "Missing Id for TD";
        
        $tab = str_repeat("\t",$depth);
        $str = $tab.'<td id="'.$this->_id.'" width="'.$this->_width.'" colspan="'.$this->_colspan.'" rowspan="'.$this->_rowspan.'" align="'.$this->_align.'"';
        if($this->_height) $str.= ' height="'.$this->_height.'"';
        if($this->_style) $str .= ' style="'.$this->_style.'"';
        if($this->_class) $str .= ' class="'.$this->_class.'"';
        if($this->_event) $str .= ' "'.$this->_event.'"';
        if($this->_nowrap) $str .= ' nowrap';
        $str.= '>';
        
        if($this->_element=="0") $str .= "0";
        elseif($this->_element) $str .= (string)$this->_element;
        elseif(is_object($this->_component)) {
            $str .= "\n";
            for($i=0,$n=count($this->_component);$i<$n;$i++){
                $str.= $this->_component[$i]->Render($depth+1)."\n";
            }
        }        
        
        $str.= $tab.'</td>';
        return $str;
    }
}


class InoTable
{
     var $_id;
     var $_width;
     var $_height;
     var $_cellpadding;
     var $_cellspacing;
     var $_align;
     var $_border;

     function InoTable($in_id,$in_width="100%",$in_align="center",$in_height=null,$in_class=null)
     {
          $this->_id = $in_id;
          $this->_width = $in_width;
          $this->_height = $in_height;
          $this->_class = $in_class;
          $this->_align = $in_align;
          $this->_border = 1;
          $this->_cellpadding = 1;
          $this->_cellspacing =1;
     }
     
     function RenderView($in_header,$in_content,$in_bottom=null)
     {
          if(!$this->_id) return "Missing ID for table";
          
          $tab = "\t";
          $str = '<table id="'.$this->_id.'" width="'.$this->_width.'" border="'.$this->_border.'" align="'.$this->_align.'" cellpadding="'.$this->_cellpadding.'" cellspacing="'.$this->_cellspacing.'"';
          if($this->_height) $str.= ' height="'.$this->_height.'"';
          if($this->_class) $str.= ' class="'.$this->_class.'"';
          $str.= '>'."\n";

          // ---- ini bagian construct header nya ---- //
          for($i=0,$n=count($in_header);$i<$n;$i++){     
               $str .= $tab.'<tr class="subheader" >'."\n";
               for($j=0,$k=count($in_header[$i]);$j<$k;$j++){
                    $str .= $tab.$tab.'<td width="'.$in_header[$i][$j][TABLE_WIDTH].'"';
                    if($in_header[$i][$j][TABLE_COLSPAN]) $str .= ' colspan="'.$in_header[$i][$j][TABLE_COLSPAN].'"';
                    if($in_header[$i][$j][TABLE_ROWSPAN]) $str .= ' rowspan="'.$in_header[$i][$j][TABLE_ROWSPAN].'"';
                    if($in_header[$i][$j][TABLE_NOWRAP]) $str .= ' nowrap';
                    if($in_header[$i][$j][TABLE_ALIGN]) $str .= ' align="'.$in_header[$i][$j][TABLE_ALIGN].'"';
				else $str .= ' align="center"';
                    $str .=  '>'.$in_header[$i][$j][TABLE_ISI].'</td>'."\n";
               }
               $str.= $tab.'</tr>'."\n";
          }

          // ---- ini bagian construct content nya ---- //
          for($i=0,$n=count($in_content);$i<$n;$i++){
               $str .= $tab.'<tr ';
               if($in_content[$i][0][TABLE_ID]) $str .= ' id="'.$in_content[$i][0][TABLE_ID].'"';
               $str .= '>'."\n";
               
               for($j=0,$k=count($in_content[$i]);$j<$k;$j++){
                    if($in_content[$i][$j][TABLE_CLASS]) $class = $in_content[$i][$j][TABLE_CLASS];
                    elseif($i%2==0) $class="tablecontent-odd";
                    else $class="tablecontent";
               
                    $str .= $tab.$tab.'<td align="'.$in_content[$i][$j][TABLE_ALIGN].'" class="'.$class.'" ';
                    if($in_content[$i][$j][TABLE_COLSPAN]) $str .= ' colspan="'.$in_content[$i][$j][TABLE_COLSPAN].'"';
                    if($in_content[$i][$j][TABLE_ROWSPAN]) $str .= ' rowspan="'.$in_content[$i][$j][TABLE_ROWSPAN].'"';
                    if($in_content[$i][$j][TABLE_VALIGN]) $str .= ' valign="'.$in_content[$i][$j][TABLE_VALIGN].'"';
                    if($in_content[$i][$j][TABLE_WIDTH]) $str .= ' width="'.$in_content[$i][$j][TABLE_WIDTH].'"';
                    if($in_content[$i][$j][TABLE_NOWRAP]) $str .= ' nowrap';
                    $str .=  '>'.$in_content[$i][$j][TABLE_ISI].'</td>'."\n";
               }
               $str.= $tab.'</tr>'."\n";
          }

          // ---- ini bagian construct bottom nya ---- //
          for($i=0,$n=count($in_bottom);$i<$n;$i++){     
               $str .= $tab.'<tr class="tablesmallheader">'."\n";
               for($j=0,$k=count($in_bottom[$i]);$j<$k;$j++){
                    $str .= $tab.$tab.'<td align="'.$in_bottom[$i][$j][TABLE_ALIGN].'" width="'.$in_bottom[$i][$j][TABLE_WIDTH].'"';
                    if($in_bottom[$i][$j][TABLE_COLSPAN]) $str .= ' colspan="'.$in_bottom[$i][$j][TABLE_COLSPAN].'"';
                    if($in_bottom[$i][$j][TABLE_ROWSPAN]) $str .= ' rowspan="'.$in_bottom[$i][$j][TABLE_ROWSPAN].'"';
                    if($in_bottom[$i][$j][TABLE_NOWRAP]) $str .= ' nowrap';
                    $str .=  '>'.$in_bottom[$i][$j][TABLE_ISI].'</td>'."\n";
               }
               $str.= $tab.'</tr>'."\n";
          }
          
          $str.= '</table>'."\n";
          
          return $str;
     }
}


?>
