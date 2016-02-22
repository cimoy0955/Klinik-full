<?php
function CheckUpload(& $in_files, $in_lokasi, $in_maxSize=null, $in_name=null)
{
    if($in_maxSize && ($in_files["size"] > $in_maxSize))   // -- check size
        return false;

    if($in_name) $destfile = $in_lokasi."/".$in_name;
    else $destfile = $in_lokasi."/".$in_files["name"];

    if (is_uploaded_file($in_files["tmp_name"])) {
		return copy($in_files["tmp_name"], $destfile);
    } else  return false;

}

function InoUpload(& $in_files, $in_lokasi, $in_element_name, $in_maxSize=null,& $in_name, $arr_mime=null) {

     if(!empty($in_files['error'])) {
		switch($in_files['error']) {

			case '1':
				$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case '2':
				$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case '3':
				$error = 'The uploaded file was only partially uploaded';
				break;
			case '4':
				$error = 'No file was uploaded.';
				break;
			case '6':
				$error = 'Missing a temporary folder';
				break;
			case '7':
				$error = 'Failed to write file to disk';
				break;
			case '8':
				$error = 'File upload stopped by extension';
				break;
			case '999':
			default:
				$error = 'No error code avaiable';
		}
	
     }elseif(empty($_FILES[$in_element_name]['tmp_name']) || $_FILES[$in_element_name]['tmp_name'] == 'none') {
		$error = 'No file was uploaded...';
	
     }elseif(!$in_lokasi) {
          $error = 'Missing Location';
	
     }elseif($in_maxSize && ($in_files["size"] > $in_maxSize)) {
          // -- check size
          $error = 'Max File Excedeed';
          
     } else {
          if($arr_mime) {
               if(array_search($in_files["type"],$arr_mime)===false) $error = "Invalid Extensions";
          } 
          
          if(!$error) {
               if($in_name) $destfile = $in_lokasi."/".$in_name;
               else{
                    $pos = strrpos($in_files["name"],".");
                    $in_name = rand().mktime().".".substr($in_files["name"],$pos-strlen($in_files["name"])+1);
                    $destfile = $in_lokasi."/".$in_name;
		}
		chmod($in_files["tmp_name"],0777);
		//move_uploaded_file($in_files["tmp_name"],$destfile);
		copy($in_files["tmp_name"],$destfile);
          }
	}
     
     return $error;
}
?>
