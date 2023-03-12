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
include_once $sroot.'inc/security.php';
include_once $sroot.'inc/functions.php';

/* echo "{";
echo "\"jwt\":\"".Generate_JWT_Token()."\"";
echo "}"; */

$jwtraw=Generate_JWT_Token("GENERAL","ANY",$_SESSION);
$jwtjson=json_decode($jwtraw);

$jwt=$jwtjson->jwt;


echo $jwt;

?>