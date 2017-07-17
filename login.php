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

$message = "";
$email = "";

//print_r($_REQUEST);print_r($_SESSION);

if(isset($_REQUEST['redir']) && $_REQUEST['redir'] == 'reset')
  {
    if(!isset($_POST['email']))
      $message = "Please specify a valid login address";
    else
      {
        $query = "SELECT user_id FROM tbl_user WHERE email = '".$_POST['email']."'";
        if(!$result = mysql_query($query))
          {
            $message = 'Could not reset password.  Please contact the admin';
            $userid = false;
          }
        elseif(!mysql_num_rows($result))
          {
            $message = $_POST['email'].' was not found in the system.';
            $userid = false;
          }
        else 
          $userid = mysql_result($result,0);
          
        if($userid)
          {
            if(!resetPassword($userid))
              {
                $message = 'Could not reset password.  Please contact the admin.';
              }
            else
              {
                $message = 'Password reset.  It has been mailed to '.$_POST['email'];
              }
          }
      }
    $redir = 'index.php';
  }
elseif(isset($_REQUEST['redir']) && $_REQUEST['redir'] == 'logout')
  {
    $redir = 'index.php';
    if(isset($_SESSION['id'])) 
      {
        $sessionid = $_SESSION['id'];
        $query = "DELETE FROM tbl_session WHERE sessionid = '$sessionid'";
        mysql_query($query);
      }
    $message = "You have been logged out";
    unset($_SESSION);
    unset($_POST);
    session_destroy();
  }
elseif(isset($_REQUEST['redir']) && $_REQUEST['redir'] != 'logout')
  {
    $redir = $_REQUEST['redir'];
  }
else
  $redir = 'index.php';
  
if(isset($_POST['email']) && $_REQUEST['redir'] != 'reset'){
 $email = $_POST['email'];
 $userpw = $_POST['userpw'];
 $query="select level,user_id from tbl_user where email=\"".mysql_real_escape_string($email)."\" and password=\"".mysql_real_escape_string(md5($email.$userpw))."\";";
 //echo $query;
 $result=mysql_query($query) or $message="error:".mysql_error();
 if($row = mysql_fetch_row($result)){ // create session, create php session and redirect to index
 //echo "Login verified.";
  mysql_query("delete from tbl_session where date<".date("Y-m-d G:i:s",time()-3600).";"); // delete all sessions idle for more than an hour
  mysql_query("delete from tbl_session where user_id = ".$row[1]);
  $level=$row[0];$user_id=$row[1];
  $sessionid=md5($email.$userpw.time().$level);
  $query="insert into tbl_session (user_id,level,sessionid) values ($user_id,$level,'$sessionid')";
  mysql_query($query);
  $_SESSION['baconid']=$sessionid;
  $_SESSION['user'] = $email;
  $_SESSION['userid'] = $user_id;
  $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
  $_SESSION['level'] = $level;
  //print_r($_SESSION);
  session_write_close();
  header("Location: $redir");
 }
 else{ 
  //print "no";
  $message.="<p>Wrong E-Mail or password<p>";
  $email = $_POST['email'];
 }
}
if(isset($_GET["error"])){ // redirected from another page with a message
 $message=$_GET["error"];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<link href="javascript/greybox/gb_styles.css" rel="stylesheet" type="text/css" />
	<title>Bacon Map Login</title>
	<script language="JavaScript" type="text/javascript">
  
  function breakout_of_frame()
  {
    if (top.location != location) {
      top.location.href = document.location.href ;
    }
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
	
	function forgotPassword() {
	 if(isEmpty(document.form0.email))
	   {
	     alert("Please enter your email address to reset your password");
	     return false;
	   }
	 if(confirm("Your password will be reset to a random string and will be emailed to you.\n\n Continue?"))
	   {
	     document.form0.redir.value = 'reset';
	     document.form0.submit();
	   }
	}
	
	function doLogin() {
	return true;
	}
</script>
</head>
<body onLoad="breakout_of_frame()">
	<div id="content">
		<div id="logo">
			<img src="images/baconmap2.jpg" />
		</div>
<center><h1>Please <span class="blue">Login</span> to BaconMap.</h1></center>		
		<div id="intro" align="right">
			<h1>Resources <span class="white">Defined</span>.</h1>
		</div>
<div id="right">
<h3><?php echo $message;?></h3>
<form name="form0" action="login.php" method="post">
<input type="hidden" name="redir" value="<?php echo $redir;?>" />
<table>
<tr><td>E-Mail</td><td><input type="text" name="email" value="<?php echo $email;?>" /></td></tr>
<tr><td>Password</td><td><input type="password" name="userpw" value="" /></td></tr>
<tr><td><a href="#" onClick="forgotPassword()">Forgot your password?</a></td><td style="text-align:right"><input type="submit" name="login" value="Login" onClick="return doLogin();" /></td></tr>
</table>
</form>			
</div>
<?php /*		<div id="footer">
				<p class="right"><a href="http://baconmap.nmsu.edu/about.php" target="_blank">About BaconMap and the Development Team</a></p>
				<p class="left"><a href="http://www.gnu.org/licenses/gpl.html" target="_blank">Developed under the GPL (General Public License)</a><br />
				&copy; Copyright 2008 <a href="http://www.research.nmsu.edu" target="_blank">NMSU Research IT</a>, <a href="http://www.nmsu.edu" target="_blank">New Mexico State University</a>, and <a href="http://www.baconmap.org" target="_blank">BaconMap.org</a></p>
		</div>
*/ ?>
</div>	
</body>
</html>
