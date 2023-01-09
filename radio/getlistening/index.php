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


foreach ($InternalIPRanges as $InternalIPRange)
{

        if (ip_in_range($clientip, $InternalIPRange) == true) { $range=true; continue;}

}

if ($range == false)
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

$Streams = new Streams($db);

//Get post data
$data = json_decode(file_get_contents("php://input"));

//set server values
		if (isset($data->Password)) { $Streams->Password         = $data->Password; }
		$Streams->created       = date('Y-m-d H:i:s');
		if (isset($data->NumberOfEvents)) { $Streams->NumberOfEvents         = $data->NumberOfEvents; };
                if (isset($data->StationID)) { $Streams->StationID         = $data->StationID; };
                
            
//Create server

$rc = $Streams->getlistening();

if (isset($rc["Submessage"])) { $submess=$rc["Submessage"]; } else { $submess = "None";}

if($rc["result"] <> "FALSE"){
        echo $rc["message"];
       /* echo '{ "message": "Success",';
        echo "\"subcode\": \"".$rc["message"]."\",";
        echo "\"submessage\": \"".$submess."\" }"; */
}else{
        echo '{ "message": "Failure",';
        echo "\"subcode\": \"".$rc["message"]."\",";
        echo "\"submessage\": \"".$submess."\" }";
				http_response_code(400);
}


?>
