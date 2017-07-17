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
    
    This file is the upload / download files for BaconMap.
    
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


$rowheight = 25;
$height=50;
$type=$_GET["type"];$id=$_GET["id"];
// Processing upload
if(isset($_POST["doc_upload"]) and isset($_FILES['uploadthis']['tmp_name']))
  {
   $encrypted=0; 
   if(isset($_POST["encrypted"]) && $_POST['encrypted'] != "") 
      $encrypted=1;
      
   // file name/ID
   $random=rand();
   while(is_readable("uploads/$random")){$random++;}
   if(move_uploaded_file($_FILES['uploadthis']['tmp_name'],"uploads/$random"))
     { // no file
      $query = "insert into tbl_upload (id,name,encrypted,objtype,objid,flen) values ($random,'".$_FILES['uploadthis']['name']."',$encrypted,'$type',$id,".filesize("uploads/$random").");";
      mysql_query($query) or die("Can`t insert: $query");
     }
   // Encrypt
   if($encrypted)
    { // encrypt the file
      $file= "uploads/$random";
      // Password
      $query = "SELECT password FROM tbl_user WHERE level = 256;";
      $result = mysql_query($query) or die("Can`t select:$query");
      if($row = mysql_fetch_row($result))
        {
          $key = $row[0];
        }
      $iv  = '12345678';
      $cipher = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
     
      $h=fopen($file,"r");
      $w=fopen("$file.encrypted","w");
      $len=mcrypt_enc_get_block_size($cipher);
      mcrypt_generic_init($cipher, $key, $iv);
      while($text=fread($h,$len)){
       $encryptedbuffer = mcrypt_generic($cipher,$text);
       fwrite($w,$encryptedbuffer);
      }
      mcrypt_generic_deinit($cipher);
      fclose($h);
      fclose($w);
      rename("$file.encrypted",$file);
   }
}
if(isset($_POST["doc_delete"]) && $_POST['doc_delete'] != ''){ // delete
 $file=$_POST["doc_delete"];
 $query = "delete from tbl_upload where id=$file;";
 mysql_query($query) or die("Can`t delete: $query");
 unlink("uploads/$file");
}
if(isset($_POST["doc_download"]) && $_POST['doc_download'] != ''){ // download
 $file=$_POST["doc_download"];
 $query = "SELECT name,encrypted,flen FROM tbl_upload WHERE id=$file;";
 $result = mysql_query($query) or die("Can`t select:$query");
 if($row = mysql_fetch_row($result)){
 header('Content-type: application/octet-stream');
 header("Content-Disposition: attachment; filename=$row[0]");
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
<title>Bacon Map Upload</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="javascript/dhtmlgrid/dhtmlxgrid.css" />
  <script type="text/javascript" src="javascript/dhtmlgrid/dhtmlxcommon.js"></script>
  <script type="text/javascript" src="javascript/dhtmlgrid/dhtmlxgrid.js"></script>
</head>
<body onLoad="setHeight()">
<form name="upload" action="uploads.php<?php echo "?type=$type&amp;id=$id";?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="doc_download" value="" />
<input type="hidden" name="doc_upload" value="" />
<input type="hidden" name="doc_delete" value="" />
<table>
  <tr>
    <th align="left">Name</th><th align="left">Encryption</th><th colspan=3></th>
  </tr>
  <?php 
  $query = "SELECT name,id,encrypted FROM tbl_upload WHERE objtype='$type' and objid = $id;";
  $result = mysql_query($query) or die("Can`t select:$query");
  while($row = mysql_fetch_row($result)){
  // print "<tr><td><A HREF='' onclick='document.upload.doc_download.value=$row[1];document.upload.submit();return false'>$row[0]</A></td>\n";
  // onclick='document.upload.target=\"_blank\";
   print "<tr><td>";
  if($row[2])
   echo $row[0];
  else
   print "<A HREF='' onclick='document.upload.doc_download.value=".$row[1].";document.upload.submit();return false;'>$row[0]</a>";
   print "</td><td><div id=\"enc$row[1]\">";
   if($row[2])
    print "Yes";
   else
    print "No";
   print "</div><div style=\"display:none\" id=\"pwd$row[1]\"><input type=\"password\" name=\"pass$row[1]\" /></div></td>\n";
   if($row[2]==1){ // encrypted
    print "<td><div id=\"getpwd$row[1]\"><A HREF='' onclick='explode(\"$row[1]\");return false;'>Password</A></div><div style=\"display:none\" id=\"download$row[1]\">
    <A HREF='' onclick='document.upload.doc_download.value=$row[1];document.upload.submit();document.upload.target=\"_blank\";return false;'>Download</A></div></td>\n";
   }
   else{
    print "<td><A HREF='' onclick='document.upload.doc_download.value=".$row[1].";document.upload.submit();document.upload.target=\"_blank\";return false;'>Download</A></td>\n";
   }
   print "<td width=\"3%\"></td><td><A HREF='' onclick='verifyDelete(\"".$row[1]."\");'>Delete</A></td></tr>\n\n";
   $height+=$rowheight;
  }
  ?>
  <tr><td>&nbsp;</td></tr>
  <tr>
    <td colspan="2" align="center">Upload a new file: <input type="file" name="uploadthis" size="20" /></td>
    <td>
      <table>
        <tr>
          <td align="center"> Encrypted: <input type="checkbox" name="encrypted" value="1" /></td>
          <td><td colspan=3 align="center"><input type="button" value="Upload" onclick='document.upload.doc_upload.value=1;document.upload.submit();return false;' /></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<script type="text/javascript">
 var current_row="";
        function setHeight() {
        var newheight = 25 + <?php echo $height; ?>;
        parent.document.getElementById('upframe').height = newheight;
        parent.document.getElementById('uploads').style.height = newheight+5+'px';
        }
function verifyDelete(delid) {
  if(confirm("You are about to remove this document from BaconMap.  This cannot be undone.\n\nContinue?"))
    {
      document.upload.doc_delete.value = delid; 
      document.upload.submit();
    }
}
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
</body>
</html>
