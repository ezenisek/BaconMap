<?php
$file="1698219139";
$cc = 'my secret text';
$key = 'my secret key';
$iv = '12345678';
$cipher = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');

$h=fopen("$file.encrypted","r");
$w=fopen("$file.decrypted","w");
$len=mcrypt_enc_get_block_size($cipher);
print "len:$len\n\n";
mcrypt_generic_init($cipher, $key, $iv);
while($text=fread($h,$len)){
 $decrypted = mdecrypt_generic($cipher,$text);
 fwrite($w,$decrypted);
} 
mcrypt_generic_deinit($cipher);
fclose($h);
ftruncate($w,915);
fclose($w);

?>
