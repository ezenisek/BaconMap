<?php
 /*
    **THIS NOTICE MUST APPEAR ON ALL PAGES AND VERSIONS OF BACONMAP**
       
    BaconMap - Resources Defined.
    Copyright 2008-09 NMSU Research IT, New Mexico State University
    Originally developed by Ed Zenisek, Denis Elkanov, and Abel Sanchez.
    
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
    require_once('includes/functions.php');
    $configfile = 'includes/settings.php';
    $nowrite = false;
    $writeme = '';
    
    if(isset($_REQUEST['from'])) $from = $_REQUEST['from']; else $from = '';
    if($from == 'dbsetup')
      {
        $currentfile = file($configfile);
        foreach($_POST as $name => $value)
          {
            // The n designates the new value.  We want to go through and check the 
            // OLD values, and then check them against the new equivalent. 
            if(substr($name,-1) == 'n')
              {
                $old = '';
                $new = $_POST[$name];
                $line = substr($name,0,-2);
                if($old != $new)
                  {
                    $newstring = ereg_replace("'.*'","'$new'",$currentfile[$line]);
                    $currentfile[$line] = $newstring;
                  }
              }
          }
         
         foreach($currentfile as $line)
          {
            $writeme .= trim($line)."\n";
          }
           
          $fh = fopen($configfile,'w+') or die("Cannot open $configfile");
          if(!fwrite($fh,trim($writeme)))
            {
              $error = 'Could not write to'.$filepath;
              include 'includes/error.php';
              exit();
            }
            
          if(!$query = file_get_contents('dbsetup.txt'))
            {
              echo 'Could not load database settings from dbsetup.txt.  Installation Failure.';
              exit();
            }
            
        require_once($configfile);
        dbConnect($dbhost,$database,$username,$password);
        $qbits = explode(';',$query);
        foreach($qbits as $q)
          {
            //echo $q.'<br /><br />';
            if($q != ' ' && !empty($q))
            mysql_query($q) or die('Could not create database tables.  Installation Failure.<br />Reason: '.mysql_error());
          }
          
        $reason = mysql_error();
        $query = "SELECT * from tbl_box";
        $result = mysql_query($query) or die('Tables could not be created because '.$reason);
      
      $admin = mysql_real_escape_string($_POST['adminemail']);
      $adminpw = md5($admin.$_POST['adminpw1']);
      $encryptpw = md5($_POST['encryptpw1']);
      
      $query = "INSERT INTO tbl_user (email,level,password)
      VALUES ('upload','256','$encryptpw')";
      mysql_query($query) or die ('Could not create encryption password entry because '.mysql_error());
      
      $query = "INSERT INTO tbl_user (email,level,password)
      VALUES ('$admin','0','$adminpw')";
      mysql_query($query) or die ('Could not create admin user because '.mysql_error());
              
      }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Bacon Map Setup</title>
	<script>
	
	// Email validation
	function echeck(str) {

		var at="@"
		var dot="."
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		if (str.indexOf(at)==-1){
		   alert("Invalid E-mail ID")
		   return false
		}

		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		   alert("Invalid E-mail ID")
		   return false
		}

		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		    alert("Invalid E-mail ID")
		    return false
		}

		 if (str.indexOf(at,(lat+1))!=-1){
		    alert("Invalid E-mail ID")
		    return false
		 }

		 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		    alert("Invalid E-mail ID")
		    return false
		 }

		 if (str.indexOf(dot,(lat+2))==-1){
		    alert("Invalid E-mail ID")
		    return false
		 }
		
		 if (str.indexOf(" ")!=-1){
		    alert("Invalid E-mail ID")
		    return false
		 }

 		 return true					
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
	
	function verifyFields()
	 {
	     if(isEmpty(document.setup.adminemail))
	       {
	         alert("Please enter a valid Administrator Email");
	         return false;
	       }
	     if(isEmpty(document.setup.adminpw1))
	       {
	         alert("Please enter a valid Administrator Password");
	         return false;
	       }
       if(isEmpty(document.setup.adminpw2))
	       {
	         alert("Please verify the Administrator Password");
	         return false;
	       }
       if(isEmpty(document.setup.encryptpw1))
	       {
	         alert("Please enter a valid Encryption Password");
	         return false;
	       }
       if(isEmpty(document.setup.encryptpw2))
	       {
	         alert("Please verify the Encryption Password");
	         return false;
	       }
       if(document.setup.adminpw1.value != document.setup.adminpw2.value)
        {
          document.setup.adminpw1.value = '';
          document.setup.adminpw2.value = '';
          alert("Your administrator passwords do not match.  Please verify they are correct");
          return false;
        }
       if(document.setup.encryptpw1.value != document.setup.encryptpw2.value)
        {
          document.setup.encryptpw1.value = '';
          document.setup.encryptpw2.value = '';
          alert("Your encryption passwords do not match.  Please verify they are correct");
          return false;
        }   
      if(echeck(document.setup.adminemail.value)==false){
		      document.setup.adminemail.value="";
		      document.setup.adminemail.focus();
		      alert("Please enter a valid Administrator Email Address");
		      return false;
		    }
	    document.setup.submit();
   }
	</script>
	</head>
<body>
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
	<div id="content">
		<div id="logo">
			<img src="images/baconmap2.jpg" />
		</div>
		<ul id="menu">
			<li>Thank you for choosing BaconMap.  Please follow the instructions to complete your installation.</li>
		</ul>
		<div id="intro" align="right">
			<h1>Resources <span class="white">Defined</span>.</h1>
		</div>
    <div id="wholepage">
    <h2 class="header">BaconMap <span class="blue">Setup</span></h2>
    <div class="minorheader">Welcome to BaconMap!</div>
    On behalf on the NMSU Research IT Team and BaconMap developers we would like to thank you for 
    using BaconMap to define and manage your resources.  We think you'll find that
    the BaconMap is a very beneficial piece of software that will give you a unique outlook 
    on your hardware and software dependencies.  We would love to hear what you think, so if you have
    any comments or questions please visit <a href="http://www.baconmap.org">BaconMap.org</a> and drop
    us a line.
    <br /><br />
    <div class="minorheader">Setting up your system</div>
    <?php if(!$from) { ?>
    <?php
    if(!is_writable($configfile))
      {
        echo '<div id="warning"><strong>The configuration file is not writable!</strong>  
        No changes can be made. 
        Please ensure the file <i>../includes/settings.php</i> exists and is writable by PHP.</div>';
        echo '<b><i>Once the file has been made writable refresh this page to clear this warning.</i></b>';
        echo '<br /><br />';
        $nowrite = true;
      }
    else
      $nowrite = false;
      
      $contents = file($configfile);
      $variables = array();
      $i = 0;
      $v = 0;
      while($i <= count($contents))
        {
          if(isset($contents[$i]) && substr($contents[$i],0,2) == '//')
            {
              $bits = explode('-',$contents[$i]);
              $variables[$v]['name'] = trim($bits[1]);
              $i++;
              $bits = explode('-',$contents[$i]);
              $variables[$v]['type'] = trim($bits[1]);
              $variables[$v]['description'] = '';
              if(($variables[$v]['type'] != 'text') && ($variables[$v]['type'] != 'password'))
                {
                  $i++;
                  $bits = explode('-',$contents[$i]);
                  $variables[$v]['options'] = trim($bits[1]);
                }
              $i++;
              while(isset($contents[$i]) && substr($contents[$i],0,2) == '//')
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
    If you can see this page then it means you have PHP successfully running on the webserver, and that's good.
    <br /><br />
    Next you'll need to ensure that the following files and/or directories are writable by the php user running under your webserver:
    <ul>
      <li>/logs and all subdirectories</li>
      <li>/image</li>
      <li>/includes/settings.php</li>
      <li>/includes/ostypes.txt</li>
      <li>/includes/poctypes.txt</li>
      <li>/includes/roles.txt</li>
      <li>/includes/dbtypes.txt</li>
      <li>/uploads</li>
    </ul>
    <br />
    We'll now verify that GraphViz is installed correctly.  GraphViz is the mapping software we use to create resource
    maps and flowcharts.  It's free and open source, but not bundled with BaconMap.  To get the GraphViz installation and 
    for more information, please visit <a href="http://www.graphviz.org">GraphViz.org</a>.<br />
    <iframe frameborder=0 name="graphvizcheck" id="graphvizcheck" width="800" height="140" src="graphvizcheck.php">Could not launch frame.</iframe>
    <br />
    You'll also need a mySQL installation running and ready to go.  Once it is you'll need to do the following:
    <ol>
      <li>Create a database in mySQL for BaconMap to use.</li>
      <li>Create a user in that database that BaconMap can use to connect.</li>
      <li>Set a password for that user, preferably something hard to guess.</li>
      <li>Ensure that the configuration file (/includes/settings.php) is writable by PHP. (If not, you'll see a warning message just below 'Setting up your system')</li>
    </ol>
    <br />
    Once these things are done, please enter the requested information below.  Mouse over the text in blue for more information on each option:
    <br />  
    <span style="color:red; font-size:.9em;"><b>A note about the encryption password:</b> 
    The encryption password is used to encrypt secure documents within BaconMap.  Any time someone wishes 
    to view a secured document, this password will be needed to decrpyt it. DO NOT LOSE THIS PASSWORD. 
    If this password is lost, all encrypted documents will be made worthless.  This password cannot be changed once set.
    </span>
    <center>
     <form name="setup" action="setup.php" method="post">
     <input type="hidden" name="from" value="dbsetup" />
    <br />
    <table class="formtable">
    <tr class="formtr"><td class="formtd" colspan="2">
    <hr>
    </td></tr>
    <tr class="formtr"><td class="formtd" colspan="2">
    <b>Database Settings</b></td></tr>
    <?php      
        $variables = array_slice($variables,0,5);
        foreach($variables as $key => $variable)
          {
            $regs = array();
            $description = scrub($variable['description']);
            echo '<tr class="formtr">';
            echo '<td class="formtd"  
                 onmouseover=\'Tip("'.$description.'")\'
                 onmouseout="UnTip()">';
            echo '<span class="blue">'.$variable['name'].': </span>';
            echo '</td><td class="formtd" style="text-align:right">';
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
    <hr>
    </td></tr>
    <tr class="formtr"><td class="formtd" colspan="2">
    <b>Administration Settings</b></td></tr>
    <tr class="formtr">
      <td class="formtd">
      Administrator Email: 
      </td><td class="formtd">
      <input type="text" name="adminemail" size="30" />
      </td></tr>
    <tr class="formtr">
      <td class="formtd">
      Administrator Password: 
      </td><td class="formtd">
      <input type="password" name="adminpw1" size="30" />
      </td></tr>
    <tr class="formtr">
      <td class="formtd">
      ReType Password: 
      </td><td class="formtd">
      <input type="password" name="adminpw2" size="30" />
      </td></tr>
    <tr class="formtr"><td class="formtd" colspan="2">
    <hr>
    </td></tr>
    <tr class="formtr"><td class="formtd" colspan="2">
    <b>Document Encryption Password</b>
    </td></tr>
    <tr class="formtr">
      <td class="formtd">
      Encryption Password: 
      </td><td class="formtd">
      <input type="password" name="encryptpw1" size="30" />
      </td></tr>
    <tr class="formtr">
      <td class="formtd">
      ReType Password: 
      </td><td class="formtd">
      <input type="password" name="encryptpw2" size="30" />
      </td></tr>
    <tr><td colspan="2">
    <center>
    Once everything is ready, click 'Complete Setup' to finish installation.
    <br />
    <input type="button" name="editfields" value="Complete Setup" onClick="verifyFields();"/>
    </center>
    <?php if($nowrite) echo '<script>document.setup.editfields.disabled = true;</script>'; 
          else echo '<script>document.setup.editfields.disabled = false;</script>';?>
    </td></tr>
    </table>
    </center>
    </form>
    <?php } else { // if !$from ?>
    Congratulations!  Your BaconMap installation should be complete.  <br /><br />
    <a href="<?php echo $rootdir; ?>">Begin Using BaconMap</a>
    <?php } ?>
    </div>
  </div>
</body>
</html>
