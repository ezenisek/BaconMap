<?php
require_once('includes/settings.php');
require_once('includes/functions.php');
dbConnect($dbhost,$database,$username,$password);


if(!isset($_GET['id']))
  {
      $error = "No id found for deletion.  Please try again.";
      include('/includes/error.php');
      exit();
  }
$resource = $_GET['id'];
$olist = getOrphans($resource);

$bits = explode('_',$resource);
$type = $bits[0]; $id = $bits[1];

$query = 'SELECT name FROM tbl_'.$type.' WHERE '.$type.'_id = '.$id;
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$resname = $row[0];

if($olist)
{
  $orphans = array();
  foreach($olist as $key => $thisorphan)
    {
      $bits = explode('_',$thisorphan);
      $otype = $bits[0]; $oid = $bits[1];
      $query = 'SELECT name FROM tbl_'.$otype.' WHERE '.$otype.'_id = '.$oid;
      $result = mysql_query($query);
      $row = mysql_fetch_row($result);
      $orphans[$key]['name'] = $row[0];
      $orphans[$key]['type'] = $otype;
      $orphans[$key]['id'] = $oid;
    }
  $orphnum = count($orphans);
  $height = 20 + (25 * $orphnum);
}
else
  $orphnum = 'No';
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Bacon Map Delete Page</title>
	<link rel="stylesheet" type="text/css" href="javascript/dhtmlgrid/dhtmlxgrid.css">
  <script type="text/javascript" src="javascript/dhtmlgrid/dhtmlxcommon.js"></script>
  <script type="text/javascript" src="javascript/dhtmlgrid/dhtmlxgrid.js"></script>
  <script type="text/javascript" src="javascript/dhtmlgrid/dhtmlxgridcell.js"></script>
  <script type="text/javascript">
  parent.document.getElementById('mainwindowtitle').innerHTML = 'Deleting <span class="blue"><?php echo $resname; ?></span>, It Has <span class="blue"><?php echo $orphnum; ?></span> Orphans.';
  function doRowClick(id,index) {
   if(index == 3)
	   parent.parent.GB_myShow("More Details", "<?php echo $rootdir; ?>/details.php?id="+id,500,530);
	 else if(index == 4)
	   parent.parent.GB_myShow("Edit Resource", "<?php echo $rootdir; ?>/edit.php?id="+id,500,600);
	 }
	 
	function doDelete() {
	if(confirm("Please confirm this resource will be deleted.")) {
	document.del.submit();
	}
	}
	function doCancel() {
	window.location = 'welcome.php';
	}
	</script>
	</head>
<body>
<form name="del" method="post" action="dodelete.php" >
<input type="hidden" name="id" value="<?php echo $resource; ?>" />
<input type="hidden" name="verification" value="yeppers" />
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<div class="minorheader">Deleting the <?php echo ucwords($type.' '.$resname); ?></div>
<a href="javascript:parent.GB_myShow('More Details', '/baconmap/details.php?id=<?php echo $type.'_'.$id; ?>',600,600)">More Information about <?php echo $resname; ?></a>
<?php
  if($olist)
    {
     echo '<br /><br /><div class="minorheader">Deleting '.$resname.' will leave the following Orphans</div>';
     echo '<center><div id="warning">Deleting '.$resname.' will also delete these orphans</div></center>';
     echo '<center><div id="gridbox" style="height:'.$height.'px;width:540px;"></div></center>';
    ?>
    <script type="text/javascript">
    mygrid = new dhtmlXGridObject('gridbox');
    mygrid.setImagePath("javascript/dhtmlgrid/imgs/");
    mygrid.setEditable(true);
    mygrid.setSkin("mt");
    mygrid.setHeader(" ,Orphan Type,Name,,");
    mygrid.setInitWidths("30,100,150,120,140");
    mygrid.setColTypes("img,ro,ro,ro,ro");
    mygrid.setColAlign("left,left,left,left,left");
    mygrid.setColSorting("na,na,na,na,na");
    mygrid.enableResizing("true,true,true,true,true");
    mygrid.attachEvent("onRowSelect",doRowClick);
    mygrid.init();
    <?php 
      foreach($orphans as $thisorphan)
      {
       $otype = $thisorphan['type'];
       $oid = $thisorphan['id'];
       $oname = $thisorphan['name'];
       $typeid = $thisorphan;
       $pic = getPic($otype);
       $link1 = '<a href=#>Tell Me More</a>';
       $link2 = '<a href=#>Choose Alternate Parent(s)</a>';
       echo 'mygrid.addRow("'.$otype.'_'.$oid.'","tree_images/'.$pic.'.png,'.ucwords($otype).','.$oname.','.$link1.','.$link2.'",0);';
      }
    echo '</script>';
    }
    else
    echo '<div>This Resource would leave no orphans because is has no children or other parents care for its children.</div>';
?>
<center>
<br />
<hr width="95%">
<br />
<input type="button" name="delete" value="Delete this Resource" onClick="doDelete()" />
 <input type="button" name="cancel" value="Cancel" onClick="doCancel()" /></center>
</form>
</body>
</html>
