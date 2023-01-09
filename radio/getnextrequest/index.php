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

        if (ip_in_range($clientip, $InternalIPRange) == true) { $range=true; continue;}

}

if ($range == false)
{
        echo '{ "message": "Failure",';
                echo "\"subcode\": \""."Access denied"."\",";
                echo "\"submessage\": \""."Your IP is not in the allowed range"."\" }";
                                        http_response_code(400);
                                        exit;
}

//Db conn and instances
$database = new Database();
$db=$database->getConnection();

$Tracks = new Tracks($db);

//Get post data

$data = json_decode(file_get_contents("php://input"));

# Debug code
#$raw=file_get_contents("php://input");
#$data = json_decode($raw);
#$decoded="JönSON: ".$raw;
#$pos = strpos($raw, "Dr.");
#if ($pos !=0) {mail("erik@zalitis.se","Dr Awesome",$decoded);}


//set server values
		if (isset($data->Password)) { $Tracks->Password         = $data->Password; }
		$Tracks->created       = date('Y-m-d H:i:s');
//Create server

$rc = $Tracks->getnextrequest();

if (isset($rc["Submessage"])) { $submess=$rc["Submessage"]; } else { $submess = "None";}

if($rc["result"] <> "FALSE"){
        echo $rc["message"];
}else{
        echo '{ "message": "Failure",';
        echo "\"subcode\": \"".$rc["message"]."\",";
        echo "\"submessage\": \"".$submess."\" }";
				http_response_code(400);
}


?>
