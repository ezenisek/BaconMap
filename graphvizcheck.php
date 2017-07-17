<?php
  $configfile = 'includes/settings.php';
  require_once('includes/functions.php');
  $nowrite = false;
  if(isset($_GET['action']) && $_GET['action'] == 'edit')
    {
        $currentfile = file($configfile);
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
              include 'includes/error.php';
              exit();
            }
    }
  require_once('includes/settings.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>GraphViz Test Page</title>
</head>
<body>
<script src="javascript/wz_tooltip.js" type="text/javascript" language="JavaScript"></script>
<form name="graphviz" method="POST" action="graphvizcheck.php?action=edit" >
<table class="formtable">
<?php
if(!is_writable($configfile))
      {
        $nowrite = true;
      }
      $contents = file($configfile);
      $variables = array();
      $i = 0;
      $v = 0;
      while($i <= count($contents))
        {
          if(isset($contents[$i]) && substr($contents[$i],0,2) == '//')
            {
              $bits = explode('-',$contents[$i]);
              $variables[$v]['name'] = trim($bits[1]);
              $i++;
              $bits = explode('-',$contents[$i]);
              $variables[$v]['type'] = trim($bits[1]);
              $variables[$v]['description'] = '';
              if(($variables[$v]['type'] != 'text') && ($variables[$v]['type'] != 'password'))
                {
                  $i++;
                  $bits = explode('-',$contents[$i]);
                  $variables[$v]['options'] = trim($bits[1]);
                }
              $i++;
              while(substr($contents[$i],0,2) == '//')
              {
                 $bits = explode('-',$contents[$i]);
                 $variables[$v]['description'] .= $bits[1];
                 $i++;
              }
              $variables[$v]['definition'] = $contents[$i];
              $variables[$v]['key'] = $i;
              $v++;
            }
            $i++;
        }
        $variable = $variables[5];
        $regs = array();
        $description = scrub($variable['description']);
        echo '<tr class="formtr">';
        echo '<td class="formtd"  
                 onmouseover=\'Tip("'.$description.'")\'
                 onmouseout="UnTip()">';
        echo '<span class="blue">'.$variable['name'].': </span>';
        echo '</td><td class="formtd" style="text-align:right">';
        if(!ereg("'(.*)'",$variable['definition'],$regs))
          {
            echo 'Gah!  Variable not found.  Dying.';
           //exit();
          }
        echo '<input type="text" name="'.$variable['key'].'_n" value="'.$regs[1].'" />';
        echo '<input type="hidden" name="'.$variable['key'].'" value="'.$regs[1].'" />';       
        echo '</td>';
        echo '<td><input type="submit" name="submit" value="Update" /></td>';
        echo '</tr>';
?>
</table>
GraphViz Status: 
    <?php
      if(testgraphviz())
        {
          echo 'Your GraphViz installation is working correctly!';
          echo '<script>parent.document.getElementById(\'graphvizcheck\').height = 60;</script>';
        }
      else
        {
          echo '<span id="warning">GraphViz not found or not working - maps unavailable.</span>';
          echo '<br />This may be because GraphViz is not installed, because BaconMap couldn\'t find it in the designated path, ';
          echo 'or because the image folder in your BaconMap installation is not writable.';
          echo 'To remedy this, please ensure that GraphViz is installed and the executable location above is correct. ';
          echo 'BaconMap will still function without GraphViz, however maps and flowcharts will not be available.';
          echo '<script>parent.document.getElementById(\'graphvizcheck\').height = 130;</script>';
        }
    ?>	
</form>
 <?php if($nowrite) echo '<script>document.graphviz.submit.disabled = true;</script>'; ?>
</body>
</html>
