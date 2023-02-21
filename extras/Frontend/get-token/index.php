<?php


$sroot=realpath(dirname(__FILE__));

if (strrpos($sroot, '\\') != "") { 
	# Windows
	$sroot=substr($sroot,0,strrpos($sroot, '\\'));
	$sroot=$sroot."\\";
}
else 
{
	# Linux
	$sroot=substr($sroot,0,strrpos($sroot, '/'));
	$sroot=$sroot."/";
}

require $sroot.'config.php';
#require $sroot.'inc/security.php';
require $sroot.'inc/functions.php';

/* echo "{";
echo "\"jwt\":\"".Generate_JWT_Token()."\"";
echo "}"; */

$jwtraw=Generate_JWT_Token();
$jwtjson=json_decode($jwtraw);

$jwt=$jwtjson->jwt;


echo $jwt;

?>