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
  require_once('includes/settings.php');
  require_once('includes/functions.php');
 $level[0]="Admin";$level[1]="Read/Write";$level[2]="Read only";
  
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
    authorize(0,"useradmin.php");

$message="";
if($_POST['usertodel'] != ""){ // delete a user
 $query="delete from tbl_user where user_id=".$_POST['usertodel'].";";
 mysql_query($query) or $message="Could not delete: $query<BR>error:".mysql_error();
}
else{
 if($_POST['create']=="Create" and $_POST['newemail']!=""  and $_POST['newpassword']!=""){ # create a new user
  $query="insert into tbl_user (email,level,password) values('".mysql_real_escape_string($_POST['newemail'])."',".$_POST['newlevel'].",'".md5($_POST['newemail'].$_POST['newpassword'])."');";
  mysql_query($query) or $message="Could not insert: $query<BR>error:".mysql_error();
 }
}
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<link href="javascript/greybox/gb_styles.css" rel="stylesheet" type="text/css" />
	<title>Bacon Map User administration</title>
</head>
<body>
	<div id="content">
		<div id="logo">
			<img src="images/baconmap2.jpg" />
		</div><H1>User Administration</H1>
<?php/*			<ul id="menu">
			<li><a href="welcome.php" target="main">Main</a></li>
			<li><a href="add.php?new=1" target="main">Add Resource</a></li>
			<li><a href="picture.php" target="_blank">Big Picture</a></li>
			<li><a href="group.php" target="main">Resource Groups</a></li>
			<li><a href="contacts.php" target="main">Contacts</a></li>
			<li><a href="whatif.php" target="main">What If?</a></li>
			<li><a href="reports.php" target="main">Reports</a></li>
			<li><a href="admin/" target="_self">Admin</a></li>
		</ul>
*/?>		<div id="intro" align="right">
			<h1>Resources <span class="white">Defined</span>.</h1>
		</div>
<div id="right">
<?php echo $message; ?><BR>
<form name="form0" action="useradmin.php" method="POST">
<input type="hidden" name="usertodel" value="">
<table style="width:100%"><tr><th></th><th>E-mail/id</th><th>Access level</th><th>Password</th><th>Action</th></tr>
<?php
 $query1="select user_id,email,level from tbl_user;";
 $result = mysql_query($query1) or die("Could not select $query1");
 while($row = mysql_fetch_row($result)){
  print "<tr><td>$row[0]</td><td>$row[1]</td><td>".$level[$row[2]]."</td><td></td><td><a href=\"\" onclick=\"document.form0.usertodel.value='$row[0]';document.form0.submit();return false;\"> Delete </td></tr>\n";
 }
?>
<tr><td></td><td><input type="text" name="newemail"></td><td><select name="newlevel"><option value="0">Admin</option><option value="1">Read/Write</option><option value="2" selected>Read only</option></select></td><td><input type="password" name="newpassword"></td><td><input type="submit" name="create" value="Create"></td></tr>
</table>
</form>			
</div>
<?php/*		<div id="footer">
				<p class="right"><a href="http://baconmap.nmsu.edu/about.php" target="_blank">About BaconMap and the Development Team</a></p>
				<p class="left"><a href="http://www.gnu.org/licenses/gpl.html" target="_blank">Developed under the GPL (General Public License)</a><br />
				&copy; Copyright 2008 <a href="http://www.research.nmsu.edu" target="_blank">NMSU Research IT</a>, <a href="http://www.nmsu.edu" target="_blank">New Mexico State University</a>, and <a href="http://www.baconmap.org" target="_blank">BaconMap.org</a></p>
		</div>
*/?>
</div>	
</body>
</html>
