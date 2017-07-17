<?php
  require_once('../includes/settings.php');
  require_once('../includes/functions.php');
  
  /*  This file updates all of the various lists BaconMap uses for dropdowns.
      We want to ensure that all the entries in the database are consistent, but
      we also want the users to be able to customize all the values.  What we do 
      is create a number of text files in the includes directory, and each text
      file contains the contents of the drop down lists we use.  Editing these 
      files also edits the contents of the drop downs, allowing the user to 
      modify them, but still keeping a measure of consistency for database
      queries.
  */
  
  
  if(!isset($_POST['filepath']))
    {
      $error = "Cannot find the filepath specified.  Please try again.";
      include('../includes/error.php');
      exit();
    }
  $filepath = $_POST['filepath'];
  $filename = basename($filepath);
  $writeme = '';
  $status = '';
  if(isset($_POST['begin']))
    {
      $newlines = explode("\n",$_POST['begin']);
      foreach($newlines as $line);
        {
          $line = trim($line);
          if(!empty($line))
          $writeme .= $line."\n";
        }
      $newlines = explode("\n",$_POST['filelist']);
        foreach($newlines as $line)
        {
          $line = trim($line);
          if(!empty($line))
          $writeme .= $line."\n";
        }
     $fh = fopen($filepath,'w+') or die("Cannot open $filepath");
        if(!fwrite($fh,$writeme))
          $status = '- Could not write to'.$filepath;
        else
          $status = '- File Updated';
    }
    
  switch($filename) {
    case 'roles.txt':
    $title = 'Resource Roles';
    break;
    case 'poctypes.txt':
    $title = 'Point of Contact Types';
    break;
    case 'dbtypes.txt':
    $title = 'Database Types';
    break;
    case 'ostypes.txt':
    $title = 'Operating System Types';
    break;
  }
  
  $nowrite = false;
  $linecount = 0;
  $base = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="../css/main.css" type="text/css" />
	<title>Resource Details for <?php echo $header; ?></title>
<script type="text/javascript">
function doUpdate() {
document.updatelist.submit();
}
</script>
</head>
<body>
<div id="boxed">
<div class="minorheader"><?php echo $title.' '.$status; ?></div> 
<center>
<?php
if(!is_writable($filepath))
      {
        echo '<div id="warning"><strong>This list file is not writable.</strong><br />   
        The file <i>../includes/'.$filename.'</i> must be writable to edit this list.</div>';
        $nowrite = true;
        $base = 30;
      }
?>
<div>Entries will be listed in the dropdown menu in the order they appear here.</div>
<form name="updatelist" method="post" action="updatelist.php">
<textarea cols="45" rows="5" name="filelist" id="area">
<?php
  $thisfile = file($filepath);
  $beginfile = '';
  foreach($thisfile as $line)
    {
      $line = trim($line);       
        if(stristr($line, '#'))
          $beginfile .= $line . "\n";  
        else
          {            
          echo $line . "\n";
          $linecount++;
          }            
    }
?></textarea><br />
<input type="hidden" name="filepath" value="<?php echo $filepath; ?>" />
<input type="hidden" name="begin" value="<?php echo $beginfile; ?>" />
<input type="button" name="doit" value="Update List" onClick="doUpdate()" />
<script type="text/javascript">
  <?php
    $base = $base + 150;
    if($linecount >= 5 && $linecount <= 10)
      {
        echo " document.getElementById('area').rows = $linecount;";
      }  
    elseif($linecount > 10)
      {
        echo " document.getElementById('area').rows = 10;";
        $linecount = 10;
      }
    $height = $base + ($linecount * 15);
    echo " parent.document.getElementById('$filename').height = $height;";
    
    if($nowrite)
      echo " document.updatelist.doit.disabled = true;";  
  ?>
  
</script>
</form>
</center>
</div>
</body>
</html>
