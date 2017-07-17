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
    
    This file is the index page for BaconMap.
    
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
  unset($database);
  unset($username);
  unset($password);
  require_once('../includes/settings.php');
  require_once('../includes/functions.php');
  $level = Array("Admin","Read/Write","Read only");
 
  // First we're going to check that the appropriate settings are found from 
  // the settings file.  If not, then we need to automatically forward to the
  // setup page.
  
  if(empty($database) || empty($username) || empty($password))
    {
      header("Location: setup.php");
    }
  else
    dbConnect($dbhost,$database,$username,$password);

    // Second, check the authorization
    //authorize(0,"useradmin.php");

$message="";
if(isset($_POST['usertodel']) && $_POST['usertodel'] != ''){ // delete a user
 $query="delete from tbl_user where user_id=".$_POST['usertodel'].";";
 mysql_query($query) or $message="Could not delete: $query<BR>error:".mysql_error();
}
elseif(isset($_POST['newemail']) && isset($_POST['newpassword']) && !empty($_POST['newemail'])){ // create a new user
  $query="insert into tbl_user (email,level,password) values('".mysql_real_escape_string($_POST['newemail'])."',".$_POST['newlevel'].",'".md5($_POST['newemail'].$_POST['newpassword'])."');";
  mysql_query($query) or $message="Could not insert: $query<BR>error:".mysql_error();
 }
elseif(isset($_POST['usertoreset']) && $_POST['usertoreset'] != '') {
  if(!resetPassword($_POST['usertoreset']))
    {
      echo "Could not reset password";
      die();
    }
}
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="../css/main.css" type="text/css" />
	<link href="../javascript/greybox/gb_styles.css" rel="stylesheet" type="text/css" />
	<title>Bacon Map User Administration</title>
	<script type="text/javascript">
	// Email validation
	function echeck(str) {

		var at="@"
		var dot="."
		var lat=str.indexOf(at)
		var lstr=str.length
		var ldot=str.indexOf(dot)
		if (str.indexOf(at)==-1){
		   //alert("Invalid E-mail ID")
		   return false
		}

		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		   //alert("Invalid E-mail ID")
		   return false
		}

		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		    //alert("Invalid E-mail ID")
		    return false
		}

		 if (str.indexOf(at,(lat+1))!=-1){
		    //alert("Invalid E-mail ID")
		    return false
		 }

		 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		    //alert("Invalid E-mail ID")
		    return false
		 }

		 if (str.indexOf(dot,(lat+2))==-1){
		    //alert("Invalid E-mail ID")
		    return false
		 }
		
		 if (str.indexOf(" ")!=-1){
		    //alert("Invalid E-mail ID")
		    return false
		 }

 		 return true					
	}
	
	function doValidate() {
	if(echeck(document.form0.newemail.value)==false){
		      document.form0.newemail.value="";
		      document.form0.newemail.focus();
		      alert("Please enter a valid Email Address");
		      return false;
		    }
	document.form0.submit();
	}
	
	function doDelete(id) {
	 if(confirm("You are about to remove this user from the system.  This cannot be undone.\n\nContinue?")) {
    document.form0.usertodel.value=id;
    document.form0.submit();
   }
	}
	
	function doReset(id) {
	 if(confirm("This user's password will be reset and the new password will be e-mailed to their listed address.\n\n Continue?")) {
	 document.form0.usertoreset.value=id;
	 document.form0.submit();
	 }
	}
</script>
</head>
<body>

<form name="form0" action="useradmin.php" method="post" target="useradmin">
<input type="hidden" name="usertodel" value="" />
<input type="hidden" name="usertoreset" value="" />

<table style="width:550px"><tr><th></th><th align="left">E-Mail</th><th>Access level</th><th>Password</th><th>Action</th></tr>
<?php
 $query1="select user_id,email,level from tbl_user WHERE level != 256;";
 $result = mysql_query($query1) or die("Could not select $query1");
 while($row = mysql_fetch_row($result)){
  print "<tr><td></td><td>$row[1]</td><td>".$level[$row[2]]."</td><td align='center'><a href=\"#\" onClick=\"doReset($row[0])\">Reset</a></td><td align='center'><a href=\"#\" onclick=\"doDelete('$row[0]')\"> Delete </a></td></tr>\n";
 }
?>
<tr>
  <td></td>
  <td><input type="text" name="newemail" /></td>
  <td><select name="newlevel">
    <option value="0">Admin</option>
    <option value="1">Read/Write</option>
    <option value="2" selected>Read only</option>
    </select></td>
  <td><input type="password" name="newpassword" /></td>
  <td><input type="button" name="create" value="Create" onClick="doValidate()" /></td>
</tr>
</table>

</form>			

</body>
</html>
