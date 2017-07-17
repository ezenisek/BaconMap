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
  authorize(2,"index.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<link href="javascript/greybox/gb_styles.css" rel="stylesheet" type="text/css" />
	<title>Bacon Map</title>
	<script type="text/javascript">
	
	var shownode = (shownode == null) ? 0 : shownode;
	
	function evalOrder() {
	   for (var i=0; i < document.sortorder.what.length; i++)
      {
        if (document.sortorder.what[i].checked)
          {
          var what = document.sortorder.what[i].value;
          }
      }
     for (var i=0; i < document.sortorder.how.length; i++)
      {
        if (document.sortorder.how[i].checked)
          {
          var how = document.sortorder.how[i].value;
          }
      } 
	 tree.location = "<?php echo $rootdir; ?>/jstreeview.php?how="+how+"&what="+what;
	 }
	 
	function reloadTree() {
	 evalOrder();
	 if(shownode != 0)
	   {
	     var title = '';
	     switch(tree.what) {
        case 'box':
          title = 'Boxes';
          break;
        case 'server':
          title = 'Servers';
          break;
        case 'device':
          title = 'Devices';
          break;
        case 'application':
          title = 'Applications';
          break;
        case 'database':
          title = 'Databases';
          break;
        case 'service':
          title = 'Services';
          break;
        }
    
       switch(tree.how) {
        case 'child':
          title = title + ' and Children';
          break;
        case 'parent':
          title = title + ' and Parents';
          break;
        }
          
	     shownode = title + shownode;
	     self.setTimeout("tree.selectNode(shownode)",1000);
	   }
	}
	
	</script>
	<script language="JavaScript" type="text/javascript">
<!--
  var GB_ROOT_DIR = "javascript/greybox/";
	function GB_myShow(caption, url, /* optional */ height, width, callback_fn) {
	 var options = {
	 caption: caption,
	 height: height,
	 width: width,
	 fullscreen: false,
	 overlay_click_close: true,
	 show_loading: true,
	 center_win: true,
	 callback_fn: callback_fn
	 }
	var win = new GB_Window(options);
	//return win.show(url);
  win.show(url);
  }
  </script>
  <script type="text/javascript" src="javascript/greybox/AJS.js"></script>
  <script type="text/javascript" src="javascript/greybox/AJS_fx.js"></script>
  <script type="text/javascript" src="javascript/greybox/gb_scripts.js"></script>
</head>
<body>
	<div id="content">
		<div id="logo">
			<img src="images/baconmap2.jpg" />
		</div>
		<ul id="menu">
			<li><a href="welcome.php" target="main">Main</a></li>
			<?php if($_SESSION['level'] < 2) { ?>
			<li><a href="add.php?new=1" target="main">Add Resource</a></li>
			<?php } ?>
			<li><a href="picture.php" target="_blank">Big Picture</a></li>
			<li><a href="group.php" target="main">Resource Groups</a></li>
			<li><a href="contacts.php" target="main">Contacts</a></li>
			<li><a href="whatif.php" target="main">What If?</a></li>
			<li><a href="reports.php" target="main">Reports</a></li>
			<?php if($_SESSION['level'] < 1) { ?>
			<li><a href="admin/" target="_self">Admin</a></li>
			<?php } ?>
		</ul>
		<div id="intro" align="right">
			<h1>Resources <span class="white">Defined</span>.</h1>
		</div>	
		<div id="left">	
			<center><h2>Choose <span class="blue">What</span> and <span class="blue">How</span> to Sort</h2></center>
      <center>
      <form name="sortorder" method="post" action="self">
      <table width="284">
       <tr>
        <td align="center"><input type="radio" name="how" value="child" checked onClick="evalOrder()"/> Child View</td>
        <td><input type="radio" name="how" value="parent" onClick="evalOrder()"/> Parent View</td>
       </tr>
      </table>
      <table width="284" class="info">
       <tr>
        <td><input type="radio" name="what" value="box" checked onClick="evalOrder()"/> Box</td>
        <td><input type="radio" name="what" value="service" onClick="evalOrder()"/> Service</td>
        <td><input type="radio" name="what" value="database" onClick="evalOrder()"/> Database</td>
       </tr>
       <tr>
        <td><input type="radio" name="what" value="server" onClick="evalOrder()"/> Server</td>
        <td><input type="radio" name="what" value="application" onClick="evalOrder()"/> Application</td>
        <td><input type="radio" name="what" value="device" onClick="evalOrder()"/> Device</td>
       </tr>
      </table>
      </form>
      <iframe src="jstreeview.php" width="280" height="350" frameborder="0" name="tree" id="tree">
      I'm sorry, your browser doesn't support iFrames</iframe>
      	</center>
      <div id="treecontrol">
        <span id="expandtree" onClick="tree.expandAll()"><a href="#">Expand All</a></span> 
        <span id="collapsetree" onClick="tree.closeAll()"><a href="#">Collapse All</a></span>
      </div>
      <div id="timer"></div>
      <?php if($displayhelp) { ?>
			<ul id="articles">
				<li>Learn How Sorting Works<br /><a href="#">View Documentation</a></li>
				<li class="last">Learn How Relationships Work<br /><a href="#">View Documentation</a></li>
			</ul>
		  <?php } else { ?>
		  <ul id="articles">
				<li>&nbsp;<br/>&nbsp;</li>
				<li class="last">&nbsp;<br/>&nbsp;</li>
			</ul>
      <?php } ?>
		</div>
	  <div id="right">
			<center><h2><span id="mainwindowtitle"></span></h2></center> 
			<iframe <?php if(isset($_GET['frame'])) echo 'src="'.urldecode($_GET['frame']).'"'; else echo 'src="welcome.php"'; ?> width="580" height="520" frameborder="0" name="main" id="main">
      I'm sorry, your browser doesn't support iFrames</iframe>
		</div>
		<div id="footer">
				<p class="right"><a href="http://baconmap.nmsu.edu/about.php" target="_blank">About BaconMap and the Development Team</a><br />
        <a href="login.php?redir=logout">Logout (<?php echo $_SESSION['user'];?>)</a> - <a href="changepassword.php" target="main">Change Password</a></p>
				<p class="left"><a href="http://www.gnu.org/licenses/gpl.html" target="_blank">Developed under the GPL (General Public License)</a><br />
				&copy; Copyright 2008 <a href="http://www.research.nmsu.edu" target="_blank">NMSU Research IT</a>, <a href="http://www.nmsu.edu" target="_blank">New Mexico State University</a>, and <a href="http://www.baconmap.org" target="_blank">BaconMap.org</a></p>
    </div>
</div>	
</body>
</html>
