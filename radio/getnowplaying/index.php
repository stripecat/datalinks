<?php

//Req headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset:UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//Req includes
include_once '../../config/database.php';
include_once '../../objects/apiericade.php';


# Check IP-range.

$range=false;
$clientip=$_SERVER['REMOTE_ADDR'];


foreach ($InternalIngestIPRanges as $InternalIPRange)
{

        if (ip_in_range($clientip, $InternalIPRange) == false) { $range=true; continue;}

}

if ($range == true)
{
        echo '{ "message": "Failure",';
                echo "\"subcode\": \""."Access denied"."\",";
                echo "\"submessage\": \""."Your IP " . $clientip . " is not in the allowed range"."\" }";
                                        http_response_code(400);
                                        exit;
}



//Db conn and instances
$database = new Database();
$db=$database->getConnection();

$Tracks = new Tracks($db);

//Get post data
#$data = json_decode(file_get_contents("php://input"));

//set server values

		if (isset($_GET["Password"])) { $Tracks->Password         = $_GET["Password"]; }
		$Tracks->created       = date('Y-m-d H:i:s');
                if (isset($_GET["StationID"])) { $Tracks->StationID         = $_GET["StationID"]; };
                
            
//Create server

$rc = $Tracks->getnowplaying();

        echo $rc;


?>
