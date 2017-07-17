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
    
    This file is the update password file for BaconMap.
    
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
     
    authorize(2,"changepassword.php"); 

//print_r($_REQUEST);

if(isset($_POST['currentpw']) && !empty($_POST['currentpw']))
  {
    $query = "SELECT password FROM tbl_user WHERE user_id = ".$_SESSION['userid'];
    //echo $query;
    $result = mysql_query($query);
    $oldpw = mysql_result($result,0);
    //echo '<br />Old:'.$oldpw.' = '.md5($_SESSION['username'].$_POST['currentpw']);
    if($oldpw != md5($_SESSION['user'].$_POST['currentpw']))
      {
            $error = "The current password you provided is incorrect.  Please go back and try again.";
            include 'includes/error.php';
            exit();
      }
    if($_POST['newpw1'] == $_POST['newpw2'])
      {
        $userid = $_SESSION['userid'];
        $newpw = md5($_SESSION['user'].$_POST['newpw1']);
        $query = "UPDATE tbl_user SET password = '$newpw' WHERE user_id = '$userid'";
        if(!mysql_query($query))
          {
            $error = "Could not update your password.  Please contact the admin.";
            include 'includes/error.php';
            exit();
          }
        else
          {
            $message = "Your password has been updated.";
            include 'includes/success.php';
            exit();
          }
      }
  }
  elseif(isset($_POST['currentpw']))
    {
        $error = "An unknown error occurred.  Please contact the admin.";
        include 'includes/error.php';
        exit();
    }

?>  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Bacon Map Change Password Page</title>
	<script type="text/javascript">
	function isEmpty(mytext) {
	var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
		if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
		return true;
		}
		else {
		return false;
		}
	}
	
	function verifyUpdate() {
	  if(isEmpty(document.update.newpw1))
	   {
	     alert("Please specify a new password to update.");
	     return false;
	   }
	  if(isEmpty(document.update.currentpw))
	   {
	     alert("Please enter your current password to verify your login.");
	     return false;
	   }
	  if(document.update.newpw1.value != document.update.newpw2.value)
        {
          document.update.newpw1.value = '';
          document.update.newpw2.value = '';
          alert("Your new passwords do not match.  Please verify they are correct");
          return false;
        }
    document.update.submit();
	}
	parent.document.getElementById('mainwindowtitle').innerHTML = 'Update <span class="blue">Password</span>';
	</script>
</head>
<body >
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<div class="minorheader">Current Password</div>
<form name="update" action="changepassword.php" method="POST">
  <table class="formtable">
    <tr class="formtr">
      <td class="formtd">Password:</td>
      <td class="formtd"><input type="password" name="currentpw" size="25" maxlength="50" /></td>
    </tr>
   </table>
<br />
<div class="minorheader">New Password</div><br />
<table class="formtable">
    <tr class="formtr">
      <td class="formtd">New Password:</td>
      <td class="formtd"><input type="password" name="newpw1" size="25" maxlength="50" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">Verify Password:</td>
      <td class="formtd"><input type="password" name="newpw2" size="25" maxlength="50" /></td>
    </tr>
</table>
<br /><br />
<center><input type="button" name="doupdate" value="Update Password" onClick="verifyUpdate()" /></center>

 </form>
 <br /><br />
</body>
</html>
