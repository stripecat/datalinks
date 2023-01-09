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

if (isset($data->Artist)) {
        $Tracks->artist         = $data->Artist;
};
if (isset($data->Title)) {
        $Tracks->title         = $data->Title;
};
if (isset($data->EpisodeNumber)) {
        $Tracks->EpisodeNumber         = $data->EpisodeNumber;
};
if (isset($data->IsPodcast)) {
        $Tracks->IsPodcast         = $data->IsPodcast;
};
if (isset($data->IsNews)) {
        $Tracks->IsNews         = $data->IsNews;
};
if (isset($data->News)) {
        $Tracks->News         = $data->News;
};
if (isset($data->BroadcastDate)) {
        $Tracks->BroadcastDate         = $data->BroadcastDate;
};
if (isset($data->Equipment)) {
        $Tracks->Equipment         = $data->Equipment;
};
if (isset($data->PlayList)) {
        $Tracks->PlayList         = $data->PlayList;
};
if (isset($data->ProductionNotes)) {
        $Tracks->ProductionNotes         = $data->ProductionNotes;
};

//set server values
if (isset($data->jwt)) {
        $Tracks->jwt         = $data->jwt;
}
$Tracks->created       = date('Y-m-d H:i:s');
if (isset($data->Filename)) {
        $Tracks->Filename         = $data->Filename;
};
if (isset($data->FileData)) {
        $Tracks->FileData         = $data->FileData;
};


//Create server

$rc = $Tracks->addnews();

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
