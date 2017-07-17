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

/*  This is the script that will create an image and a map and then pass it to the viewer script
*/
  require_once 'includes/functions.php';
  require_once 'includes/settings.php';
  session_name("baconmappic");
    
 	$hash=array();
 	$names=array();
 	$shape["box"]=$boxshape;
	$shape["box1"]=$virtualservershape;
	$shape["server"]=$servershape;
	$shape["service"]=$serviceshape;
	$shape["application"]=$applicationshape;
	$shape["device"]=$deviceshape;
	$shape["database"]=$databaseshape;

 	dbConnect($dbhost,$database,$username,$password);
 	$orientation = $maporientation;

 	$picture="picture.php";
 	if(isset($_GET['type'])) $type=$_GET["type"]; else $type = '';
 	if(isset($_GET['id'])) $id=$_GET["id"]; else $id = '';
 	if(isset($_POST['child'])) $child=$_POST["child"]; else $child = 0;
 	if(isset($_POST['parent'])) $parent=$_POST["parent"]; else $parent = 0;
 	if($child!="1"&&$parent!="1"){$child="1";$parent="1";}

/* $array=cacheResources("box");
 foreach($array as $key => $value){$names["box"][$key]=$value['name'];}
 $array=cacheResources("device");
 foreach($array as $key => $value){$names["device"][$key]=$value['name'];}
 $array=cacheResources("server");
 foreach($array as $key => $value){$names["server"][$key]=$value['name'];}
 $array=cacheResources("service");
 foreach($array as $key => $value){$names["service"][$key]=$value['name'];}
 $array=cacheResources("application");
 foreach($array as $key => $value){$names["application"][$key]=$value['name'];}
 $array=cacheResources("database");
 foreach($array as $key => $value){$names["database"][$key]=$value['name'];}*/

 	$maxch=0;
 	$names["box"]=cacheResources("box");
 	if(count($names["box"])) { foreach($names["box"] as $key => $value)
		{if($value["children"]>$maxch){$maxch=$value["children"];}}}

 	$names["device"]=cacheResources("device");
 	if(count($names["device"])) { foreach($names["device"] as $key => $value)
		{if($value["children"]>$maxch){$maxch=$value["children"];}}}

 	$names["server"]=cacheResources("server");
 	if(count($names["server"])) { foreach($names["server"] as $key => $value)
		{if($value["children"]>$maxch){$maxch=$value["children"];}}}

 	$names["service"]=cacheResources("service");
 	if(count($names["service"])) { foreach($names["service"] as $key => $value)
		{if($value["children"]>$maxch){$maxch=$value["children"];}}}

 	$names["application"]=cacheResources("application");
 	if(count($names["application"])) { foreach($names["application"] as $key => $value)
		{if($value["children"]>$maxch){$maxch=$value["children"];}}}

 	$names["database"]=cacheResources("database");
 	if(count($names["database"])) { foreach($names["database"] as $key => $value)
		{if($value["children"]>$maxch){$maxch=$value["children"];}}}

 	$groups=cacheGroups();

 	system("find ./image/ -mtime +7 -exec rm {} \;"); // delete files that are 7 or more days old
 	$random=rand();
 	$ext=".gif";$ext2=".map";
 	while(is_readable("image/$random$ext"))
		{$random++;}

 	$handle=popen("$graphvizpath -Tcmapx -oimage/$random$ext2 -Tgif -oimage/$random$ext","w");

	fwrite($handle,"digraph G {\n");

	if($orientation == 'topdown')
   		fwrite($handle,"graph [ compound=true ]\n");
 	elseif($orientation == 'leftright')
   		fwrite($handle,"graph [ compound=true , rankdir = \"LR\" ]\n");
 	elseif($orientation == 'rightleft')
   		fwrite($handle,"graph [ compound=true , rankdir = \"RL\" ]\n");
 	elseif($orientation == 'bottomup')
   		fwrite($handle,"graph [ compound=true , rankdir = \"BT\" ]\n");

 	if($type == "" && $id == ""){ // big picture
  		$query1="select p_table,p_id,c_table,c_id from tbl_dep;";
  		$result = mysql_query($query1) or die("Could not select $query1");
  		while($row = mysql_fetch_row($result)){
  			//$img = getPic($row[0]);

   			if(!isset($hash["$row[0]$row[1]"]) || $hash["$row[0]$row[1]"] !=1){
    				$hash["$row[0]$row[1]"]=1;
    				$c=sprintf("%2x",234*(1-$names[$row[0]][$row[1]]["children"]/$maxch));
				$gvimage = "$row[0].png";
				
				if($row[0] == "server") {
					if(isVM($row[1]))
						$gvimage = "vmos.png";
				}


    				fwrite($handle,"node [shape=\"rect\", image=\"$gvimage\",  style=filled, color=\"#ff$c$c\", URL=\"$picture?type=$row[0]&id=$row[1]\"]; \"".$names[$row[0]][$row[1]]["name"]."\";\n");
   			}

   			if(!isset($hash["$row[2]$row[3]"]) || $hash["$row[2]$row[3]"] !=1){
    				$hash["$row[2]$row[3]"]=1;
    				$c=sprintf("%2x",234*(1-$names[$row[2]][$row[3]]["children"]/$maxch));
				$gvimage = "$row[2].png";

				if($row[2] == "server")
					if(isVM($row[3]))
						$gvimage = "vmos.png";

  				fwrite($handle,"node [shape=\"rect\", image=\"$gvimage\", style=filled color=\"#ff$c$c\", height=2.0,  labelloc=b, URL=\"$picture?type=$row[2]&id=$row[3]\"]; \"".$names[$row[2]][$row[3]]["name"]."\";\n");
   			}

   		$color="";if(isset($groups[$row[0].$row[1]]) && $groups[$row[0].$row[1]] > 0){$color=" [color=blue]";}
   		fwrite($handle," \"".$names[$row[0]][$row[1]]["name"]."\" -> \"".$names[$row[2]][$row[3]]["name"]."\" $color\n");
  		}

  		fwrite($handle,clusterGroups());
  		$_SESSION["return"]="";
  		$_SESSION["anchor"]="";
 	} 

 	else{ // picture of the node relations
  		$type=$_GET["type"];
		$id=$_GET["id"];
  		$c=sprintf("%2x",234*(1-$names[$type][$id]["children"]/$maxch));
		$gvimage = "$type.png";

		if($type == "server")
			if(isVM($id))
				$gvimage = "vmos.png";

  		fwrite($handle,"node [shape=\"rect\", image=\"$gvimage\", style=filled, color=\"#ff$c$c\", height=2.0, labelloc=b,  URL=\"$picture?type=$type&id=$id\"]; \"".$names[$type][$id]["name"]."\"; \n");
  		$hash[$type.$id]=1;

  		if($parent=="1"){
   			$parents=getParentsRecursive($type, $id);
   			write_graph($parents,$handle,$names[$type][$id]["name"],$type,$id,"parents");
  		}

  		if($child=="1"){
   			$children=getChildrenRecursive($type, $id);
   			write_graph($children,$handle,$names[$type][$id]["name"],$type,$id,"children");
  		}

  		fwrite($handle,clusterGroups());
  		$_SESSION["return"]="$picture?type=$type&id=$id";
  		$_SESSION["parent"]=$parent;
  		$_SESSION["child"]=$child;
  		$_SESSION["anchor"]=$names[$type][$id]["name"];
 	}

 	fwrite($handle,"}\n");
 	pclose($handle);


 	function write_graph($array,$handle,$name,$otype,$oid,$PorC){
  		$output = '';
  		global $names;
  		global $hash;
  		global $shape;
  		global $maxch;
  		global $picture;
  		global $groups;

  		foreach($array as $key => $value){
   			$type=$value['type'];$id=$value['id'];
   			//$img = getPic($type); 

   			if(!isset($hash[$type.$id]) || $hash[$type.$id] != 1){
    				$hash[$type.$id]=1;
    				$c=sprintf("%2x",234*(1-$names[$type][$id]["children"]/$maxch)); // node color
				//print $names[$type][$id]["name"].":".$names[$type][$id]["children"]."<p>";
				$gvimage = "$type.png";

				if($type == "server")
					if(isVM($id))
						$gvimage = "vmos.png";

    				fwrite($handle,"node [shape=\"rect\", image=\"$gvimage\", style=filled color=\"#ff$c$c\", height=2.0, labelloc=b, URL=\"$picture?type=$type&id=$id\"]; \"".$names[$type][$id]["name"]."\";\n");

    				if(isset($value[$PorC])){
     					write_graph(write_graph($value[$PorC],$handle,$names[$type][$id]["name"],$type,$id,$PorC));
    				}
   			}

   			if($PorC=="parents"){
    				$color="";if(isset($groups[$type.$id]) && $groups[$type.$id] > 0){$color=" [color=blue]";}
    				$bufer=" \"".$names[$type][$id]["name"]."\" -> \"$name\"$color\n";
    				fwrite($handle,$bufer);
				//print "$bufer<BR>";
   			}
   			else{
    				$color="";if(isset($groups[$otype.$oid]) && $groups[$otype.$oid] > 0){$color=" [color=blue]";}
    				$bufer=" \"$name\" -> \"".$names[$type][$id]["name"]."\"$color\n";
    				fwrite($handle,$bufer);
				//print "$bufer<BR>";
   			}
  		}

  		return $output;
 	}

	function isVM($servid) {
		//Returns true or false on whether a server is a virtual server.
		$query = 'SELECT virtual, name FROM tbl_server WHERE server_id = '.$servid;
		$result = mysql_query($query);

		$vrow = mysql_fetch_row($result);

		if ($vrow[0] > 0)
			$val = 1;
		else
			$val = 0;

		return $val;
	}

	function cacheResources($type) {

      		// Returns all resources of the given type
      		$res = array();
      		$query = 'SELECT  '.$type.'_id, name, children FROM tbl_'.$type;
      		$result = mysql_query($query);

      		if(mysql_num_rows($result) == 0)
         		return 0;

      		while($row = mysql_fetch_row($result)) {
          		$res[$row[0]]['name'] = $row[1];
          		$res[$row[0]]['children'] = $row[2];
        	}
      		return $res;
	}

	function cacheGroups(){
 		$answer=array();
 		$query="select mtype,mid,group_id from tbl_group order by group_id";
 		$result = mysql_query($query); 
 		while($row = mysql_fetch_row($result)){
  			$answer[$row[0].$row[1]]=$row[2];
 		}
 		return $answer;
	}

	function clusterGroups(){
 		global $groups;
 		global $names;
 		global $hash;
 		$clustercode = array();
 		$clusternum=0;
 		$prevgroupid="noGroupsSeen";
 		//go thru each member in the list of all groups

		 foreach($groups as $groupMember => $groupid){
  			//if we haven't seen this group ID before, start a new cluster
  			if($groupid != $prevgroupid){
   				$clusternum++;
   				$clustercode[$clusternum] = "subgraph \"cluster".$clusternum."\" {\nstyle=filled\nfillcolor=\"#00202020\"\n";
  			}
  			//if this member will be drawn, list it in the cluster
  			if($hash[$groupMember]==1){
   				ereg("^([^0-9]+)([0-9]+)$",$groupMember,$clusterMemberParts);
   				$clustercode[$clusternum] .= '"'.$names[$clusterMemberParts[1]][$clusterMemberParts[2]]["name"].'"'."\n";
  			}
  			$prevgroupid = $groupid;
 		}

 		for($idx=1; $idx<=sizeof($clustercode); ++$idx){
  			$clustercode[$idx] .= "}\n";
  			$allclustercode .= $clustercode[$idx];
 		}
 		return $allclustercode;
	}

	if(file_exists("image/$random$ext")){
 		// create a grid
 		$gif0=imagecreatefromgif("image/$random$ext");
 		$sizes=getimagesize("image/$random$ext");
 		$width=$sizes[0];$height=$sizes[1];
 		$wstep=200;$hstep=200;
 		$font=5;
 		//print "width:$sizes[0];height:$sizes[1]\n";
 		//imagesetstyle($gif,$style);
 		$hgrid=imagefontheight($font)+2;
 		$wgrid=imagefontwidth($font)*2+2;
 		$gif=imagecreate($width+$wgrid,$height+$hgrid);
 		imagecopy($gif,$gif0,$wgrid,$hgrid,0,0,$width,$height);
 		$w   = imagecolorallocate($gif, 55, 55, 55);
 		$c = imagecolorallocate($gif, 0, 0, 0);
 		/* Draw a dashed line, 5 red pixels, 5 white pixels */
 		$style = array($c, $c, $c, $c, $c, $w, $w, $w, $w, $w);
 		imagesetstyle($gif, $style);
 		$label="1";

		 for($i=0;$i<$width;$i+=$wstep,$label++){
  			for($j=0;$j<strlen($label);$j++)
				{imagechar($gif,$font,$i+$wgrid+$wstep/2+$j*$wgrid,0,substr($label,$j,1),$c);}
				#  imageline($gif,$i+$wgrid,0,$i+$wgrid,$height+$hgrid,IMG_COLOR_STYLED);
 		}

 		$label="A";

 		for($i=0;$i<$height;$i+=$hstep,$label++){
  			for($j=0;$j<strlen($label);$j++)
				{imagechar($gif,$font,0,$i+$hgrid+$hstep/2+$j*$hgrid,substr($label,$j,1),$c);}
				#  imageline($gif,0,$i+$hgrid,$width+$wgrid,$i+$hgrid,IMG_COLOR_STYLED);
 		}

 		imagegif($gif,"image/$random$ext");
 		imagedestroy($gif);
  
?>

<DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Loading resource map</title>
</head>
<body onload="getheight(); document.form0.submit(); ">
<table border width=100% height=100%><tr><td> loading... </td></tr></table>
<form name="form0" method="POST" action="googlemap1.php">
<img src="image/<?php echo "$random$ext?".time()?>" id="picture" style="top:0px;left:0px;visibility: hidden;">
<input type="hidden" name="width" value="0">
<input type="hidden" name="height" value="0">
<input type="hidden" name="fullwidth" value="0">
<input type="hidden" name="fullheight" value="0">
<input type="hidden" name="image" value="<?php echo $random;?>">
</form>
<script type="text/javascript"><!--
function getheight() {
  var myWidth = 0, myHeight = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;
  }
  document.form0.width.value=myWidth;
  document.form0.height.value=myHeight;
 document.form0.fullwidth.value=document.form0.picture.width;
 document.form0.fullheight.value=document.form0.picture.height;
//alert("w="+myWidth+"; h="+myHeight);  
}
--></script>
</body>
</html>
<?php
}
else{
?>
<DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Please install GraphViz</title>
</head>
<body>
Cannot draw a graph with the GraphViz dot utility. Please download and install GraphViz software from <a href="http://www.graphviz.org/">www.graphviz.org</a> and make sure "dot" command is in the command search path.
</body>
<?php
}
?>
