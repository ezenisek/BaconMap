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
    
    This file is the welcome page for BaconMap.
    
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Bacon Map Welcome Page</title>
	<script type="text/javascript">
	parent.document.getElementById('mainwindowtitle').innerHTML = '<span class="blue">Welcome<\/span> to BaconMap!';
	</script>
</head>
<body>
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<strong>Welcome to the BaconMap <a href="http://en.wikipedia.org/wiki/Guinea_pig" target="_blank">Guinea</a> Release. (Beta)</strong><br /><br />
BaconMap is a program orignally conceived and written by the IT team at New 
Mexico State University's Research Department with the aim of simplifiying and 
mapping information technology resources.  Why the name BaconMap?  Here's a hint: 
<a href="http://en.wikipedia.org/wiki/Six_Degrees_of_Kevin_Bacon" title="A tribute 
to the Kevin Bacon Game" target="_blank">Six Degrees of Kevin Bacon</a>.<br /><br />
Almost everything that needs to be done in setting up your BaconMap can be done via the
tree menu to the left.  Adding, editing, and deleting nodes in your map can be done from there. 
More information is in the <a href="documentation/" title="BaconMap Documentation">BaconMap documentation</a>.<br /><br />
Thanks to the following people:
<ul>
  <li>Base CSS Template: Luka Cvrk (<a href="http://www.solucija.com" target="_blank">www.solucija.com</a>)</li>
  <li>Javascript Tree: Tobias Bender (<a href="http://www.phpxplorer.org" target="_blank">www.phpexplorer.org</a>)</li>
  <li>Javascript Tooltips: Walter Zorn (<a href="http://www.walterzorn.com" target="_blank">www.walterzorn.com</a>)</li>
  <li>Icons: Mark James (<a href="http://www.famfamfam.com" target="_blank">www.famfamfam.com</a>)</li>
  <li>GreyBox: Orangoo Labs (<a href="http://www.orangoo.com" target="_blank">www.orangoo.com</a>)</li>
  <li>DHTMLGrid: DHTMLX (<a href="http://www.dhtmlx.com" target="_blank">www.dhtmlx.com</a>)</li>
  <li>PDF Reports: FPDF (<a href="http://www.fpdf.org/" target="_blank">www.fpdf.org</a>)</li>
  <li>Graphing Reports: ezComponents (<a href="http://ez.no/ezcomponents" target="_blank">www.ez.no/ezcomponents</a>)</li>
</ul>
<br />
Thanks for using BaconMap!  - <span class="blue" onmouseover="TagToTip('team')" onmouseout="UnTip()">The BaconMap Team</span>
<br /><br />
<?php if($displayhelp) {  ?>
<ul id="articles">
				<li>Read the BaconMap Quick Start Guide<br /><a href="#">View Documentation</a></li>
				<li class="last">BaconMap FAQ<br /><a href="#">View Documentation</a></li>
</ul>	
<?php } ?>
<div id="team">
<table>
<tr><td style="padding: 0 5px 1px 1px"><b>Abel Sanchez:</b></td><td>Project Manager</td></tr>
<tr><td><b>Ed Zenisek:</b></td><td>Lead Developer</td></tr>
<tr><td><b>Paul Klassen:</b></td><td>Developer</td></tr>
<tr><td><b>Scott Smith:</b></td><td>Developer</td></tr>
</table>
</div>
</body>
</html>
