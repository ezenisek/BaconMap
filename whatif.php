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
    
    This file is the what if generator menu for BaconMap.
    
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
    require_once('includes/settings.php');
    require_once('includes/functions.php');
    dbConnect($dbhost,$database,$username,$password);

    // Now we check that the user is logged in.  If not, we forward to the login page.
    authorize(2,"index.php?frame=".urlencode('whatif.php'));

    $dead = 0;
    $saved = 0;
    $results = false;
    if(isset($_POST['resources']))
      {
        $results = runScenario($_POST['resources']);
        $dead = $results['dead'];
        $saved = $results['saved'];
      }
    $deadheight = 25 + (25*count($dead));
    $savedheight = 25 + (25*count($saved));
    if($deadheight > 200)
      {
        $deadheight = 200;
      }
    if($savedheight > 100)
      {
        $savedheight = 100;
      }
    
?>    
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="javascript/dhtmlgrid/dhtmlxgrid.css" />
  <script type="text/javascript" src="javascript/dhtmlgrid/dhtmlxcommon.js"></script>
  <script type="text/javascript" src="javascript/dhtmlgrid/dhtmlxgrid.js"></script>
  <script type="text/javascript" src="javascript/dhtmlgrid/dhtmlxgridcell.js"></script>
  <title>Bacon Map What If?</title>
	<script type="text/javascript">
  
  function doWhatIf() {
  document.whatif.submit();
  }
  
	function startUp() {
   parent.document.getElementById('mainwindowtitle').innerHTML = 'What If<span class="blue">?<\/span>';
   } 
   
  function doDeadRowClick(id,index) {
    if(index == 4) {
    parent.GB_myShow("Resource Details", "<?php echo $rootdir; ?>/details.php?id="+id,550,530);
    } 
    if(index == 5) {
    parent.GB_myShow("Contact Details", "<?php echo $rootdir; ?>/details.php?poc=1&id="+id,550,530);
    } 
  }
  
  function doSavedRowClick(id,index) {
    if(index == 4) {
    parent.GB_myShow("Resource Details", "<?php echo $rootdir; ?>/details.php?id="+id,550,530);
    } 
    if(index == 5) {
    parent.GB_myShow("Contact Details", "<?php echo $rootdir; ?>/details.php?poc=1&id="+id,550,530);
    }
  }
  
	</script>
</head>
<body onLoad="startUp()">
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<div class="framed">
<div class="minorheader">What if one of your resources goes down?</div>
<form name="whatif" action="whatif.php" method="post">
<div>
<?php
  // Get a nice list of all resources in the database
  $services = getResources('service');
  $servers = getResources('server');
  $devices = getResources('device');
  $databases = getResources('database');
  $boxes = getResources('box');
  $applications = getResources('application');
?>
<table class="formtable">
    <tr class="formtr">
      <td class="formtd" valign="top"> 
      Choose one or more resources: 
      </td>
      <td class="formtd"><select multiple name="resources[]" size="8">
<?php
    echo '<optgroup label="Boxes&nbsp;" class="boxdropdown" style="background-image: url(tree_images/server.png);">';
    foreach($boxes as $thisbox)
      {
        echo '<option value="box_'.$thisbox['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/server.png);">'.$thisbox['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    // --------
    echo '<optgroup label="Servers&nbsp;" class="boxdropdown" style="background-image: url(tree_images/computer.png);">';
    foreach($servers as $thisserver)
      {
        echo '<option value="server_'.$thisserver['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/computer.png);">'.$thisserver['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    // ---------
    echo '<optgroup label="Databases&nbsp;" class="boxdropdown" style="background-image: url(tree_images/database.png);">';
    foreach($databases as $thisdatabase)
      {
        echo '<option value="database_'.$thisdatabase['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/database.png);">'.$thisdatabase['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    // ----------
    echo '<optgroup label="Services&nbsp;" class="boxdropdown" style="background-image: url(tree_images/cog.png);">';
    foreach($services as $thisservice)
      {
        echo '<option value="service_'.$thisservice['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/cog.png);">'.$thisservice['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    // ----------
    echo '<optgroup label="Applications&nbsp;" class="boxdropdown" style="background-image: url(tree_images/application.png);">';
    foreach($applications as $thisapplication)
      {
        echo '<option value="application_'.$thisapplication['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/application.png);">'.$thisapplication['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    // ----------
    echo '<optgroup label="Devices&nbsp;" class="boxdropdown" style="background-image: url(tree_images/drive.png);">';
    foreach($devices as $thisdevice)
      {
        echo '<option value="device_'.$thisdevice['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/drive.png);">'.$thisdevice['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
?>
</select>
</td>
  </tr>
  <tr>
    <td>&nbsp;</td><td><input type="button" name="doit" value="Run Scenario" onClick="doWhatIf()" /></td>
  </tr>
</table>
</div>
 </form>
 <?php 
  if($results) 
    {
          // Get all the contacts in a nice list
      $poclist = array();
      $query = "SELECT first, last, poc_id from tbl_poc";
      $result = mysql_query($query);
      while($row = mysql_fetch_row($result))
        {
          $poclist[$row[2]]['name'] = $row[0].' '.$row[1];
        }
    }
?>
 <?php if($dead) { ?>
 <div class="minorheader">The following resources are no longer available:</div>
 <div id="gridbox" style="height:<?php echo $deadheight; ?>px;width:530px;"></div>
 <script>
  deadgrid = new dhtmlXGridObject('gridbox');
  deadgrid.setImagePath("javascript/dhtmlgrid/imgs/");
  deadgrid.setEditable(true);
  deadgrid.setSkin("mt");
  deadgrid.setHeader(" ,Type,Name,Role,Details,Contact");
  deadgrid.setInitWidths("28,80,125,120,80,*");
  deadgrid.setColTypes("img,ro,ro,ro,ro,ro");
  deadgrid.setColAlign("center,left,left,left,left,left");
  deadgrid.setColSorting("na,na,na,na,na,na");
  deadgrid.enableResizing("true,true,true,true,true,true");
  deadgrid.attachEvent("onRowSelect",doDeadRowClick);
  deadgrid.init();
</script>

<?php 
  
  $dead = array_reverse($dead);
  
  echo '<script>';
  foreach($dead as $res)
    {
      $bits = explode('_',$res);
      $id = $bits[1];
      $type = $bits[0];
      
      // Get the info for this thing
      $query = 'SELECT name, role, poc_id FROM tbl_'.$type.' WHERE '.$type.'_id = '.$id;
      $result = mysql_query($query);
      $row = mysql_fetch_assoc($result);
      $poc_id = $row['poc_id'];
      $pocname = $poclist[$poc_id]['name'];    
      $resname = trim($row['name']);
      $role = trim($row['role']);
      $pic = getPic($type);
      $reslink = '<a href=#>Details</a>';
      $poclink = '<a href=#>'.$pocname.'</a>';    
      echo 'deadgrid.addRow("'.$type.'_'.$id.'","tree_images/'.$pic.'.png,'.ucwords($type).','.$resname.','.$role.','.$reslink.','.$poclink.'",0);';    
    }
  echo '</script>';
  } ?>
<?php if($saved) { ?>
<br />
<div class="minorheader">The following resources were saved by a group redundancy:</div>
 <div id="savedgridbox" style="height:<?php echo $savedheight; ?>px;width:530px;"></div>
 <script>
  savedgrid = new dhtmlXGridObject('savedgridbox');
  savedgrid.setImagePath("javascript/dhtmlgrid/imgs/");
  savedgrid.setEditable(true);
  savedgrid.setSkin("mt");
  savedgrid.setHeader(" ,Type,Name,Role,Details,Contact");
  savedgrid.setInitWidths("28,80,125,120,80,*");
  savedgrid.setColTypes("img,ro,ro,ro,ro,ro");
  savedgrid.setColAlign("center,left,left,left,left,left");
  savedgrid.setColSorting("na,na,na,na,na,na");
  savedgrid.enableResizing("true,true,true,true,true,true");
  savedgrid.attachEvent("onRowSelect",doSavedRowClick);
  savedgrid.init();
</script>
<?php 
  
  $saved = array_reverse($saved);
  echo '<script>';
  foreach($saved as $res)
    {
      $bits = explode('_',$res);
      $id = $bits[1];
      $type = $bits[0];
      
      // Get the info for this thing
      $query = 'SELECT name, role, poc_id FROM tbl_'.$type.' WHERE '.$type.'_id = '.$id;
      $result = mysql_query($query);
      $row = mysql_fetch_assoc($result);
      $poc_id = $row['poc_id'];
      $pocname = $poclist[$poc_id]['name'];
      $resname = trim($row['name']);
      $role = trim($row['role']);
      $pic = getPic($type);
      $reslink = '<a href=#>Details</a>';
      $poclink = '<a href=#>'.$pocname.'</a>';    
      echo 'savedgrid.addRow("'.$type.'_'.$id.'","tree_images/'.$pic.'.png,'.ucwords($type).','.$resname.','.$role.','.$reslink.','.$poclink.'",0);';    
    }
  echo '</script>';
} ?>
</div>
</body>
</html>
