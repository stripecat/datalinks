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
		if (isset($data->TrackRating)) { $Tracks->TrackRating         = $data->TrackRating; };
		if (isset($data->TrackID)) { $Tracks->TrackID         = $data->TrackID; };
                if (isset($data->StationID)) { $Tracks->StationID       = $data->StationID; };

//Create server

$rc = $Tracks->updatestar();

if (isset($rc["Submessage"])) { $submess=$rc["Submessage"]; } else { $submess = "None";}

if($rc["result"] <> "FALSE"){
        echo '{ "message": "Success",';
        echo "\"subcode\": \"".$rc["message"]."\",";
        echo "\"submessage\": \"".$submess."\" }";
}else{
        echo '{ "message": "Failure",';
        echo "\"subcode\": \"".$rc["message"]."\",";
        echo "\"submessage\": \"".$submess."\" }";
				http_response_code(400);
}


?>

