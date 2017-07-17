<?php
require_once('includes/settings.php');
require_once('includes/functions.php');
dbConnect($dbhost,$database,$username,$password);


if(!isset($_POST['type']) || !isset($_POST['name']))
{
  $error = "A type and name could not be found for the resource you are trying to add.
  Please go back and verify that everything is correct.";
  include("includes/error.php");
  exit();
}

$inputs = array();
$type = $_POST['type'];
$inputs['name'] = $_POST['name'];
$inputs['role'] = $_POST['role'];
$inputs['description'] = $_POST['description'];
if(isset($_POST['host_id'])) { $inputs['host_id'] = $_POST['host_id']; }
$inputs['annual_cost'] = $_POST['annual_cost'];
$inputs['vendor'] = $_POST['vendor'];
$inputs['last_maint_date'] = date('Y-m-d',strtotime($_POST['last_maint_date']));
$inputs['date_purchased'] = date('Y-m-d',strtotime($_POST['purchase_date']));
$inputs['rto'] = $_POST['rto'];

// Figure impact
// Internal - 4, external - 2, foreign - 1
// IE: Impact of 6 is Internal / External
//     Impact of 7 is all three
//     Impact of 4 is internal only

$inputs['impacted'] = 0;
if(isset($_POST['impact_internal']) && $_POST['impact_internal'])
    $inputs['impacted'] += 4;
if(isset($_POST['impact_external']) && $_POST['impact_external'])
    $inputs['impacted'] += 2;
if(isset($_POST['impact_foreign']) && $_POST['impact_foreign'])
    $inputs['impacted'] += 1;

if($type == 'box')
  {
    $inputs['cpu_num'] = $_POST['cpu_num'];
    $inputs['cpu_speed'] = $_POST['cpu_speed'];
    $inputs['memory'] = $_POST['memory'];
    $inputs['disk_space'] = $_POST['disk_space'];
    $inputs['raid'] = $_POST['raid'];
    $inputs['virtual_os'] = $_POST['virtual_os'];
    $inputs['serial'] = $_POST['box_serial'];
    $inputs['model'] = $_POST['box_model'];
    $inputs['location'] = $_POST['box_serial'];
    unset($inputs['host_id']);
  }
if($type == 'server')
  {
    $inputs['virtual'] = $_POST['server_virtual'];
    $inputs['OS'] = $_POST['os_type'];
  }
if($type == 'database')
  {
    $inputs['type'] = $_POST['db_type'];
    $inputs['host_id'] = $_POST['host_id'];
  }
if($type == 'device')
  {
    $inputs['serial'] = $_POST['device_serial'];
    $inputs['model'] = $_POST['device_model'];
    $inputs['location'] = $_POST['device_location'];
    unset($inputs['host_id']);
  }
  
if($_POST['poc_id'] == 'new')
  {
    $pocinputs = array();
    $pocinputs['first'] = $_POST['first'];
    $pocinputs['middle'] = $_POST['middle'];
    $pocinputs['last'] = $_POST['last'];
    $pocinputs['phone'] = cleanNumber($_POST['phone']);
    $pocinputs['email'] = $_POST['email'];
    $pocinputs['poc_type'] = $_POST['poc_type'];
    $pocinputs['description'] = $_POST['poc_description'];
  }
else
  {
    $pocinputs = false;
    $inputs['poc_id'] = $_POST['poc_id'];
  }
  
if($_POST['dependencies'])
  {
    $parents = explode(',',$_POST['dependencies']);
  }
  


// If we need to insert a new POC, we need to do that first.
if($pocinputs)
  {
    foreach($pocinputs as $name => $value)
      {
        $names .= $name.',';
        $values .= "'".trim($value)."',";
      }
    $names = substr($names,0,-1);
    $values = substr($values,0,-1);
    $query = 'INSERT INTO tbl_poc ('.$names.') VALUES ('.$values.')';
    echo '<br /><br />'.$query;
    mysql_query($query);
    $inputs['poc_id'] = mysql_insert_id();
  }

// Now we can Build our addition query

$names = '';
$values = '';
foreach($inputs as $name => $value)
  {
    if(!empty($value))
      {
        $names .= $name.',';
        $values .= "'$value',";
      }
  }
$names = substr($names,0,-1);
$values = substr($values,0,-1);

$query = 'INSERT INTO tbl_'.$type.' ('.$names.') VALUES ('.$values.')';
//echo '<br /><br />'.$query;
mysql_query($query) or die("Could not insert new resource because ".mysql_error());

// Getting the child id for when we need to do dependencies
$c_id = mysql_insert_id();

// Do dependencies
if(isset($parents))
  {
    foreach($parents as $thisparent)
      {
        $pbits = explode('_',$thisparent);
        $ptype = $pbits[0];
        $pid = $pbits[1];
        if($ptype != 'group')
          {
            $query = "INSERT INTO tbl_dep (c_id,c_table,p_id,p_table) VALUES 
            ('$c_id','$type','$pid','$ptype')";
            //echo '<br />'.$query;
            mysql_query($query);
          }
        else
          {
            $query = "SELECT mtype,mid FROM tbl_group WHERE group_id = '$pid'";
            $result = mysql_query($query);
            while($row = mysql_fetch_row($result))
              {
                $query = "INSERT INTO tbl_dep (c_id,c_table,p_id,p_table) VALUES 
                ('$c_id','$type','$row[1]','$row[0]')";
                mysql_query($query);
              }
          }
       }
   }

// Everything is set, all the dependencies should be good, so now we need to
// Update the childeren field in all the parents of this resource.

$parents = array();
$parents = getParentsRecursive($type,$c_id);
$parents = breakDown($parents,'parents');
//print_r($parents);
foreach($parents as $thisparent)
  {
    $pbits = explode('_',$thisparent);
    $ptype = $pbits[0];
    $pid = $pbits[1];
    $query = 'UPDATE tbl_'.$ptype.' SET children = (children + 1) WHERE '.$ptype.'_id = '.$pid;
    //echo '<br>'.$query;
    mysql_query($query);
  } 

$nodes = buildReference($type.'_'.$c_id);
$nodes .= '|'.$inputs['name'];
   
// Got here with no errors?  Good.  We can tell everyone how cool things went.
$message = 'Successfully Added the '.$type.' resource '.$inputs['name'].'.
<script type="text/javascript">
parent.shownode = \''.$nodes.'\';
parent.reloadTree();
</script>';
header('Location: includes/success.php?message='.urlencode($message));
   
?>
