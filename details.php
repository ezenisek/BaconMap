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
    
  require_once('includes/settings.php');
  require_once('includes/functions.php');
  dbConnect($dbhost,$database,$username,$password);
  
  // Now we check that the user is logged in.  If not, we forward to the login page.
  authorize(2,"index.php");
  
  if(!isset($_REQUEST['id']))
    {
      $error = "I'm sorry, the page you requested could not be displayed because
      a resource ID could not be found.";
      require('includes/error.php');
      exit();
    }

  
  $id = $_REQUEST['id'];
  $idbits = explode('_',$id);
  $type = $idbits[0];
  $id = $idbits[1];
  $pic = getPic($type);
  
  if($type == 'group')
    {
      header("Location: groupdetails.php?id=$id");
    }    
  
  $query = 'SELECT * FROM tbl_'.$type.' WHERE '.$type."_id = '$id'";
  $result = mysql_query($query);
  $resourcerow = mysql_fetch_assoc($result);
  if(mysql_num_rows($result) == 0)
    {
      $error = "I'm sorry, the resource ID that was requested could not be found.";
      require('includes/error.php');
      exit();
    }
  if(isset($_REQUEST['poc']))
    {
      header("Location: pocdetails.php?id=$resourcerow[poc_id]");
    }
  if($impact = $resourcerow['impacted'])
    {
      switch($impact) {
          case '1':
            $impacttext = 'Foreign Only'; 
          break;
          case '2':
            $impacttext = 'External Only';
          break;
          case '3':
            $impacttext = 'External and Foreign'; 
          break;
          case '4':
            $impacttext = 'Internal Only';
          break;
          case '5': 
            $impacttext = 'Internal and Foreign';
          break;
          case '6':
            $impacttext = 'Internal and External';
          break;
          case '7':
            $impacttext = 'Internal, External, and Foreign';
          break;
          }
    }
  else
    $impacttext = 'None Listed';
  /*
  if(isset($resourcerow['host_id']))
  {  
    $query = "SELECT name FROM tbl_box WHERE box_id = '$resourcerow[host_id]'";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    if($row[0])
      $hostname = $row[0];
    else
      $hostname = 'None Listed';
   }
   else
    $hostname = "Not Available";
  */
  $query = "SELECT * FROM tbl_poc WHERE poc_id = '$resourcerow[poc_id]'";
  $result = mysql_query($query);  
  if(mysql_num_rows($result) == 0)
    {
      $pocrow = false;
    }
  else
    {
      $pocrow = mysql_fetch_assoc($result);
    }
  
  $header = ucwords($type).': '.$resourcerow['name'];  
?>  
  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Resource Details for <?php echo $header; ?></title>
</head>
<body>
<div id="boxed">
  <h2 class="header"><img src="tree_images/<?php echo $pic; ?>.png" /> <span class="blue">Resource</span> Details for <?php echo $header; ?></h2>
  <center>
  <table class="details">
    <tr class="detailstr">
      <td class="detailstd" width="20%"><strong>Name:</strong></td>
      <td class="detailstd"><?php echo $resourcerow['name']; ?></td>
    </tr>
    <tr class="detailstr">
      <td class="detailstd"><strong>Description:</strong></td>
      <td class="detailstd"><?php echo $resourcerow['description']; ?></td>
    </tr>
    <tr class="detailstr">
      <td class="detailstd"><strong>Role:</strong></td>
      <td class="detailstd"><?php echo $resourcerow['role']; ?></td>
    </tr>
    <tr class="detailstr">
      <td class="detailstd" NOWRAP><strong>Purchase/Install Date:</strong></td>
      <td class="detailstd"><?php if(!empty($resourcerow['date_purchased']) && strtotime($resourcerow['date_purchased'])) echo date('Y-m-d',strtotime($resourcerow['date_purchased'])); ?></td>
    </tr>
    <tr class="detailstr">
      <td class="detailstd"><strong>Vendor:</strong></td>
      <td class="detailstd"><?php echo $resourcerow['vendor']; ?></td>
    </tr>
    <tr class="detailstr">
      <td class="detailstd"><strong>Annual Cost:</strong></td>
      <td class="detailstd"><?php echo $resourcerow['annual_cost']; ?></td>
    </tr>
    <tr class="detailstr">
      <td class="detailstd"><strong>Impact:</strong></td>
      <td class="detailstd"><?php echo $impacttext; ?></td>
    </tr>
    <tr class="detailstr">
      <td class="detailstd"><strong>Last Maintained:</strong></td>
      <td class="detailstd"><?php if(!empty($resourcerow['last_maint_date']) && strtotime($resourcerow['last_maint_date'])) echo date('Y-m-d',strtotime($resourcerow['last_maint_date'])); ?></td>
    </tr>
    <?php
      $groupquery = "SELECT DISTINCT name, group_id FROM tbl_group WHERE mid = '$id' AND mtype = '$type'";
      $groupresult = mysql_query($groupquery);
      //echo $groupquery;
      if(mysql_num_rows($groupresult) != 0)
        {
          $grouprow = mysql_fetch_row($groupresult);
          echo '<tr class="detailstr">';
          echo '<td><strong>Group:</strong></td>';
          echo '<td><a href="groupdetails.php?id='.$grouprow[1].'">'.$grouprow[0].'</a></td>';
          echo '</tr>';
        }
    ?>
  </table>
  <?php
    $query = "SELECT name FROM tbl_upload where objtype = '$type' and objid = '$id'";
    $result = mysql_query($query);
    if(mysql_num_rows($result))
      {
      echo '<table class="details">
            <tr class="detailstr">
            <td class="detailstd">';
      echo '<img src="tree_images/book_open.png" /> This resource has <a href="'.$rootdir.'/documents.php?id='.$type.'_'.$id.'">documentation.</a>';
      echo '</td></tr></table>';
      }
  ?>
<?php
    /* Now we do resource specific details */
    
    if($type == 'box') {
?>
  <table class="details">
    <tr>
      <td class="detalstd" width="30%">Number of CPUs:</td><td class="detailstd blue"><?php echo $resourcerow['cpu_num']; ?></td>
    </tr>
    <tr>
      <td class="detalstd">CPU Speed:</td><td class="detailstd blue"><?php echo $resourcerow['cpu_speed']; ?> MHz</td>
    </tr>
    <tr>
      <td class="detalstd">Memory:</td><td class="detailstd blue"><?php echo $resourcerow['memory']; ?> GB</td>
    </tr>
    <tr>
      <td class="detalstd">Disk Space:</td><td class="detailstd blue"><?php echo $resourcerow['disk_space']; ?> GB</td>
    </tr>
    <tr>
      <td class="detalstd">RAID: </td><td class="detailstd blue"><?php if($resourcerow['RAID']) echo 'Yes'; else echo 'No'; ?></td>
    </tr>
    <tr>  
      <td class="detailstd">Virtual OS: </td><td class="detailstd blue"><?php if($resourcerow['virtual_os']) echo 'Yes'; else echo 'No'; ?></td>
    </tr>
    <tr>
      <td class="detalstd">Serial Number:</td><td class="detailstd blue" colspan="3"><?php echo $resourcerow['serial']; ?></td>
    </tr>
    <tr>
      <td class="detalstd">Model:</td><td class="detailstd blue" colspan="3"><?php echo $resourcerow['model']; ?></td>
    </tr>
    <tr>
      <td class="detalstd">Physical Location:</td><td class="detailstd blue" colspan="3"><?php echo $resourcerow['location']; ?></td>
    </tr>
   </table>     
<?php  } elseif($type == 'server') { ?>
   <table class="details"> 
    <tr>  
      <td class="detailstd" width="30%">Operating System: </td><td class="detailstd blue"><?php echo $resourcerow['OS']; ?></td>
    </tr>
    <tr>  
      <td class="detailstd">Virtual OS: </td><td class="detailstd blue"><?php if($resourcerow['virtual']) echo 'Yes'; else echo 'No'; ?></td>
    </tr>
   </table>
<?php  } elseif($type == 'device') { ?>
  <table class="details">
   <tr>
      <td class="detalstd" width="30%">Serial Number:</td><td class="detailstd blue" colspan="3"><?php echo $resourcerow['serial']; ?></td>
    </tr>
    <tr>
      <td class="detalstd">Model:</td><td class="detailstd blue" colspan="3"><?php echo $resourcerow['model']; ?></td>
    </tr>
    <tr>
      <td class="detalstd">Physical Location:</td><td class="detailstd blue" colspan="3"><?php echo $resourcerow['location']; ?></td>
    </tr>
   </table> 
<?php  } elseif($type == 'application') { ?>

<?php  } elseif($type == 'service') { ?>

<?php  } elseif($type == 'database') { ?>
   <table class="details"> 
    <tr>  
      <td class="detailstd" width="30%">Database Type: </td><td class="detailstd blue"><?php echo $resourcerow['type']; ?></td>
    </tr>
   </table>
<?php  } ?>
   <table class="details">
   <tr>
      <td class="detailstd" colspan="4"><div class="minorheader">Point of Contact</div></td>
   </tr>
   <tr>
      <td class="detalstd">Name:</td><td class="detailstd blue"><strong>
      <a href="pocdetails.php?id=<?php echo $resourcerow['poc_id']; ?>">
      <?php echo $pocrow['first']; if($pocrow['middle']) echo ' '.$pocrow['middle']; echo ' '.$pocrow['last']; ?>
      </a></strong></td>
      <td class="detailstd">Contact Type: </td><td class="detailstd blue"><?php echo $pocrow['poc_type']; ?></td>
   </tr>
   <tr>
      <td class="detalstd">Phone:</td><td class="detailstd blue"><?php echo formatPhone($pocrow['phone']); ?></td>
      <td class="detalstd">E-Mail:</td><td class="detailstd blue"><a href="mailto:<?php echo $pocrow['email']; ?>" ><?php echo $pocrow['email']; ?></a></td>
   </tr>
   </table>   
</center>
</div>	
</body>
</html>
