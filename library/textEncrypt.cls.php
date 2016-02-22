<?
require_once("root.inc.php");
require_once($ROOT."library/crack.cls.php");


define("STR_FRONT","ITS");
define("STR_BACK","Winata");

/**
* @desc TextEncrypt :  A simple class to encode & decode given string
* @author : Gerar Gumarang modified by Agustinus Winata
* @copyright Inosoft Trans Sistem 2006
*/
class TextEncrypt
{

    /**
    * variable to salt the front of given string
    * @access private
    */
    var $_prefix;

    /**
    * variable to salt the back of given string
    * @access private
    */
    var $_sufix;

    function TextEncrypt()
    {
        $this->_prefix = STR_FRONT;
        $this->_sufix = STR_BACK;
    }

    /**
    * @return encoding string
    * @param theString :  string to be encrypted
    * @desc encode :  Encode the param string
    * @access public
    */
    function Encode($theString = "")
    {
        $theString = (string)$theString;
        if(!$theString) $theString = " ";
        $b = $this->_prefix.$theString.$this->_sufix;

        /*
        $opt = ord($theString[0]);
        $d = $theString[0];

        for ( $i = 0; $i < strlen($b); $i++ ) {
            $tmp = $tmp.chr(ord($b[$i])+$opt);
        } $tmp = $tmp.$d;
*/
        return base64_encode($b);
    }


    /**
    * @return decoding string
    * @param theString :  string to be decoded
    * @desc decode :  Decode the param string
    * @access public
    */
    function Decode($theString = "")
    {

        $theString = base64_decode($theString);
        
        if($err = $this->_CheckSalt($theString)){
            $a = strlen($this->_prefix);
            $c = strlen($this->_sufix);
            $b = strlen($theString) - ($a + $c);

            $d = substr($theString,$a ,$b);

            $tmp = $d;
            if($tmp == " ") $tmp = "";
            return $tmp;
        }
    }        

    /**
    * @return bolean
    * @param theString :  string to be checked
    * @desc _CheckSalt :  check encription pattern
    * @access private
    */
    function _CheckSalt($theString = "")
    {
        $a = strlen($this->_prefix);
        $c = strlen($this->_sufix);
        $prefix = substr($theString,0,$a);
        $sufix = substr($theString,($c*-1));
        
        if(strcmp($prefix,$this->_prefix)!=0)  
            die ("Error Code 100p!! Contact Your Administrator");
        elseif(strcmp($sufix,$this->_sufix)!=0)  
            die ("Error Code 100s!! Contact Your Administrator");
        else return true;
        
    }

    /**
    * @return string
    * @access private
    */
    function _GetOriChar($in_char,$in_opt)
    {
        for ($i = 0; $i < strlen($in_char); $i++) {
            $tmp = $tmp.chr(ord($in_char[$i]) - $in_opt);
        } return $tmp;
    }
    
    function FileEncrypt($src_file,$dest_file)
    {
        $fd = fopen ($src_file, "r");
        $data = fread ($fd, filesize ($src_file));
        fclose ($fd);
        $gz_data = gzcompress($data);
        $fp = fopen($dest_file, "w+");
        fwrite($fp, $gz_data);
        fclose($fp);
    }
}
//user:sr280t3cwNLXyt3KaQ==
//pass:sr280t3c3dHY1MDS18rdymk=
//db:rbi3yNPP2MnWu83SxdjFZA==

/*$enc = new TextEncrypt();
$tes = $enc->Encode("n");
echo $tes."<br>";
$tes2 = $enc->Decode($tes);
echo $tes2."<br>";
*/
?>
