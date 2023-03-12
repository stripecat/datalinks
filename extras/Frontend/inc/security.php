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
require $sroot.'inc/functions.php';

 $currentcustomer="ABCD1234";

 define('USER_READ', 0x80000000);
 define('USER_WRITE', 0x40000000);
 define('USER_ADMIN', 0x20000000);
 define('USER_PWDREAD', 0x10000000);
 define('USER_PWDADMIN', 0x8000000);
 define('USER_PENDING', 0x4000000);

// Numeric sanitization -- Lets trough 0-9
/**
 * @param mixed $string 
 * @param string $min 
 * @param string $max 
 * @return string|string[]|null|false 
 */
function sanitize_numeric_string($string, $min='', $max='')
{
	$len = strlen($string);
  $string = preg_replace("/[^0-9]/", "", $string);
  $string = preg_replace("/'/", " ", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// Hexadecimal sanitization -- Lets trough 0-9
function sanitize_hexdec_string($string, $min='', $max='')
{
	$len = strlen($string);
  $string = preg_replace("/[^0-9a-f]/", "", $string);
  $string = preg_replace("/'/", " ", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// paranoid sanitization -- Lets trough alphanumeric + åäöÅÄÖ and _-
function sanitize_paranoid_string($string, $min='', $max='')
{
	$len = strlen($string);
  $string = preg_replace("/[^a-zA-Z0-9\x20åäöÅÄÖ_-]/", "", $string);
  $string = preg_replace("/'/", " ", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// Logon sanitization -- Lets trough alphanumeric + åäöÅÄÖ and _-.
function sanitize_logon_string($string, $min='', $max='')
{
	$len = strlen($string);
  $string = preg_replace("/[^a-zA-Z0-9\x20åäöÅÄÖ._\\\\-]/", "", $string);
  $string = preg_replace("/'/", " ", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// half paranoid sanitization -- Lets trough alphanumeric + åäöÅÄÖ and _-.
function sanitize_halfparanoid_string($string, $min='', $max='')
{
	$len = strlen($string);
  $string = preg_replace("/[^a-zA-Z0-9\x20åäöÅÄÖé._-]/", "", $string);
  $string = preg_replace("/'/", " ", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// Normal sanitization -- Lets trough alphanumeric + åäöÅÄÖ and _-%!?"
function sanitize_normal_string($string, $min='', $max='')
{
	$len = strlen($string);
	$string = sanitize_sql_string($string, $min=$min, $max=$max);
	 $string = preg_replace("/'/", " ", $string);
  $string = preg_replace("/[^a-zA-Z0-9\x20åäöÅÄÖ?!._-]/", "", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}


// date sanitization -- allows / -
function sanitize_date_string($string, $min='', $max='')
{
	$len = strlen($string);
	$string = sanitize_sql_string($string, $min=$min, $max=$max);
  $string = preg_replace("/[^a-zA-Z0-9\x20åäöÅÄÖ#@.,\+\-\/:]/", "", $string);
  $string = preg_replace("/'/", " ", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// url sanitization -- allows / = : @ _
function sanitize_url_string($string, $min='', $max='')
{
	$len = strlen($string);
	$string = sanitize_sql_string($string, $min=$min, $max=$max);
  $string = preg_replace("/[^a-zA-Z0-9\x20åäöÅÄÖ#@.\-\/:=?]_/", "", $string);
  $string = preg_replace("/'/", " ", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// Tolerant sanitization -- all, but caps sizes. No ' allowed
function sanitize_tolerant_string($string, $min='', $max='')
{
	$len = strlen($string);
	$string = sanitize_sql_string($string, $min=$min, $max=$max);
  $string = preg_replace("/'/", " ", $string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}


// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_sql_string($string, $min='', $max='')
{
	$len = strlen($string);
	//$string = preg_replace("/'/", " ", $string); // Ta bort ' 
  $pattern[0] = '/(\\\\)/';
  $pattern[1] = "/\"/";
  $pattern[2] = "/'/";
  $replacement[0] = '\\\\\\';
  $replacement[1] = '\"';
  $replacement[2] = "\\'";
  
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return preg_replace($pattern, $replacement, $string);
}


// sanitize a string for HTML (make sure nothing gets interpreted!)
function sanitize_html_string($string, $min='', $max='')
{
  $len = strlen($string);  
  $pattern[0] = '/\&/';
  $pattern[1] = '/</';
  $pattern[2] = "/>/";
  $pattern[3] = '/\n/';
  $pattern[4] = '/"/';
  $pattern[5] = "/'/";
  $pattern[6] = "/%/";
  $pattern[7] = '/\(/';
  $pattern[8] = '/\)/';
  $pattern[9] = '/\+/';
  $pattern[10] = '/\{/';
  $pattern[11] = '/\}/';
  $pattern[12] = '/\|/';
  $pattern[13] = '/\~/';
	$pattern[14] = '/å/';
	$pattern[15] = '/ä/';
	$pattern[16] = '/ö/';
	$pattern[17] = '/Å/';
	$pattern[18] = '/Ä/';
	$pattern[19] = '/Ö/';
  $replacement[0] = '&amp;';
  $replacement[1] = '&lt;';
  $replacement[2] = '&gt;';
  $replacement[3] = '<br>';
  $replacement[4] = '&quot;';
  $replacement[5] = '&#39;';
  $replacement[6] = '&#37;';
  $replacement[7] = '&#40;';
  $replacement[8] = '&#41;';
  $replacement[9] = '&#43;';
  $replacement[10] = '&#123;';
  $replacement[11] = '&#125;';
  $replacement[12] = '&#124;';
  $replacement[13] = '&#126;'; 
	$replacement[14] = '&aring;';
	$replacement[15] = '&auml;';
	$replacement[16] = '&ouml;';
	$replacement[17] = '&Aring;';
	$replacement[18] = '&Auml;';
	$replacement[19] = '&Ouml;';

if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return preg_replace($pattern, $replacement, $string);
}

function convertprefix ($inbytes)
{
	if ($inbytes < 1024) { $totalbytes=$inbytes; $totalbytes=round($totalbytes,1)." bytes"; }
	if ($inbytes > 1023 && $inbytes < 1048576) { $totalbytes=$inbytes/1024; $totalbytes=round($totalbytes,1)." kB"; }
	if ($inbytes > 1048575 && $inbytes < 1073741825) { $totalbytes=$inbytes/1024/1024; $totalbytes=round($totalbytes,1)." MB"; }
	if ($inbytes > 1073741824 && $inbytes < 1099511627776) { $totalbytes=$inbytes/1024/1024/1024; $totalbytes=round($totalbytes,1)." GB"; }
	if ($inbytes > 1099511627775) { $totalbytes=$inbytes/1024/1024/1024/1024; $totalbytes=round($totalbytes,1)." TB"; }
	return $totalbytes;
}

####################################################################
#
#	Script proper.
#


/*
	try {
	    $dbht = new PDO('mysql:host='.$dbhost.';dbname='.$dbname.'', $dbuser, $dbpasswd,array( PDO::ATTR_PERSISTENT => true));
	} catch (PDOException $e) {
	    if ($debugmode == 1) { echo 'Connection failed: ' . $e->getMessage(); } else { die ("Couldn't connect to the database. Please contact Moleant.com"); }
	}

	*/

if (isset($_SESSION['roles'])) {
	if ($debugmode == 1) { PRINT ("Session exists..."); }
}
	else {
		if ($debugmode == 1) { PRINT ("Session does not exist,setting it up..."); }
	session_start();
	}
 $row="Nothing";
 #$dbpasswd="ee";

 // Connect to database
 
 
# $dbpasswd="fff";

#$current_user=wp_get_current_user();

#echo "Logged in as: ".$_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_NAME']."<br>";
#echo "GUID: ".$_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_ID']."<br>";


if ($debugmode == 1) { 
	echo "Logged in as: ".$_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_NAME']."<br>";
	echo "GUID: ".$_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_ID']."<br>";
  }
  
  if (isset($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_ID']))
  {
  	$objectid=$_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_ID'];
  }
  elseif ($allowlocalrun == 1)
  {
		# Warning: This MUST be removed when system goed into production!!!! 2021-05-07 Erik Zalitis
		$objectid="e7466113-2e01-458c-9c89-acc0b56e215b";
		#define("HTTP_X_MS_CLIENT_PRINCIPAL_ID", "e7466113-2e01-458c-9c89-acc0b56e215b");
		#define("HTTP_X_MS_CLIENT_PRINCIPAL_NAME", "erik@zalitis.se");
  }
  else
  {
	  die("Unknown error during authentication, please contact MoleAnt administration!");
  }
  $_SESSION['roles'] = array();
  
# Get JWT

$jwtraw=Generate_JWT_Token();

$jwtjson=json_decode($jwtraw);

$jwt=$jwtjson->jwt;

#$jwt=Generate_JWT_Token();

#echo "JWT:".$jwt."....";
#echo "<br>";

# Send JWT to server to get roles.

 $url="https://api.moleant.com/discover/SecBuildTicket/";
 $data = [
	 'jwt' => $jwt
];
$json_data = json_encode($data);
#echo $json_data;
$jsonreq = array(
'http' => array(
 'method'  => 'POST',
 'content' => $json_data,
 'header'=>  "Host: api.moleant.com\r\n" .
			 "Content-Type: application/json\r\n"
)
);

#echo "Authorization: Bearer ".$access_token."\r\n";

#echo "JSON: ".$jsonreq;
/*
$context  = stream_context_create( $jsonreq );

#echo "Context:".$context;

$result = file_get_contents( $url, false, $context );

#echo "Kodat svar: ".$result.".";

$resultdec=json_decode($result);

if (isset($resultdec->message)) { echo "Misslyckades..."; }
else {
	#echo "Lyckades...";

	foreach ($resultdec->Roles as $role)
	{
		#echo "Guid: ".$role->guid.".";
		#echo "Roles: ".$role->roles.".";
		$_SESSION['roles'][$role->guid] = array();
		#$_SESSION['orgs'][$role->guid] = array();
		array_push($_SESSION['roles'][$role->guid],$role->roles);
		array_push($_SESSION['roles'][$role->guid],$role->CustomerName);
		array_push($_SESSION['roles'][$role->guid],$role->guid);
		#array_push($_SESSION['orgs'][$role->guid],$role->CustomerName);

	}
}
*/
function Destroy_Token()
{

// remove all session variables
session_unset();

// destroy the session
session_destroy();

return TRUE;
}

function Check_SystemTypeMask($SystemTypeMask,$Type)
{
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
	
	# Set binary from database
	
	#$binary=$_SESSION['roles'][$customer][0];
	
	#$binary="11101000000000000000000000000000";
	
	define('SYSTEM_REGULAR', 0x80000000);
	define('SYSTEM_AD', 0x40000000);
	/* define('USER_ADMIN', 0x20000000);
	define('USER_PWDREAD', 0x10000000);
	define('USER_PWDADMIN', 0x8000000);
	define('USER_PENDING', 0x4000000); */
	
	if ($Type == "SYSTEM_REGULAR") { $epermission = SYSTEM_REGULAR; }
		elseif ($Type == "SYSTEM_AD") { $epermission = SYSTEM_AD; }
	/*	elseif ($mask == "USER_ADMIN") { $epermission = USER_ADMIN;  }
			elseif ($mask == "USER_PWDREAD") { $epermission = USER_PWDREAD; }
				elseif ($mask == "USER_PWDADMIN") { $epermission = USER_PWDADMIN; }
				elseif ($mask == "USER_PENDING") { $epermission = USER_PENDING; } */
				else { $epermission = 0; } # Should not happen :)
	
	#$hex = dechex(bindec($binary));
	$dec = bindec($SystemTypeMask);


if ($debugmode == 1) { 
	
		print ("Mask: ");
		
		if ($dec & SYSTEM_REGULAR) {
			print("Regular system");
	}
	
		if ($dec & SYSTEM_AD) {
			print(" AD");
	}
}

if ($dec & $epermission) { $res = 1; } else { $res = 0; }
#$res = 1;
	if ($debugmode == 1) { print ("Requsted perm: ".$Type.". Held: ".$res."."); }
return $res;
}


function Check_Permission($customer,$permission)
{

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

	# Set binary from database

	#if (isset($_SESSION['roles'])) { die("You don't have any assigned roles"); }
	
	$binary=$_SESSION['roles'][$customer][0];
	
	#$binary="11101000000000000000000000000000";

	
	if ($permission == "USER_READ") { $epermission = USER_READ; }
		elseif ($permission == "USER_WRITE") { $epermission = USER_WRITE; }
		elseif ($permission == "USER_ADMIN") { $epermission = USER_ADMIN;  }
			elseif ($permission == "USER_PWDREAD") { $epermission = USER_PWDREAD; }
				elseif ($permission == "USER_PWDADMIN") { $epermission = USER_PWDADMIN; }
				elseif ($permission == "USER_PENDING") { $epermission = USER_PENDING; }
				else { $epermission = 0; } # Should not happen :)
	
	#$hex = dechex(bindec($binary));
	$dec = bindec($binary);


if ($debugmode == 1) { 
	
		print ("Mask: ");
		
		if ($dec & USER_ADMIN) {
			print("ADMIN");
	}
	
		if ($dec & USER_WRITE) {
			print(" WRITE");
	}
		if ($dec & USER_READ) {
			print(" READ");
	}
		if ($dec & USER_PWDREAD) {
			print(" PWDREAD");
	}
		if ($dec & USER_PWDADMIN) {
			print(" PWDADMIN");
	}
		if ($dec & USER_PENDING) {
			print(" PENDING");
	}
}

if ($dec & $epermission) { $res = 1; } else { $res = 0; }
#$res = 1;
	if ($debugmode == 1) { print ("Requsted perm: ".$permission.". Held: ".$res."."); }
return $res;
}

function Init_user()
{
}

function Check_PermHeld($rolestring,$permission)
{
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
	
	# Set binary from database
	
	#$debugmode = "1";
	$binary="11111000000000000000000000000000";
	
	/*define('USER_READ', 0x80000000);
	define('USER_WRITE', 0x40000000);
	define('USER_ADMIN', 0x20000000);
	define('USER_PWDREAD', 0x10000000);
	define('USER_PWDADMIN', 0x8000000);
	define('USER_PENDING', 0x4000000); */
	

	if ($permission == "USER_READ") { $epermission = USER_READ; }
		elseif ($permission == "USER_WRITE") { $epermission = USER_WRITE; }
		elseif ($permission == "USER_ADMIN") { $epermission = USER_ADMIN;  }
			elseif ($permission == "USER_PWDREAD") { $epermission = USER_PWDREAD; }
				elseif ($permission == "USER_PWDADMIN") { $epermission = USER_PWDADMIN; }
				elseif ($permission == "USER_PENDING") { $epermission = USER_PENDING; }
				else { $epermission = 0; } # Should not happen :)
	
#echo $rolestring;

	#$hex = dechex(bindec($binary));
	$dec = bindec($rolestring);


if ($debugmode == 1) { 
	
		print ("Mask: ");
		
		if ($dec & USER_ADMIN) {
			print("ADMIN");
	}
	
		if ($dec & USER_WRITE) {
			print(" WRITE");
	}
		if ($dec & USER_READ) {
			print(" READ");
	}
		if ($dec & USER_PWDREAD) {
			print(" PWDREAD");
	}
		if ($dec & USER_PWDADMIN) {
			print(" PWDADMIN");
	}
	if ($dec & USER_PENDING) {
			print(" PENDING");
	}
}

if ($dec & $epermission) { $res = 1; } else { $res = 0; }
#$res = 1;
	if ($debugmode == 1) { print ("Requsted perm: ".$permission.". Held: ".$res."."); }
return $res;
}

function Get_Token() 
{
		
	$url = 'https://login.microsoftonline.com/14a85273-ad7b-4ed5-ad47-18751e5af61f/oauth2/token';
$data = array('grant_type' => 'client_credentials', 'client_id' => '4c6eec08-71d4-46e7-968e-e31444ee2455', 'client_secret' => '3BFez6j0v.YfzB2gOL.TL.=es=o]G8sV', 'resource' => 'https://graph.microsoft.com/');

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
	
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === FALSE) { 
	/* Handle error */ 
	
	}
	//echo print_r($result);
	
	return substr(strstr($result,"access_token\":\""),15,-2);
}

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $pass[] = 1;
    $pass[] = "N";
    $pass[] = "o";
    return implode($pass); //turn the array into a string
}

?>