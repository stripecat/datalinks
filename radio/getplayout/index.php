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

//set server values
		if (isset($data->Password)) { $Tracks->Password         = $data->Password; }
                if (isset($data->jwt)) { $Tracks->jwt         = $data->jwt; }
		$Tracks->created       = date('Y-m-d H:i:s');
		if (isset($data->NumberOfTracks)) { $Tracks->NumberOfTracks         = $data->NumberOfTracks; };
                if (isset($data->LastAddedMode)) { $Tracks->LastAddedMode         = $data->LastAddedMode; };
                if (isset($data->StationID)) { $Tracks->StationID         = $data->StationID; };
                
            
//Create server

$rc = $Tracks->getplayout();

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
