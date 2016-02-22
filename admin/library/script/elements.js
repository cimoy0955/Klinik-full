// JavaScript Document
function setPointer(baris,tipe,bgcol){
	if(tipe=='over') baris.bgColor='#FFFFDD';
	if(tipe=='out')	baris.bgColor=bgcol;
}

function cetakHalaman() {
	print();
	//history.back();
}

function flattenstring(strsource)
{
  dest = "";
  var listar = strsource.split(",");
  strsource = listar[0];
  for(var k=0;k<strsource.length;k+=1)
  	{  
	  if((strsource.charAt(k) >= '0') && (strsource.charAt(k) <= '9'))
	    {
		  dest += strsource.charAt(k);	  
		}
	}
  return dest;
}

function selectstring(source)
{

  source.select();
  return true;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  
  
  if(!d) 
	  d=document; 
  
  if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; 
	n=n.substring(0,p);
  }
  
  if(!(x=d[n])&&d.all) 
	  x=d.all[n]; 
  
  for (i=0;!x&&i<d.forms.length;i++) 
	  x=d.forms[i][n];
  
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) 
	  x=MM_findObj(n,d.layers[i].document);
  
  if(!x && d.getElementById) 
	  x=d.getElementById(n); 
  
  return x;
}

function getNextElement(field) 
{
    var frm = field.form;
    var bIs = false;
    
    
	if (field == frm[frm.length -1]) return field;
    for (var e=0;e<frm.length;e++)
    {
      if (frm.elements[e].type!="hidden" && bIs && (frm.elements[e].style.visibility != "hidden") && (!frm.elements[e].readOnly)) return frm.elements[e];
	  if(e==frm.length-1) return field;
      if (field==frm[e] && !bIs) bIs=true;
    }
}

function getNextElement_with_button(field) 
{
    var frm = field.form;
    var bIs = false;
	if (field == frm[frm.length -1]) return field;
    for (var e=0;e<frm.length;e++)
    {
      if ((frm[e].type!="hidden" && bIs) && (frm[e].style.visibility != "hidden")) return frm.elements[e];
	  if(e==frm.length-1) return field;
      if (field==frm[e]) bIs=true;
    }
}

function tabOnEnter (field, evt) {

    var keyCode = document.layers ? evt.which : document.all ? 
    evt.keyCode : evt.keyCode;
   if (keyCode != 13) return true;
    else {
        getNextElement(field).focus();
        return false;
    }
}

function tabOnEnter_select (field, evt) {
    var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;

    if (keyCode != 13) { 
		return true;
    } else {
		getNextElement(field).focus();
        getNextElement(field).select();
        return false;
    }
}

function tabOnEnter_select_with_button (field, evt) {
    var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;

    if (keyCode != 13) { 
		return true;
    } else {
		getNextElement_with_button(field).focus();
		getNextElement_with_button(field).select();
        return false;
    }
}


function EnterOnSubmit (form, evt) {
    var theForm = MM_findObj(form);
	var keyCode = document.layers ? evt.which : document.all ? 
    evt.keyCode : evt.keyCode;

	if (keyCode != 13) { 
		//theForm.submit();
		alert("tes");
		//return true;
	}

    /*else {
        getNextElement(field).focus();
        return false;
    }*/
}
