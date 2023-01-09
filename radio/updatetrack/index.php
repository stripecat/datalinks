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


# Debug code
$raw=file_get_contents("php://input");
$data = json_decode($raw);
#$decoded="JönSON: ".$raw;
#$pos = strpos($raw, "Dr.");
#if ($pos !=0) {mail("erik@zalitis.se","Mrup",$decoded);}
#mail("erik@zalitis.se","Mrup",$decoded);


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

#$data = json_decode(file_get_contents("php://input"));


//set server values
		if (isset($data->Password)) { $Tracks->Password         = $data->Password; }
		$Tracks->created       = date('Y-m-d H:i:s');
		if (isset($data->Artist)) { $Tracks->Artist         = $data->Artist; };
		if (isset($data->Title)) { $Tracks->Title         = $data->Title; };
                if (isset($data->Comments)) { $Tracks->Comments        = $data->Comments; };
                if (isset($data->Album)) { $Tracks->Album        = $data->Album; };
                if (isset($data->Genre)) { $Tracks->Genre         = $data->Genre; };
                if (isset($data->Guid)) { $Tracks->Guid       = $data->Guid; };
                if (isset($data->Year)) { $Tracks->Year       = $data->Year; };       
                if (isset($data->Duration)) { $Tracks->Duration       = $data->Duration; };
                if (isset($data->OutCue)) { $Tracks->OutCue       = $data->OutCue; };
                if (isset($data->Tags)) { $Tracks->Tags       = $data->Tags; };
                if (isset($data->Disabled)) { $Tracks->Disabled        = $data->Disabled; };
                if (isset($data->Type)) { $Tracks->Type       = $data->Type; };
                if (isset($data->Intro)) { $Tracks->Intro       = $data->Intro; };
                if (isset($data->CueIn)) { $Tracks->CueIn       = $data->CueIn; };
                if (isset($data->Path)) { $Tracks->Path       = $data->Path; };
                if (isset($data->Segue)) { $Tracks->Segue       = $data->Segue; };
                if (isset($data->CueOut)) { $Tracks->CueOut       = $data->CueOut; };
                if (isset($data->Added)) { $Tracks->Added       = $data->Added; };
                if (isset($data->Sweeper)) { $Tracks->Sweeper       = $data->Sweeper; };
                if (isset($data->NoFade)) { $Tracks->NoFade       = $data->NoFade; };
                if (isset($data->ValidFrom)) { $Tracks->ValidFrom       = $data->ValidFrom; };
                if (isset($data->Expires)) { $Tracks->Expires       = $data->Expires; };
                if (isset($data->StationID)) { $Tracks->StationID       = $data->StationID; };

//Create server

$rc = $Tracks->updatetrack();

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
