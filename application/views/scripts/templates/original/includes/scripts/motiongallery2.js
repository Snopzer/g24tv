/***********************************************
* CMotion Image Gallery II- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* Visit http://www.dynamicDrive.com for source code
* Modified by jscheuer1 for vertical orientation, at http://www.dynamicDrive.com/forums
***********************************************/

var restarea=6 //1) width of the "neutral" area in the center of the gallery in px
var maxspeed=7 //2) top scroll speed in pixels. Script auto creates a range from 0 to top speed.
var endofgallerymsg="";//"<span style='font-size: 11px'>End of Gallery</span>" //3) message to show at end of gallery. Enter "" to disable message.

function enlargeimage(path, optWidth, optHeight){ //function to enlarge image. Change as desired.
var actualWidth=typeof optWidth!="undefined" ? optWidth : "600px" //set 600px to default width
var actualHeight=typeof optHeight!="undefined" ? optHeight : "500px" //set 500px to  default height
var winattributes="width="+actualWidth+",height="+actualHeight+",resizable=yes"
window.open(path,"", winattributes)
}

////NO NEED TO EDIT BELOW THIS LINE////////////

var iedom=document.all||document.getElementById
var scrollspeed=0
var movestate=""

var actualheight=''
var cross_scroll
var loadedyes=0

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function creatediv(){
statusdiv=document.createElement("div")
statusdiv.setAttribute("id","statusdiv")
document.body.appendChild(statusdiv)
statusdiv=document.getElementById("statusdiv")
statusdiv.innerHTML=endofgallerymsg
}

function positiondiv(){
menuwidth=parseInt(crossmain.offsetWidth)
mainobjoffsetW=getposOffset(crossmain, "left")
statusdiv.style.left=mainobjoffsetW+(menuwidth/2)-(statusdiv.offsetWidth/2)+"px"
statusdiv.style.top=menu_height+mainobjoffset+10+"px"
}

function showhidediv(what){
if (endofgallerymsg!="")
statusdiv.style.visibility=what
}

function getposOffset(what, offsettype){
var totaloffset=(offsettype=="left")? what.offsetLeft: what.offsetTop;
var parentEl=what.offsetParent;
while (parentEl!=null){
totaloffset=(offsettype=="left")? totaloffset+parentEl.offsetLeft : totaloffset+parentEl.offsetTop;
parentEl=parentEl.offsetParent;
}
return totaloffset;
}


function moveup(){
if (loadedyes){
movestate="up"
if (iedom&&parseInt(cross_scroll.style.top)>(menu_height-actualheight)){
cross_scroll.style.top=parseInt(cross_scroll.style.top)-scrollspeed+"px"
showhidediv("hidden")
}
else
showhidediv("visible")
}
uptime=setTimeout("moveup()",10)
}

function movedown(){
if (loadedyes){
movestate="down"
if (iedom&&parseInt(cross_scroll.style.top)<0){
cross_scroll.style.top=parseInt(cross_scroll.style.top)+scrollspeed+"px"
showhidediv("hidden")
}
else
showhidediv("visible")
}
downtime=setTimeout("movedown()",10)
}

function motionengine(e){
var dsocx=(window.pageXOffset)? pageXOffset: ietruebody().scrollLeft;
var dsocy=(window.pageYOffset)? pageYOffset : ietruebody().scrollTop;
var curposy=window.event? event.clientY : e.clientY? e.clientY: ""
curposy-=mainobjoffset-dsocy
var leftbound=(menu_height-restarea)/2
var rightbound=(menu_height+restarea)/2
if (curposy>rightbound){
scrollspeed=(curposy-rightbound)/((menu_height-restarea)/2) * maxspeed
if (window.downtime) clearTimeout(downtime)
if (movestate!="up") moveup()
}
else if (curposy<leftbound){
scrollspeed=(leftbound-curposy)/((menu_height-restarea)/2) * maxspeed
if (window.uptime) clearTimeout(uptime)
if (movestate!="down") movedown()
}
else
scrollspeed=0
}

function contains_ns6(a, b) {
while (b.parentNode)
if ((b = b.parentNode) == a)
return true;
return false;
}

function stopmotion(e){
if ((window.event&&!crossmain.contains(event.toElement)) || (e && e.currentTarget && e.currentTarget!= e.relatedTarget && !contains_ns6(e.currentTarget, e.relatedTarget))){
if (window.downtime) clearTimeout(downtime)
if (window.uptime) clearTimeout(uptime)
movestate=""
}
}

function fillup(){
if (iedom){
crossmain=document.getElementById? document.getElementById("motioncontainer") : document.all.motioncontainer
menu_height=parseInt(crossmain.style.height)
mainobjoffset=getposOffset(crossmain, "top")
cross_scroll=document.getElementById? document.getElementById("motiongallery") : document.all.motiongallery
actualheight=cross_scroll.offsetHeight

crossmain.onmousemove=function(e){
motionengine(e)
}

crossmain.onmouseout=function(e){
stopmotion(e)
showhidediv("hidden")
}
}
if (window.opera){
cross_scroll.style.top=menu_height-actualheight+'px'
setTimeout('cross_scroll.style.top=0', 10)
}
loadedyes=1
if (endofgallerymsg!=""){
creatediv()
positiondiv()
}
}
window.onload=fillup