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
require_once('Base/src/base.php');

function __autoload( $className )
	{
  	    ezcBase::autoload( $className );
	}

require_once('includes/settings.php');
require_once('includes/functions.php');
require_once('includes/fpdf.php');
require_once('includes/excel.php');
require_once('includes/ofc/php-ofc-library/open-flash-chart.php');
dbConnect($dbhost,$database,$username,$password);
	

if(!isset($_POST['choice']))
  {
    $error = "The report generator could not perfrom the requested action.";
    include("includes/error.php");
    exit();
  }

$choice = $_POST['choice'];

// Report or Graph?  Set this variable when making the report or graph below
$reporttype = 'report';

if(!isset($_REQUEST['width']))
	$gwidth = 500;
else
	$gwidth = $_REQUEST['gwidth'];
	
if(!isset($_REQUEST['height']))
	$gheight = 400;
else
	$gheight = $_REQUEST['gheight'];
	
// Report data is sent in an array with the following structure
// $reportdata[0] = 'Overall Report Title Goes Here';
// $reportdata[1]['header'] = array('col1','col2','col3');
// $reportdata[1]['data'][1] = array('row1','row2','row3');
// $reportdata[1]['data'][2] = array('row1','row2','row3');

// Subreports are done like so
// $reportdata[1]['data'][2]['sub'][0]['header'] = array('col1','col2','col3');
// $reportdata[1]['data'][2]['sub'][0]['data'] = array('row1','row2','row3');
// etc

// Each new row in the $reportdata array is a seperate section with the 
// appropriate header and data information.
// If sub sections are utilized, they MUST be formatted as above, including the 
// ['sub'] array just as indicated.


$reportdata = array();

switch($choice) {
  //********************************************
  //    BEGIN SWITCH STATEMENT FOR REPORT CHIOCE
  //   	BEGIN TEXTUAL REPORTS
  //********************************************
  //******************Start Resource Report***********************
  case 'resourcereport':
  $reporttype = 'report';
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
  if($parents)
  foreach($parents as $parent)
    {
        $query = 'SELECT r.name, r.role, r.impacted, \'NA\' as host, c.first, c.last
        from tbl_'.$parent['type'].' r LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE '.$parent['type'].'_id = '.$parent['id'];
      $result = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_assoc($result);
      $reportdata[3]['data'][$i] = array($row['name'],$row['role'],$row['host'],$row['first'].' '.$row['last']);
      $i++;   
   } 
  $i = 1;
  if($children)
  foreach($children as $child)
    {
        $query = 'SELECT r.name, r.role, r.impacted, \'NA\' as host, c.first, c.last
        from tbl_'.$child['type'].' r LEFT OUTER JOIN tbl_poc c
        ON r.poc_id = c.poc_id WHERE '.$child['type'].'_id = '.$child['id'];
      $result = mysql_query($query);
      $row = mysql_fetch_assoc($result);
      $reportdata[4]['data'][$i] = array($row['name'],$row['role'],$row['host'],$row['first'].' '.$row['last']);
      $i++;   
    }
  break;  // Case resourcereport
  //************************End Resource Report*************************
  
  
  case 'childreport':
  //************************Start Child Report**************************
  $reporttype = 'report';
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
  //***********************End Child Report*******************************
  
  
  case 'parentreport':
  //***********************Start Parent Report****************************
  $reporttype = 'report';
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
  //*************************End Parent Report****************************
  
  
  case 'allcontact':
  //*************************Start Contact Report*************************
  $reporttype = 'report';
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
  //************************End Contact Report***************************
  
  
  case 'allmaintenance';
  //************************Begin Maintenance Report*********************
  $reporttype = 'report';
  $title = 'MaintenanceReport';
  $reportdata[0] = 'Maintenance Report';
  $reportdata[1]['header'] = array('Resource Name','Type','Purchase Date','Last Maintained');
  $sortorder = $_POST['allmaintenanceorder'];
  $sortby = $_POST['allmaintenancesort'];
  $types = array('device','server','service','database','box','application');
  foreach($types as $type)
    {
      $query = 'SELECT name, date_purchased, last_maint_date FROM tbl_'.$type;
      $result = mysql_query($query);
      while($row = mysql_fetch_row($result))
        { 
          $pdate = strtotime($row[1]);
          $mdate = strtotime($row[2]);
  
          if($pdate > 0)
          	$pdate = date('Y-m-d',$pdate);
          else
          	$pdate = 'N/A';
          if($mdate > 0)
          	$mdate = date('Y-m-d',$mdate);
          else
          	$mdate = 'N/A';
   
          $reportdata[1]['data'][] = array($row[0],$type,$pdate,$mdate);
        }
    }
  $reportdata[1]['data'] = php_multisort($reportdata[1]['data'], array(array('key'=>3,'sort'=>$sortorder)));
  break;
  //************************End Maintenance Report***********************
  
  
  case 'resourceage';
  //************************Begin Resource Age Report********************
  $reporttype = 'report';
  $title = 'ResourceAgeReport';
  $reportdata[0] = 'Resource Aging Report';
  $reportdata[1]['header'] = array('Resource Name','Type','Purchase Date','Age');
  $sortorder = $_POST['resourceageorder'];
  $sortby = $_POST['resourceagesort'];
  $types = array('device','server','service','database','box','application');
  foreach($types as $type)
    {
      $query = 'SELECT name, date_purchased FROM tbl_'.$type;
      $result = mysql_query($query);
      while($row = mysql_fetch_row($result))
        { 
          $pdate = strtotime($row[1]);
          if($pdate > 0)
          	{
          		$diff = time() - $pdate;
          		$pdate = date('Y-m-d',$pdate);
          		$diff = sec2Time($diff);
          		$age = '';
          		if($diff['years'])
          		{
          			$age .= $diff['years']. ' year(s), ';
          		} 
          		$age .= $diff['days'].' day(s) old';
          	}
          else
          	{
          		$pdate = 'N/A';
 				$age = 'N/A';
          	}           
   
          $reportdata[1]['data'][] = array($row[0],$type,$pdate,$age);
        }
    }
  $reportdata[1]['data'] = php_multisort($reportdata[1]['data'], array(array('key'=>2,'sort'=>$sortorder)));
  break;
  //************************End Resource Age Report**********************
  
  
  case 'allcost';
  //************************Begin Cost Report****************************
  $reporttype = 'report';
  $title = 'CostReport';
  $reportdata[0] = 'Resource Cost Report';
  $sortorder = $_POST['allcostorder'];
  $sortby = $_POST['allcostsort'];
    // For this report, if sort by is set to type, then we do sub-reports 
    // for each type of resource.  If sort by is annual_cost, we just 
    // list all resources by cost.
    if($sortby == 'type')
    	{
    		// Do sub reports
    		$types = array('device','server','service','database','box','application');
    		$i = 1;
    		$totalresources = 0;
    		$totalcost = 0;
  			foreach($types as $type)
    		{
    			$reportdata[$i] = array();
    			$reportdata[$i]['data'] = array();
    			$reportdata[$i]['header'] = array();
    			$reportdata[$i]['data'][2] = array();
    			$typeresources = 0;
    			$typecost = 0;
    			$query = "SELECT name, annual_cost FROM tbl_".$type." order by annual_cost $sortorder";
    			$result = mysql_query($query);
    			while($row = mysql_fetch_row($result))
    				{
    					$typeresources++;
    					$typecost += $row[1];
    					$reportdata[$i]['data'][2]['sub'][0]['data'][$typeresources] = array($row[0],'Cost: $'.number_format($row[1]));
    				}
    			$average = number_format($typecost / $typeresources);
    			$reportdata[$i]['header'] = array('Annual Cost Report for '.ucwords($type).' Resources',' ');
    			$reportdata[$i]['data'][1] = array('Total annual cost for '.$typeresources.' '.$type.' resources: $'.number_format($typecost),
    											   'Average cost per resource: $'.$average);
    			$reportdata[$i]['data'][2]['sub'][0]['header'] = array('Resource Name','Annual Cost');
    			$totalresources += $typeresources;
    			$totalcost += $typecost;
    			$i++;
    		}
    		$average = number_format($totalcost / $totalresources);
    		$reportdata[$i+1]['header'] = array("Total cost for $totalresources resources: $".number_format($totalcost),
    		                                    "Average cost per resource: $".$average);
    		$reportdata[$i+1]['data'][0] = array(" "," ");
    	}
    else
    	{
    		// Do simple report
    		$reportdata[1]['header'] = array('Resource Name','Type','Annual Cost');
    		$costdata = array();
    		$types = array('device','server','service','database','box','application');
			  foreach($types as $type)
			    {
			    	$query = "SELECT name, annual_cost FROM tbl_".$type;
			    	$result = mysql_query($query);
			    	while($row = mysql_fetch_row($result))
			    		{
			    			$id = $row[0].'|'.$type;
			    			$costdata[$id] = $row[1];
			    		}
			    }
			if($sortorder == 'asc')
		    	asort($costdata,SORT_NUMERIC);
		    else	
		    	arsort($costdata,SORT_NUMERIC);
		    $i = 0;
		    $totalcost = 0;
		    $average = 0;
		    foreach($costdata as $name => $cost)
		    	{
		    		$bits = explode('|',$name);
		    		$name = $bits[0];
		    		$type = $bits[1];
		    		$reportdata[1]['data'][$i] = array($name,$type,'$'.number_format($cost));
		    		$totalcost += $cost;
		    		$i++;	    		
		    	}
		    $average = number_format($totalcost / $i);
		    $reportdata[2]['header'] = array("Total cost for $i resources: $".number_format($totalcost),
    		                                 "Average cost per resource: $".$average);
    		$reportdata[2]['data'][0] = array(" "," ");
    	}
  
  break;
  //************************End Cost Report******************************
  
  
  case 'rolereport':
  //***********************Start Role Report*****************************
  $reporttype = 'report';
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
  //***********************End Role Report******************************
  
  
  case 'rtosimple':
  //***********************Start RTO Report*****************************
  $reporttype = 'report';
  $title = 'RTOReport';
  $reportdata[0] = 'Recovery Time Objective Report';
  $reportdata[1]['header'] = array('Resource Name','Type','Impact','Contact','RTO');
  $sortorder = $_POST['rtosimpleorder'];
  $types = array('device','server','service','database','box','application');
  foreach($types as $type)
    {
      $query = 'SELECT r.name, r.rto, r.impacted, p.first, p.last FROM tbl_'.$type.' r, tbl_poc p WHERE p.poc_id = r.poc_id';
      $result = mysql_query($query);
      while($row = mysql_fetch_row($result))
      	{
      		$impact = getPrettyImpact($row[2]);
      		$reportdata[1]['data'][] = array($row[0],$type,$impact,$row[3].' '.$row[4],$row[1]);	
      	}
    } 
  $reportdata[1]['data'] = php_multisort($reportdata[1]['data'], array(array('key'=>4,'sort'=>$sortorder)));
  break;
  //***********************End Role Report******************************
  
  
  //*******************************
  // END TEXTUAL REPORTS
  //
  // BEGIN GRAPHS AND CHARTS
  //*******************************
  
  case 'allage':
  	
  //**********************Start Box Age Bar Graph********************	

	$reporttype = 'graph';
	$charttype = $_POST['allagetype'];
	
	$query = 'SELECT date_purchased FROM tbl_'.$charttype.' WHERE date_purchased IS NOT NULL';
   	$result = mysql_query($query);
   	if (!mysql_num_rows($result))
  	{
    	$error = 'The '.$charttype.' graph/chart cannot be generated: 0 records returned.';
    	include("includes/error.php");
    	exit();
  	}
  	$years = array(0=>0,1=>0,2=>0,3=>0,4=>0);
   	while($row = mysql_fetch_row($result))
   	{ 
     	$pdate = strtotime($row[0]);
       	if($pdate > 0)
       	{
           	$diff = time() - $pdate;
           	$pdate = date('Y-m-d',$pdate);
           	$diff = sec2Time($diff);
          	$age = '';

          	if($diff['years'])
           	{
           		if($diff['years'] > 5)
           		{
                  	$years[4]++;
           		}
                else
                {
                  	$years[$diff['years']-1]++;
                }
			} 
			else
			{
      			$years[0]++;
          	}
    	}
    }
	
	$chart = new open_flash_chart();
	$bar = new bar_filled('#299DCA','#DDDDDD');
	$bar->set_alpha('.70');
	$bar->set_on_show(new bar_on_show('grow-up',1, 0));
	
	if ($charttype == 'box')
		$title = new title('Number of '.$charttype.'es grouped by age');
	else
		$title = new title('Number of '.$charttype.'s grouped by age');
	
	$chart->set_title( $title );
	$chart->set_bg_colour( '#DFDFDF' );
	
	//x-axis
	$x = new x_axis();
	$x_labels = new x_axis_labels();
    $x_labels->set_labels(array('1','2','3','4','5'));
    $x->set_labels($x_labels);
    $x->set_grid_colour('#AAAAAA');
    $chart->set_x_axis($x);
	$x_legend = new x_legend( 'Years' );
    $x_legend->set_style( '{font-size: 12px;}' );
    $chart->set_x_legend( $x_legend );
	
	
	//y-axis
	$y = new y_axis();
    $y->set_range( 0, max($years), round((max($years)/10)) );
    $y->set_grid_colour('#AAAAAA');
    $chart->add_y_axis( $y );
    $y_legend = new y_legend( 'Quantity' );
    $y_legend->set_style( '{font-size: 12px;}' );
    $chart->set_y_legend( $y_legend );
    
    $bar->set_values($years);
    $chart->add_element($bar);
  	
  	
	break;
  //***********************End Device Age Bar Graph********************
  
  case 'resallcost':
  //***********************Start Type Cost Comparison Graph****************
	$reporttype = 'graph';
	$charttype = $_POST['allcosttype'];
	$chart = new open_flash_chart();
	$chart->set_bg_colour( '#DFDFDF' );
	
	$query = 'SELECT name, annual_cost FROM tbl_'.$charttype.' where annual_cost != 0';
	$result = mysql_query($query);
	
	if (!mysql_num_rows($result))
  	{
    	$error = 'The '.$charttype.' graph/chart cannot be generated: 0 records returned.';
    	include("includes/error.php");
    	exit();
  	}
		
	$rescost = array();
	while($row = mysql_fetch_row($result))
    {
    	if ($row[1] == NULL)
    		$row[1] = 0;
    	
    	$rescost[$row[0]] = $row[1];
    }	
    
    $pie = new pie();
    $pie->set_alpha(.7);
    $pie->set_start_angle(35);
    $pie->add_animation(new pie_fade());
    $pie->set_tooltip('#val# of #total#<br>#percent# of 100%');
    $pie->set_colours(array('#299DCA','#FF3333','#008000','#444444'));
    $respie = array();
    foreach($rescost as $name => $cost)
    {
    		$respie[] = new pie_value(intval($cost), "$name");
    }
    $pie->set_values($respie);
    
    $title = new title(ucwords($charttype.' type cost comparison'));
    $chart->set_title($title);
    $chart->add_element($pie);
    
    break;
  //*********************End Type Cost Comparison Graph*******************  
	
	
  case 'OpSys':
  //***********************Start Operating Systems Graph****************
	$reporttype = 'graph';
	$chart = new open_flash_chart();
    $chart->set_bg_colour( '#DDDDDD' );
    
		$query="select count(os), 
			os from tbl_server 
			where os in (select distinct os from tbl_server) 
			group by os";
		
		$result=mysql_query($query);
		$osdata=array();
		while ($row=mysql_fetch_row($result))
		{
			$osdata[$row[1]]=$row[0];
		}
    $pie = new pie();
    $pie->set_alpha(.7);
    $pie->set_start_angle(35);
    $pie->add_animation(new pie_fade());
    $pie->set_tooltip('#val# of #total#<br>#percent# of 100%');
    $pie->set_colours(array('#299DCA','#FF3333','#008000','#444444'));
    $ospie = array();
    foreach($osdata as $name => $number)
    {
    		$ospie[] = new pie_value(intval($number), "$name");
    }
    $pie->set_values($ospie);
    
    $title = new title(ucwords('Operating System Comparison'));
    $chart->set_title($title);
    $chart->add_element($pie);	
		
  break;
  //*********************End Operating Systems Graph*******************
  
  //*********************Begin Type Cost Comparison Pie Chart************************		
  
  case 'typecomp':
  	
  		$reporttype = 'graph';
  		$chart = new open_flash_chart();
        $chart->set_bg_colour( '#DDDDDD' );
		$types = array('device','server','service','database','box','application');
		$bigsum = 0;
		foreach($types as $type)
    	{
      		$query = 'SELECT SUM(annual_cost) FROM tbl_'.$type;
      		$result = mysql_query($query);
      		$row = mysql_fetch_row($result);
      		if($row[0])
      		{
      			$typecost[$type] = $row[0];
    			$bigsum += $row[0];
      		}
    	}
    	if(!$bigsum)
  		{
    	$error = 'The graph/chart cannot be generated: 0 records returned.';
    	include("includes/error.php");
    	exit();
  		}
		$pie = new pie();
	    $pie->set_alpha(.7);
	    $pie->set_start_angle(35);
	    $pie->add_animation(new pie_fade());
	    $pie->set_tooltip('#val# of #total#<br>#percent# of 100%');
	    $pie->set_colours(array('#299DCA','#FF3333','#008000','#444444'));
	    $typepie = array();
	    //print_r($typecost);
	    foreach($typecost as $type => $cost)
	    {
	    	    if(!empty($cost))
	    		$typepie[] = new pie_value(intval($cost), $type);
	    }
	    $pie->set_values($typepie);
	    
	    $title = new title(ucwords('Type Cost Comparison'));
	    $chart->set_title($title);
	    $chart->add_element($pie);
		
  break;
  //*********************End Type Cost Comparison Graph***************************		
    	
  
  case 'VirtOS':
  //*********************Begin Virtual OS Graph************************		
		$reporttype = 'graph';
		$chart = new open_flash_chart();
        $chart->set_bg_colour( '#DDDDDD' );
		$query="SELECT count(dep.c_id) as ccount, srv.name from tbl_server srv, tbl_dep dep 
		WHERE srv.server_id = dep.p_id and dep.c_table = 'server' 
		and dep.p_table = 'server' 
		group by name
		order by name asc";
		
		$result = mysql_query($query);
		if (!mysql_num_rows($result))
  		{
    	$error = 'The graph/chart cannot be generated: 0 records returned.';
    	include("includes/error.php");
    	exit();
  		}
  		
		$values = array();
		$labels = array();
		$count=0;
		while ($row=mysql_fetch_row($result))
		{
			$values[$count] = intval($row[0]);
		    $labels[$count] = $row[1];
			$count++;
		};
        
        $bar = new bar_filled('#299DCA','#DDDDDD');
		$bar->set_alpha('.70');
		$bar->set_on_show(new bar_on_show('grow-up',1, 0));
		
		$title = new title('Virtual Servers per VM Host');
		
		$chart->set_title( $title );
		$chart->set_bg_colour( '#DFDFDF' );
		
		//x-axis
		$x = new x_axis();
		$x_labels = new x_axis_labels();
	    $x_labels->set_labels($labels);
	    $x_labels->rotate(30);
	    $x->set_labels($x_labels);
	    $x->set_grid_colour('#AAAAAA');
	    $chart->set_x_axis($x);
		$x_legend = new x_legend( 'Server' );
	    $x_legend->set_style( '{font-size: 12px;}' );
	    $chart->set_x_legend( $x_legend );
		
		
		//y-axis
		$y = new y_axis();
	    $y->set_range( 0, max($values), round((max($values)/5)) );
	    $y->set_grid_colour('#AAAAAA');
	    $chart->add_y_axis( $y );
	    $y_legend = new y_legend( 'Quantity' );
	    $y_legend->set_style( '{font-size: 12px;}' );
	    $chart->set_y_legend( $y_legend );
	    
	    $bar->set_values($values);
	    $chart->add_element($bar);

  break;
  //*********************End Virtual OS Graph***************************
  
  //******************************************
  //	END GRAPHS AND CHARTS
  // 	END SWITCH STATEMENT FOR REPORT CHOICE
  //******************************************
  break;	
}

if($reporttype == 'report')
	{
		if((!isset($_POST['format'])) || $_POST['format'] != 'xls')
 			{
	 			createPDFReport($reportdata,$title);
 			}
			else
 			{
    			createExcelReport($reportdata);
  			}
  		die();
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Baconmap Graph</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="javascript/jquery/css/ui-darkness/jquery-ui-1.7.2.custom.css" />
<script type="text/javascript" src="includes/ofc/js/json/json2.js"></script>
<script type="text/javascript" src="javascript/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="javascript/jquery/js/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="includes/ofc/js/swfobject.js"></script>
<script type="text/javascript">
swfobject.embedSWF("includes/ofc/open-flash-chart.swf", "Bacon_Graph", "100%", "100%", "9.0.0");

$(function(){
$("#resize").resizable();
});

function ofc_ready()
{
    //alert('ofc_ready');
}

function open_flash_chart_data()
{
    return JSON.stringify(data);
}

function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}
    
var data = <?php echo $chart->toPrettyString(); ?>;

function doConvert() 
{ 
    var imageData = document.getElementById('Bacon_Graph').get_img_binary();
    document.getElementById('image_data').value = imageData;
    document.getpic.submit();
}

</script>
</head>
<body>
<center>
<div id="resize" style="width:<?php echo $gwidth; ?>px; height:<?php echo $gheight; ?>px; padding: 10px; background-color:#EEE;">
<div id="Bacon_Graph"></div>
</div>
<br />
<form name="getpic" action="graphimage.php?do=show" method="post"> 
<input type="hidden" name="image_data" id="image_data" />
<input type="button" name="print" value="Download Graph as Image" onclick="return doConvert();" />
</form>
</body>
</center>
</html>