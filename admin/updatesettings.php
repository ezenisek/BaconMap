<?php
$configfile = '../includes/settings.php';
  require_once($configfile);
  require_once('../includes/functions.php');
  
  if(!isset($_POST['doit']))
  {
    $error = "Cannot find the filepath specified.  Please try again.";
      include('../includes/error.php');
      exit();
  }
  
  if(!is_writable($configfile))
      {
        $error = '<div id="warning"><strong>The configuration file is not writable!</strong>  
        No changes can be made. 
        Please ensure the file <i>../includes/settings.php</i> exists and is writable by PHP.</div><br />';
        include('../includes/error.php');
        exit();
      }
  
  $currentfile = file($configfile);
  //print_r($_POST);
  //print_r($currentfile);
  foreach($_POST as $name => $value)
    {
      // The n designates the new value.  We want to go through and check the 
      // OLD values, and then check them against the new equivalent. 
      if(substr($name,0,-1) != 'n')
        {
          $n = $name.'_n';
          $old = $value;
          $new = $_POST[$n];
          if($old != $new)
            {
              $newstring = ereg_replace("'.*'","'$new'",$currentfile[$name]);
              $currentfile[$name] = $newstring;
            }
        }
    }
   
   foreach($currentfile as $line)
    {
      $writeme .= trim($line)."\n";
    }
     
    $fh = fopen($configfile,'w+') or die("Cannot open $configfile");
        if(!fwrite($fh,trim($writeme)))
          {
           $error = 'Could not write to'.$filepath;
           include '../includes/error.php';
           exit();
          }
        else
          header("Location: index.php?update=success");

   //echo '<br /><br />';
  //print_r($currentfile);
?>
