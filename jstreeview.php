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
    
    This file is tree menu file for BaconMap.
    
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
?>
<?php
  // Initiate the timer to display how long this script took to run.
  $mtime = microtime(); 
  $mtime = explode(' ', $mtime); 
  $mtime = $mtime[1] + $mtime[0]; 
  $starttime = $mtime; 
  // Include needed files for this page
  require_once('includes/settings.php');
  require_once('includes/functions.php');
  
  // Set up sort order variables
  if(!isset($_REQUEST['how']))
    $how = 'child';
  else
    $how = $_REQUEST['how'];
  
  if(!isset($_REQUEST['what']))
    $what = 'box';
  else
    $what = $_REQUEST['what'];
    
   // Connect to the database
   $conn = dbConnect($dbhost,$database,$username,$password);

    
   /* Now comes the fun part.  We need to take each object and 
      find all its children, then find all their children, then all their children,
      so on and so on.  From that we can build our tree menu.  Luckily we have a 
      function that does that for us.
   */
   $treemenu = createTree($how,$what);
  
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">   
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<link rel="stylesheet" href="css/jsTree.css" type="text/css" />
	<title>Bacon Map JSTree</title>
	<script src="javascript/jsTree.js" type="text/javascript" language="JavaScript"></script> 
  <script  language="JavaScript" type="text/javascript">

function doDelete(children,resource) {
    if(confirm("You are about to delete this resource. You will be able to review this decision on the next page."+
      "\nContinue?")){ 
      parent.main.location="delete.php?from=dodelete&id="+resource; 
     }  
} 
var newNodeCount = 0

var en_nodeContextMenu = []

jst_context_menu = en_nodeContextMenu

function _foo(){}

var arrNodes = <?php echo $treemenu; ?>;

var what = '<?php echo $what; ?>';
var how = '<?php echo $how; ?>';

//-->
</script>
</head>
<body onLoad="">
<div id="treeContainer"></div>
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<script type="text/javascript">
var jst_container = "document.getElementById('treeContainer')"
var jst_image_folder_user = "tree_images/"
renderTree();
</script>
<?php
// Set stop time for script timer
$mtime = microtime(); 
$mtime = explode(" ", $mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime;
$executiontime = ($endtime - $starttime);
?>
<script type="text/javascript">
parent.document.getElementById('timer').innerHTML = "<?php echo 'Tree generated in '.$executiontime.' seconds'; ?>";
</script>	
</body>
</html>
