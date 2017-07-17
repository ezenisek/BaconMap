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
    
    This file is the documents display file for BaconMap.
    
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
  dbConnect($dbhost,$database,$username,$password);
  
  // Now we check that the user is logged in.  If not, we forward to the login page.
  authorize(2,"index.php?frame=".urlencode('documents.php'));
  
  if(!isset($_REQUEST['id']))
    {
      $error = "I'm sorry, the page you requested could not be displayed because
      a resource ID could not be found.";
      require('includes/error.php');
      exit();
    }
  
  $id = $_REQUEST['id'];
  $idbits = explode('_',$id);
  $type = $idbits[0];
  $id = $idbits[1];
  $pic = getPic($type);
  
  if($type == 'group')
    {
      header("Location: groupdetails.php?id=$id");
    }    
  
  $query = 'SELECT * FROM tbl_'.$type.' WHERE '.$type."_id = '$id'";
  $result = mysql_query($query);
  $resourcerow = mysql_fetch_assoc($result);
  if(mysql_num_rows($result) == 0)
    {
      $error = "I'm sorry, the resource ID that was requested could not be found.";
      require('includes/error.php');
      exit();
    }
  
  $query = "SELECT * FROM tbl_upload WHERE objtype = '$type' and objid = '$id'";
  $docresult = mysql_query($query);
  
  $header = ucwords($type).': '.$resourcerow['name'];  
    
 if(isset($_POST["doc_download"])){ // download
 $file=$_POST["doc_download"];
 $query = "SELECT name,encrypted,flen FROM tbl_upload WHERE id=$file;";
 $result = mysql_query($query) or die("Can`t select: $query");
 if($row = mysql_fetch_row($result)){
  header('Content-type: application/octet-stream');
  header('Content-Disposition: attachment; filename="'.$row[0].'"');
  if($row[1]==1){ // encrypted
   $key = $_POST["pass$file"];
   //echo "Pre Key:".$key; 
   $key = md5("$key"); // as it is stored in the database
   //echo "<br />Post Key:".$key;
   $iv = '12345678';
   $cipher = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
   $h=fopen("uploads/$file","r");
   $len=mcrypt_enc_get_block_size($cipher);
   mcrypt_generic_init($cipher, $key, $iv);
   $totallength=$row[2];
   $clength=0;
   while($text=fread($h,$len)){
    $decrypted = mdecrypt_generic($cipher,$text);
    if($totallength-$clength<$len){$decrypted=substr($decrypted,0,$totallength-$clength);}
    $clength+=$len;
    echo $decrypted;
   }
   mcrypt_generic_deinit($cipher);
   fclose($h);
  }
  else{
   $h=fopen("uploads/$file","r");
   while($text=fread($h,1024)){
    echo $text;
   }
   fclose($h);
  }
 }
 exit(0);
}  
    
?>  
  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<title>Documentation for <?php echo $header; ?></title>
</head>
<body>
<form name="docview" action="documents.php<?php echo '?id='.$type.'_'.$id;?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="doc_download" value="">
<div id="boxed">
  <h2 class="header"><img src="tree_images/<?php echo $pic; ?>.png" alt="tree image"/> <span class="blue">Documentation</span> for <?php echo $header; ?></h2>
  <center>
  <table class="details">
    <tr class="detailstr">
      <td class="detailstd" width="20%"><strong>Name:</strong></td>
      <td class="detailstd"><?php echo $resourcerow['name']; ?></td>
    </tr>
    <tr class="detailstr">
      <td class="detailstd"><strong>Description:</strong></td>
      <td class="detailstd"><?php echo $resourcerow['description']; ?></td>
    </tr>
  </table>
  <table class="details">
  <tr>
    <td>&nbsp;</td>
    <td><b>Document Filename</b></td>
    <td>Encryption</td>
  </tr>  
  <?php
    while($docrow = mysql_fetch_row($docresult))
      {
        echo '<tr>';
        echo '<td><img src="tree_images/book_open.png" /></td>';
        echo '<td>'.$docrow[0].'</td>';
        echo '<td><div id="enc'.$docrow[4].'">';
          if($docrow[1]) echo 'Yes';
          else echo 'No';
        echo "</div><div style=\"display:none\" id=\"pwd$docrow[4]\"><input type=\"password\" name=\"pass$docrow[4]\" onkeypress=\"return event.keyCode!=13\"></div></td>\n";
        if($docrow[1]){ // encrypted
        echo "<td><div id=\"getpwd$docrow[4]\"><A HREF='' onclick='explode(\"$docrow[4]\");return false;'>Password</A></div><div style=\"display:none\" id=\"download$docrow[4]\">
        <A HREF='' onclick='document.docview.doc_download.value=$docrow[4];document.docview.submit();document.docview.target=\"_blank\";return false;'>Download</A></div></td>\n";
        }
        else
         echo "<td><A HREF='' onclick='document.docview.doc_download.value=$docrow[4];document.docview.submit();document.docview.target=\"_blank\";return false;'>Download</A></td>\n";
        echo '</tr>';
      }
 
  ?>  
  </table>
</center>
</div>	
</form>
</body>
<script type="text/javascript">
	var current_row = "";
	function explode(rowno){
 if(current_row!=""){implode(current_row);}
 imp('getpwd'+rowno);
 exp('download'+rowno);
 imp('enc'+rowno);
 exp('pwd'+rowno);
 current_row=rowno;
}
function implode(rowno){
 exp('getpwd'+rowno);
 imp('download'+rowno);
 exp('enc'+rowno);
 imp('pwd'+rowno);
}
function exp(element){
 window.document.getElementById(element).style.display='';
}
function imp(element){
 window.document.getElementById(element).style.display='none';
}
</script>
</html>
