<?php
require_once('includes/settings.php');
require_once('includes/functions.php');
dbConnect($dbhost,$database,$username,$password);

$header = '';
$done = 0;
if(isset($_REQUEST['task']))  
  { 
    $task = $_REQUEST['task'];
  }
else
  $task = 'add';


if($task == 'edit')
  {
    $id = $_REQUEST['id'];
    $query = "SELECT * FROM tbl_poc WHERE poc_id = '$id'";
    $result = mysql_query($query);
    if(mysql_num_rows($result) == 0)
      {
        $error = "No contacts with the specified ID could be found.  Please try again.";
        include 'includes/error.php';
        exit();
      }
   $row = mysql_fetch_assoc($result);
   $message = 'Editing Contact: <span class="blue">'.$row['first'].' '.$row['last'];
   $header = 'Edit '.$row['first'].' '.$row['last'];
   $done = 0;
  }
elseif($task == 'doneadd')
  {
    $message = 'New Contact <span class="blue">Added</span>';
    $done = 1;
  }
elseif($task == 'doneedit')
  {
    $message = 'Contact <span class="blue">Edited</span>';
    $done = 1;
  }
elseif($task == 'donedelete')
  {
    $message = 'Contact <span class="blue">Deleted</span>';
    $done = 1;
  }
else
  {
    $message = 'New Contact <span class="blue">Addition</span>';
    $header = 'Add New Contact';
    $done = 0;
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Bacon Map Contacts</title>
	<script type="text/javascript">
	function startUp() {
   top.document.getElementById('mainwindowtitle').innerHTML = <?php echo "'$message'"; ?>;
   if(<?php echo $done; ?> == 1)
      parent.location = 'contacts.php';
   }
	
	function doContact() {
    if(isEmpty(document.contact.first))  {
      alert("Please enter a First Name");
      return false;
    }
    if(isEmpty(document.contact.last))  {
      alert("Please enter a Last Name");
      return false;
    }
  document.contact.submit();
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
	
</script>
</head>
<body onLoad="startUp()">
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<form name="contact" method="post" action="docontact.php">
<input type="hidden" name="task" value="<?php echo $task; ?>" />
<?php if(isset($_REQUEST['id'])) { ?>
<input type="hidden" name="id" value="<?php echo $_REQUEST['id']; ?>" />
<?php } ?>
<div class="minorheader"><?php echo $header; ?></div>
  <table class="formtable">
    <tr class="formtr">
      <td class="formtd">First Name:</td>
      <td class="formtd"><input type="text" name="first" size="25" maxlength="50" value="<?php if(isset($row['first'])) echo $row['first']; ?>" /></td>
      <td class="formtd">Middle:</td>
      <td class="formtd"><input type="text" name="middle" size="5" maxlength="5" value="<?php if(isset($row['middle'])) echo $row['middle']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">Last Name:</td>
      <td class="formtd"><input type="text" name="last" size="25" maxlength="50" value="<?php if(isset($row['last'])) echo $row['last']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">Phone:</td>
      <td class="formtd"><input type="text" name="phone" size="15" maxlength="20" value="<?php if(isset($row['phone'])) echo $row['phone']; ?>" /></td>
      <td class="formtd">E-Mail:</td>
      <td class="formtd"><input type="text" name="email" size="25" maxlength="50" value="<?php if(isset($row['email'])) echo $row['email']; ?>" /></td>
    </tr>
    <tr class="formtr">
      <td class="formtd">POC Type</td>
       <td class="formtd"><select name="poc_type">
        <?php
         // Here we pull all of roles out of the file roles.txt and put them in the drop down box.
         // If the role is already in a session variable, we check to see if it matches the role
         // we're currently putting in the box.  If it does, we make it selected to reflect the variable.
         $types = array();
         $types = file('includes/poctypes.txt');
         foreach($types as $thistype)
          {
            $thistype = trim($thistype);
            if(stristr($thistype, '#') === false)
            {
              if($thistype == $row['poc_type'])
                echo '<option selected value="'.$thistype.'"> '.$thistype.' &nbsp;</option>';
              else
                echo '<option value="'.$thistype.'"> '.$thistype.' &nbsp;</option>';     
            }
          }
        ?>
      </select>  
      </td>
    </tr>
  </table>
  <div>    
  POC Description: <br />
  <textarea name="description" rows="3" cols="60"><?php
  if(!isset($row['description']))
    echo 'Please enter a description.';
  else
    echo $row['description'];
  ?>
  </textarea>
  <br />
  <center><input type="button" name="docontact" value="<?php echo ucwords($task).' Contact'; ?>" onClick="doContact()" /></center>
</form>
</body>
</html>
