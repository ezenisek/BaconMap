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
    
    This file is the point of contact editor menu for BaconMap.
    
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
    authorize(2,"index.php?frame=".urlencode('contacts.php'));
    
    $query = "SELECT poc_id,poc_type,first,last FROM tbl_poc ORDER BY poc_type, first DESC, last DESC";
    $result = mysql_query($query);
    if(mysql_num_rows($result) == 0)
      $contacts = false;
    else
      {
        $contacts = array();
        $i = 0;
        while($row = mysql_fetch_assoc($result))
          {
                $contacts[$i]['name'] = $row['first'].' '.$row['last'];
                $contacts[$i]['id'] = $row['poc_id'];
                $contacts[$i]['type'] = $row['poc_type'];
                $i++;
          }
        $height = 25+(25*count($contacts));
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
  <title>Bacon Map Contact Information</title>
	<script type="text/javascript">
  
  var selected = '';
  
	function startUp() {
   parent.document.getElementById('mainwindowtitle').innerHTML = 'Point of <span class="blue">Contact<\/span> Management';
   } 
   
  function doRowClick(id,index) {
    if(index == 4) {
    parent.GB_myShow("Contact Details", "<?php echo $rootdir; ?>/pocdetails.php?id="+id,550,530);
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
  document.contact.task.value = 'add';
  document.getElementById('contactframe').src = "editcontact.php?task=add";
  }
  
  function editContact() {
  if(selected) {
    document.contact.task.value = 'edit';
    document.contact.id.value = selected;
    document.getElementById('contactframe').src = "editcontact.php?task=edit&id="+selected; 
    }
  else
    alert("You must select a contact to edit.");
  }
  
  function deleteContact() {
  if(selected) {
    if(confirm("The selected Contact will be deleted.  All resources having this "+
    "person as a point of contact will have that infomation removed and will default "+
    "to having no point of contact until they are updated.\n\nContinue?")) {
    document.contact.task.value = 'delete';
    document.contact.id.value = selected;
    document.getElementById('contactframe').src = "docontact.php?task=delete&id="+selected;;
    }
    }
    else
      alert("You must select a contact to delete.");
  }
	</script>
</head>
<body onLoad="startUp()">
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<?php if($displayhelp) { ?>
   <div id="help">Learn About Contacts<br /><a href="#">View Documentation</a></div>
<?php } ?>
<div class="framed">
<div class="minorheader">Currently Existing Points of Contact</div>
<form name="contact" action="docontact.php" method="post">
<input type="hidden" name="task" />
<input type="hidden" name="id" />
<?php 
  if($contacts)
    {
    echo '<br /><center><div id="gridbox" style="height:'.$height.'px;width:540px;"></div>';
    echo '<script type="text/javascript">';
    ?>
    mygrid = new dhtmlXGridObject('gridbox');
    mygrid.setImagePath("javascript/dhtmlgrid/imgs/");
    mygrid.setEditable(true);
    mygrid.setSkin("mt");
    mygrid.setHeader(", ,Contact Type,Name,View Details");
    mygrid.setInitWidths("30,30,120,160,*");
    mygrid.setColTypes("ra,img,ro,ro,ro");
    mygrid.setColAlign("center,left,left,left,left");
    mygrid.setColSorting("na,na,na,na,na");
    mygrid.enableResizing("true,true,true,true,true");
    mygrid.setOnCheckHandler(doCheckbox);
    mygrid.attachEvent("onRowSelect",doRowClick);
    mygrid.init();
    <?php
    //print_r($groups);
    foreach($contacts as $thiscontact)
      {
        $ctype = $thiscontact['type'];
        $cid = $thiscontact['id'];
        $cname = $thiscontact['name'];
        $pic = 'status_online';
        $link1 = '<a href=#>Show Contact Details<\/a>';
        echo 'mygrid.addRow("'.$cid.'","\''.$cid.'\',tree_images/'.$pic.'.png,'.ucwords($ctype).','.$cname.','.$link1.'",0);';
        echo 'mygrid.cells(\''.$cid.'\',\'0\').setChecked(false);';
      }
    echo '</script>';
      if($_SESSION['level'] < 2) {
      echo '<hr style="width:540px;" /><table width="540"><tr>';
      echo '<td align="left"><input type="button" name="add" value="Add New Contact" onClick="addNew()"/></td>';
      echo '<td align="center"><input type="button" name="add" value="Edit Selected" onClick="editContact()"/></td>';
      echo '<td align="right"><input type="button" name="add" value="Remove Selected" onClick="deleteContact()"/></td>';
      echo '</tr></table>';
      }
    echo '</center><br />';
    }
    else
    {
      echo 'There are no Contacts in the database. ';
      if($_SESSION['level'] < 2) 
        echo '<a href="#" onClick="addNew()">Add a Contact</a><br /><br />';
    }
?>
<div>
<center>
<?php if($_SESSION['level'] < 2) { ?>
<iframe id="contactframe" scrolling="no" width="560" height="250" name="contactframe" frameborder="0"></iframe>
<?php } ?>
</center>
</div>
 </form>
</div>
</body>
</html>
