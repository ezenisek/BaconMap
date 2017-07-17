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
    
    This file is the group manager file for BaconMap.
    
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
    authorize(2,"index.php?frame=".urlencode('group.php'));

    $query = "SELECT DISTINCT group_id, mtype, name FROM tbl_group ORDER BY group_id";
    $result = mysql_query($query);
    if(mysql_num_rows($result) == 0)
      $groups = false;
    else
      {
        $groups = array();
        $i = 0;
        while($row = mysql_fetch_assoc($result))
          {
                $thisid = $row['group_id'];
                $type = $row['mtype'];
                $groups[$i]['name'] = $row['name'];
                $groups[$i]['id'] = $row['group_id'];
                $groups[$i]['type'] = $type;
                
                $m = 0;
                $midquery = 'SELECT '.$type.'_id, name FROM tbl_'.$type.' WHERE '.$type.'_id IN 
                (SELECT mid FROM tbl_group WHERE group_id = '.$thisid.')';
                //echo $midquery;
                $midresult = mysql_query($midquery);
                while($midrow = mysql_fetch_row($midresult))
                  {
                      $groups[$i]['members'][$m]['id'] = $midrow[0];
                      $groups[$i]['members'][$m]['name'] = $midrow[1];
                      $m++;
                  }
                $i++;
          }
        $height = 25+(25*count($groups));
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
  <title>Bacon Map Group Page</title>
	<script type="text/javascript">
  
  var selected = '';
  
	function startUp() {
   parent.document.getElementById('mainwindowtitle').innerHTML = '<span class="blue">Group<\/span> Management';
   } 
   
  function doRowClick(id,index) {
    if(index == 4) {
    parent.GB_myShow("Group Details", "<?php echo $rootdir; ?>/groupdetails.php?id="+id,500,530);
    }
    else
    {
     	mygrid.cells(id,'0').setChecked(true);
     	doCheckbox(id,index,true);
    }     
  }
  
  function doCheckbox(id,index,state) {
    if(state)
      {
        selected = id;
      }
    else
      {
        selected = false;
      }
  }
  
  function addNew() {
  document.getElementById('groupframe').src = "editgroup.php";
  }
  
  function editGroup() {
  if(selected) {
    document.group.id.value = selected;
    document.getElementById('groupframe').src = "editgroup.php?task=edit&id="+selected;
    }
  else
    alert("You must select a group to edit.");
  }
  
  function disbandGroup() {
  if(selected) {
    if(confirm("The selected group will be disbanded.  Group members and all " +
    "relationships will remain intact, however group members will no longer be " +
    "redundant.\n\nContinue?")) {
    document.group.id.value = selected;
    document.group.action = 'dogroup.php?task=disband&id='+selected;
    document.group.submit();
    }
    }
    else
      alert("You must select a group to disband.");
  }
	</script>
</head>
<body onLoad="startUp()">
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<div class="framed">
<?php if($displayhelp) { ?>
   <div id="help">Learn About Groups<br /><a href="#">View Documentation</a></div>
<?php } ?>
<div class="minorheader">Currently Existing Groups</div>
<form name="group" action="dogroup.php" method="post">
<?php 
  if($groups)
    {
    echo '<br /><center><div id="gridbox" style="height:'.$height.'px;width:540px;"></div>';
    echo '<script type="text/javascript">';
    ?>
    mygrid = new dhtmlXGridObject('gridbox');
    mygrid.setImagePath("javascript/dhtmlgrid/imgs/");
    mygrid.setEditable(true);
    mygrid.setSkin("mt");
    mygrid.setHeader(", ,Group Type,Name,View Details");
    mygrid.setInitWidths("30,30,100,150,*");
    mygrid.setColTypes("ra,img,ro,ro,ro");
    mygrid.setColAlign("center,left,left,left,left");
    mygrid.setColSorting("na,na,na,na,na");
    mygrid.enableResizing("true,true,true,true,true");
    mygrid.setOnCheckHandler(doCheckbox);
    mygrid.attachEvent("onRowSelect",doRowClick);
    mygrid.init();
    <?php
    //print_r($groups);
    foreach($groups as $thisgroup)
      {
        $gtype = $thisgroup['type'];
        $group_id = $thisgroup['id'];
        $gname = $thisgroup['name'];
        $pic = getPic($gtype);
        $link1 = '<a href=#>Show Member Information ('.count($thisgroup['members']).')<\/a>';
        echo 'mygrid.addRow("'.$group_id.'","\''.$group_id.'\',tree_images/'.$pic.'.png,'.ucwords($gtype).','.$gname.','.$link1.'",0);';
        echo 'mygrid.cells(\''.$group_id.'\',\'0\').setChecked(false);';
      }
    echo '</script>';
      if($_SESSION['level'] < 2) {
      echo '<hr style="width:540px;" /><table width="540"><tr>';
      echo '<td align="left"><input type="button" name="add" value="Create New Group" onClick="addNew()"/></td>';
      echo '<td align="center"><input type="button" name="add" value="Edit Selected Group" onClick="editGroup()"/></td>';
      echo '<td align="right"><input type="button" name="add" value="Disband Selected Group" onClick="disbandGroup()"/></td>';
      echo '</tr></table>';
      }
    echo '</center><br />';
    }
    else
    {
      echo 'There are no groups currently configured. ';
      if($_SESSION['level'] < 2) 
      echo '<a href="#" onClick="addNew()">Create a Group</a><br /><br />';
    }
?>
<div id="addform">
<?php if($_SESSION['level'] < 2) { ?>
<iframe id="groupframe" scrolling="no" width="560" height="250" name="groupframe" frameborder="0"></iframe>
<?php } ?>
</div>
 </form>
</div>
</body>
</html>
