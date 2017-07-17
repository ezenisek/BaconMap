<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
require_once('../includes/settings.php');
require_once('../includes/functions.php');
dbConnect($dbhost,$database,$username,$password);
?>
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="../css/main.css" type="text/css" />
	<title>Bacon Map Database Checks</title>
</head>
<body>
<?php
  $type = $_GET['type'];
  $error = false;
  if($type == 'relationship')
    {
    echo '<br />Running Database Relationship Checks:<br />';
    if($err = checkDBRelationships(true))
      $error .= $err;
    }
  if($type == 'orphan')
    {
    echo '<br />Running Orphan Checks:<br />';
    if($err = cleanOrphans(true))
      $error .= '<br />Orphans found and delted';
    }
  if($type == 'group')
    {
    echo '<br />Running Group Membership Checks:<br />';
    if($err = checkGroups(true))
      $error .= $err;
    }
  if($type == 'all')
    {
      echo '<br />Running Complete Database Integrity Check<br />';
      if($err = checkDBRelationships(true))
      $error .= $err;
      if($err = cleanOrphans(true))
      $error .= '<br />Orphans found and delted';
      if($err = checkGroups(true))
      $error .= $err;
    }   
  echo '<br /><strong>Results:</strong><br />';  
  if($error)
    echo "<script>
    parent.document.getElementById('warning').innerHTML = 'ERRORS WERE DETECTED. See log for more details';
    </script>".$error;
  else
    echo "No errors were found<script>parent.document.getElementById('warning').innerHTML = '';</script>";
?>
</body>
</html>
