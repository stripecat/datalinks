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

$Streams = new Streams($db);

//Get post data
$data = json_decode(file_get_contents("php://input"));

//set server values
		if (isset($data->Password)) { $Streams->Password         = $data->Password; }
		$Streams->created       = date('Y-m-d H:i:s');
		if (isset($data->IP)) { $Streams->IP         = $data->IP; };
		if (isset($data->Event)) { $Streams->Event         = $data->Event; };
                if (isset($data->Country)) { $Streams->Country         = $data->Country; };
                if (isset($data->Playtime)) { $Streams->Playtime         = $data->Playtime; };
                if (isset($data->Eventtype)) { $Streams->Eventtype         = $data->Eventtype; };
                if (isset($data->Agent)) { $Streams->Agent         = $data->Agent; };
                if (isset($data->Regionname)) { $Streams->Regionname         = $data->Regionname; };
                if (isset($data->Isp)) { $Streams->Isp         = $data->Isp; };
                if (isset($data->StreamID)) { $Streams->StreamID         = $data->StreamID; };
                if (isset($data->Identifier)) { $Streams->Identifier         = $data->Identifier; };
                if (isset($data->Zip)) { $Streams->Zip         = $data->Zip; };
                if (isset($data->City)) { $Streams->City         = $data->City; };
               
//Create server

$rc = $Streams->updatestreamevent();

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
