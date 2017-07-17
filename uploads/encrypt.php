<?php
$file="1698219139";
$cc = 'my secret text';
$key = 'my secret key';
$iv = '12345678';
$cipher = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');

$h=fopen($file,"r");
$w=fopen("$file.encrypted","w");
$len=mcrypt_enc_get_block_size($cipher);
mcrypt_generic_init($cipher, $key, $iv);
while($text=fread($h,$len)){
 $encrypted = mcrypt_generic($cipher,$text);
 fwrite($w,$encrypted);
} 
mcrypt_generic_deinit($cipher);
fclose($h);
fclose($w);

#mcrypt_generic_init($cipher, $key, $iv);
#$decrypted = mdecrypt_generic($cipher,$encrypted);
#mcrypt_generic_deinit($cipher);
?>
