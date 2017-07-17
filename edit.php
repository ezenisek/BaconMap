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
    
    This file is the edit resource menu for BaconMap.
    
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
    authorize(1,"index.php?frame=".urlencode('edit.php?id='.$_REQUEST['id']));

    if(!isset($_REQUEST['id']))
      {
        $error = "There was no resource specified.  Please try again.";
        include 'includes/error.php';
        exit();
      }
    $resource = $_REQUEST['id'];
    $bits = explode('_',$resource);
    $type = $bits[0];
    $id = $bits[1];
    
    $query = 'SELECT * FROM tbl_'.$type.' WHERE '.$type.'_id = '.$id;
    $result = mysql_query($query);
    if(mysql_num_rows($result) == 0)
      {
        $error = "The specified resource could not be found.  Please try again.";
        include 'includes/error.php';
        exit();
      }
    $row = mysql_fetch_assoc($result);
    
    $internal = false;
    $external = false;
    $foreign = false;        
    if($impact = $row['impacted'])
      {                     
         switch($impact) {
          case '1': 
            $internal = false;
            $external = false;
            $foreign = true;
          break;
          case '2':
            $internal = false;
            $external = true;
            $foreign = false;
          break;
          case '3': 
            $internal = false;
            $external = true;
            $foreign = true;
          break;
          case '4':
            $internal = true;
            $external = false;
            $foreign = false;
          break;
          case '5': 
            $internal = true;
            $external = false;
            $foreign = true;
          break;
          case '6':
            $internal = true;
            $external = true;
            $foreign = false;
          break;
          case '7':
            $internal = true;
            $external = true;
            $foreign = true;
          break;
          }
      }        
      $names = getNames();
      $thiskey = array_keys($names,$row['name']);
      unset($names[$thiskey[0]]);
      $nameslist = '';
      foreach($names as $name)
        {
          $nameslist .= "'$name',";
        }
      $nameslist = substr($nameslist,0,-1);
?>    
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Bacon Map Add Page</title>
	<script type="text/javascript">
	/*  
	The startup function sets the form for whatever type of resource
	we're editing.  
	*/
	var names = [<?php echo $nameslist; ?>];
	
	function startUp() {
   <?php echo 'document.edit.type.value = \''.$type.'\';'; ?>  
	 formSelect();
	 checkPOC();
	 setDepFrame();
   parent.document.getElementById('mainwindowtitle').innerHTML = 'Edit Detials For <span class="blue"><?php echo $row['name']; ?><\/span>'; 
   }
  function setDepFrame()
  {
    var location = 'dependencies.php?id=<?php echo $resource; ?>&thistype=' + 
    document.edit.type.value + 
    '<?php if(isset($_REQUEST['parent'])) echo '&parent='.$_REQUEST['parent']; ?>';
    
    window.open(location,'depframe');

// And I also put upload reload here
//    location = 'uploads.php?id=<?php echo "$id"?>&type='+document.edit.type.value;
//    window.open(location,'upframe');

  }
  var specificelement = '';
  var displayelements = new Array('boxdetails','serverdetails','dbdetails','devdetails','appdetails','servicedetails');
  function setDisplay(option) {
    option = option || "";
    for(var i=0;i<displayelements.length;i++)
	    {
	      document.getElementById(displayelements[i]).style.display = option;
          if(option != 'none')
            {
              document.getElementById('specificdetailheader').style.backgroundImage = "url('images/arrow_up.png')";
            }
          else
            {
              document.getElementById('specificdetailheader').style.backgroundImage = "url('images/arrow_down.png')";
            }
	    }
  }
  function formSelect() {
    document.getElementById('specificdetails').style.display = "";
	   if(document.edit.type.value == 'device')
	     {  
	       specificelement = 'devdetails';      
	     }	   
	   if(document.edit.type.value == 'box')
	     {
	       specificelement = 'boxdetails';
	     }
	   if(document.edit.type.value == 'server')
	     {
	       specificelement = 'serverdetails';
	     }	   
	   if(document.edit.type.value == 'application')
	     {
	       specificelement = 'appdetails';
	     }
	   if(document.edit.type.value == 'database')
	     {
	       specificelement = 'dbdetails';
	     }	   
	   if(document.edit.type.value == 'service')
	     {
	       specificelement = 'servicedetails';
	     }
	  setDisplay('none');
	  setDepFrame();
	} 
	function checkPOC() {
	   if(document.edit.poc_id.value == 'new')
	     {
	       document.getElementById('newpoc').style.display = "";
	       document.getElementById('poclist').style.height = '240px';
	     }
	   else
	     {
        document.getElementById('newpoc').style.display = "none";
        document.getElementById('poclist').style.height = '50px';
	     }
  }
	
	function addPOC() {
	   document.edit.poc_id.value = 'new';
	   checkPOC();
	}
	
	function isEmpty(mytext) {
	var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
		if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
		return true;
		}
		else {
		return false;
		}
	}
	
	function isNumeric(strString)
   //  check for valid numeric strings	
   {
   var strValidChars = "0123456789.-";
   var strChar;
   var blnResult = true;

   if (strString.length == 0) return false;

   //  test strString consists of valid characters listed above
   for (i = 0; i < strString.length && blnResult == true; i++)
      {
      strChar = strString.charAt(i);
      if (strValidChars.indexOf(strChar) == -1)
         {
         blnResult = false;
         }
      }
   return blnResult;
   }
	
	function oc(a)
  {
    var o = {};
    for(var i=0;i<a.length;i++)
    {
      o[a[i]]='';
    }
    return o;
  }
	
  function verifyEdit() {
  /*  Here we need to verify all the fields of our form depending on what type
  of resource we're editing and if we're also adding a Point of Contact.  The 
  dependencies will need to be pulled from the grid inside the iframe and passed
  along as well, and we do that by filling in some hidden fields.
  
  Once everything is verified and set to go to our editing script, we can 
  send the form along.
  */
  
  // Start by checking the name:
  if(isEmpty(document.edit.name))
    {
      alert("Please specify a name for this resource");
      return false;
    }
  if(document.edit.name.value in oc(names))
    {
      alert("You've chosen a name for this resource that already exists somewhere else in the map.  Please choose another.");
      return false;
    }
  if(!isNumeric(document.edit.annual_cost.value))
  	{
  	  alert("The annual cost must be a numeric value");
  	  return false;
  	}
  
  // Now we need to get more specific.
  if(document.edit.type.value == 'box')
    {
      if(isEmpty(document.edit.cpu_num))
        {
          alert("Please specify the number of CPUs");
          return false;
        }
      if(!isNumeric(document.edit.cpu_num.value))
        {
          alert("The number of CPUs must be a numeric value");
          return false;
        }
      if(isEmpty(document.edit.cpu_speed))
        {
          alert("Please specify the CPU Speed");
          return false;
        }
      if(!isNumeric(document.edit.cpu_speed.value))
        {
          alert("The CPU speed must be a numeric value");
          return false;
        }
      if(isEmpty(document.edit.memory))
        {
          alert("Please specify the amount of memory");
          return false;
        }
      if(!isNumeric(document.edit.memory.value))
        {
          alert("The Memory must be a numeric value");
          return false;
        }
      if(isEmpty(document.edit.disk_space))
        {
          alert("Please specify the amount of hard disk space");
          return false;
        }
      if(!isNumeric(document.edit.disk_space.value))
        {
          alert("The Disk Space must be a numeric value");
          return false;
        }
    }
    
   // If we have a new point of contact, we need to verify that information as well
   if(document.edit.poc_id.value == 'new')
   {
      if(isEmpty(document.edit.first))
        {
          alert("Please specify the POC First Name");
          return false;
        }
      if(isEmpty(document.edit.last))
        {
          alert("Please specify the POC Last Name");
          return false;
        }
      if(isEmpty(document.edit.phone))
        {
          alert("Please specify a POC Phone Number");
          return false;
        }
      if(isEmpty(document.edit.email))
        {
          alert("Please specify a POC email");
          return false;
        }
     }
     
    // If we're through that, now we can go about pulling the dependency 
    // information from our iframe.
    var framegrid = window.depframe.mygrid;
    document.edit.dependencies.value = framegrid.getAllRowIds(',');
    if(isEmpty(document.edit.dependencies) && (document.edit.type.value != 'box' && document.edit.type.value != 'device'))
      {
        alert("Only boxes and devices can be orphans. Please choose a parent for this resource.");
        return false;
      }
      
    // If we've got this far, it means most of our inputs are scrubbed and ready to be 
    // added to the database.  Good job!
    
    document.edit.submit();  
  }
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
<body >
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<script src="javascript/scw.js" type="text/JavaScript"></script>
<div class="framed">
<form name="edit" action="doedit.php">
<div class="minorheader dropheader" id="generalheader" onMouseOver="style.backgroundColor='#f4f4f4'" onMouseOut="style.backgroundColor='#FFF'" onClick="expand('general',this)">General Resource Details</div>
<input type="hidden" name="dependencies" />
<input type="hidden" name="type" value="<?php echo $type; ?>" />
<input type="hidden" name="id" value="<?php echo $id; ?>" />
Currently Editing Resource <?php echo ucwords($type).' '.$id.' ('.$row['name'].')'; ?>
<div id="general" class="slider" style="display:none;height:335px;" >
  <table class="formtable">
    <tr class="formtr">
      <td class="formtd"><span class="blue" 
      onmouseover="Tip('Please use a unique name.  It is recommended that common resources are named individually.<br />'+
      'For example: Several servers all run IIS as a service.  You might name each service like <i>IIS - ServerName</i>')"
      onmouseout="UnTip()">Resource Name:</span></td>
      <td class="formtd"><input type="text" name="name" size="25" maxlength="50" value="<?php if(isset($row['name'])) echo $row['name']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">Resource Role:</td>
      <td class="formtd"><select name="role">
        <?php
         // Here we pull all of roles out of the file roles.txt and put them in the drop down box.
         // If the role is already in a session variable, we check to see if it matches the role
         // we're currently putting in the box.  If it does, we make it selected to reflect the variable.
         $roles = array();
         $roles = file('includes/roles.txt');
         foreach($roles as $thisrole)
          {
            $thisrole = trim($thisrole);
            if(stristr($thisrole, '#') === false)
            {
              if(isset($row['role']) && $thisrole == trim($row['role']))
                echo '<option selected value="'.$thisrole.'"> '.$thisrole.'&nbsp;</option>';
              else
                echo '<option value="'.$thisrole.'"> '.$thisrole.'&nbsp;</option>';     
            }
          }
        ?>
      </select>  
      </td>
    </tr>
    <tr class="formtr">  
        <td class="formtd">Annual Cost:</td>
        <td class="formtd" colspan="2"><input type="text" name="annual_cost" size="15" maxlength="50" value="<?php if(isset($row['annual_cost'])) echo $row['annual_cost']; ?>" /></td>
      </tr>
      <tr class="formtr">  
        <td class="formtd">Vendor:</td>
        <td class="formtd" colspan="2"><input type="text" name="vendor" size="40" maxlength="50" value="<?php if(isset($row['vendor'])) echo $row['vendor']; ?>" /></td>
      </tr>
      <tr class="formtr">  
        <td class="formtd">Last Maintenance Date:</td>
        <td class="formtd" colspan="2"><input type="text" name="last_maint_date" size="10" maxlength="50" value="<?php if(isset($row['last_maint_date'])) { if(strtotime($row['last_maint_date']) > 0) echo date('Y-m-d',strtotime($row['last_maint_date'])); } ?>" />
        <img src="javascript/scw.gif" onclick="scwShow(document.edit.last_maint_date,this)" /></td>
      </tr>
      <tr class="formtr">
        <td class="formtd">Date of Purchase/Installation:</td>
        <td class="formtd" colspan="2"><input type="text" name="purchase_date" size="10" maxlength="50" value="<?php if(isset($row['date_purchased'])) { if(strtotime($row['date_purchased']) > 0) echo date('Y-m-d',strtotime($row['date_purchased'])); } ?>" />
        <img src="javascript/scw.gif" onclick="scwShow(document.edit.purchase_date,this)" /></td>
      </tr>
      <tr class="formtr">
        <td class="formtd"><span class="blue"
        onmouseover="Tip('RTO is a measure of how many days it would be acceptible for this resource to be down in an emergency situation.')" onmouseout="UnTip()">Recovery Time Objective:</span></td>
        <td class="formtd" colspan="2">Less than 
        <select name="rto">
        <?php
        	for($i=0;$i<=30;$i++)
        		{
        			if(isset($row['rto']) && $i == $row['rto'])
        				echo '<option value="'.$i.'" selected>'.$i.'</option>';
        			else
        				echo '<option value="'.$i.'" >'.$i.'</option>';	
        		}
        ?>
        </select> days
        </td>
      </tr>
   </table>
   <?php if($displayhelp) { ?>
   <div id="help">Learn About Impact<br /><a href="#">View Documentation</a></div>
   <?php } ?>
  Impact:  &nbsp;&nbsp;
  <input type="checkbox" name="impact_internal" <?php if($internal) echo 'checked'; ?> /> Internal
  &nbsp;&nbsp;
  <input type="checkbox" name="impact_external" <?php if($external) echo 'checked'; ?> /> External
  &nbsp;&nbsp;
  <input type="checkbox" name="impact_foreign" <?php if($foreign) echo 'checked'; ?> /> Foreign
  <br /><br /> 
  <div>    
  Resource Description: <br />
  <textarea name="description" rows="3" cols="60"><?php
  if(!isset($row['description']))
    echo 'Please enter a description of the resource.';
  else
    echo $row['description'];
  ?>
  </textarea>
  <br />
  </div>
</div>
<div id="specificdetails">
<br />
<div class="minorheader dropheader" onMouseOver="style.backgroundColor='#f4f4f4'" onMouseOut="style.backgroundColor='#FFF'" id="specificdetailheader" onClick="expand(specificelement,this)">Resource Specific Details</div><br />
<div id="boxdetails" class="slider" style="height:160px;display:none;">
<table class="formtable">
    <tr class="formtr">
      <td class="formtd">Number of CPUs:</td>
      <td class="formtd"><input type="text" name="cpu_num" size="3" maxlength="5" value="<?php if(isset($row['cpu_num'])) echo $row['cpu_num']; ?>" /></td>
      <td class="formtd">CPU Speed (MHz):</td>
      <td class="formtd"><input type="text" name="cpu_speed" size="5" maxlength="5" value="<?php if(isset($row['cpu_speed'])) echo $row['cpu_speed']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">Memory (Gbytes):</td>
      <td class="formtd"><input type="text" name="memory" size="3" maxlength="5" value="<?php if(isset($row['memory'])) echo $row['memory']; ?>" /></td>
      <td class="formtd">Hard Disk Space (Gbytes):</td>
      <td class="formtd"><input type="text" name="disk_space" size="5" maxlength="5" value="<?php if(isset($row['disk_space'])) echo $row['disk_space']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd" colspan="2">RAID: &nbsp;&nbsp;
                         <input type="radio" name="raid" value="1" <?php if(isset($row['raid']) && $row['raid']) echo 'checked'; ?> /> Yes&nbsp;&nbsp; 
                         <input type="radio" name="raid" value="0" <?php if(!isset($row['raid']) || !$row['raid']) echo 'checked'; ?> /> No</td>
      <!-- <td class="formtd" colspan="2">Virtual OS: &nbsp;&nbsp;
                         <input type="radio" name="virtual_os" value="1" <?php if(isset($row['virtual_os']) && $row['virtual_os']) echo 'checked'; ?> /> Yes&nbsp;&nbsp; 
                         <input type="radio" name="virtual_os" value="0" <?php if(!isset($row['virtual_os']) || !$row['virtual_os']) echo 'checked'; ?> /> No</td> -->
    </tr>
    <tr class="formtr">
      <td class="formtd">Serial Number:</td>
      <td class="formtd" colspan="2"><input type="text" name="box_serial" size="40" maxlength="50" value="<?php if(isset($row['serial']) )echo $row['serial']; ?>" /></td>
    </tr>
    <tr class="formtr">  
      <td class="formtd">Model:</td>
      <td class="formtd" colspan="2"><input type="text" name="box_model" size="40" maxlength="50" value="<?php if(isset($row['model'])) echo $row['model']; ?>" /></td>
    </tr>
    <tr class="formtr">  
      <td class="formtd">Physical Location:</td>
      <td class="formtd" colspan="2"><input type="text" name="box_location" size="40" maxlength="50" value="<?php if(isset($row['location'])) echo $row['location']; ?>" /></td>
    </tr>
</table>
</div>
<div id="dbdetails" class="slider" style="height:50px;display:none;">
Database Type: 
    <select name="db_type">
        <?php
          // Here we pull all of the available host box ids and names from the database and 
          // put them in a dropdown.  We also check the session variable to see if it's already
          // set.
         $dbtypes = array();
         $dbtypes = file('includes/dbtypes.txt');
         foreach($dbtypes as $thistype)
          {
          	$thistype = trim($thistype);
            if(stristr($thistype, '#') === false)
            {
              if(isset($row['db_type']) && $thistype == $row['db_type'])
                echo '<option selected value="'.$thistype.'"> '.$thistype.'&nbsp;</option>';
              else
                echo '<option value="'.$thistype.'"> '.$thistype.'&nbsp;</option>';     
            }
          }
        ?>
    </select>
</div>
<div id="devdetails" class="slider" style="height:100px;display:none;">
<table class="formtable">    
    <tr class="formtr">
      <td class="formtd">Serial Number:</td>
      <td class="formtd" colspan="2"><input type="text" name="device_serial" size="40" maxlength="50" value="<?php if(isset($row['serial'])) echo $row['serial']; ?>" /></td>
    </tr>
    <tr class="formtr">  
      <td class="formtd">Model:</td>
      <td class="formtd" colspan="2"><input type="text" name="device_model" size="40" maxlength="50" value="<?php if(isset($row['model'])) echo $row['model']; ?>" /></td>
    </tr>
    <tr class="formtr">  
      <td class="formtd">Physical Location:</td>
      <td class="formtd" colspan="2"><input type="text" name="device_location" size="40" maxlength="50" value="<?php if(isset($row['location'])) echo $row['location']; ?>" /></td>
    </tr>
</table>
</div>
<div id="serverdetails" class="slider" style="height:50px;display:none;">
<table class="formtable">
    <tr class="formtr">
      <td class="formtd">
      Operating System: 
      <select name="os_type">
        <?php
          // Here we pull all of the available host box ids and names from the database and 
          // put them in a dropdown.  We also check the session variable to see if it's already
          // set.
         $ostypes = array();
         $ostypes = file('includes/ostypes.txt');
         foreach($ostypes as $thistype)
          {
          	$thistype = trim($thistype);
            if(stristr($thistype, '#') === false)
            {
              if(isset($row['OS']) && $thistype == $row['OS'])
                echo '<option selected value="'.$thistype.'"> '.$thistype.'&nbsp;</option>';
              else
                echo '<option value="'.$thistype.'"> '.$thistype.'&nbsp;</option>';     
            }
          }
        ?>
    </select>
    </td>
    <td class="formtd">Virtual OS: &nbsp;&nbsp;
                         <input type="radio" name="server_virtual" value="1" <?php if(isset($row['virtual']) && $row['virtual']) echo 'checked'; ?> /> Yes&nbsp;&nbsp;
                         <input type="radio" name="server_virtual" value="0" <?php if(isset($row['virtual']) && !$row['virtual']) echo 'checked'; ?> /> No</td>
  </tr>
</table>
</div>
<div id="appdetails" class="slider" style="height:50px;display:none;">
  <table class="formtable">
      <tr class="formtr">
        <td class="formtd">
        There are no Resource Specific Details for Applications.
        </td>
      </tr>
  </table>
  </div>
  <div id="servicedetails" class="slider" style="height:50px;display:none;">
  <table class="formtable">
      <tr class="formtr">
        <td class="formtd">
        There are no Resource Specific Details for Services.
        </td>
      </tr>
  </table>
  </div>
</div>
<div class="minorheader dropheader" onMouseOver="style.backgroundColor='#f4f4f4'" onMouseOut="style.backgroundColor='#FFF'" onClick="expand('deps',this)">Dependencies</div>
<div class="slider" id="deps" style="display:none;height:225px">
<iframe id="depframe" scrolling="no" width="560" height="220" name="depframe" frameborder="0"></iframe>
</div>
<br />
<div class="minorheader dropheader" onMouseOver="style.backgroundColor='#f4f4f4'" onMouseOut="style.backgroundColor='#FFF'" onClick="expand('poclist',this)">Point of Contact Information</div>
<div class="slider" id="poclist" style="display:none;height:50px">
Choose a <span class="blue" onmouseover="Tip('Point of Contact')" onmouseout="UnTip()">POC</span>:
<select name="poc_id" onChange="checkPOC()"> 
  <?php
    // Here we pull all of the available POC ids and names from the database and 
    // put them in a dropdown.  We also check the session variable to see if it's already
    // set.
      $query = "SELECT poc_id, first, middle, last FROM tbl_poc ORDER BY first, last";
      $result = mysql_query($query);
        if(mysql_num_rows($result) == 0)
          echo '<option value="new"> No POCs Found - Please Add One </option>';
        else
         {    
           while($pocrow = mysql_fetch_row($result))
             {
              $pocname = $pocrow[1];
              if(!empty($row[2]))
                $pocname .= ' '.$pocrow[2];
              $pocname .= ' '.$pocrow[3];
              
               if(isset($row['poc_id']) && $pocrow[0] == $row['poc_id'])
                  echo '<option selected value="'.$pocrow[0].'"> '.$pocname.' &nbsp;</option>';
               else
                  echo '<option value="'.$pocrow[0].'"> '.$pocname.' &nbsp;</option>';
             }
         }
    ?>
    <option value="new">New POC</option>
</select> &nbsp;&nbsp;&nbsp;&nbsp;
<input onmouseover="Tip('Add a new Point of Contact')" type="button" 
name="addpoc" value="New POC" onclick="addPOC()" onmouseout="UnTip()" />
<br />
<div id="newpoc">
<br />
<table class="formtable">
    <tr class="formtr">
      <td class="formtd">First Name:</td>
      <td class="formtd"><input type="text" name="first" size="25" maxlength="50" value="<?php if(isset($row['first'])) echo $row['first']; ?>" /></td>
      <td class="formtd">Middle:</td>
      <td class="formtd"><input type="text" name="middle" size="5" maxlength="5" value="<?php if(isset($row['middle'])) echo $row['middle']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">Last Name:</td>
      <td class="formtd"><input type="text" name="last" size="25" maxlength="50" value="<?php if(isset($row['last'])) echo $row['last']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">Phone:</td>
      <td class="formtd"><input type="text" name="phone" size="15" maxlength="20" value="<?php if(isset($row['phone'])) echo $row['phone']; ?>" /></td>
      <td class="formtd">E-Mail:</td>
      <td class="formtd"><input type="text" name="email" size="30" maxlength="50" value="<?php if(isset($row['email'])) echo $row['email']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">POC Type</td>
       <td class="formtd"><select name="poc_type">
        <?php
         // Here we pull all of roles out of the file roles.txt and put them in the drop down box.
         // If the role is already in a session variable, we check to see if it matches the role
         // we're currently putting in the box.  If it does, we make it selected to reflect the variable.
         $types = array();
         $types = file('includes/poctypes.txt');
         foreach($types as $thistype)
          {
            $thistype = trim($thistype);
            if(stristr($thistype, '#') === false)
            {
              if(isset($row['poc_type']) && $thistype == $row['poc_type'])
                echo '<option selected value="'.$thistype.'"> '.$thistype.' &nbsp;</option>';
              else
                echo '<option value="'.$thistype.'"> '.$thistype.' &nbsp;</option>';     
            }
          }
        ?>
      </select>  
      </td>
    </tr>
  </table>
  <div>    
  POC Description: <br />
  <textarea name="poc_description" rows="3" cols="60"><?php
  if(!isset($row['pocdescription']))
    echo 'Please enter a description.';
  else
    echo $row['pocdescription'];
  ?>
  </textarea>
  </div>
</div>
</div>
<br />
<div class="minorheader dropheader" onMouseOver="style.backgroundColor='#f4f4f4'" onMouseOut="style.backgroundColor='#FFF'" onClick="expand('uploads',this)">Document Upload and Download</div>
<div class="slider" id="uploads" style="display:none;height:100px">
<iframe id="upframe" scrolling="no" width="560" height="220" name="upframe" frameborder="0" src="uploads.php?type=<?php echo "$type&amp;id=$id"?>"
>No iframes</iframe>
</div>
<div id="endform">
<br />
<div class="minorheader">Review and Submit Edited Resource</div><br />
Please review the above information and click 'Update Resource Information'.<br /><br/>
<center><input type="button" name="doedit" value="Update Resource Information" onClick="verifyEdit()" /></center>
</div>
<script type="text/javascript">startUp();</script>
 </form>
 <br /><br />
 </div>
</body>
</html>
