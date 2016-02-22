<?php
require_once("root.inc.php");
require_once($ROOT."library/crack.cls.php");

  function setbit($val, $bit) {
     if (readbit($val, $bit)) return $val;
     return $val += '0x'.dechex(1<<($bit-1));
  }

  function clearbit($val, $bit) {
     if (!readbit($val, $bit)) return $val;
     return $val^(0+('0x'.dechex(1<<($bit-1))));
  }

  function readbit($val, $bit) {
     return ($val&(0+('0x'.dechex(1<<($bit-1)))))?'1':'0';
  }

  function debug($var, $bitlength=32) {
     for ($j=$bitlength;$j>0;$j--) {
        echo readbit($var, $j);
        if ($j%4 == 1) echo ' ';
     }
  }
  
     function HitungKPK($items){
         //Input: An Array of numbers
         //Output: The LCM of the numbers
         while(2 <= count($items)){
             array_push($items, lcm(array_shift($items), array_shift($items)));
         }
         return reset($items);
     }
     
     //His Code below with $'s added for vars
     
     function gcd($n, $m) {
        $n=abs($n); $m=abs($m);
        if ($n==0 and $m==0)
            return 1; //avoid infinite recursion
        if ($n==$m and $n>=1)
            return $n;
        return $m<$n?gcd($n-$m,$n):gcd($n,$m-$n);
     }
     
     function lcm($n, $m) {
        return $m * ($n/gcd($n,$m));
     }
  
?>
