<?php

function compare_array($needle, & $haystack) {
     foreach($haystack as $key => $value){
          if($value == $needle){
               unset($haystack[$key]);
               $dapet = 1;
               break;
          }
     }
     return ($dapet==1) ? true:false;
}


?>
