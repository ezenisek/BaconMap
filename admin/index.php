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
    
    This file is the admin menu for BaconMap.
    
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
  $configfile = '../includes/settings.php';
  require_once($configfile);
  require_once('../includes/functions.php');
  $nowrite = false;
      
  if(isset($_GET['update']))
    $status = '- Configuration Updated';
  else
    $status = '';
  
  // First we're going to check that the appropriate settings are found from 
  // the settings file.  If not, then we need to automatically forward to the
  // setup page.
  
  if(empty($database) || empty($username) || empty($password))
    {
      header("Location: setup.php");
    }
  else
    dbConnect($dbhost,$database,$username,$password);
    
  // Now we check that the user is logged in.  If not, we forward to the login page.
  authorize(0,"admin/index.php");
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="../css/main.css" type="text/css" />
	<title>Bacon Map Administration</title>
	<script type="text/javascript">
	function doUpdate() {
	document.admin.submit();
  }
  function dbCheck(type) {
    if(type == 'all')
      {
        if(confirm("A complete database verification performs several checks on each resource, group, and relationship in the database.  It will automatically fix any errors it can, and errors it can't will be logged.  Any Orphans found will also be deleted.  It may take some time to complete.\n\nContinue?"))
        document.getElementById('dbcheck').src = 'dbcheck.php?type=all';
        else
        return false;
      }
    else if(type == 'orphan')
      {
        if(confirm("This procedure will delete, without warning, all orphans it finds in the database.\n\nContinue?"))
        document.getElementById('dbcheck').src = 'dbcheck.php?type=orphan';
        else
        return false;
      }
    else
      {
        document.getElementById('dbcheck').src = 'dbcheck.php?type='+type;
      }
  }
  </script>
</head>
<body>
<script src="../javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
	<div id="content">
		<div id="logo">
			<img src="../images/baconmap2.jpg" />
		</div>
		<ul id="menu">
			<li><a href="../index.php?frame=<?php echo urlencode('welcome.php'); ?>">Main</a></li>
			<li><a href="../index.php?frame=<?php echo urlencode('add.php?new=1'); ?>">Add Resource</a></li>
			<li><a href="../picture.php" target="_blank">Big Picture</a></li>
			<li><a href="../index.php?frame=<?php echo urlencode('group.php'); ?>">Resource Groups</a></li>
			<li><a href="../index.php?frame=<?php echo urlencode('contacts.php'); ?>">Contacts</a></li>
			<li><a href="../index.php?frame=<?php echo urlencode('whatif.php'); ?>">What If?</a></li>
			<li><a href="../index.php?frame=<?php echo urlencode('reports.php'); ?>">Reports</a></li>
		</ul>
		<div id="intro" align="right">
			<h1>Resources <span class="white">Defined</span>.</h1>
		</div>
    <div id="wholepage">
    <h2 class="header">BaconMap <span class="blue">Administration <?php echo $status; ?></span></h2>
    <?php 
      if(!is_writable($configfile))
      {
        echo '<div id="warning"><strong>The configuration file is not writable!</strong>  
        No changes can be made. 
        Please ensure the file <i>../includes/settings.php</i> exists and is writable by PHP.</div><br />';
        $nowrite = true;
      }
      $contents = file($configfile);
      $variables = array();
      $i = 0;
      $v = 0;
      while($i < count($contents))
        {
          if(substr($contents[$i],0,2) == '//')
            {
              $bits = explode('-',$contents[$i]);
              $variables[$v]['name'] = trim($bits[1]);
              $i++;
              $bits = explode('-',$contents[$i]);
              $variables[$v]['type'] = trim($bits[1]);
              if(($variables[$v]['type'] != 'text') && ($variables[$v]['type'] != 'password'))
                {
                  $i++;
                  $bits = explode('-',$contents[$i]);
                  $variables[$v]['options'] = trim($bits[1]);
                }
              $i++;
              $variables[$v]['description'] = '';
              while(substr($contents[$i],0,2) == '//')
              {
                 $bits = explode('-',$contents[$i]);
                 $variables[$v]['description'] .= $bits[1];
                 $i++;
              }
              $variables[$v]['definition'] = $contents[$i];
              $variables[$v]['key'] = $i;
              $v++;
            }
            $i++;
        }  
    ?>
    <form name="admin" action="updatesettings.php" onsubmit="validateForm()" method="post">
    <div class="minorheader">BaconMap Settings</div>
    <?php if($displayhelp) { ?>
    <div id="help">Learn what all these settings do<br /><a href="#">View Documentation</a></div>
	<?php } ?>
    <center>
    <table class="formtable">
    <?php      
        foreach($variables as $key => $variable)
          {
            $regs = array();
            $description = scrub($variable['description']);
            echo '<tr class="formtr">';
            echo '<td class="formtd"  
                 onmouseover=\'Tip("'.$description.'")\'
                 onmouseout="UnTip()">';
            echo '<span class="blue">'.$variable['name'].': </span>';
            echo '</td><td class="formtd">';
            if(!ereg("'(.*)'",$variable['definition'],$regs))
              {
                echo 'Gah!  Variable not found.  Dying.';
                //exit();
              }
            switch(trim($variable['type'])) {
              case 'text':
              echo '<input type="text" name="'.$variable['key'].'_n" value="'.$regs[1].'" />';
              break;
              case 'password':
              echo '<input type="password" name="'.$variable['key'].'_n" value="'.$regs[1].'" />';
              break;
              case 'checkbox':
              echo '<input type="checkbox" name="'.$variable['key'].'_n"';
                if($regs[1])
                  echo ' checked';
              echo ' value="1"/> '.$variable['options'];
              break;
              case 'select':
              $bits = explode(',',$variable['options']);
              echo '<select name="'.$variable['key'].'_n">';
              $i=0;
              while($i < count($bits))
                {
                  echo '<option value="'.$bits[$i].'" ';
                  if($regs[1] == $bits[$i])
                    echo 'selected ';
                  echo '>'.$bits[$i+1].'&nbsp;</option>';
                  $i++;$i++;
                }
              echo '</select>';
              break;
              case 'radio':
              $bits = explode(',',$variable['options']);
              $i=0;
              while($i < count($bits))
                { 
                  echo '<input type="radio" name="'.$variable['key'].'_n" value="'.$bits[$i].'"';
                  if($regs[1] == $bits[$i])
                    echo ' checked';
                  echo ' />';
                  echo ' '.$bits[$i+1].' &nbsp;&nbsp;';
                  $i++;$i++;
                }
              break;
              case 'textarea':
              $bits = explode(',',$variable['options']);
              echo '<textarea name="'.$variable['key'].'_n" rows="'.$bits[0].'" cols="'.$bits[1].'">'.$regs[1].'</textarea>';
              break;
            }
            echo '<input type="hidden" name="'.$variable['key'].'" value="'.$regs[1].'" />';       
            echo '</td></tr>';
          }
    ?>    
    <tr class="formtr"><td class="formtd" colspan="2">
    <hr />
    <center>
    <br />
    <input type="button" name="editfields" value="Update Settings" onclick="doUpdate()" />
    </center>
    <?php if($nowrite) echo '<script>document.admin.editfields.disabled = true;</script>'; ?>
    </td></tr>
    </table>
    <br />
    </center>
    <div class="minorheader">BaconMap User Administration</div>
    <center>
    <iframe id="useradmin" name="useradmin" frameborder=0 width="575" height="250" src="useradmin.php"></iframe>
    </center>
    <div class="minorheader">Database Integrity Checks</div>
    <?php if($displayhelp) { ?>
    <div id="help">Learn about database integrity<br /><a href="#">View Documentation</a></div>
	<?php } ?>
    <center>
    <table width="540">
      <tr>
        <td>
        <input type="button" name="orphan" value="Orphan Check" 
        onmouseover="Tip('Checks for orphans in the database and deletes them if found')"
        onmouseout="UnTip()" onclick="dbCheck('orphan')" />
        </td>
        <td>
        <input type="button" name="group" value="Group Check" 
        onmouseover="Tip('Checks for integrity within resource groups')"
        onmouseout="UnTip()" onclick="dbCheck('group')" />
        </td>
        <td>
        <input type="button" name="relationship" value="Relationship Check" 
        onmouseover="Tip('Checks that all relationships are connected properly')"
        onmouseout="UnTip()" onclick="dbCheck('relationship')" />
        </td>
        <td>
        <input type="button" name="all" value="Full Database Verification" 
        onmouseover="Tip('Runs all of the available checks on the database to ensure integrity')"
        onmouseout="UnTip()" onclick="dbCheck('all')" />
        </td>
      </tr>
    </table>
    <div><span id="warning"></span></div>
    <br />
    <iframe id="dbcheck" name="dbcheck" width="540" height="100" frameborder=0 ></iframe>
    </center>
    <div class="minorheader">BaconMap Drop Down Lists</div>
    <?php
    
     // Here we go into the includes directory and find all the text files.
     // These should be all the drop down lists used in the program.  In this 
     // way, we can allow the user to edit what is contained in all of these 
     // lists.  We get all the lists and create textareas out of them so they
     // can be edited.
     
      $directory = $_SERVER['DOCUMENT_ROOT'].$rootdir.'/includes/';
      $files = array();
      if($dir = opendir($directory))
        {
        while(($file=readdir($dir)) !== false)
          {
            if(substr($file,strpos($file,'.')+1) == 'txt')
              $files[] = $file;
          }
        }
      else
        echo 'Could not open directory: '.$directory;
      foreach($files as $file)
        {
          $thisfile = $directory.$file;
          echo '<center><iframe id="'.$file.'" frameborder=0 width="540" height="300" src="updatelist.php?filepath='.$thisfile.'"></iframe></center>';
        }  
?>
<input type="hidden" name="doit" value=1 />
</form>
    </div>
		<div id="footer">
				<p class="right"><a href="http://baconmap.nmsu.edu/about.php" target="_blank">About BaconMap and the Development Team</a><br />
        <a href="../login.php?redir=logout" class="blue">Logout</a></p>

				<p class="left"><a href="http://www.gnu.org/licenses/gpl.html" target="_blank">Developed under the GPL (General Public License)</a><br />
				&copy; Copyright 2008 <a href="http://www.research.nmsu.edu" target="_blank">NMSU Research IT</a>, <a href="http://www.nmsu.edu" target="_blank">New Mexico State University</a>, and <a href="http://www.baconmap.org" target="_blank">BaconMap.org</a></p>
		</div>

</div>	
</body>
</html>
