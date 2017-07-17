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
    
    This file is the resource edit handler for BaconMap.
    
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


if(!isset($_REQUEST['type']) || !isset($_REQUEST['id']))
{
  $error = "A type and id could not be found for the resource you are trying to edit.
  Please go back and verify that everything is correct.";
  include("includes/error.php");
  exit();
}

$inputs = array();
$type = $_REQUEST['type'];
$id = $_REQUEST['id'];
$inputs['name'] = $_REQUEST['name'];
$inputs['role'] = $_REQUEST['role'];
$inputs['description'] = $_REQUEST['description'];
if(isset($_REQUEST['host_id'])) $inputs['host_id'] = $_REQUEST['host_id'];
$inputs['annual_cost'] = $_REQUEST['annual_cost'];
$inputs['vendor'] = $_REQUEST['vendor'];
$inputs['last_maint_date'] = date('Y-m-d',strtotime($_REQUEST['last_maint_date']));
$inputs['date_purchased'] = date('Y-m-d',strtotime($_REQUEST['purchase_date']));
$inputs['rto'] = $_REQUEST['rto'];

// Figure impact
// Internal - 4, external - 2, foreign - 1
// IE: Impact of 6 is Internal / External
//     Impact of 7 is all three
//     Impact of 4 is internal only

$inputs['impacted'] = 0;
if(isset($_REQUEST['impact_internal']) && $_REQUEST['impact_internal'])
    $inputs['impacted'] += 4;
if(isset($_REQUEST['impact_external']) && $_REQUEST['impact_external'])
    $inputs['impacted'] += 2;
if(isset($_REQUEST['impact_foreign']) && $_REQUEST['impact_foreign'])
    $inputs['impacted'] += 1;

if($type == 'box')
  {
    $inputs['cpu_num'] = $_REQUEST['cpu_num'];
    $inputs['cpu_speed'] = $_REQUEST['cpu_speed'];
    $inputs['memory'] = $_REQUEST['memory'];
    $inputs['disk_space'] = $_REQUEST['disk_space'];
    $inputs['raid'] = $_REQUEST['raid'];
    $inputs['virtual_os'] = $_REQUEST['virtual_os'];
    $inputs['serial'] = $_REQUEST['box_serial'];
    $inputs['model'] = $_REQUEST['box_model'];
    $inputs['location'] = $_REQUEST['box_location'];
    unset($inputs['host_id']);
  }
if($type == 'server')
  {
    $inputs['virtual'] = $_REQUEST['server_virtual'];
    $inputs['OS'] = $_REQUEST['os_type'];
  }
if($type == 'database')
  {
    $inputs['type'] = $_REQUEST['db_type'];
    $inputs['host_id'] = $_REQUEST['host_id'];
  }
if($type == 'device')
  {
    $inputs['serial'] = $_REQUEST['device_serial'];
    $inputs['model'] = $_REQUEST['device_model'];
    $inputs['location'] = $_REQUEST['device_serial'];
    unset($inputs['host_id']);
  }
  
if($_REQUEST['poc_id'] == 'new')
  {
    $pocinputs = array();
    $pocinputs['first'] = $_REQUEST['first'];
    $pocinputs['middle'] = $_REQUEST['middle'];
    $pocinputs['last'] = $_REQUEST['last'];
    $pocinputs['phone'] = cleanNumber($_REQUEST['phone']);
    $pocinputs['email'] = $_REQUEST['email'];
    $pocinputs['poc_type'] = $_REQUEST['poc_type'];
    $pocinputs['description'] = $_REQUEST['poc_description'];
  }
else
  {
    $pocinputs = false;
    $inputs['poc_id'] = $_REQUEST['poc_id'];
  }
  
if($_REQUEST['dependencies'])
  {
    $parents = explode(',',$_REQUEST['dependencies']);
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

// Now we can Build our update query

$nvp = '';
foreach($inputs as $name => $value)
  {
    if($value === "0" || !empty($value))
    $nvp .= $name.' = \''.$value.'\',';
  }
$nvp = substr($nvp,0,-1);

$query = 'UPDATE tbl_'.$type.' SET '.$nvp.' WHERE '.$type.'_id = '.$id;
//echo '<br /><br />'.$query;
mysql_query($query) or die("Could not update resource because ".mysql_error());

// Getting the child id for when we need to do dependencies
$c_id = $id;

// Do dependencies
// This is the tricky bit.  Since the dependencies could change, we need to 
// ensure that all the rules are still enforced throughout the change.
// The easiest thing is to just wipe all the dependencies that
// currently exist and start fresh with the information that was provided by the
// form.  Then the dependencies for the thing we're adding will be all set.

// The tricky part comes from the children of this thing.  Since the children
// of this resource might be linked all over the map, we need to make sure that
// none of them break the rules after they've been moved with this resource.
// We'll get all the children of this resource and do a direct parentage check on them.
// If any of the parents of THIS resource appear in the list of parents for a
// CHILD resource, the child connection to that found parent needs to be 
// wiped... no double parentage.  Grandparents can't be the fathers of their 
// grandchildren... at least not here.

// First things first.  Remove all parent links for this resource.
$query = "DELETE FROM tbl_dep WHERE c_id='$c_id' AND c_table='$type'";
//echo '<br><br>'.$query;
mysql_query($query);

// Now add them back in, if they exist.
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
    
    // Now that we have the new parent connections set up, we need to get a 
    // comprehensive (recursive) list of all parents for this resource.
    
    $parents = breakDown(getParentsRecursive($type,$id),'parents');
    
    // We have a nice array of parents to check against, let's get all the 
    // children now.
    
    $children = breakDown(getChildrenRecursive($type,$id),'children');
    
    // We have a list of parents and a list of children for this resource.
    // Now we need to check each child.
    
    foreach($children as $child)
      {
        $cbits = explode('_',$child);
        $ctype = $cbits[0];
        $cid = $cbits[1];
        
        // Now we find all direct parents of this child.
        $directparents = breakDown(getParents($ctype,$cid),'parents');
        
        // We need to make sure that the direct parent isn't already
        // accounted for in our overall parent list.
        $doublecoverage = array_intersect($directparents,$parents);
        
        // Now we blow them away.  Bye Double Daddios.
        foreach($doublecoverage as $remove)
          {
            $rbits = explode('_',$remove);
            $rtype = $rbits[0];
            $rid = $rbits[1];
            
            $query = "DELETE FROM tbl_dep WHERE c_id = '$cid' AND c_table = '$ctype' AND 
            p_id = '$rid' AND p_table = '$rtype'";
            echo '<br><br>'.$query;
            mysql_query($query);
          }
     }
}
// Everything is set, all the dependencies should be good, so now we need to
// Update the childeren field in all the parents of this resource and all the
// parents of all the other resources that might've changed and....
// Bah.  We'll just run a database child check and let it take care 
// of it for us.  It's probably not the most efficent way of doing it, but
// it certainly is the cleanest.

$types = array('device','server','service','database','box','application');
    $error = false;
    foreach($types as $thistype)
      {
        verifyChildren($thistype);
      }
    

$nodes = buildReference($type.'_'.$c_id);
$nodes .= '|'.$inputs['name'];
   
// Got here with no errors?  Good.  We can tell everyone how cool things went.
$message = 'Successfully Edited the '.$type.' resource '.$inputs['name'].'.
<script type="text/javascript">
parent.shownode = \''.$nodes.'\';
parent.reloadTree();
</script>';
//echo $message;
header('Location: includes/success.php?message='.urlencode($message));
   
?>
