<?php
require_once('includes/settings.php');
require_once('includes/functions.php');
dbConnect($dbhost,$database,$username,$password);


if(!isset($_REQUEST['task']))
{
  $error = "The requested page cannot be displayed.";
  include("includes/error.php");
  exit();
}

$inputs = array();
$task = $_REQUEST['task'];
$name = $_REQUEST['name'];
$members = $_REQUEST['reslist'];
//print_r($members);
if($task == 'add')
  {  
    $query = "SELECT MAX(group_id) FROM tbl_group";
    $result = mysql_query($query);
    $gid = mysql_result($result,0,0);
    if(empty($gid))
      $gid = 1;
    else
      {
        $gid++;
      }
    foreach($members as $thismember)
      {
        $bits = explode('_',$thismember);
        $mid = $bits[1];
        $mtype = $bits[0];
        $query = "INSERT INTO tbl_group (group_id,name,mid,mtype) 
        VALUES ('$gid','$name','$mid','$mtype')";
        //echo $query;
        mysql_query($query);
      }
  $message = 'Successfully Added Group: <span class="blue">'.$name.'</span>';
  $done = 1;
  }
elseif($task == 'disband')
  {
    $id = $_REQUEST['id'];
    $query = "DELETE FROM tbl_group WHERE group_id = '$id'";
    mysql_query($query);
    $query = "SELECT gid FROM tbl_group WHERE group_id = '$id'";
    $result = mysql_query($query);
    if(mysql_num_rows($result) != 0)
      {
        $error = 'Could not delete '.$name.' from
        the database for an unknown reason.';
        include 'includes/error.php';
        exit();
      }
  }
else
  {
    $id = $_REQUEST['id'];
    $currentmembers = array();
    $newmembers = $members;
    $remove = array();
    $add = array();
    $query = "SELECT mid, mtype FROM tbl_group WHERE group_id = '$id'";
    //echo $query.'<br>';
    $result = mysql_query($query);
    
    // Find out who is currently in the group and who needs to be added or 
    // deleted from the edited group by comparing the two.
    while($row = mysql_fetch_row($result))
      {
        $currentmembers[] = $row[1].'_'.$row[0];
      }
    echo 'New Members: '; print_r($newmembers);
    //echo '<br>Current Members: '; print_r($currentmembers);
    
    // Get all memembers who are in the newmember group, but are not current members.
    $add = array_diff($newmembers,$currentmembers);
    //echo '<br>Members to add: '; print_r($add);
        
    // Get all members who are current members, but don't exist in the new member group.
    $remove = array_diff($currentmembers,$newmembers);
    //echo '<br>Members to remove: '; print_r($remove); echo '<br>';
      
        foreach($add as $member)
          {
            $bits = explode('_',$member);
            $mtype = $bits[0];
            $mid = $bits[1];
            $query = "INSERT INTO tbl_group (group_id,name,mid,mtype) 
            VALUES ('$id','$name','$mid','$mtype')";
            //echo $query.'<br>';
            mysql_query($query);
          }
        foreach($remove as $member)
          {
            $bits = explode('_',$member);
            $mtype = $bits[0];
            $mid = $bits[1];
            $query = "DELETE FROM tbl_group WHERE group_id = '$id' AND mid = '$mid' AND mtype = '$mtype'";
            //echo $query.'<br>';
            mysql_query($query);
          }
    
    $id = $_REQUEST['id'];
    $query = "UPDATE tbl_group SET name = '$name' WHERE group_id = '$id'";
    //echo $query.'<br>';
    mysql_query($query);
  } 
header('Location: editgroup.php?task=done'.$task);
?>

