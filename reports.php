<?php
/*
    **THIS NOTICE MUST APPEAR ON ALL PAGES AND VERSIONS OF BACONMAP**
       
    BaconMap - Resources Defined.
    Copyright 2008-09 NMSU Research IT, New Mexico State University
    Originally developed by Ed Zenisek, Denis Elkanov, and Abel Sanchez.
    
    Other open source projects used in BaconMap are copyright 
    their respective owners.
    
    This file is the report menu for BaconMap.
    
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
  
  // Now we check that the user is logged in.  If not, we forward to the login page.
  authorize(2,"index.php?frame=".urlencode('reports.php'));

  // Get a nice list of all resources in the database
  $services = getResources('service');
  $servers = getResources('server');
  $devices = getResources('device');
  $databases = getResources('database');
  $boxes = getResources('box');
  $applications = getResources('application');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Bacon Map Reports</title>
	<script type="text/javascript">
	parent.document.getElementById('mainwindowtitle').innerHTML = 'BaconMap <span class="blue">Reports</span>';
	var callingElement = '';
  	var slideElement = '';
	  function expand(element,thisElement) {
	    callingElement = thisElement;
	    slideElement = document.getElementById(element);
	    toggleSlide(element);
	    setTimeout("checkBG()",500);
	  }
	  function checkBG() {
	    if(slideElement.style.display == "block")
	      {
	        callingElement.style.backgroundImage = "url('images/arrow_up.png')";
	      }
	    else
	      {
	        callingElement.style.backgroundImage = "url('images/arrow_down.png')";
	      }   
	  }
	</script>
	<script src="javascript/motionpack.js" type="text/javascript" language="JavaScript"></script>
</head>
<body>
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<form name="report" action="doreport.php" method="post" />
<div class="minorheader dropheader" id="generalheader" onMouseOver="style.backgroundColor='#f4f4f4'" onMouseOut="style.backgroundColor='#FFF'" onClick="expand('textreports',this)">Textual Reports</div>
<div id="textreports" class="slider" style="display:none;height:220px;" >
	<table class="formtable">
	    <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="resourcereport" checked/>
	      <span onmouseover="Tip('A report on the chosen resource, all children, all parents, and contact info.')"
	      onmouseout="UnTip()">Resource Report</span>
	      </td>
	      <td class="formtd">
	      <select name="resource" />
	      <?php
	        echo '<optgroup label="Boxes&nbsp;" class="boxdropdown" style="background-image: url(tree_images/server.png);">';
	        foreach($boxes as $thisbox)
	          {
	            echo '<option value="box_'.$thisbox['id'].'" class="boxdropdown" 
	            style="background-image: url(tree_images/server.png);">'.$thisbox['name'].'&nbsp;</option>';
	          }
	        echo '</optgroup>';
	        // --------
	        echo '<optgroup label="Servers&nbsp;" class="boxdropdown" style="background-image: url(tree_images/computer.png);">';
	        foreach($servers as $thisserver)
	          {
	            echo '<option value="server_'.$thisserver['id'].'" class="boxdropdown" 
	            style="background-image: url(tree_images/computer.png);">'.$thisserver['name'].'&nbsp;</option>';
	          }
	        echo '</optgroup>';
	        // ---------
	        echo '<optgroup label="Databases&nbsp;" class="boxdropdown" style="background-image: url(tree_images/database.png);">';
	        foreach($databases as $thisdatabase)
	          {
	            echo '<option value="database_'.$thisdatabase['id'].'" class="boxdropdown" 
	            style="background-image: url(tree_images/database.png);">'.$thisdatabase['name'].'&nbsp;</option>';
	          }
	        echo '</optgroup>';
	        // ----------
	        echo '<optgroup label="Services&nbsp;" class="boxdropdown" style="background-image: url(tree_images/cog.png);">';
	        foreach($services as $thisservice)
	          {
	            echo '<option value="service_'.$thisservice['id'].'" class="boxdropdown" 
	            style="background-image: url(tree_images/cog.png);">'.$thisservice['name'].'&nbsp;</option>';
	          }
	        echo '</optgroup>';
	        // ----------
	        echo '<optgroup label="Applications&nbsp;" class="boxdropdown" style="background-image: url(tree_images/application.png);">';
	        foreach($applications as $thisapplication)
	          {
	            echo '<option value="application_'.$thisapplication['id'].'" class="boxdropdown" 
	            style="background-image: url(tree_images/application.png);">'.$thisapplication['name'].'&nbsp;</option>';
	          }
	        echo '</optgroup>';
	        // ----------
	        echo '<optgroup label="Devices&nbsp;" class="boxdropdown" style="background-image: url(tree_images/drive.png);">';
	        foreach($devices as $thisdevice)
	          {
	            echo '<option value="device_'.$thisdevice['id'].'" class="boxdropdown" 
	            style="background-image: url(tree_images/drive.png);">'.$thisdevice['name'].'&nbsp;</option>';
	          }
	        echo '</optgroup>';
	      ?>
	      </td>
	    </tr>
	
	    <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="childreport"/>
	      <span onmouseover="Tip('A report of all resources, grouped by type, and how many children they have.')"
	      onmouseout="UnTip()">Children Report</span>
	      </td>
	      <td class="formtd">
	      Ordered by Number of Children:
	      <select name="childreportorder" />
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	    </tr>
	    <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="parentreport" />
	      <span onmouseover="Tip('A report of all resources, grouped by type, and how many parents they have.')"
	      onmouseout="UnTip()">Parents Report</span>
	      </td>
	      <td class="formtd">
	      Ordered by Number of Parents:
	      <select name="parentreportorder" />
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	    </tr>
	
	    <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="allcontact" />
	      <span onmouseover="Tip('A report containing all Points of Contact and their information.')"
	      onmouseout="UnTip()">Point of Contact Report</span>
	      </td>
	      <td class="formtd">
	      Ordered by:
	      <select name="allcontactsort" />
	      <option value="last">Last Name</option>
	      <option value="first">First Name</option>
	      <option value="resources">Number of Resources&nbsp;</option>
	      </select>
	      <select name="allcontactorder" />
	      <option value="asc">Ascending</option>
	      <option value="desc">Descending&nbsp;</option>
	      </select>
	      </td>
	    </tr>
	
		<tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="allmaintenance" />
	      <span onmouseover="Tip('A report giving all resources and the date they were last maintained.')"
	      onmouseout="UnTip()">Maintenance Report</span>
	      </td>
	      <td class="formtd">
	      Ordered by Date:
	      <input type="hidden" name="allmaintenancesort" value="last_maint_date" />
	      <!--
	      <select name="allmaintenancesort" />
	      <option value="last_maint_date">Date</option>
	      </select>
	      -->
	      <select name="allmaintenanceorder" />
	      <option value="asc">Ascending</option>
	      <option value="desc">Descending&nbsp;</option>
	      </select>
	      </td>
	    </tr>
	
		<tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="resourceage" />
	      <span onmouseover="Tip('A report showing the age of all resources.')"
	      onmouseout="UnTip()">Resource Aging Report</span>
	      </td>
	      <td class="formtd">
	      Ordered by Purchase/Installation Date:
	      <input type="hidden" name="resourceagesort" value="date_purchased" />
	      <!--
	      <select name="resourceagesort" />
	      <option value="date_purchased">Date</option>
	      </select>
	      -->
	      <select name="resourceageorder" />
	      <option value="asc">Ascending</option>
	      <option value="desc">Descending&nbsp;</option>
	      </select>
	      </td>
	    </tr>
	
		<tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="allcost" />
	      <span onmouseover="Tip('A report showing the operating costs of your resources.')"
	      onmouseout="UnTip()">Resource Cost Report</span>
	      </td>
	      <td class="formtd">
	      Ordered by:
	      <select name="allcostsort" />
	      <option value="annual_cost">Cost</option>
	      <option value="type">Resource Type</option>
	      </select>
	      <select name="allcostorder" />
	      <option value="asc">Ascending</option>
	      <option value="desc">Descending&nbsp;</option>
	      </select>
	      </td>
	    </tr>
	
		<tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="rtosimple" />
	      <span onmouseover="Tip('A report listing all the Recovery Time Objectives for your resources.')"
	      onmouseout="UnTip()">RTO Report</span>
	      </td>
	      <td class="formtd">Ordered by RTO: 
	      <select name="rtosimpleorder" />
	      <option value="asc">Ascending</option>
	      <option value="desc">Descending&nbsp;</option>
	      </select>
	      </td>
	    </tr>
	
	    <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="rolereport" />
	      <span onmouseover="Tip('A report of resource roles and the resources that fill them.')"
	      onmouseout="UnTip()">Role Report</span>
	      </td>
	    </tr>
	</table>
<br />
<!--
Report Format: &nbsp;&nbsp;     
<input type="radio" name="format" value="pdf" checked /> PDF
&nbsp;&nbsp;&nbsp;
<input type="radio" name="format" value="excel" /> Excel
<br />
//-->  
</div>
<br />
<div class="minorheader dropheader" id="generalheader" onMouseOver="style.backgroundColor='#f4f4f4'" onMouseOut="style.backgroundColor='#FFF'" onClick="expand('graphs',this)">Graphs and Charts</div>
<div id="graphs" class="slider" style="display:none;height:120px;" >
	<table class="formtable">
	    <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="allage" />
	      <span onmouseover="Tip('A report containing a chart by resource type age.')"
	      onmouseout="UnTip()">Age Chart by</span>
	      <select name="allagetype" >
	      <option value="box">Box</option>
	      <option value="device">Device</option>
	      <option value="server">Server&nbsp;</option>
	      </select>
	      </td>
	   </tr>
	   <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="typecomp" />
	      <span onmouseover="Tip('A pie chart with type cost comparison')"
	      onmouseout="UnTip()">Type Cost Comparison</span>
	      </td>
	   </tr>
	    <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="resallcost" />
	      <span onmouseover="Tip('A pie chart resource cost by type.')"
	      onmouseout="UnTip()">Cost Chart Comparison by</span>
	      <select name="allcosttype" >
	      <option value="application">Application</option>
	      <option value="box">Box</option>
	      <option value="database">Database</option>	      
	      <option value="device">Device</option>
	      <option value="service">Service</option>	      
	      <option value="server">Server&nbsp;</option>
	      </select>
	      </td>
	   </tr>
	   <tr class="formtr">
	      <td class="formtd">
	      <input type="radio" name="choice" value="OpSys" />
	      <span onmouseover="Tip('Operating sytem type pie chart')"
	      onmouseout="UnTip()">Operating Systems</span>
	      </td>
	      <td class="formtd">
	      <input type="radio" name="choice" value="VirtOS" />
	      <span onmouseover="Tip('Virtual systems by host bar graph')"
	      onmouseout="UnTip()">Virtual Systems</span>
	      </td>
	    </tr>
	</table>
</div>
<br />    
<hr>
<br />  
<input type="submit" name="sumbit" value="Generate" />

</body>
</html>
