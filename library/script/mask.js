function anyMask(event, sMask) {
	//var sMask = "**?##?####";
	var KeyTyped = String.fromCharCode(getKeyCode(event));
	var targ = getTarget(event);
	
	keyCount = targ.value.length;
	//alert(sMask.charAt(keyCount));
	
	if (sMask.charAt(keyCount) == '*')
 	   	return true;
 
	if (sMask.charAt(keyCount) == KeyTyped)
    	{
		return true;
	}
	
	if ((sMask.charAt(keyCount) == '#') && isNumeric(KeyTyped)) 
	   return true; 
	
	if ((sMask.charAt(keyCount) == 'A') && isAlpha(KeyTyped))
         return true; 
    
      if ((sMask.charAt(keyCount) == '?') && isPunct(KeyTyped))
         return true; 
	if (KeyTyped.charCodeAt(0) < 32) return true;
    
    return false;	   
   
	
}

 function getTarget(e) {
  // IE5
   if (e.srcElement) {
   	return e.srcElement;
   }
    if (e.target) {
   	return e.target;
   }	
 }

  function getKeyCode(e) {
 //IE5
 if (e.srcElement) {
 	return e.keyCode
 }
  // NC5
  if (e.target) {
   return e.which
  }
 }

 function isNumeric(c)
{
	var sNumbers = "01234567890";
	if (sNumbers.indexOf(c) == -1)
		return false;
	else return true;
	
}  

function isAlpha(c)
{
	var lCode = c.charCodeAt(0);
	if (lCode >= 65 && lCode <= 122 )
 	  {	
		return true;
         }
	else 
	return false;
}  

function isPunct(c)
{
	var lCode = c.charCodeAt(0);
	if (lCode >= 32 && lCode <= 47 )
 	  {	
		return true;
         }
	else 
	return false;

}
 
//  End -->
