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
    
    This file is the report handler for BaconMap.
    
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
require_once('includes/fpdf.php');
require_once('includes/excel.php');
dbConnect($dbhost,$database,$username,$password);


if(!isset($_POST['choice']))
  {
    $error = "The report generator could not perfrom the requested action.";
    include("includes/error.php");
    exit();
  }

$choice = $_POST['choice'];

// Report data is sent in an array with the following structure
// $reportdata[0] = 'Overall Report Title Goes Here';
// $reportdata[1]['header'] = array('col1','col2','col3');
// $reportdata[1]['data'][1] = array('row1','row2','row3');
// $reportdata[1]['data'][2] = array('row1','row2','row3');
// etc

// Each new row in the $reportdata array is a seperate section with the 
// appropriate header and data information.

$reportdata = array();

switch($choice) {
  case 'resourcereport':
  $bits = explode('_',$_POST['resource']);
  $type = $bits[0];
  $id = $bits[1];
  if($type != 'box' && $type != 'device')
    $query = 'SELECT r.name, r.role, r.impacted, r2.name as host, c.first, c.last, c.poc_type, c.email, c.phone 
        from tbl_'.$type.' r LEFT OUTER JOIN tbl_box r2
        ON r.host_id = r2.box_id LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE '.$type.'_id = '.$id;
  else
    $query = 'SELECT r.name, r.role, r.impacted, \'NA\' as host, c.first, c.last, c.poc_type, c.email, c.phone 
        from tbl_'.$type.' r LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE '.$type.'_id = '.$id;
  $result = mysql_query($query);
  $row = mysql_fetch_assoc($result);
  $title = $row['name'].'Report';
  $reportdata[0] = 'Resource Report for '.$row['name'];
  $reportdata[1]['header'] = array('Resource Name','Role','Host','Impact','Contact');
  $impacttext = getPrettyImpact($row['impacted']);
  $reportdata[1]['data'][1] = array($row['name'],$row['role'],$row['host'],$impacttext,$row['first'].' '.$row['last']);
  $reportdata[2]['header'] = array('Contact First','Last','Type','Phone','Email');
  $phone = formatPhone($row['phone']);
  $reportdata[2]['data'][1] = array($row['first'],$row['last'],$row['poc_type'],$phone,$row['email']);
  
  $children = getChildren($type,$id);
  $parents = getParents($type,$id);
  if($parents)
    $reportdata[3]['header'] = array('Direct Parent Name','Role','Host','Contact');
  else
    $reportdata[3]['header'] = array('No Direct Parents Found');
  if($children)
    $reportdata[4]['header'] = array('Direct Child Name','Role','Host','Contact');
  else
    $reportdata[4]['header'] = array('No Direct Children Found');
    
  $i = 1;
  foreach($parents as $parent)
    {
      if($parent['type'] != 'box' && $type != 'device')
        $query = 'SELECT r.name, r.role, r.impacted, r2.name as host, c.first, c.last
        from tbl_'.$parent['type'].' r LEFT OUTER JOIN tbl_box r2
        ON r.host_id = r2.box_id LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE '.$parent['type'].'_id = '.$parent['id'];
      else
        $query = 'SELECT r.name, r.role, r.impacted, \'NA\' as host, c.first, c.last
        from tbl_'.$parent['type'].' r LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE '.$parent['type'].'_id = '.$parent['id'];
      $result = mysql_query($query);
      $row = mysql_fetch_assoc($result);
      $reportdata[3]['data'][$i] = array($row['name'],$row['role'],$row['host'],$row['first'].' '.$row['last']);
      $i++;   
   } 
  $i = 1;
  foreach($children as $child)
    {
      if($child['type'] != 'box')
        $query = 'SELECT r.name, r.role, r.impacted, r2.name as host, c.first, c.last
        from tbl_'.$child['type'].' r LEFT OUTER JOIN tbl_box r2
        ON r.host_id = r2.box_id LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE '.$child['type'].'_id = '.$child['id'];
      else
        $query = 'SELECT r.name, r.role, r.impacted, \'NA\' as host, c.first, c.last
        from tbl_'.$child['type'].' r LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE '.$child['type'].'_id = '.$child['id'];
      $result = mysql_query($query);
      $row = mysql_fetch_assoc($result);
      $reportdata[4]['data'][$i] = array($row['name'],$row['role'],$row['host'],$row['first'].' '.$row['last']);
      $i++;   
    }
  break;  // Case resourcereport
  
  case 'childreport':
  $types = array('device','server','service','database','box','application');
  foreach($types as $type)
    {
      if($type != 'box' && $type != 'device')
        $query = 'SELECT r.name, r.role, r.impacted, r2.name as host, c.first, c.last, c.poc_type, c.email, c.phone, r.children 
            from tbl_'.$type.' r LEFT OUTER JOIN tbl_box r2
            ON r.host_id = r2.box_id LEFT OUTER JOIN tbl_poc c
            ON r.poc_id = c.poc_id';
      else
        $query = 'SELECT r.name, r.role, r.impacted, \'NA\' as host, c.first, c.last, c.poc_type, c.email, c.phone, r.children 
            from tbl_'.$type.' r LEFT OUTER JOIN tbl_poc c
            ON r.poc_id = c.poc_id';
      $result = mysql_query($query);
      while($row = mysql_fetch_assoc($result))
        {
          $reportdata[1]['data'][] = array($row['name'],$row['role'],$row['host'],$row['first'].' '.$row['last'],$row['children']);
        }
    }
  $title = 'ChildrenReport';
  $reportdata[0] = 'Resources and Number of Children';
  $reportdata[1]['header'] = array('Resource Name','Role','Host','Contact','Children');
  $sortorder = $_POST['childreportorder'];
  $reportdata[1]['data'] = php_multisort($reportdata[1]['data'], array(array('key'=>4,'sort'=>$sortorder)));
  break;
  
  case 'parentreport':
  $types = array('device','server','service','database','box','application');
  foreach($types as $type)
    {
      if($type != 'box' && $type != 'device')
        $query = 'SELECT r.'.$type.'_id, r.name, r.role, r.impacted, r2.name as host, c.first, c.last, c.poc_type, c.email, c.phone 
            from tbl_'.$type.' r LEFT OUTER JOIN tbl_box r2
            ON r.host_id = r2.box_id LEFT OUTER JOIN tbl_poc c
            ON r.poc_id = c.poc_id';
      else
        $query = 'SELECT r.'.$type.'_id, r.name, r.role, r.impacted, \'NA\' as host, c.first, c.last, c.poc_type, c.email, c.phone
            from tbl_'.$type.' r LEFT OUTER JOIN tbl_poc c
            ON r.poc_id = c.poc_id';
      $result = mysql_query($query);
      while($row = mysql_fetch_assoc($result))
        {
          $parents = count(array_unique(breakDown(getParentsRecursive($type,$row[$type.'_id']),'parents')));
          //echo $parents;
          $reportdata[1]['data'][] = array($row['name'],$row['role'],$row['host'],$row['first'].' '.$row['last'],$parents);
        }
    }
  $title = 'ParentsReport';
  $reportdata[0] = 'Resources and Number of Parents';
  $reportdata[1]['header'] = array('Resource Name','Role','Host','Contact','Parents');
  $sortorder = $_POST['parentreportorder'];
  $reportdata[1]['data'] = php_multisort($reportdata[1]['data'], array(array('key'=>4,'sort'=>$sortorder)));
  break;
  
  case 'allcontact':
  $title = 'AllContactReport';
  $reportdata[0] = 'Point of Contact Report';
  $reportdata[1]['header'] = array('Contact Name','Type','Phone','Email','Resources');
  $sortorder = $_POST['allcontactorder'];
  $sortby = $_POST['allcontactsort'];
  if($sortby == 'resources')
    $orderby = 'last';
  else
    $orderby = $sortby;
  $query = "SELECT poc_id, first, middle, last, phone, email, poc_type from tbl_poc ORDER BY $orderby $sortorder";
  $result = mysql_query($query);
  while($row = mysql_fetch_assoc($result))
    {
      $resources = count(getPOCResources($row['poc_id']));
      if(!empty($row['middle']))
        $name = $row['first'].' '.$row['middle'].' '.$row['last'];
      else
        $name = $row['first'].' '.$row['last'];
        
      $reportdata[1]['data'][] = array($name,$row['poc_type'],formatPhone($row['phone']),$row['email'],$resources);
    }
  if($sortby == 'resources')
    $reportdata[1]['data'] = php_multisort($reportdata[1]['data'], array(array('key'=>4,'sort'=>$sortorder)));
  break;
  
  case 'rolereport':
  $title = 'RoleReport';
  $reportdata[0] = 'Resource Role Report';
  $roles = getRoles();
  $i = 1;
  foreach($roles as $role)
    {
      $reportdata[$i]['header'] = array($role.' Name','Impact','Host','Contact');
      $resources = getRoleResources($role); 
      //print_r($resources);
      foreach($resources as $res)
        {
          if($res['type'] != 'box' && $res['type'] != 'device')
              $query = 'SELECT r.name, r.impacted, r2.name as host, c.first, c.last
              from tbl_'.$res['type'].' r LEFT OUTER JOIN tbl_box r2
              ON r.host_id = r2.box_id LEFT OUTER JOIN tbl_poc c
              ON r.poc_id = c.poc_id WHERE r.'.$res['type'].'_id = '.$res['id'];
          else
              $query = 'SELECT r.name, r.impacted, \'NA\' as host, c.first, c.last
              from tbl_'.$res['type'].' r LEFT OUTER JOIN tbl_poc c
              ON r.poc_id = c.poc_id WHERE r.'.$res['type'].'_id = '.$res['id'];
          $result = mysql_query($query);
          $row = mysql_fetch_assoc($result);
          $impact = getPrettyImpact($row['impacted']);
          $reportdata[$i]['data'][] = array($row['name'],$impact,$row['host'],$row['first'].' '.$row['last']);
        }
      $i++;
    }
   break;

	case 'piegraph':
	    set_include_path( "/srv/www/includes/baconmap/Graph" . PATH_SEPARATOR .  get_include_path());
	    $title = 'Village Inn Pie Chart';

		$graph = new ezcGraphPieChart();
		$graph->title = 'Access statistics';

		$graph->data['Access statistics'] = new ezcGraphArrayDataSet( array(
		'Mozilla' => 19113,
		'Explorer' => 10917,
		'Opera' => 1464,
		'Safari' => 652,
		'Konqueror' => 474,
		) );
		$graph->data['Access statistics']->highlight['Opera'] = true;

		$graph->render( 400, 150, 'tutorial_simple_pie.svg' ); 
	break;
  
}

if($_POST['format'] != 'xls')
  {
    createPDFReport($reportdata,$title);
  }
else
  {
    createExcelReport($reportdata);
  }
//print_r($reportdata);
//echo '<br /><hr><br />';
//print_r($testdata);
?>
