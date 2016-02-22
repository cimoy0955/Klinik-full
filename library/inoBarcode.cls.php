<?php
require_once("root.inc.php");
require($ROOT."library/barcode/barcode.php");		   
require($ROOT."library/barcode/i25object.php");
require($ROOT."library/barcode/c39object.php");
require($ROOT."library/barcode/c128aobject.php");
require($ROOT."library/barcode/c128bobject.php");
require($ROOT."library/barcode/c128cobject.php"); 
 
define (__TRACE_ENABLED__, false);
define (__DEBUG_ENABLED__, false);

class InoBarcode {
	var $_output;
	var $_type;
	var $_style;
	var $_width;
	var $_height;
	var $_xres;
	var $_font;
	var $_border;
	var $_drawtext;
	var $_stretchtext;
	
	function InoBarcode($el=null) {
		
		$this->_output = ($el["output"]) ? $el["output"] : "png";
		$this->_type = ($el["type"]) ? $el["type"] : "C39";
		$this->_width = ($el["width"]) ? $el["width"] : "200";
		$this->_height = ($el["height"]) ? $el["height"] : "64";
		$this->_xres = ($el["xres"]) ? $el["xres"] : "1";
		$this->_font = ($el["font"]) ? $el["font"] : "1";

		$this->_border = "off";
		$this->_drawtext = "on";
		$this->_stretchtext = "on";
		
		$this->_style  = BCS_ALIGN_CENTER;					       
		$this->_style |= ($this->_output  == "png" ) ? BCS_IMAGE_PNG  : 0; 
		$this->_style |= ($this->_output  == "jpeg") ? BCS_IMAGE_JPEG : 0; 
		$this->_style |= ($this->_border  == "on"  ) ? BCS_BORDER 	  : 0; 
		$this->_style |= ($this->_drawtext== "on"  ) ? BCS_DRAW_TEXT  : 0; 
		$this->_style |= ($this->_stretchtext== "on" ) ? BCS_STRETCH_TEXT  : 0; 
		$this->_style |= ($this->_negative== "on"  ) ? BCS_REVERSE_COLOR  : 0; 

	}
	
	function Render($in_code) {
		global $ROOT;
		if($in_code) return '<img src="'.$ROOT.'library/barcode/image.php?code='.strtoupper($in_code).'&style='.$this->_style.'&type='.$this->_type.'&width='.$this->_width.'&height='.$this->_height.'&xres='.$this->_xres.'&font='.$this->_font.'">';
		else return "Please Provide valid Code ...";
	}
}
?>