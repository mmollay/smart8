function ajax(method,strURL,variable,value,sid) {
    var xmlHttpReq = false;
    var self = this;
	

    // Mozilla/Safari
    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq.open(method, strURL, true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	
    self.xmlHttpReq.onreadystatechange = function() {
		
	if(value == 'signgb' ){
	document.signgb.submit.disabled = true;
	document.getElementById('signdiv').innerHTML = '<center><img src="'+httpd_path+'/images/loading.gif"><br><br><b>'+loadtext+'...</b><br></center>';
			
	}else{
	document.getElementById('entries').innerHTML = '<center><br><br><br><br><img src="'+httpd_path+'/images/loading.gif"><br><br><b>'+loadtext+'...</b><br><br><br></center>';
	}
	
       if (self.xmlHttpReq.readyState == 4) {
			if(value == 'signgb' ){
			updatepage(self.xmlHttpReq.responseText,'signgb');
			}else{
            updatepage(self.xmlHttpReq.responseText,'page');
			}
	   }
        
    }
	
	if (value == 'signgb'){
	name=document.signgb.name.value;
	email=document.signgb.email.value;
	homepage=document.signgb.homepage.value;
	message=document.signgb.message.value;
	guestbook_id=document.signgb.guestbook_id.value;
	user_id=document.signgb.user_id.value;
	
	//Fix the "&" bug
	name2 = name.replace(/&/g, "^amp^");
	message2 = message.replace(/&/g, "^amp^");
	
	self.xmlHttpReq.send('dosign=' + pcode.charAt(0) + pcode.charAt(2) + pcode.charAt(4) + pcode.charAt(1) + pcode.charAt(5) + '&name=' + name2 + '&email=' + escape(email) + '&homepage=' + escape(homepage) + '&message=' + message2 + '&user_id=' + guestbook_id + '&user_id=' + user_id);
	}else{
    self.xmlHttpReq.send(escape(variable) + '=' + escape(value));
	}
	
}

function updatepage(str, value){
	
	//if (self.xmlHttpReq.readyState == 4) {
	if (value == 'signgb'){
    document.getElementById("signdiv").innerHTML = str;
	showdiv('signdiv');
	document.signgb.submit.disabled = false;
	
	//Thanks to Hiric for the fix
	if (str.length <= 2) {  
		//Create cookie
		var date = new Date();
		date.setTime(date.getTime()+(c_minute*60*1000));
		var expires = "; expires="+date.toGMTString();
		document.cookie = "signed=yes"+expires+"; path=/";

		
        hidediv('signform'); 
        ajax('POST',httpd_path+'/gb_view.php?user_id='+user_id+'&guestbook_id='+guestbook_id+'','page','1'); 
      } 

	}else{
	document.getElementById("entries").innerHTML = str;
	showdiv('entries');
	}
	//}
}

function hidediv(id) {
	//safe function to hide an element with a specified id
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'none';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'none';
		}
		else { // IE 4
			document.all.id.style.display = 'none';
		}
	}
	if(id == 'signform'){
	showdiv('entries');
	hidediv('signdiv');
	}
}

function showdiv(id) {
	//safe function to show an element with a specified id
		  
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'block';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'block';
		}
		else { // IE 4
			document.all.id.style.display = 'block';
		}
	}
	if(id == 'signform')
	hidediv('entries');
	
	$('#name').focus();
}
  function smiley(s){
  document.signgb.message.value = document.signgb.message.value + s;
  document.signgb.message.focus();
}

function newwindow(source,name,width,height) 
{ 
window.open(source,name,'width='+width+',height='+height+',resizable=no'); 
} 
