<?php
require_once('includes/settings.php');
require_once('includes/functions.php');
dbConnect($dbhost,$database,$username,$password);

$done = 0;
if(isset($_REQUEST['task']))  
  { 
    $task = trim($_REQUEST['task']);
  }
else
  $task = 'add';

if($task == 'edit')
  {
    $id = $_REQUEST['id'];
    $query = "SELECT * FROM tbl_group WHERE group_id = '$id'";
    $result = mysql_query($query);
    if(mysql_num_rows($result) == 0)
      {
        $error = "No groups with the specified ID could be found.  Please try again.";
        include 'includes/error.php';
        exit();
      }
    $members = array();
    while($row = mysql_fetch_assoc($result))
      {
        $type = $row['mtype'];
        $group_id = $row['group_id'];
        $name = $row['name'];
        $members[] = $type.'_'.$row['mid'];
      }
   $message = 'Editing Group: <span class="blue">'.$name;
   $header = 'Edit '.$name;
   $done = 0;
  }
elseif($task == 'doneadd')
  {
    $message = 'New Group <span class="blue">Added</span>';
    $done = 1;
  }
elseif($task == 'doneedit')
  {
    $message = 'Group <span class="blue">Edited</span>';
    $done = 1;
  }
elseif($task == 'donedisband')
  {
    $message = 'Group <span class="blue">Disbanded</span>';
    $done = 1;
  }
else
  {
    $message = 'New Group <span class="blue">Creation</span>';
    $header = 'Create a New Group';
    $done = 0;
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Bacon Map Groups</title>
	<script type="text/javascript">
	function startUp() {
   top.document.getElementById('mainwindowtitle').innerHTML = <?php echo "'$message'"; ?>;
   if(<?php echo $done; ?> == 1)
      parent.location.reload();
   formSelect();
   }
	
	function doAdd() {
    if(isEmpty(document.addgroup.name))  {
      alert("Please enter a Group Name");
      return false;
    }
    var c = 0;
    for(var i=0;i<document.addgroup.reslist.options.length;i++)
      {
        if(document.addgroup.reslist.options[i].selected)
        c++;
      }
    if(c<2)
      {
        alert("You must choose at least two resources to group");
        return false;
      }
  document.addgroup.submit();
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
	
	function oc(a)
  {
    var o = {};
    for(var i=0;i<a.length;i++)
    {
      o[a[i]]='';
    }
    return o;
  }
	
	var box = new Array();
	var server = new Array();
	var service = new Array();
	var application = new Array();
	var database = new Array();
	var device = new Array();
	var temp = new Array();
	
  <?php 
    if($task == 'edit') 
      {
        echo 'var current = new Array();';
        foreach($members as $member)
          {
            echo 'current.push(\''.$member.'\');';
          }
      }
	
	$types = array('box','device','server','service','application','database');
	foreach($types as $t)
	 {
	   $query = 'SELECT name, '.$t.'_id FROM tbl_'.$t;
	   $result = mysql_query($query);
	   while($row = mysql_fetch_row($result))
	     {
	       echo 'temp = [];';
	       echo 'temp.push(\''.$row[0].'\');';
	       echo 'temp.push(\''.$t.'_'.$row[1].'\');';
	       echo $t.'.push(temp);
         ';
       }
	 }	         
	 ?>
	 
	 
	 function formSelect() {
  switch(document.addgroup.type.value) {
    case 'box':
    document.getElementById('reslist').options.length = 0;
    for(i=0;i<box.length;i++) {
      document.getElementById('reslist').options[i] = new Option(box[i][0]+"  ",box[i][1]);
      <?php if($task == 'edit') { ?>
      if(box[i][1] in oc(current)) { document.getElementById('reslist').options[i].selected = true; }
      <?php } ?>
    }
    break;
    case 'server':
    document.getElementById('reslist').options.length = 0;
    for(i=0;i<server.length;i++) {
      document.getElementById('reslist').options[i] = new Option(server[i][0]+"  ",server[i][1]);
      <?php if($task == 'edit') { ?>
      if(server[i][1] in oc(current)) { document.getElementById('reslist').options[i].selected = true; }
      <?php } ?>
    }
    break;
    case 'service':
    document.getElementById('reslist').options.length = 0;
    for(i=0;i<service.length;i++) {
      document.getElementById('reslist').options[i] = new Option(service[i][0]+"  ",service[i][1]);
      <?php if($task == 'edit') { ?>
      if(service[i][1] in oc(current)) { document.getElementById('reslist').options[i].selected = true; }
      <?php } ?>
    }
    break;
    case 'device':
    document.getElementById('reslist').options.length = 0;
    for(i=0;i<device.length;i++) {
      document.getElementById('reslist').options[i] = new Option(device[i][0]+"  ",device[i][1]);
      <?php if($task == 'edit') { ?>
      if(device[i][1] in oc(current)) { document.getElementById('reslist').options[i].selected = true; }
      <?php } ?>
    }
    break;
    case 'application':
    document.getElementById('reslist').options.length = 0;
    for(i=0;i<application.length;i++) {
      document.getElementById('reslist').options[i] = new Option(application[i][0]+"  ",application[i][1]);
      <?php if($task == 'edit') { ?>
      if(application[i][1] in oc(current)) { document.getElementById('reslist').options[i].selected = true; }
      <?php } ?>
    }
    break;
    case 'database':
    document.getElementById('reslist').options.length = 0;
    for(i=0;i<database.length;i++) {
      document.getElementById('reslist').options[i] = new Option(database[i][0]+"  ",database[i][1]);
      <?php if($task == 'edit') { ?>
      if(database[i][1] in oc(current)) { document.getElementById('reslist').options[i].selected = true; }
      <?php } ?>
    }
    break;
    }
  }
</script>
</head>
<body onLoad="startUp()">
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<form name="addgroup" method="post" action="dogroup.php">
<input type="hidden" name="task" value="<?php echo $task; ?>" />
<?php if(isset($_REQUEST['id'])) { ?>
<input type="hidden" name="id" value="<?php echo $_REQUEST['id']; ?>" />
<?php } ?>
<div class="minorheader"><?php echo $header; ?></div>
  <table class="formtable">
    <tr class="formtr">
      <td class="formtd">Type of group:</td>
      <td class="formtd">
        <select name="type" onChange="formSelect()">
          <option value="box">Box &nbsp;</option>
          <option value="server">Server &nbsp;</option>
          <option value="application">Application &nbsp;</option>
          <option value="database">Database &nbsp;</option>
          <option value="service">Service &nbsp;</option>
          <option value="device">Device &nbsp;</option>
        </select>
      </td>
    </tr>
    <?php if($task == 'edit') { 
      $javopts = array('box','server','application','database','service','device');
       echo '<script type="text/javascript">';
       echo 'document.addgroup.type.disabled = true;';
       $key = $key = array_search($type,$javopts);
       echo 'document.addgroup.type.options['.$key.'].selected = true;';
       echo '</script>';
      } 
    ?>
    <tr class="formtr">
      <td class="formtd" valign="top">Resources to include in this group:</td>
      <td class="formtd">
        <select multiple name="reslist[]" id="reslist" size="5" onmouseover="Tip('Select multiples by holding CTRL and clicking.')"
        onmouseout="UnTip();">
        </select>
      </td>
    </tr>
    <tr class="formtr">
      <td class="formtd">Group Name:</td>
      <td class="formtd">
        <input type="text" name="name" size="30" value="<?php echo $name; ?>" />
      </td>
    </tr>
    <tr class="formtr">
      <td class="formtd" colspan="2">
      <br />
      <input type="button" name="addgroup" value="<?php echo ucwords($task); ?> Group" onClick="doAdd()" />
      </td>
    </tr>
    </table>
</form>
</body>
</html>
