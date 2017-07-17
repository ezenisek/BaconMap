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
$inputs['first'] = $_REQUEST['first'];
$inputs['middle'] = $_REQUEST['middle'];
$inputs['last'] = $_REQUEST['last'];
$inputs['phone'] = cleanNumber($_REQUEST['phone']);
$inputs['email'] = $_REQUEST['email'];
$inputs['middle'] = $_REQUEST['middle'];
$inputs['poc_type'] = $_REQUEST['poc_type'];
$inputs['description'] = $_REQUEST['description'];


if($task == 'add')
  {
    foreach($inputs as $name => $value)
    {
      $names .= $name.',';
      $values .= "'$value',";
    }
    $names = substr($names,0,-1);
    $values = substr($values,0,-1);
    $query = 'INSERT INTO tbl_poc ('.$names.') VALUES ('.$values.')';
    mysql_query($query);
  }
elseif($task == 'delete')
  {
    $id = $_REQUEST['id'];
    $query = "DELETE FROM tbl_poc WHERE poc_id = '$id'";
    mysql_query($query);
    $query = "SELECT poc_id FROM tbl_poc WHERE poc_id = '$id'";
    $result = mysql_query($query);
    if(mysql_num_rows($result) != 0)
      {
        $error = 'Could not delete '.$inputs['first'].' '.$inputs['last'].' from
        the database for an unknown reason.';
        include 'includes/error.php';
        exit();
      }
  }
else
  {
    $id = $_REQUEST['id'];
    $query = 'UPDATE tbl_poc set ';
    foreach($inputs as $name => $value)
      {
        $query .= "$name = '$value',";
      }
    $query = substr($query,0,-1);
    $query .= " WHERE poc_id = '$id'";
    mysql_query($query);
  } 
//echo '<br /><br />'.$query;

header('Location: editcontact.php?task=done'.$task);
   
?>
