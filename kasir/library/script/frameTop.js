defaultrowconf = "89,*,15";
defaultcolconf = "175,*";
collrowconf = "25,*,15";
first_img = "images/bd_downpage.png";
second_img = "images/bd_uppage.png";

function collapseTop(){
    var _top_ = document.getElementById("_top_frame");
    if (_top_)
    {
        if ( _top_.rows != collrowconf ){
            _top_.rows = collrowconf;
			// --- hidden gambar atas
			window.topFrame.document.getElementById("tblHead").style.display="none";
        } else {
            _top_.rows = defaultrowconf;
			window.topFrame.document.getElementById("tblHead").style.display="block";
		}

    }
}

function resizeLeft(){
    var _left_ = document.getElementById("_left_frame");
    
    if (_left_)
    {
        if (_left_.cols != defaultcolconf)
            _left_.cols = defaultcolconf;
    }
}

function changeTopImage() {
	var _top_img_ = window.topFrame.document.getElementById("_top_img_");
    if (_top_img_.title != 'up')
    {
        _top_img_.src = first_img;
		_top_img_.title = 'up';
		_top_img_.width = '13';
		_top_img_.height = '20';
    } 
	else
	{	
		_top_img_.src = second_img;
		_top_img_.title = 'down';
	}
}

function MM_findObj(n, d) { //v4.01
	var p,i,x;  
  
	if ( !d ) 
		d=document;
  
	if((p=n.indexOf("?"))>0&&parent.frames.length) 
	{
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
