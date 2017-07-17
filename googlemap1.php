<?php
/*
    **THIS NOTICE MUST APPEAR ON ALL PAGES AND VERSIONS OF BACONMAP**

    BaconMap - Resources Defined.
    Copyright 2008 NMSU Research IT, New Mexico State University
    Originally developed by Ed Zenisek, Denis Elkanov, and Abel Sanchez.

    Other open source projects used in BaconMap are copyright
    their respective owners:
    jsTree is copyright 2003-2004 Tobias Bender (tobias@phpXplorer.org)
    wz_tooltip is copyright 2002-2008 Walter Zorn. All rights reserved.
    DHTMLGrid is copyright DHTMLX LTD. http://www.dhtmlx.com

    This file is the detials display file for BaconMap.

    BaconMap is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BaconMap is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    */

/*  This script is supposed to take a big picture and show it in the browser window with zoom, drag and drop 
 Parameters are:
 image - the picture to view
 width - screen width
 height - screen height
 fullwidth - picture width
 fullheight - picture height
*/

require_once 'includes/functions.php';    
if(isset($_POST['image'])) $image=$_POST['image']; else $image = '';
if(isset($_POST['width'])) $wwidth=$_POST['width']; else $wwidth = '';
if(isset($_POST['height'])) $wheight=$_POST['height']; else $wheight = '';
if(isset($_POST['fullwidth'])) $fullwidth=$_POST['fullwidth']; else $fullwidth = '100';
if(isset($_POST['fullheight'])) $fullheight=$_POST['fullheight']; else $fullheight = '100';
if(isset($_SESSION['child'])) $child = $_SESSION['child']; else $child = 0;
if(isset($_SESSION['parent'])) $parent = $_SESSION['parent']; else $parent = 0;
if(isset($_POST['c']))
  $c=$_POST['c'];
else
  $c='';
$ext=".gif";
$ext2=".map";

$width=$wwidth-60;if($width<100){$width=100;}
$height=$wheight-86;if($height<100){$height=100;}
$ratio=$height/$fullheight;
if($width/$fullwidth<$ratio){
 $ratio=$width/$fullwidth;
}
if($ratio>1){$ratio=1;} // the picture is big enough
?>

<html>
<head>
<style type="text/css">
td{border-width: 0px 0px 0px 0px;white-space: nowrap;padding: 0px 0px 0px 0px;}
table{border-width: 0px 0px 0px 0px;padding: 0px 0px 0px 0px;}
H3 {font: bold normal normal 10pt verdana,Comic Sans MS,georgia,arial;}
.drag{position:relative;cursor:default;border:0;}
.resizer{position:relative;cursor:default;}
a:link{cursor:pointer;}
area{cursor:pointer;}
</style>
<script type="text/javascript">
function getheight() {
  var myWidth = 0, myHeight = 0;
  if( typeof( window.innerWidth ) == 'number' ) { // ff
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) { // ie4
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) { // ie6
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;
  }
  document.form0.width.value=myWidth;
  document.form0.height.value=myHeight;
  document.form0.submit();
//alert("w="+myWidth+"; h="+myHeight);
}

/*Credit JavaScript Kit www.javascriptkit.com*/
var dragapproved=false
var z,x,y,temp1,temp2,width,height,fullwidth,fullheight,cwidth,cheight,ratios
function move(event){
 if (dragapproved){
  if(!document.all){ // ff
   event.preventDefault();
//alert("temp1:"+temp1+" event.screenX:"+event.screenX+" x:"+x+" adjscr:"+adjscr(temp1+event.screenX-x,width,cwidth)+" width:"+width+" cwidth:"+cwidth);
   z.style.left=adjscr(temp1+event.screenX-x,width,cwidth);
   z.style.top=adjscr(temp2+event.screenY-y,height,cheight);
  }
  else{
   event = window.event;
   z.style.pixelLeft=adjscr(temp1+event.clientX-x,width,cwidth);
   z.style.pixelTop=adjscr(temp2+event.clientY-y,height,cheight);
  }
 }
 return false
}
function resizer(event){
 var pos,picture;
 if (dragapproved){
  if(!document.all){ // ff
   event.preventDefault();
   pos=temp2+event.screenY-y
  } 
  else{
   event = window.event;
   pos=temp2+event.clientY-y
  }
  if(pos<0){pos=0;}else if(pos>72){pos=72;}
  z.style.top=pos
  stretch(ratios[0]+(1-ratios[0])*(72-pos)/72);
//  imagemapset((72-pos)/72);
 }
 return false
}
function drags(event){
 if(!document.all){ // ff
  event.preventDefault();
  z=event.target
  x=event.screenX
  y=event.screenY
  temp1=(z.style.left==""?0:parseInt(z.style.left))
  temp2=(z.style.top==""?0:parseInt(z.style.top))
 }
 else{
  event = window.event;
  z=event.srcElement
  x=event.clientX
  y=event.clientY
  temp1=z.style.pixelLeft
  temp2=z.style.pixelTop
 }
 if (z.className=="drag"){
  dragapproved=true
  document.onmousemove=move
  z.style.cursor="move"
 }
 if (z.className=="resizer"){
  dragapproved=true
  document.onmousemove=resizer
 }
}
function enddrag(event){
 if(dragapproved){
  z.style.cursor="default";
  dragapproved=false;
  if(z.className=="resizer"){
   cratio=parseInt((72-parseInt(z.style.top))/7.3);
   stretch(ratios[cratio]);
   imagemapset(cratio);
   z.style.top=8*(9-cratio);
  } 
 }
}

//http://adomas.org/javascript-mouse-wheel/plain.html
function handle(delta) {
 if (delta < 0){
  cratio--;if(cratio<0){cratio=0;}
 }
 else{
  cratio++;if(cratio>9){cratio=9;}
 }
 z=document.form0.slider;
 z.style.top=8*(9-cratio);
 stretch(ratios[cratio]);
 imagemapset(cratio);
}
function adjscr(x,scrx,cpx){ // x - left(top),scrx - screen width(height), cpx - current picture width. make picture fit the screen
 if(x>0){return 0;}
 if(x+cpx<scrx){
  if(scrx<cpx){
   return parseInt(scrx-cpx);
  }
  else{
   return 0;
  }
 }
 return x;
}
function wheel(event){
 var delta = 0;
 if (!event) event = window.event;
 if (event.wheelDelta){
  delta = event.wheelDelta/120;
  if (window.opera) delta = -delta;
 }
 else if (event.detail){
  delta = -event.detail/3;
 }
 if (delta)
  handle(delta);
 if (event.preventDefault)
  event.preventDefault();
 event.returnValue = false;
}
function stretch(ratio){ // set the picture width fullwidth*ratio and adjust the center
 var left,top,z;
 z=document.form0.picture;
 left=(z.style.left==""?0:parseInt(z.style.left));
 top=(z.style.top==""?0:parseInt(z.style.top));
 var ratio0=cwidth/fullwidth;
 cwidth=fullwidth*ratio;cheight=fullheight*ratio;
 z.style.width=fullwidth*ratio;
 left=adjscr(((left-width/2)/ratio0)*ratio+width/2,width,cwidth);
 top=adjscr(((top-height/2)/ratio0)*ratio+height/2,height,cheight);
 z.style.left=left;
 z.style.top=top;
}
function center(x,y){ // center the view on the point and set ratio to 1:1
 var left,top,z;
 cratio=9;
 z=document.form0.picture;
 stretch(ratios[cratio]);
 imagemapset(cratio);
 left=adjscr(-x+width/2,width,fullwidth);
 top=adjscr(-y+height/2,height,fullheight);
// alert("x="+x+";width="+width+";fullwidth="+fullwidth+";left="+left);
 z.style.left=left;
 z.style.top=top;
 z=document.form0.slider;
 z.style.top=8*(9-cratio);
}
function imagemapset(no){ // use the imagemap #no
 document.form0.picture.useMap="#G"+parseInt(no)
}
function ch2pointer(){
 document.form0.picture.style.cursor="pointer";
}
function ch2default(){
 document.form0.picture.style.cursor="default";
}
function initialize(){
 var ratio,i,j,str,str0,map;
 width=<?php echo $width?>;height=<?php echo $height?>;
 fullwidth=document.form0.picture.width;fullheight=document.form0.picture.height;
 if(width/fullwidth>height/fullheight){
  ratio=height/fullheight;
 }
 else{
  ratio=width/fullwidth;
 }
 if(ratio>1){ratio=1;} // the picture is big enough
 cwidth=fullwidth*ratio;cheight=fullheight*ratio;
 ratios=new Array();cratio=0;
 ratios[9]=1;
 for(i=0;i<9;i++){ // linear zoom
  ratios[i]=ratio+i*(1-ratio)/9;
 }
// x=Math.exp(Math.log(1/ratio)/10); // exponential zoom
// for(i=0;i<9;i++){
//  ratios[i]=ratio;
//  ratio*=x;
// }
 stretch(ratios[cratio]);
 imagemapset(cratio);
/* Initialization code. */
if (window.addEventListener)
 window.addEventListener('DOMMouseScroll', wheel, false);
 window.onmousewheel = document.onmousewheel = wheel;
 document.onmousedown=drags
 document.onmouseup=enddrag
}

</script> 
</head>
<body onload="initialize();">
<form name="form0" action="googlemap1.php" method="post">
<input type="hidden" name="width" value="<?php echo $wwidth;?>">
<input type="hidden" name="height" value="<?php echo $wheight;?>">
<input type="hidden" name="image" value="<?php echo $image;?>">
<input type="hidden" name="fullheight" value="<?php echo $fullheight;?>">
<input type="hidden" name="fullwidth" value="<?php echo $fullwidth;?>">
<a name="top"></a>
<table  width=100%><tr>
<td><h3><?php echo $_SESSION["anchor"]?></h3></td>
<td><a href="picture.php">Big picture</a></td>
<?php if(isset($_SESSION["return"])) { // big picture  ?>
<td><input type="checkbox" name="parent" value="1" onclick="document.form0.action='<?php echo $_SESSION["return"]?>';document.form0.submit();"<?php if($parent=="1"){print "checked";} ?>>Parent relations
<input type="checkbox" name="child" value="1" onclick="document.form0.action='<?php echo $_SESSION["return"]?>';document.form0.submit();"<?php if($child=="1"){print " checked";} ?>>Child relations</td>
<?php } ?>
<td align="left"><a href="" onclick="document.form0.action='googlemap2.php';document.form0.submit();return false;">No zoom</a></td>
</tr>
<tr><td colspan=13>
<table>
<tr><td>
<img src="images/plus.png" style='width:15px;height:15px;border:1px solid black;' onclick="handle(1);">
<div style='width:15px;height:85px;border:1px solid black;overflow:hidden;background-image:url("images/slider.png");'>
<img src="images/right.bmp" id="slider" class="resizer" style="top:<?php echo 72; ?>">
</div>
<img src="images/minus.png" style='width:15px;height:15px;border:1px solid black;' onclick="handle(-1);">
</td><td colspan=13>
<div style="width:<?php echo $width?>px;height:<?php echo $height; ?>px;border:1px solid black;overflow:hidden">
<img src="image/<?php echo "$image$ext?".time(); ?>" id="picture" style="top:0px;left:0px;" USEMAP="#G" class="drag" onmouseout="enddrag();"><br>
</div>
</td></tr></table>
</td></tr>
</table>
<?php             // 10 imagemaps from the original size to the smallest
$font=5;
$hgrid=imagefontheight($font)+2;  // these are horizontal and vartical margins for 
$wgrid=imagefontwidth($font)*2+2; // grid number and letter marks
$titles=array();
$ratios = '';
for($i=9;$i>=0;$i--){
 $r=$ratio+$i*(1-$ratio)/9; if($i==9){$r=1;}
 $ratios.="ratios[$i]=$r;";
 $handle = @fopen("image/$image$ext2", "r");
 $tptr=0;
 if($handle) {
  while(!feof($handle)) {
   $buffer = fgets($handle);
   if(preg_match("/<area shape=\"([^\"]+)\" (.*) title=\"([^\"]+)\" (.*) coords=\"([^\"]+)\"\/>/",$buffer,$matches)){
    $shape=$matches[1];$coordinates=$matches[5];
if($i==9){$centerx[$matches[3]]=0;$centery[$matches[3]]=0;$titles[$tptr]=$matches[3];$coord_num=0;}
    print "<area shape=\"$shape\" $matches[2] title=\"$matches[3]\" $matches[4] coords=\"";
    if($shape=="poly"){
     $coordinates.=" ";
     while(preg_match("/^(\d+),(\d+) /",$coordinates,$matches)){
      $x=$matches[1];$y=$matches[2];
      print intval(($x+$wgrid)*$r).",".intval(($y+$hgrid)*$r)." ";
      $coordinates=preg_replace("/^\d+,\d+\s+/","",$coordinates);
if($i==9){$centerx[$titles[$tptr]]+=$x;$centery[$titles[$tptr]]+=$y;$coord_num++;}      
     }
    }
    if($shape=="rect"){
     if(preg_match("/^(\d+),(\d+),(\d+),(\d+)/",$coordinates,$matches)){
      print intval(($matches[1]+$wgrid)*$r).",".intval(($matches[2]+$hgrid)*$r).",".intval(($matches[3]+$wgrid)*$r).",".intval(($matches[4]+$hgrid)*$r);
if($i==9){$centerx[$titles[$tptr]]+=$matches[1];$centery[$titles[$tptr]]+=$matches[2];$coord_num++;}      
     }
    }
    if($shape=="circle"){
     if(preg_match("/^(\d+),(\d+),(\d+)/",$coordinates,$matches)){
      print intval(($matches[1]+$wgrid)*$r).",".intval(($matches[2]+$hgrid)*$r).",".intval($matches[3]*$r);
if($i==9){$centerx[$titles[$tptr]]=$matches[1];$centery[$titles[$tptr]]=$matches[2];$coord_num=1;}      
     }
    }
    print "\"/>\n";
if($i==9){
 $centerx[$titles[$tptr]]=intval($centerx[$titles[$tptr]]/$coord_num)+$wgrid;
 $centery[$titles[$tptr]]=intval($centery[$titles[$tptr]]/$coord_num)+$hgrid;
 $tptr++;
}    
   }
   else{
    if(preg_match("/<map id=\"G\" name=\"G\">/",$buffer,$matches)){
     print "<map id=\"G$i\" name=\"G$i\">\n";
    }
    else{
     print $buffer;
    } 
   }
  }
  fclose($handle);
 }
}

sort($titles);
$left[0]="A";for($i=0;$i<1000;$i++){$left[$i+1]=$left[$i];$left[$i+1]++;}
#foreach ($titles as $key => $val) {
# print "<a href='#top' onclick='center($centerx[$val],$centery[$val]);'>$val:".$left[intval(($centery[$val]-$hgrid)/200)].intval(1+($centerx[$val]-$wgrid)/200)."</a><BR>\n";
#}
$max=count($titles);
print "<table width=100%>\n";
for($i=0;$i<$max;$i+=4){
 print "<tr>\n";
 for($j=0;$j<4;$j++){
   print "<td>";
   if($i+$j<$max){print "<a href='#top' onclick='center(".$centerx[$titles[$i+$j]].",".$centery[$titles[$i+$j]].");'>".$titles[$i+$j].":".$left[intval(($centery[$titles[$i+$j]]-$hgrid)/200)].intval(1+($centerx[$titles[$i+$j]]-$wgrid)/200)."</a>";}
   print "</td>\n";}
 print "<tr>\n";
}
print "</table>\n"; 
?>
</form>
</body>
</html>
