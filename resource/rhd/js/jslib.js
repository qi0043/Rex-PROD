function wSE (p1,p2,p3,p4) {
  document.write('<a hr'+'ef=\"ma'+'ilto:'+p1+'@'+p2+'\">');
  document.write(p3+'<\/a>');
}


function wSEME(p1) {
  var i;
  var d=new Date();
  var dn= d.getDay()+1;
  var str2 = ''; 
  for (i=0;i<p1.length;i+=3) {str2+=String.fromCharCode(Number(p1.substring(i,i+3)));}  //-dn
  document.write(str2);
}
var ap_instances = new Array();


function jumpToQuickLink() {
  var newloc = document.quicklinks.urllist.options[document.quicklinks.urllist.selectedIndex].value;
//    if ((window.pageTracker._trackPageview)) {pageTracker._trackPageview('flinders-quicklink/'+newloc);};
    location.href = newloc;
  return true;
}


function showHide(what,showorhide) {
 var obj = eval("document.getElementById('"+what+"');"); 
    if (showorhide=='show') {
		obj.style.display = 'block';
	}
	else {
		obj.style.display = 'none';
	}
}
function gotop(){scroll(0,0);}

function isSmallScreen() {
 var mobile = (/iphone|mobi|mobile|android|blackberry|mini|windows\sce|palm|symbianos/i.test(navigator.userAgent.toLowerCase()));
 var crawler = (/gsa-crawler|www.google.com|ColdFusion|bot|crawler|spider|yandex|Wordpress|archive.org_bot|slurp/i.test(navigator.userAgent.toLowerCase()));
 if (!crawler && (mobile || ((screen.width < 700)&&(screen.height<700)))) return true; else return false;
}

