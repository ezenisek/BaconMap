<?php
require_once('includes/settings.php');
require_once('includes/functions.php');
dbConnect($dbhost,$database,$username,$password);



// We don't want to inadvertently reload this page and delete something we
// shouldn't.
if($_REQUEST['verification'] !== 'yeppers')
{
  $error = "Could not delete the requested resource, however some relationships may 
  have been affected.  Please run a database check from the Administration Page and try again.";
  include('includes/error.php');
  exit();
}

$resource = $_REQUEST['id'];
$bits = explode('_',$resource);
$type = $bits[0];
$id = $bits[1];

$orphans = getOrphans($resource);

// The first thing we have to do is go back through the relationship maps and 
// decrement the children field on all parents of all resources we're about 
// to delete.

// Decrement for main resource
$parents = getParentsRecursive($type,$id);
$parents = breakDown($parents,'parents');
foreach($parents as $thisparent)
  {
    $pbits = explode('_',$thisparent);
    $ptype = $pbits[0];
    $pid = $pbits[1];
    $query = 'UPDATE tbl_'.$ptype.' SET children = (children - 1) WHERE '.$ptype.'_id = '.$pid;
    //echo '<br>'.$query;
    mysql_query($query);
  } 

// Decrement parents for each orphan.
if($orphans)
foreach($orphans as $thisorphan)
  {
    $bits = explode('_',$thisorphan);
    $otype = $bits[0];
    $oid = $bits[1];
    $parents = getParentsRecursive($otype,$oid);
    $parents = breakDown($parents,'parents');
    // No need to update orphans we're deleting.
    $parents = array_diff($parents,$orphans);
    // No need to update the main parent we're deleting either.
    //echo '<br>Main Resource: '.$resource.'<br>Orphan: '.$thisorphan.'<br>Parents: ';
    //print_r($parents);
    $pkey = array_search($resource,$parents);
    if($pkey !== false)
    {
      //echo '<br>Unsetting '.$pkey;
      unset($parents[$pkey]);
    }
      foreach($parents as $thisparent)
        {
          $pbits = explode('_',$thisparent);
          $ptype = $pbits[0];
          $pid = $pbits[1];
          $query = 'UPDATE tbl_'.$ptype.' SET children = (children - 1) WHERE '.$ptype.'_id = '.$pid;
          //echo '<br>'.$query;
          mysql_query($query);
        } 
  }

// Remove the main resource from any groups it may be in.
$query = "DELETE FROM tbl_group WHERE mid = '$id' AND mtype = '$type'";
mysql_query($query);

// Delete the main resource
$query = 'DELETE FROM tbl_'.$type.' WHERE '.$type.'_id = '.$id;
//echo 'Main Delete Query: '.$query;
mysql_query($query);

// Make sure it's gone.
$query2 = 'SELECT '.$type.'_id FROM tbl_'.$type.' WHERE '.$type.'_id = '.$id;
$result = mysql_query($query2);
if(mysql_num_rows($result))
  {
    $error = "The requested resource could not be deleted for an unknown reason.  
    Some relationships may have been affected.  Please run a database check 
    from the Administration Page and try again.<br />
    Debug: $query";
    include('includes/error.php');
    exit();
  }
else
  {
    // Delete dependencies
    $query = 'DELETE FROM tbl_dep WHERE (c_id = '.$id.' AND c_table = \''.$type.'\') OR (p_id = '.$id.' AND p_table = \''.$type.'\')';
    mysql_query($query);
  }

// Now we delete all the orphans.  Bye bye kiddies.
if($orphans)
foreach($orphans as $orphan)
  {
    $bits = explode('_',$orphan);
    $type = $bits[0];
    $id = $bits[1];
    $query = 'DELETE FROM tbl_'.$type.' WHERE '.$type.'_id = '.$id;
    //echo '<br />Orphan Delete Query: '.$query;
    mysql_query($query);
    $query2 = 'SELECT '.$type.'_id FROM tbl_'.$type.' WHERE '.$type.'_id = '.$id;
    if(mysql_num_rows($result))
    {
      $error .= '<br /><div id="warning">Orphan '.$type.' '.$id.' could not be deleted for an unknown reason.</div>
      Please run the Orphan Cleanup from the Administrator Page.</div>';
    }
    else
    {
    // Delete dependencies
    $query = 'DELETE FROM tbl_dep WHERE (c_id = '.$id.' AND c_table = \''.$type.'\') OR (p_id = '.$id.' AND p_table = \''.$type.'\')';
    mysql_query($query);
    }
  }

// Check for errors
if($error)
  {
    include('includes/error.php');
    exit();
  }
$message = "Resource and any orphans have been successfully removed from the Baconmap.
<script>parent.reloadTree();</script>";
header("Location:includes/success.php?message=".urlencode($message));
    
?>
