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
    
    This file is the add / edit dependency menu for BaconMap.
    
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


if(!isset($_SESSION['currentparents']))
  {
    $currentparents = array();
    $_SESSION['currentparents'] = array();
  }
  else
  {
    $currentparents = $_SESSION['currentparents'];
  }
  
    // Make sure we have a type sent to us so we can filter possible parents
    if(isset($_REQUEST['thistype']))
      $thistype = $_REQUEST['thistype'];
    else
      {
          $error = "I'm sorry, dependencies can not be displayed because
          a type identifier is not specified.";
          require('includes/error.php');
          exit();
      }
    
    // If we're adding a new resource, we need to clear the session.
    // If we're editing a resource, we need to find and list its parents.
    if(isset($_REQUEST['new']) && $_REQUEST['new'])
      unset($_SESSION['currentparents']);
    elseif(isset($_REQUEST['id']) && $_REQUEST['id'])
      {
        $idbits = explode('_',$_REQUEST['id']);
        $ttype = $idbits[0];
        $tid = $idbits[1];
        $temparray = getParents($ttype,$tid);
        $temparray2 = array();
        if(is_array($temparray))
        foreach($temparray as $p)
          {
            $temparray2[] = $p['type'].'_'.$p['id'];
          }
        $temparray2 = groupCombine($temparray2);
        $_SESSION['currentparents'] = $temparray2;
      }
      
    
    // This function creates the javascript string to add a parent to the
    // list.
    
    function addParent($id)
      {
        $idbits = explode('_',$id);
        $parenttype = $idbits[0];
        $parentid = $idbits[1];
        $query = 'SELECT name FROM tbl_'.$parenttype.' where '.$parenttype."_id = '$parentid'";
        $result = mysql_query($query);
        $row = mysql_fetch_row($result);
        $parentname = $row[0];
        if($parenttype != 'group')
          $pic = getPic($parenttype);
        else
          {
            $groupquery = "SELECT mtype FROM tbl_group WHERE group_id = '$parentid'";
            $groupresult = mysql_query($groupquery);
            $grouprow = mysql_fetch_row($groupresult);
            $pic = getPic($grouprow[0]);
          }
        
          $link = '<a href=#>Tell Me More<\/a>';
          return 'mygrid.addRow("'.$parenttype.'_'.$parentid.'","0,tree_images/'.$pic.'.png,'.ucwords($parenttype).','.$parentname.','.$link.'",0);';
     
      }
    
    // Get our first parent, if any.  This way we know what options to leave.
    if(isset($_GET['parent']))
      {
        if(isset($_SESSION['currentparents']))
          $temparray = $_SESSION['currentparents'];
        else
          $temparray = array();
        $temparray[] = $_GET['parent'];
        $_SESSION['currentparents'] = $temparray;
      }
    
    // If we're adding one, grab it and add it.
    if(isset($_GET['addparent']))
      {
        if(isset($_SESSION['currentparents']))
          $temparray = $_SESSION['currentparents'];
        else
          $temparray = array();
        $temparray[] = $_GET['addparent'];
        $_SESSION['currentparents'] = $temparray;
      }
    
    // If we're removing one or more selected parents from the list,
    // throw em in an array and do a diff to kick em out.
    if(isset($_GET['removeparent']))
      {
        $removearray = explode(',',$_GET['removeparent']);
        $temparray = $_SESSION['currentparents'];
        $_SESSION['currentparents'] = array_diff($temparray,$removearray);
      }
      
    if(isset($_GET['cleargroups']) && $_GET['cleargroups'] == 1)
      {
         $temparray = $_SESSION['currentparents'];
         foreach($temparray as $key => $thisparent)
          {
            $idbits = explode('_',$thisparent);
            $newid = $idbits[0].'_'.$idbits[1];
            $temparray[$key] = $newid;
          }
         $_SESSION['currentparents'] = $temparray;   
      }
      
    // Make sure none of the parents in the list are of the same lineage.
    // If they are, use the last one added.
    
    $startcount = count($currentparents);
    $donotinclude = array(); 
    $parentarray = array();     
    foreach($currentparents as $thisparent)
    {
      $idbits = explode("_",$thisparent);
      $id = $idbits[1];
      $type = $idbits[0];
      if($type != 'group')
        $parentarray = breakDown(getParentsRecursive($type,$id),'parents');
      else
        { // Special case for groups
          $groupquery = "SELECT mid,mtype FROM tbl_group WHERE group_id = '$id'";
          $groupresult = mysql_query($groupquery);
          $temparray = array();
          while($row = mysql_fetch_row($groupresult))
            {
              $typeid = $row[1].'_'.$row[0];
              $parentarray[] = $typeid;
              $temparray = getParentsRecursive($row[1],$row[0]);
              $parentarray = array_merge($parentarray,breakDown($temparray,'parents'));
            }
        }
    }
    $donotinclude = array_merge($donotinclude,$parentarray);
    $currentparents = array_diff($currentparents,$donotinclude);
    $endcount = count($currentparents);
    if($startcount != $endcount)
      $alert = 'Because of the last parent resource you added, other parents have been removed. This may be due to lineage or group membership.  Please see the documentation for more details.';
    else
      $alert = 0;  
    
    // Read from the session and get all parents we've been working with.
    // Use the number of parents in the list to set the listbox height.
    if(isset($_SESSION['currentparents']))
    $currentparents = array_unique($_SESSION['currentparents']);
    else
    $currentpartents = array();
    $rowheight = 25;
    if(count($currentparents))
      $height = $rowheight + ($rowheight * count($currentparents));
    else
      $height = 50; 
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
	<title>Bacon Map Dependencies</title>
	<script type="text/javascript">
	var selectedparents = new Array();
	function doAdd() {
	window.location = 'dependencies.php?thistype=<?php echo $thistype; ?>&addparent='+document.depgrid.parents.value;
	}
	function doRemove() {
	window.location = 'dependencies.php?thistype=<?php echo $thistype; ?>&removeparent='+selectedparents;
	}
	function setHeight() {
	var newheight = 125 + <?php echo $height; ?>;
	parent.document.getElementById('depframe').height = newheight;
	parent.document.getElementById('dependencies').style.height = newheight+5+'px';
	}
	function doCheckbox(id,index,state) {
	if(state)
	 {
	   selectedparents.push(id);
	 }
	else
	 {
	   var i=0;
	   while(i < selectedparents.length) {
	     if(selectedparents[i] == id) {
	       selectedparents.splice(i,1);
	     }
	     else
	       i++;
	     }
	  }
	}
	function doRowClick(id,index) {
	 if(index == 4) {
	   parent.parent.GB_myShow("More Details", "<?php echo $rootdir; ?>/details.php?id="+id,600,600);
    }
   else
    {
     	if(mygrid.cells(id,'0').isChecked())
        {
          mygrid.cells(id,'0').setChecked(false);
          doCheckbox(id,index,false);
        }
      else
        {
          mygrid.cells(id,'0').setChecked(true);
         	doCheckbox(id,index,true);
     	  }
    }     
  }
  </script>
</head>
<body onLoad="setHeight()">
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<form name="depgrid" method="post" action="self">
Current Parent Dependencies for This Resource:<br />
<div id="gridbox" style="height:<?php echo $height; ?>px;width:540px;"></div>
<script type="text/javascript">
<?php if($alert) 
        echo 'alert("'.$alert.'");';
?>
mygrid = new dhtmlXGridObject('gridbox');
mygrid.setImagePath("javascript/dhtmlgrid/imgs/");
mygrid.setEditable(true);
mygrid.setSkin("mt");
mygrid.setHeader(" , ,Parent Type,Name,Info");
mygrid.setInitWidths("30,30,100,150,*");
mygrid.setColTypes("ch,img,ro,ro,ro");
mygrid.setColAlign("center,left,left,left,left");
mygrid.setColSorting("na,na,na,na,na");
mygrid.enableResizing("true,true,true,true,true");
mygrid.setOnCheckHandler(doCheckbox);
mygrid.attachEvent("onRowSelect",doRowClick);
mygrid.init();
<?php  if(isset($currentparents)) { 
    foreach($currentparents as $thisparent) {
      echo addParent($thisparent);
    }
} ?>
</script>
<input type="button" value="Delete Selected" onClick="doRemove()" />
<br />
<?php if($displayhelp) { ?>
   <div id="help">Learn About Dependencies<br /><a href="#">View Documentation</a></div>
<?php } ?>
<br />
Add Dependency Parents From the Following List:<br />
<select name="parents" id="parentbox">
<?php
    
    /* 
    Now for the fun part of this page.  This is where we populate the 
    drop down box for the available parents.  This may seem simple, but there's
    a bunch of rules that parents and children need to follow.  In order to enforce
    those rules, we need to make sure we don't have any parents in the dropdown
    list that might cause rule breakages later.  Since each type of resource has
    different rules, we run a switch statement to separate the code depending on 
    what type of resource is being added.
    
    In addition, we need to check what parents are already added to this resource
    and ensure we don't allow the dropdown to contain any of the parents of the
    parent resource.  This would allow double parantage (grandfather is also father),
    and we don't want that.  We'll go through our current parent listing for this 
    resource and find all the parents of the parents, and their parents, and so on,
    and ensure that none of those resources are in the list.
    
    If we're editing this resource, we also need to make sure that any of its
    children are not in the list.  If a child of the current resource is turned
    into its parent, then we'll have a loop and that's bad.
    */
    
    $parents = array();
    $i = 0;
    switch($thistype) {
      case 'box':
      // Boxes can only have a device as a parent.
      $devices = getResources('device');
      $groups = getGroups(array('device'));
      break;
      case 'server':
      // Servers can have a box, server, or device as a parent.
      $devices = getResources('device');
      $servers = getResources('server');
      $boxes = getResources('box');
      $groups = getGroups(array('device','server','box'));
      break;
      case 'application':
      // Applications can have databases, devices, and services as a parent.
      $databases = getResources('database');
      $services = getResources('service');
      $devices = getResources('device');
      $groups = getGroups(array('device','service','database'));
      break;
      case 'database':
      // Databases can only have a service, devices, and servers as a parent.
      $services = getResources('service');
      $servers = getResources('server');
      $devices = getResources('device');
      $groups = getGroups(array('device','server','service'));
      break;
      case 'service':
      // Services can have other services, servers, devices, or databases as a parent.
      $services = getResources('service');
      $servers = getResources('server');
      $devices = getResources('device');
      $databases = getResources('database');
      $groups = getGroups(array('device','server','service','database'));
      break;
      case 'device':
      // Devices can have anything as a parent except an application
      $services = getResources('service');
      $servers = getResources('server');
      $devices = getResources('device');
      $databases = getResources('database');
      $boxes = getResources('box');
      $groups = getGroups(array('device','server','service','database','box'));
      break;
    } 

// Now we do a parantage check on all the currently selected parents, get their
// parents, and add them to an array we'll use to compare to our listbox.  If 
// a resource exists in this array, we need to take it out of the listbox for
// selection. 

$donotinclude = array();

foreach($currentparents as $thisparent)
  {
    $idbits = explode("_",$thisparent);
    $id = $idbits[1];
    $type = $idbits[0];
    if(isset($idbits[2]))
      $group = $idbits[2];
    $parentarray = array();
    if($type != 'group')
        $parentarray = breakDown(getParentsRecursive($type,$id),'parents');
      else
        { // Special case for groups
          $groupquery = "SELECT mid,mtype FROM tbl_group WHERE group_id = '$id'";
          $groupresult = mysql_query($groupquery);
          $temparray = array();
          while($row = mysql_fetch_row($groupresult))
            {
              $typeid = $row[1].'_'.$row[0];
              $parentarray[] = $typeid;
              $temparray = getParentsRecursive($row[1],$row[0]);
              $parentarray = array_merge($parentarray,breakDown($temparray,'parents'));
            }
        }
    $donotinclude = array_merge($donotinclude,$parentarray);
    $donotinclude[] = $thisparent;
    $donotinclude[] = $type.'_'.$id; 
  }  

// If we're editing we also need to exclude the current resource and all of 
// it's children.
if(isset($tid))
  { 
    $childarray = array();
    $childarray = breakDown(getChildrenRecursive($ttype,$tid),'children');
    $donotinclude = array_merge($donotinclude,$childarray);
    $donotinclude[] = $ttype.'_'.$tid;
  }


if(isset($groups) && $groups)
  {
    foreach($groups as $key => $value)
      {
        if(in_array($value['typeid'],$donotinclude))
          unset($groups[$key]);
      }
    if(count($groups) != 0) 
    {
    echo '<optgroup label="Groups&nbsp;" class="boxdropdown" style="background-image: url(tree_images/group.png);">';
    foreach($groups as $thisgroup)
      {
        echo '<option value="group_'.$thisgroup['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/'.getPic($thisgroup['type']).'.png);">'.$thisgroup['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    }  
  }
if(isset($boxes) && $boxes)
  {
    foreach($boxes as $key => $value)
      {
        if(in_array($value['typeid'],$donotinclude))
          unset($boxes[$key]);
      }
    if(count($boxes) != 0) 
    {
    echo '<optgroup label="Boxes&nbsp;" class="boxdropdown" style="background-image: url(tree_images/server.png);">';
    foreach($boxes as $thisbox)
      {
        echo '<option value="box_'.$thisbox['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/server.png);">'.$thisbox['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    }  
  }
if(isset($servers) && $servers)
  { 
    foreach($servers as $key => $value)
      {
        if(in_array($value['typeid'],$donotinclude))
          unset($servers[$key]);
      }
    if(count($servers) != 0) 
    {
    echo '<optgroup label="Servers&nbsp;" class="boxdropdown" style="background-image: url(tree_images/computer.png);">';
    foreach($servers as $thisserver)
      {
        echo '<option value="server_'.$thisserver['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/computer.png);">'.$thisserver['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    }
  }
if(isset($databases) && $databases)
  {
    foreach($databases as $key => $value)
      {
        if(in_array($value['typeid'],$donotinclude))
          unset($databases[$key]);
      }
    if(count($databases) != 0) 
    {
    echo '<optgroup label="Databases&nbsp;" class="boxdropdown" style="background-image: url(tree_images/database.png);">';
    foreach($databases as $thisdatabase)
      {
        echo '<option value="database_'.$thisdatabase['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/database.png);">'.$thisdatabase['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    }
  }
if(isset($services) && $services)
  {
    foreach($services as $key => $value)
      {
        if(in_array($value['typeid'],$donotinclude))
          unset($services[$key]);
      }
    if(count($services) != 0) 
    {
    echo '<optgroup label="Services&nbsp;" class="boxdropdown" style="background-image: url(tree_images/cog.png);">';
    foreach($services as $thisservice)
      {
        echo '<option value="service_'.$thisservice['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/cog.png);">'.$thisservice['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    }
  }
if(isset($devices) && $devices)
  {
    foreach($devices as $key => $value)
      {
        if(in_array($value['typeid'],$donotinclude))
          unset($devices[$key]);
      }
    if(count($devices) != 0) 
    {
    echo '<optgroup label="Devices&nbsp;" class="boxdropdown" style="background-image: url(tree_images/drive.png);">';
    foreach($devices as $thisdevice)
      {
        echo '<option value="device_'.$thisdevice['id'].'" class="boxdropdown" 
        style="background-image: url(tree_images/drive.png);">'.$thisdevice['name'].'&nbsp;</option>';
      }
    echo '</optgroup>';
    }
  }  
?>
</select>
<input type="button" value="Add as Parent" onClick="doAdd()" />
</form>
</body>
</html>
