<?php
  if(!isset($error))
    $error = "There has been an unknown error.";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>There has been an error</title>
</head>
<body>
<h2 class="header">There has been an <span class="blue">Error</span></h2>
<?php echo $error; ?>

</body>
</html>
