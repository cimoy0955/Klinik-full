defaultconf = "177,*";
collconf = "10,*";
first_img = "images/bd_firstpage.png";
second_img = "images/bd_lastpage.png";

function collapseLeft(){
    var _left_ = document.getElementById("_left_frame");
    if (_left_)
    {
		if ( _left_.cols != collconf )
            _left_.cols = collconf;
        else 
            _left_.cols = defaultconf;
    }
}


function changeLeftImage() {
    //var _left_img_ = MM_findObj("_left_img_");
	var _left_img_ = document.getElementById("_left_img_");
    if (_left_img_.title != 'Collapse')
    {
        _left_img_.src = first_img;
		_left_img_.align = 'right';
		_left_img_.title = 'Collapse';
		_left_img_.width = '20';
		_left_img_.height = '13';
		_left_img_.alt = 'Collapse';
    } 
	else
	{	
		_left_img_.src = second_img;
		_left_img_.align = 'left';
		_left_img_.title = 'Expand';
		_left_img_.width = '10';
		_left_img_.height = '50';
		_left_img_.alt = 'Expand';
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
