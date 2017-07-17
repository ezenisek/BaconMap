<?php
  if(!isset($message))
    $message = $_GET['message'];
  if(empty($message))
    $message = "I'm not really sure what the operation was though... :(";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="../css/main.css" type="text/css" />
	<title>Operation Successful</title>
</head>
<body>
<h2 class="header">The operation was a <span class="blue">Success</span>!</h2>
<?php echo $message; ?>

</body>
</html>
