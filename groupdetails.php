<?php
  require_once('includes/settings.php');
  
  if(!isset($_REQUEST['id']))
    {
      $error = "I'm sorry, the page you requested could not be displayed because
      a resource ID could not be found.";
      require('includes/error.php');
      exit();
    }
  
  require_once('includes/functions.php');
  dbConnect($dbhost,$database,$username,$password);
  
  // Now we check that the user is logged in.  If not, we forward to the login page.
  authorize(2,"groupdetails.php");

  $id = $_REQUEST['id'];
  $query = "SELECT * FROM tbl_group WHERE group_id = '$id'";
  $result = mysql_query($query);
  $members = array();
  $i = 0;
   if(mysql_num_rows($result) == 0)
    {
      $error = "I'm sorry, the resource ID that was requested could not be found.";
      require('includes/error.php');
      exit();
    }
    while($row = mysql_fetch_assoc($result))
      {
        $id = $row['group_id'];
        $name = $row['name'];
        $type = $row['mtype'];
        $members[] = $row['mid'];
      } 
  
  switch($type) {
    case 'box':
      $pic = 'server';
      break;
    case 'server':
      $pic = 'computer';
      break;
    case 'device';
      $pic = 'drive';
      break;
    case 'application':
      $pic = 'application';
      break;
    case 'database':
      $pic = 'database';
      break;
    case 'service':
      $pic = 'cog';
      break;
    } 
  $header = ' <span class="blue">'.ucwords($type).' Group</span>: '.$name;  
    
?>  
  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Details for <?php echo $header; ?></title>
</head>
<body>
<div id="boxed">
  <h2 class="header"><img src="tree_images/group.png" /><img src="tree_images/<?php echo $pic; ?>.png" /> Detials for<?php echo $header; ?></h2>
  <center>
  <table class="details">
    <tr class="detailstr">
      <td class="detailstd" width="20%"><strong>Group Name:</strong></td>
      <td class="detailstd"><?php echo $name; ?></span></td>
    </tr>
  </table>
  <table class="details">
   <tr>
      <td class="detailstd" colspan="4"><div class="minorheader">Group members, their roles, and points of contact</div></td>
   </tr>
<?php
    /* Now we show member details */
    foreach($members as $thismember)
      {
        $query = "SELECT r.name, r.role, c.first, c.last, c.poc_id from tbl_".$type." r LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE ".$type."_id = '$thismember'";
        //echo $query;
        $result = mysql_query($query);
        $row = mysql_fetch_row($result);
        echo '<tr>';
        echo '<td class="detailstd"><a href="details.php?id='.$type.'_'.$thismember.'">'.$row[0].'</td>';
        echo '<td class="detailstd">'.$row[1].'</td>';
        echo '<td class="detailstd"><a href="pocdetails.php?id='.$row[4].'">'.$row[2].' '.$row[3].'</a></td>';
        echo '</tr>';
      }
?>
</table> 
</div>	
</body>
</html>
