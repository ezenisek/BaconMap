<?php
/*
**THIS NOTICE MUST APPEAR ON ALL PAGES AND VERSIONS OF BACONMAP**

BaconMap - Resources Defined.
Copyright 2008 NMSU Research IT, New Mexico State University
Originally developed by Ed Zenisek, Denis Elkanov, and Abel Sanchez.

This file is the settings file for BaconMap.

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

/* ****  BaconMap settings file.  **** */
/*
This file holds all the configuration variables for the BaconMap.
Each variable is preceeded by a commment line in the form of:
Ex: //-Variable Name
Ex: //-Variable Type (text,select,checkbox,radio,textarea)
Ex: //-Variable Options
Ex: //-Description Line 1
Ex: //-Description Line 2

The variable options line should NOT be included if the variable is of type
text or PASSWORD.  Otherwise it should include a comma delimeted list of
options for the variable.  For example:

Type: select
Options: value1,label1,value2,label2,value3,label3

Type: checkbox
Options: label

Type: radio
Options: value1,label1,value2,label2

Type: textarea
Options: rows,cols

This is for the administration portion of BaconMap so the settings can be
edited from the interface.  Descriptions can use basic HTML.

Do NOT add stray comments to this file starting with //.  Always use
the block comment characters, or the interface may mistakenly pick up
your comments.  Any line which starts with // will be read by the interface,
which is why the example lines above are preceeded by Ex:

If you wish to set a variable in this file WITHOUT it being editiable in the
admin panel, use the block comments to describe the variable instead of the //

Currently this relies on the varaibles being within the single quote characters.

*/

//-Root Directory
//-text
//-The directory where BaconMap resides in relation to the root of your site.
//-For Example, if your site is www.example.com, and you've installed BaconMap
//-in <i>www.example.com/baconmap</i>, then the root directory would be </i>/baconmap</i>.
$rootdir = '/baconmap';

//-Database Host Address
//-password
//-The Host where the BaconMap database resides.  Usually localhost.
$dbhost = 'localhost';

//-Database Name
//-text
//-The name of the database you've created for BaconMap.
$database = '';

//-Database Username
//-password
//-The username that BaconMap will use to connect.
$username = '';

//-Database Password
//-password
//-The password for the Database Username.
$password = '';

//-GraphViz Executable
//-text
//-The GraphViz executable location and filename as installed on your system.
//-If the executable resides in your default path, this should just be 'dot'.
//-Example: /usr/bin/dot.
$graphvizpath = 'dot';

/*-Display Help
-checkbox
-Check to turn on help
-If help is turned on, links to the BaconMap documentation will be displayed
-in some places on the interface.  If you already know how BaconMap works,
-set this to off in order to reduce the clutter.*/
$displayhelp = '0';

//-Default Resource Tree Click Action
//-select
//-tell,Tell Me More,add,Add Child,delete,Delete,edit,Edit,map,Map It
//-This is the action that happens when you click on the name of a resource in
//-the Resource Tree menu on the left of most BaconMap Pages.
$defaultclick = 'tell';

//-Resource Map Orientation
//-select
//-leftright,Left to Right,topdown,Top Down,rightleft,Right to Left,bottomup,Bottom Up
//-This is the orientation of the resource maps that BaconMap generates.
//-Top down lists resources across the top, and generally makes wider maps.
//-Left to Right lists resources across the left side, and generally makes taller maps.
$maporientation = 'leftright';

//-Diagram Shape to Represent a Box
//-select
//-box,Square,polygon,Polygon,triangle,Triagle,diamond,Diamond,trapezium,Trapezium,parallelogram,Parallelogram,hexagon,Hexagon,octagon,Octagon,invtriangle,Inverted triangle,invtrapezium,Inverted trapezium,circle,Circle
//-This is the shape that will represent a box on any resource maps.
$boxshape = 'hexagon';
//-Diagram Shape to Represent a Device
//-select
//-box,Square,polygon,Polygon,triangle,Triagle,diamond,Diamond,trapezium,Trapezium,parallelogram,Parallelogram,hexagon,Hexagon,octagon,Octagon,invtriangle,Inverted triangle,invtrapezium,Inverted trapezium,circle,Circle
//-This is the shape that will represent a Device/Miscellaneous item on any resource maps.
$deviceshape='triangle';
//-Diagram Shape to Represent a Server
//-select
//-box,Square,polygon,Polygon,triangle,Triagle,diamond,Diamond,trapezium,Trapezium,parallelogram,Parallelogram,hexagon,Hexagon,octagon,Octagon,invtriangle,Inverted triangle,invtrapezium,Inverted trapezium,circle,Circle
//-This is the shape that will represent a server on any resource maps.
$servershape = 'trapezium';
//-Diagram Shape to Represent a Service
//-select
//-box,Square,polygon,Polygon,triangle,Triagle,diamond,Diamond,trapezium,Trapezium,parallelogram,Parallelogram,hexagon,Hexagon,octagon,Octagon,invtriangle,Inverted triangle,invtrapezium,Inverted trapezium,circle,Circle
//-This is the shape that will represent a service on any resource maps.
$serviceshape = 'parallelogram';
//-Diagram Shape to Represent an Application
//-select
//-box,Square,polygon,Polygon,triangle,Triagle,diamond,Diamond,trapezium,Trapezium,parallelogram,Parallelogram,hexagon,Hexagon,octagon,Octagon,invtriangle,Inverted triangle,invtrapezium,Inverted trapezium,circle,Circle
//-This is the shape that will represent an application on any resource maps.
$applicationshape = 'diamond';
//-Diagram Shape to Represent a Database
//-select
//-box,Square,polygon,Polygon,triangle,Triagle,diamond,Diamond,trapezium,Trapezium,parallelogram,Parallelogram,hexagon,Hexagon,octagon,Octagon,invtriangle,Inverted triangle,invtrapezium,Inverted trapezium,circle,Circle
//-This is the shape that will represent a database on any resource maps.
$databaseshape = 'octagon';
//-Diagram Shape to Represent a Virtual server
//-select
//-box,Square,polygon,Polygon,triangle,Triagle,diamond,Diamond,trapezium,Trapezium,parallelogram,Parallelogram,hexagon,Hexagon,octagon,Octagon,invtriangle,Inverted triangle,invtrapezium,Inverted trapezium,circle,Circle
//-This is the shape that will represent a virtual server on any resource maps.
$virtualservershape = 'invtrapezium';
?>
