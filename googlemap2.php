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

    This file is the detials display file for BaconMap.

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

/*  This is the form that will display the image without zoom 
*/
require_once 'includes/functions.php';

$image=$_POST["image"];
$ext=".gif";
$ext2=".map";

?>
<html>
<body>
<form name="form0" method="post" action="googlemap1.php">
<input type="hidden" name="width" value="<?php echo $_POST['width'];?>">
<input type="hidden" name="height" value="<?php echo $_POST['height'];?>">
<input type="hidden" name="image" value="<?php echo $image;?>">
<input type="hidden" name="startx" value="<?php echo $_POST['startx'];?>">
<input type="hidden" name="starty" value="<?php echo $_POST['starty'];?>">
<input type="hidden" name="ratio" value="<?php echo $_POST['ratio'];?>">
<table  width=100%><tr>
<td><h3><?php echo $_SESSION["anchor"]; ?></h3>
<a href="picture.php">Big picture</a>
<a href="" onclick="document.form0.submit();return false;">Back</a>
<tr><td colspan=13><image onclick="handle(event);" src="image/<?php echo "$image$ext"; ?>" USEMAP="#G"></td></tr></table>
</form>
<?php
 print file_get_contents("image/$image$ext2");
?>
</body>
</html>
