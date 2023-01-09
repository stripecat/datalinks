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
		if (isset($data->Search)) { $Tracks->Search         = $data->Search; };
                if (isset($data->LastPlaysToReturn)) { $Tracks->LastPlaysToReturn         = $data->LastPlaysToReturn; };
                if (isset($data->ReturnArtistLongDescription)) { $Tracks->ReturnArtistLongDescription         = $data->ReturnArtistLongDescription; };
                if (isset($data->ReturnArtistDescription)) { $Tracks->ReturnArtistDescription         = $data->ReturnArtistDescription; };
                if (isset($data->StationID)) { $Tracks->StationID         = $data->StationID; };
                if (isset($data->ReturnProductionNotes)) { $Tracks->ReturnProductionNotes         = $data->ReturnProductionNotes; };
                if (isset($data->ReturnPlayList)) { $Tracks->ReturnPlayList         = $data->ReturnPlayList; };
                if (isset($data->Sequence)) { $Tracks->Sequence         = $data->Sequence; };
                if (isset($data->MaxResults)) { $Tracks->MaxResults         = $data->MaxResults; };
                if (isset($data->TrackID)) { $Tracks->TrackID         = $data->TrackID; };
                if (isset($data->EligibilityFilter)) { $Tracks->EligibilityFilter         = $data->EligibilityFilter; };
                if (isset($data->SimpleSearch)) { $Tracks->SimpleSearch         = $data->SimpleSearch; };
          
                  
//Create server

$rc = $Tracks->gettrack();

if (isset($rc["Submessage"])) { $submess=$rc["Submessage"]; } else { $submess = "None";}

if($rc["result"] <> "FALSE"){
        echo $rc["message"];
       /* echo '{ "message": "Success",';
        echo "\"subcode\": \"".$rc["message"]."\",";
        echo "\"submessage\": \"".$submess."\" }"; */
}elseif ($submess == "I have no new data for you."){
        echo '{ "message": "Failure",';
                echo "\"subcode\": \"".$rc["message"]."\",";
                echo "\"submessage\": \"".$submess."\" }";
                                        http_response_code(204);    
}else{
        echo '{ "message": "Failure",';
        echo "\"subcode\": \"".$rc["message"]."\",";
        echo "\"submessage\": \"".$submess."\" }";
				http_response_code(400);
}
