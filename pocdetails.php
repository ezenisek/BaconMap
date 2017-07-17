<?php
  require_once('includes/settings.php');
  require_once('includes/functions.php');
  dbConnect($dbhost,$database,$username,$password);
  
  if(!isset($_REQUEST['id']))
    {
      $error = "I'm sorry, the page you requested could not be displayed because
      a resource ID could not be found.";
      require('includes/error.php');
      exit();
    }

  // Now we check that the user is logged in.  If not, we forward to the login page.
  authorize(2,"pocdetails.php");

  $id = $_REQUEST['id'];
  $query = "SELECT * FROM tbl_poc WHERE poc_id = '$id'";
  $result = mysql_query($query);
  $members = array();
  $i = 0;
   if(mysql_num_rows($result) == 0)
    {
      $error = "I'm sorry, the resource ID that was requested could not be found.";
      require('includes/error.php');
      exit();
    }
  $pocrow = mysql_fetch_assoc($result);  
  $pic = 'status_online';
  $header = ' <span class="blue">Contact</span>: '.$pocrow['first'].' '.$pocrow['last'];  
    
?>  
  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Details for <?php echo $pocrow['first'].' '.$pocrow['last']; ?></title>
</head>
<body>
<div id="boxed">
  <h2 class="header"><img src="tree_images/<?php echo $pic; ?>.png" /> Details for<?php echo $header; ?></h2>
  <center>
  <table class="details">
   <tr class="detailstr">
      <td class="detalstd">Name:</td><td class="detailstd blue"><strong><?php echo $pocrow['first']; if($pocrow['middle']) echo ' '.$pocrow['middle']; echo ' '.$pocrow['last']; ?></strong></td>
   </tr>
   <tr class="detailstr">
      <td class="detailstd">Contact Type: </td><td class="detailstd blue"><?php echo $pocrow['poc_type']; ?></td>
   </tr>
   <tr class="detailstr">
      <td class="detalstd">Phone:</td><td class="detailstd blue"><?php echo formatPhone($pocrow['phone']); ?></td>
   </tr>
   <tr class="detailstr">   
      <td class="detalstd">E-Mail:</td><td class="detailstd blue"><a href="mailto:<?php echo $pocrow['email']; ?>" ><?php echo $pocrow['email']; ?></a></td>
   </tr>
   <tr class="detailstr">   
      <td class="detalstd">Notes:</td><td class="detailstd blue"><?php echo $pocrow['description']; ?></td>
   </tr>
   </table> 
  <table class="details">
   <tr>
      <td class="detailstd" colspan="4"><div class="minorheader">Resources this person is Point of Contact for</div></td>
   </tr>
<?php
    /* Now we show resource details */
    $resources = getPOCResources($id);
    //print_r($resources);
    if(count($resources) == 0)
      {
        echo '<tr><td class="detailstd">This contact has no resource ties</td></tr>';
      }
    else    
    foreach($resources as $resource)
      {
        $pic = getPic($resource['type']);
        
        echo '<tr>';
        echo '<td class="detialstd"><img src="tree_images/'.$pic.'.png" /></td>';
        echo '<td class="detailstd"><a href="details.php?id='.$resource['type'].'_'.$resource['id'].'">'.$resource['name'].'</a></td>';
        echo '<td class="detailstd">'.$resource['role'].'</td>';
        echo '</tr>';
      }
?>
</table> 
</center>
</div>	
</body>
</html>
