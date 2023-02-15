<?php

/**
 * Started 2021-07-20 by Erik Zalitis
 */
include_once '../../config/common.php';

/** @package Tracks is ...  */
class Tracks
{

   //Db connection and table
   private $conn;

   //Object properties
   public $token;
   public $jwt;
   public $DateCreated;
   public $Password;
   public $Artist;
   public $Title;
   public $Comments;
   public $Album;
   public $Genre;
   public $Year;
   public $Guid;
   public $LastPlaysToReturn;
   public $ReturnArtistLongDescription;
   public $ReturnArtistDescription;
   public $NumberOfTracks;
   public $Duration;
   public $OutCue;
   public $Tags;
   public $Disabled;
   public $Type;
   public $Intro;
   public $CueIn;
   public $CueOut;
   public $Added;
   public $Sweeper;
   public $NoFade;
   public $ValidFrom;
   public $Expires;
   public $Path;
   public $Segue;
   public $StationID;
   public $Sequence;
   public $TrackID;
   public $TrackRating;
   public $ReturnProductionNotes;
   public $ReturnPlayList;
   public $Search;
   public $Requester;
   public $Greeting;
   public $Slot;
   public $EligibilityFilter;
   public $SimpleSearch;
   public $MaxResults;
   public $Source;
   public $LastAddedMode;
   public $CreateDate;
   public $guid;
   public $OriginalFileName;
   public $Podcasts;
   public $News;
   public $FileData;
   public $Filename;
   public $Description;
   public $ProductionNotes;
   public $PlayList;
   public $Equipment;
   public $IsNews;
   public $IsPodcast;
   public $EpisodeNumber;
   public $title;
   public $artist;
   public $BroadcastDate;

   # Functions responding to service-calls.

   #************************************************************************************
   # getstationstats

   function getstationstats()
   {
      include '/var/www/html/config.php';
      global $dbht;

      if (isset($this->Password)) {
         $token = validatejwt($this->Password);
      } else if (isset($this->jwt)) {
         $token = validatejwt($this->jwt);
      } else {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }


      $gtoken = json_decode($token);
      $browserhash = $gtoken->data->id;
      $ip = $gtoken->data->ip;

      if ($gtoken->message == "Access denied.") {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }

      if (isset($this->StationID)) {
         $StationID = sanitize_numeric_string($this->StationID, 1, 2);
      } else {
         $StationID = "";
      }

      /*     if ($StationID == "") {
         $Stat = "";
        # return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You need to specify a StationID.');
      }
      else
      {
         $Stat = "WHERE StationID = " . $StationID . "";
      } */

      # Temporary logic to test population. This must then be moved to a scheduled population job.

      # $rc=PopulateTrackerTypes();
      $jsonpl = "{";


      $jsonpl .= "\"Stations\":[";
      //  $jsonpl .= "\"Station\":[";

      $sql = "SELECT `StationID`,`TotalLength`, `totaltracks` FROM `stats`";
      $totstations = 0;
      foreach ($dbht->query($sql) as $srow) {

         $totstations++;

         $StationIDInArray = array_search($srow["StationID"], array_column($Stations, 'stationid'));

         if ($StationIDInArray !== false) {
         } else {
            return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Station does not exist of configuration is invalid.');
         }

         $StationName = $Stations[$StationIDInArray]["StationName"];

         $jsonpl .= "{\"StationID\": \"" . $srow["StationID"] . "\",";
         $jsonpl .= "\"StationName\": \"" . $StationName . "\",";
         $jsonpl .= "\"TotalLength\": \"" . $srow["TotalLength"] . "\",";
         $jsonpl .= "\"TracksOnStation\": \"" . $srow["totaltracks"] . "\"";
         $jsonpl .= ",";

         # Get tracktypes per station

         $TotalSongs = 0;
         $Stat = "WHERE StationID = " . $srow["StationID"] . "";
         $sql = "SELECT `TrackerType`, `Extension`, `Plays`, `Comment`, `StationID`, `Percent` FROM `TrackerTypes` " . $Stat . " order by Plays DESC";

         $jsonpl .= "\"Trackerstats\":[";

         foreach ($dbht->query($sql) as $row) {
            $jsonpl .= "{\"TrackerType\":\"" . $row["TrackerType"] . "\",";
            $jsonpl .= "\"Tracks\":\"" . $row["Plays"] . "\",";
            $jsonpl .= "\"Percent\":\"" . $row["Percent"] . "\",";
            $jsonpl .= "\"StationID\":\"" . $row["StationID"] .  "\"},";
            $TotalSongs++;
         }

         if ($TotalSongs > 0) {
            $jsonpl = substr($jsonpl, 0, -1);
         }

         $jsonpl .= "]},";
      }
      if ($totstations > 0) {
         $jsonpl = substr($jsonpl, 0, -1);
      }
      $jsonpl .= "]}";

      $todaysdate = date("Y-m-d H:i:s");

      return array("result" => 'TRUE', "message" => $jsonpl, "Submessage" => 'All ok.');
   }

   #************************************************************************************
   # getrequeststats

   function getrequeststats()
   {
      include '/var/www/html/config.php';
      global $dbht;

      $token = validatejwt($this->Password);
      $gtoken = json_decode($token);
      $browserhash = $gtoken->data->id;
      $ip = $gtoken->data->ip;

      if ($gtoken->message == "Access denied.") {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }

      if (isset($this->StationID)) {
         $StationID = sanitize_numeric_string($this->StationID, 1, 2);
      } else {
         $StationID = "";
      }

      if ($StationID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You need to specify a StationID.');
      }

      # Validation of configuration.

      $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

      if ($StationIDInArray !== false) {
      } else {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Station does not exist of configuration is invalid.');
      }

      $AllowRequests = $Stations[$StationIDInArray]["AllowRequests"];

      if ($AllowRequests != 1) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'This station does not allow requests.');
      }

      $MaxPendingRequests = $Stations[$StationIDInArray]["MaxPendingRequests"];

      $PendingRequests = 0;
      $sql = "SELECT id, title, fullartist, lastplayed, `source` FROM `QueuedRequests` WHERE StationID = " . $StationID . " order by id asc";

      $jsonplr = "";
      foreach ($dbht->query($sql) as $row) {
         $jsonplr .= "{\"Song\":\"" . $row["fullartist"] . " - " . $row["title"] . "\",";
         $jsonplr .= "\"Requested\":\"" . $row["lastplayed"] . "\",";
         $jsonplr .= "\"Source\":\"" . $row["source"] . "\",";
         $jsonplr .= "\"State\":\"" . "Waiting" . "\"},";
         $PendingRequests++;
      }

      $SlotsPerHour = $Stations[$StationIDInArray]["SlotsPerHour"];

      $TimeUntilPlay = (60 / $SlotsPerHour) * ($PendingRequests + 1);

      # Get the tunes that have already played.
      # Max 10 songs of any type can be shown. If you have 3 in the queue and 123 player, the returned
      # will be 3 queued and the the last played 7 out of 123, filling it to 10.

      $limitreqs = (5 - $PendingRequests); # 
      $PlayedRequests = 0;
      $sql = "SELECT `TrackID`, `title`, `fullartist`, `lastplayed`, `source` FROM `PlayedRequests` WHERE StationID = " . $StationID . " order by lastplayedasrequest desc limit " . $limitreqs . ";";

      foreach ($dbht->query($sql) as $row) {
         $jsonplr .= "{\"Song\":\"" . $row["fullartist"] . " - " . $row["title"] . "\",";
         $jsonplr .= "\"Requested\":\"" . $row["lastplayed"] . "\",";
         $jsonplr .= "\"Source\":\"" . $row["source"] . "\",";
         $jsonplr .= "\"State\":\"" . "Played" . "\"},";
         $PlayedRequests++;
      }

      if ($PlayedRequests > 0) {
         $jsonplr = substr($jsonplr, 0, -1);
      } else if ($PendingRequests > 0) {
         $jsonplr = substr($jsonplr, 0, -1);
      }

      # The validation successful, we will proceed.

      /*  if ($i >= $MaxPendingRequests) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'There are currently too many requests on this station. Please try again later..');
      } */

      # 

      $TimeUntilPlays = ($TimeUntilPlay * 60);

      if ($PendingRequests > $MaxPendingRequests) {
         $PlayTimeHR = "Cannot accept more requests at this time. Please wait until requests are below " . $MaxPendingRequests . " requests.";
      } elseif ($TimeUntilPlays < 86400) {
         if ($TimeUntilPlays < 3600) {
            $PlayTimeHR = gmdate("i", $TimeUntilPlays) . " minutes";
         } elseif ($TimeUntilPlays < 7200) {
            $PlayTimeHR = gmdate("h", $TimeUntilPlays) . " hour " . gmdate("i", $TimeUntilPlays) . " minutes";
         } elseif ($TimeUntilPlays < 60) {
            $PlayTimeHR = gmdate("s seconds", $TimeUntilPlays);
         } else {
            $PlayTimeHR = gmdate("H", $TimeUntilPlays) . " hours " . gmdate("i", $TimeUntilPlays) . " minutes";
         }
      } else {
         $PlayTimeHR = "00:00:00";
      }


      $jsonpl = "{ \"RequestStatistics\":[{";
      $jsonpl .= "\"TracksInQueue\":\"" . $PendingRequests . "\",";
      $jsonpl .= "\"YourQueueNumber\":\"" . ($PendingRequests + 1) . "\",";
      $jsonpl .= "\"ExpectedWaitingTimeInMinutes\":\"" . $TimeUntilPlay . "\",";
      $jsonpl .= "\"ExpectedWaitingTime\":\"" . $PlayTimeHR . "\"";
      $jsonpl .= "}],";

      $jsonpl .= "\"CurrentRequests\":[";
      $jsonpl .= $jsonplr;

      $jsonpl .= "]";


      $jsonpl .= "}";

      $todaysdate = date("Y-m-d H:i:s");

      return array("result" => 'TRUE', "message" => $jsonpl, "Submessage" => 'All ok.');
   }


   #************************************************************************************
   # AddRequest

   function addrequest()
   {

      include '/var/www/html/config.php';
      global $dbht;

      $token = validatejwt($this->Password);
      $gtoken = json_decode($token);
      $browserhash = $gtoken->data->id;
      $ip = $gtoken->data->ip;

      if ($gtoken->message == "Access denied.") {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }

      $todaysdate = date("Y-m-d H:i:s");

      # Validate the fields
      $TrackID = sanitize_numeric_string($this->TrackID, 1, 6);
      $Requester = sanitize_html_string($this->Requester, 1, 120);
      $Greeting = sanitize_html_string($this->Greeting, 1, 250);
      $Source = sanitize_html_string($this->Source, 1, 20);
      if (isset($this->StationID)) {
         $StationID = sanitize_numeric_string($this->StationID, 1, 2);
      } else {
         $StationID = "";
      }

      /* if ($Requester == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You need to type in your name.');
      }
*/

      if ($TrackID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You need to specify a TrackID.');
      }


      if ($StationID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You need to specify a StationID.');
      }

      $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

      if ($StationIDInArray !== false) {
      } else {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Station does not exist of configuration is invalid.');
      }

      $AllowRequests = $Stations[$StationIDInArray]["AllowRequests"];

      if ($AllowRequests != 1) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'This station does not allow requests.');
      }

      # First, check that the trackid does exist.
      # On pass, proceed. On fail, return RESULT FALSE.

      $sql = "SELECT `id`, `title`, `fullartist`, `lastplayed`, `CueOut`, `CueIn`, `EligibilityTime`, `ArtistEligibilityTime`, `StationID`, `Guid`, `Duration`, `Path` FROM `titles` WHERE id = " . $TrackID . " and StationID = " . $StationID . ";";
      $i = 0;

      $timestamp = time();

      $RealName = "";
      foreach ($dbht->query($sql) as $row) {
         $i++;

         $RealName = $row["fullartist"] . " - " . $row["title"];
         $PlayTime = ($row["CueOut"] - $row["CueIn"]);
         $Title = $row["title"];
         $Fullartist = $row["fullartist"];
         $TrackLastPlayed = $row["lastplayed"];
         $EligibilityTime = $row["EligibilityTime"];
         $ArtistEligibilityTime = $row["ArtistEligibilityTime"];
         # $StationID = $row["StationID"];
         $TGuid = $row["Guid"];
         $Duration = $row["Duration"];
         $CueOut = $row["CueOut"];
         $CueIn = $row["CueIn"];
         $Path = $row["Path"];
      }

      if ($i == 0) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Track/Title does not exist.');
      }

      # Security checks!

      # First check: are there too many request pending at the moment. If so, we cannot let the request proceed.

      $MaxPendingRequests = $Stations[$StationIDInArray]["MaxPendingRequests"];

      $i = 0;
      $sql = "SELECT id FROM `QueuedRequests` WHERE StationID = " . $StationID . "";

      foreach ($dbht->query($sql) as $row) {
         $i++;
      }

      if ($i >= $MaxPendingRequests) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'There are currently too many requests on this station. Please try again later..');
      }

      # check how many time the browser hash has been used the five hours. Lockout if above
      # On pass, proceed. On fail, return RESULT FALSE.

      $i = 0;
      $sql = "SELECT id FROM `requestlog` WHERE browserhash = '" . $browserhash . "' and (timestamp > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 5 HOUR)))";

      foreach ($dbht->query($sql) as $row) {
         $i++;
      }

      if ($i >= 5) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You may only request 5 tracks per five hours.');
      }

      # Check if the id has been played for by the browser hash for the last three days.
      # On pass, proceed. On fail, return RESULT FALSE.

      $timestamp = 0;

      $i = 0;
      $sql = "SELECT timestamp FROM `requestlog` WHERE trackid = " . $TrackID . "";

      foreach ($dbht->query($sql) as $row) {
         $timestamp = $row["timestamp"];
         $i++;
      }

      $ValidityDays = $Stations[$StationIDInArray]["ValidityDays"];

      #$ValidityDays = 1; # Days that must pass before a track may be requested again from one identified browser. 
      # Mechanism is not super-secure, but deters script-kiddies from spamming the button through the F12 console.

      $ValidityDaysInEpoch = ($ValidityDays * 86400);

      $currenttime = time();
      $Timestamphr = date("Y-m-d H:i:s");
      /* if (($currenttime - $ValidityDaysInEpoch) < $timestamp) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You may not request this track again at this time');
      } */

      $canplay = CheckEligibility($Fullartist, $TrackLastPlayed, $EligibilityTime, $ArtistEligibilityTime, $StationID, $PlayTime);
      if ($canplay[0] != true) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Cannot request this tune, reason: ' . $canplay[1] . '.');
      }

      # Since we passed the controls, we must now create the request
      # First we put the request log.

      $i = 0;

      $sql = "INSERT INTO `requestlog`(`timestamp`, `timestamphr`, `browserhash`, `ip`, `trackid`, `nameofrequester`, `RealName`, `StationID`, `greeting`, `Path`, `Source`) VALUES (" . $currenttime . ",'" . $Timestamphr . "','" . $browserhash . "','" . $ip . "'," . $TrackID . ",'" . $Requester . "','" . $RealName . "'," . $StationID . ",'" . $Greeting . "','" . $Path . "','" . $Source . "')";

      foreach ($dbht->query($sql) as $row) {
         $i++;
      }

      # Second we put it in the Queuedlog

      $i = 0;

      $sql = "INSERT INTO `QueuedRequests`(`TrackID`, `StationID`, `title`, `fullartist`, `Guid`, `lastplayed`, `Duration`, `greeting`, `nameofrequester`, `Path`, `Source`, `CueIn`, `CueOut`) VALUES (" . $TrackID . "," . $StationID . ",'" . $Title . "','" . $Fullartist . "','" . $TGuid . "'," . $currenttime . "," . $Duration . ",'" . $Greeting . "','" . $Requester  . "','" . $Path . "','" . $Source . "'," . $CueIn . "," . $CueOut . ")";

      foreach ($dbht->query($sql) as $row) {
         $i++;
      }


      $sql = "UPDATE titles SET ArtistEligibilityTime=" . $currenttime . " WHERE fullartist = '" . $Fullartist . "' and StationID = " . $StationID;

      foreach ($dbht->query($sql) as $row) {
      }


      $sql = "UPDATE titles SET EligibilityTime=" . $currenttime . " WHERE id = " . $TrackID . " and StationID = " . $StationID;

      foreach ($dbht->query($sql) as $row) {
      }

      updatechangelog($TrackID, $Fullartist, $Title, $currenttime, $Timestamphr, $TGuid,  "Request", $Requester . " (" . $ip . ") requested " . $Fullartist . " - " . $Title . ".", $StationID);


      $json = "Added " . $RealName . " to the request log";

      return array("result" => 'TRUE', "message" => $json, "Submessage" => 'Success! The tune you requested has been added to the playlist.');
   } # addrequest



   #************************************************************************************
   # getplayout

   function getplayout()
   {

      include '/var/www/html/config.php';
      global $dbht;


      if (isset($this->Password)) {
         $token = validatejwt($this->Password);
      } else if (isset($this->jwt)) {
         $token = validatejwt($this->jwt);
      } else {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }

      $gtoken = json_decode($token);
      if ($gtoken->message == "Access denied.") {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }
      # $userid=$gtoken->data->id;
      # $useremail=$gtoken->data->email;

      $todaysdate = date("Y-m-d H:i:s");

      # Validate the fields
      $NumberOfTracks = sanitize_numeric_string($this->NumberOfTracks, 1, 4);
      $StationID = sanitize_numeric_string($this->StationID, 1, 2);

      if (isset($this->LastAddedMode)) {
         $LastAddedMode = sanitize_numeric_string($this->LastAddedMode, 1, 2);
      } else {
         $LastAddedMode = 0;
      }

      if ($StationID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You need to specify a StationID.');
      }

      if ($NumberOfTracks == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You need to specify a correct number of tracks to display.');
      }

      if ($NumberOfTracks > 100) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'You may only display up to the last 100 tracks.');
      }

      # Generate the answer

      if ($LastAddedMode == 1) {
         $sql = "SELECT `lastplayed` as \"timestamp\", `lastplayedhr` as \"timestamphr\", `fullartist` as \"artist\", `title`, `album`, `CreationDate`, `CreationDateHR`, `guid`, StationID FROM `titles` WHERE StationID = " . $StationID .  " order by id desc limit " . $NumberOfTracks . "";
      } else {
         $sql = "SELECT `timestamp`, `timestamphr`, `artist`, `title`, `guid`, `StationID` FROM `playoutlog` WHERE StationID = " . $StationID . " ORDER BY timestamp DESC LIMIT " . $NumberOfTracks . "";
      }

      $i = 0;
      $jsonpl = "{ \"PlayOutLog\":[";
      foreach ($dbht->query($sql) as $row) {

         $timestamp = $row["timestamp"];
         $timestamphr = $row["timestamphr"];
         $i++;

         $jsonpl .= "{\"TimeStampHR\":\"" . $timestamphr . "\",";
         $jsonpl .= "\"TimeStamp\":\"" . $timestamp . "\",";
         $jsonpl .= "\"Artist\":\"" .  $row["artist"] . "\",";
         $jsonpl .= "\"Title\":\"" .  $row["title"] . "\",";

         $jsonpl .= "\"CreationDate\":\"";
         if (isset($row["CreationDate"])) {
            $jsonpl .=  $row["CreationDate"];
         }
         $jsonpl .=  "\",";

         $jsonpl .= "\"CreationDateHR\":\"";
         if (isset($row["CreationDateHR"])) {
            $jsonpl .=  $row["CreationDateHR"];
         }
         $jsonpl .=  "\",";

         $jsonpl .= "\"Album\":\"";
         if (isset($row["album"])) {
            $jsonpl .=  $row["album"];
         }
         $jsonpl .=  "\",";


         $jsonpl .= "\"StationID\":\"" .  $row["StationID"] . "\",";
         $jsonpl .= "\"Guid\":\"" .  $row["guid"] . "\"},";
      }
      $jsonpl = substr($jsonpl, 0, -1);
      $jsonpl .= "] }";

      return array("result" => 'TRUE', "message" => $jsonpl, "Submessage" => 'All done.');
   }

   function getnowplaying()
   {
      include '/var/www/html/config.php';
      global $dbht;

      $guid = checkpassword($this->Password);
      if ($guid == FALSE) {
         return  'Missing or incorrect password.';
      }

      # Validate the fields

      $StationID = trim(sanitize_numeric_string($this->StationID, 1, 5));

      $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

      $offset = $Stations[$StationIDInArray]["StreamingOffset"];

      # Generate the answer


      $sql = "SELECT t1.`fullartist`, t1.`title`, t1.`lastplayed`, t1.`trackid`, t1.`StationID`, t1.`Duration`, t2.Album as \"Album\" FROM `nowplaying` t1 left join titles t2 on t1.trackid = t2.id WHERE t1.StationID = " . $StationID . "";


      #"&#37;20" => space
      #"&#37;27" => '
      # 71.&#37;20It&#37;27s&#37;20a&#37;20long&#37;20way&...


      #SELECT * FROM `playoutlog` WHERE StationID = 1 ORDER BY timestamp desc LIMIT 1,1


      $i = 0;
      foreach ($dbht->query($sql) as $row) {


         # Make an offset control. 

         $lastplayed = $row["lastplayed"];

         $now = time();

         if (($now - $offset) < $lastplayed) {

            $osql = "select playoutlog.title, playoutlog.artist,  titles.Album from playoutlog left join titles on playoutlog.trackid = titles.id WHERE playoutlog.StationID = " . $StationID . " ORDER BY playoutlog.timestamp DESC LIMIT 1,1";

            # We will 
            foreach ($dbht->query($osql) as $orow) {

               $tracktype = gettracktype($orow["Album"]);
               $Artist = convertplain($orow["artist"]);
               $Title = convertplain($orow["title"]);
            }
         } else {

            # Normal flow of operation.
            $tracktype = gettracktype($row["Album"]);

            $Artist = convertplain($row["fullartist"]);
            $Title = convertplain($row["title"]);
         }

         $i++;

         if (string_search_partial($Stations[$StationIDInArray]["RequestPrefix"], strtolower($row["fullartist"])) == true) {

            $reqlen = strlen($Stations[$StationIDInArray]["RequestPrefix"][0]) + 2;

            $Artist = substr($Artist, $reqlen,);
            $tracktype = "Listener request";
         }

         if (!isset($playing)) {
            $playing = $Artist . " - " . $Title . " [" . $tracktype . "]";
         } else {
            $playing = "ericade.radio - Live broadcast";
         }
      }

      if (!isset($playing)) {
         $playing = "ericade.radio - Live broadcast";
      }
      return $playing;
   }

   /** @return string[]  */
   function gettrack()
   {
      include '/var/www/html/config.php';
      global $dbht;

      if (isset($this->Password)) {
         $token = validatejwt($this->Password);
      } else if (isset($this->jwt)) {
         $token = validatejwt($this->jwt);
      } else {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }

      $gtoken = json_decode($token);
      if ($gtoken->message == "Access denied.") {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }
      # $userid=$gtoken->data->id;
      # $useremail=$gtoken->data->email;

      $todaysdate = date("Y-m-d H:i:s");

      # Validate the fields
      #echo "Pre".$this->Title.".\n";
      $Search = sanitize_sql_string($this->Search, 1, 30); # Don't use this variable for anything else than search with '' around it! SQL-safe but NOT XSS-safe!!!
      $LastPlaysToReturn = sanitize_numeric_string($this->LastPlaysToReturn, 1, 3);
      $StationID = sanitize_numeric_string($this->StationID, 1, 2);

      if ($StationID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Missing or invalid StationID.');
      }

      if (isset($this->Sequence)) {
         $Sequence = sanitize_numeric_string($this->Sequence, 1, 20);
      } else {
         $Sequence = "";
      }

      if (isset($this->ReturnProductionNotes)) {
         $ReturnProductionNotes = sanitize_numeric_string($this->ReturnProductionNotes, 1, 2);
      } else {
         $ReturnProductionNotes = 0;
      }


      if (isset($this->MaxResults)) {
         $MaxResults = sanitize_numeric_string($this->MaxResults, 1, 2);
         if ($MaxResults >= 50) {
            $MaxResults = 50;
         }
      } else {
         $MaxResults = 50;
      }

      if (isset($this->ReturnPlayList)) {
         $ReturnPlayList = sanitize_numeric_string($this->ReturnPlayList, 1, 2);
      } else {
         $ReturnPlayList = 0;
      }

      if (isset($this->ReturnArtistLongDescription)) {
         $ReturnArtistLongDescription = sanitize_numeric_string($this->ReturnArtistLongDescription, 1, 2);
      } else {
         $ReturnArtistLongDescription = 0;
      }

      if (isset($this->SimpleSearch)) {
         $SimpleSearch = sanitize_numeric_string($this->SimpleSearch, 1, 2);
      } else {
         $SimpleSearch = 0;
      }

      if (isset($this->TrackID)) {
         $TrackID = sanitize_numeric_string($this->TrackID, 1, 5);

         if ($TrackID == "") {
            return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Invalid track id.');
         }
      } else {
         $TrackID = 0;
      }

      if ($SimpleSearch != 0 and $SimpleSearch != 1) {
         $SimpleSearch  = 0;
      } # Only accept answers: 0 or 1. Yes, the AND is correct.
      if (isset($this->EligibilityFilter)) {
         $EligibilityFilter = sanitize_numeric_string($this->EligibilityFilter, 1, 2);
      } else {
         $EligibilityFilter = 0;
      }
      if ($EligibilityFilter != 0 and $EligibilityFilter != 1) {
         $EligibilityFilter = 0;
      } # Only accept answers: 0 or 1. Yes, the AND is correct.
      if ($ReturnArtistLongDescription != 0 and $ReturnArtistLongDescription != 1) {
         $ReturnArtistLongDescription = 0;
      } # Only accept answers: 0 or 1. Yes, the AND is correct.
      $ReturnArtistDescription = sanitize_numeric_string($this->ReturnArtistDescription, 1, 2);
      if ($ReturnArtistDescription != 0 and $ReturnArtistDescription != 1) {
         $ReturnArtistDescription = 0;
      } # Only accept answers: 0 or 1. Yes, the AND is correct.
      if ($ReturnProductionNotes != 0 and $ReturnProductionNotes != 1) {
         $ReturnProductionNotes = 0;
      } # Only accept answers: 0 or 1. Yes, the AND is correct.
      if ($ReturnPlayList != 0 and $ReturnPlayList != 1) {
         $ReturnPlayList = 0;
      } # Only accept answers: 0 or 1. Yes, the AND is correct.

      if ($ReturnArtistDescription == 0) {
         $ReturnArtistLongDescription = 0;
         $ReturnProductionNotes = 0;
         $ReturnPlayList = 0;
      }

      if ($LastPlaysToReturn == "") {
         $LastPlaysToReturn = 3;
      }
      # Check the sequence-number against the currently playing track's starttime (It's what the sequence number really is!)

      if ($Sequence != "") {
         $sql = "SELECT lastplayed FROM `nowplaying` WHERE StationID = " . $StationID . "";
         #echo $sql;
         $i = 0;
         $LastPlayedTrack = "";
         foreach ($dbht->query($sql) as $row) {
            $CurrentTrackPlayTime = $row["lastplayed"];
            $i++;
         }

         # This is a super-unlikely event.
         if ($i == 0) {
            return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Could not deduce the last track that was playing.');
         }


         if ($CurrentTrackPlayTime == $Sequence) {
            # No need to return any data! We just say: you're already playing that... 
            return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'I have no new data for you.');
         }
      }


      $override = 0;
      $isStarable = 1;
      $secondssincehour = ((date("i") * 60) + date("s"));
      if ($StationID == 2 and (date("N") >= 1 and  date("N") < 6) and ((date("i") >= 0) and $secondssincehour <= 190)) {
         $override = 1;
         $CurrentTrackPlayTime = "1111111";
      }

      $Title = sanitize_html_string($this->Title, 1, 255);

      if ($StationID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'StationID is missing.');
      }

      $patterns = array();
      $patterns[0] = '/&#37;20/';
      $patterns[1] = '/%20/';
      $replacements = array();
      $replacements[0] = ' ';
      $replacements[1] = ' ';

      $Fullartist = preg_replace($patterns, $replacements, $this->Artist);
      $Fullartist = sanitize_html_string($Fullartist, 1, 255);

      # Get track-info. Composite star rating, data etc.

      $SearchArtist = $Search;


      if ($Search != "" or $SimpleSearch == 1) {

         # Convert the title to proper coding.
         $arr = array(" " => "&#37;20", "!" => "&#37;21", "*" => "&#37;2A", "&#37;2F" => "/");
         $stemp = strtr($Search, $arr);

         $SearchTitle = $stemp;
         #$SearchArtist = $stemp;
      }


      if ($Search == "" and $TrackID == 0 and $SimpleSearch == 0) {

         # Kicks in if we don't specify any search criteria and will always return the track playing.
         if ($ReturnArtistDescription == 0) {
            $sql = "SELECT t1.id as \"trackid\", t2.id as \"titleid\", t1.title, t1.fullartist, t1.Type, t1.CueIn, t1.CueOut, t1.Comments, t1.Album,  t1.totalplays as \"tracktotalplays\", t1.EligibilityTime, t1.ArtistEligibilityTime, t2.compositerating as \"compositerating\", t2.voters as \"tvoters\", t2.about as \"about\",  t2.ProductionNotes as \"ProductionNotes\", t2.PlayList as \"PlayList\", t2.podcast as \"podcast\", t2.image as \"image\", t2.BroadcastDate as \"BroadcastDate\", t2.timestamp as \"timestamp\", t2.timestamphr as \"timestamphr\" from titles t1 left join trackdata t2 on t1.id = t2.trackid WHERE t1.StationID = " . $StationID . " ORDER BY t1.lastplayed DESC LIMIT 1";
         } else {
            $sql = "SELECT t1.id as \"trackid\", t2.id as \"titleid\", t1.title, t1.fullartist, t1.Type, t1.CueIn, t1.CueOut, t1.Comments, t1.Album,   t1.totalplays as \"tracktotalplays\", t1.EligibilityTime, t1.ArtistEligibilityTime, t2.compositerating as \"compositerating\", t2.voters as \"tvoters\", t2.about as \"about\",  t2.PodcastURL, t2.ProductionNotes as \"ProductionNotes\", t2.PodcastURL, t2.PlayList as \"PlayList\",  t2.podcast as \"podcast\", t2.image as \"image\",  t2.BroadcastDate as \"BroadcastDate\", t2.timestamp as \"timestamp\", t2.timestamphr as \"timestamphr\", t4.ShortDescription, t4.LongDescription, t4.compositerating as \"artistcompositerating\", t4.voters as \"avoters\", t4.totalplays as \"artisttotalplays\", t4.demozoo, t4.wikipedia, t4.csdb, t4.otherurl, t4.modarchive, t4.bandcamp, t4.soundcloud, t4.youtube, t4.pouet from titles t1 left join trackdata t2 on t1.id = t2.trackid left join artiststitles t3 on t1.Guid = t3.title left join artistdata t4 on t3.artist = t4.artistid WHERE t1.StationID = " . $StationID . " ORDER BY t1.lastplayed DESC LIMIT 1";
         }
      } else if ($TrackID != 0) { # Returns result when you specify a trackid. It will ALWAYS return everything.
         $sql = "SELECT t1.id as \"trackid\", t2.id as \"titleid\", t1.title, t1.fullartist, t1.Type, t1.CueIn, t1.CueOut, t1.Comments, t1.Album,   t1.totalplays as \"tracktotalplays\", t1.EligibilityTime, t1.ArtistEligibilityTime, t2.compositerating as \"compositerating\", t2.voters as \"tvoters\", t2.about as \"about\",  t2.ProductionNotes as \"ProductionNotes\", t2.PlayList as \"PlayList\", PodcastURL, t2.podcast as \"podcast\", t2.image as \"image\",  t2.BroadcastDate as \"BroadcastDate\", t2.timestamp as \"timestamp\", t2.timestamphr as \"timestamphr\", t4.ShortDescription, t4.LongDescription, t4.compositerating as \"artistcompositerating\", t4.voters as \"avoters\", t4.totalplays as \"artisttotalplays\", t4.demozoo, t4.wikipedia, t4.csdb, t4.otherurl, t4.modarchive, t4.bandcamp, t4.soundcloud, t4.youtube, t4.pouet from titles t1 left join trackdata t2 on t1.id = t2.trackid left join artiststitles t3 on t1.Guid = t3.title left join artistdata t4 on t3.artist = t4.artistid WHERE (t1.id = " . $TrackID .  ") and t1.StationID = " . $StationID . " ORDER BY t1.fullartist";
      } else {

         # This mechanism kicks in when we want a search result.
         if ($SimpleSearch == 1) {
            $sql = "SELECT t1.id as \"trackid\", t1.title, t1.fullartist, t1.Type, t1.CueIn, t1.CueOut, t1.Comments, t1.Album,   t1.totalplays as \"tracktotalplays\", t1.EligibilityTime, t1.ArtistEligibilityTime from titles t1 WHERE (t1.title like '%" . $SearchTitle . "%' OR t1.fullartist like '%" . $SearchArtist . "%') and t1.StationID = " . $StationID . " ORDER BY t1.fullartist LIMIT " . $MaxResults . "";
         } elseif ($ReturnArtistDescription == 0) { # Returns response when you're searching.
            $sql = "SELECT t1.id as \"trackid\", t2.id as \"titleid\", t1.title, t1.fullartist, t1.Type, t1.CueIn, t1.CueOut, t1.Comments, t1.Album,   t1.totalplays as \"tracktotalplays\", t1.EligibilityTime, t1.ArtistEligibilityTime, t2.compositerating as \"compositerating\", t2.voters as \"tvoters\", t2.about as \"about\", t2.ProductionNotes as \"ProductionNotes\", t2.PlayList as \"PlayList\", t2.podcast as \"podcast\", t2.image as \"image\",  t2.BroadcastDate as \"BroadcastDate\", t2.timestamp as \"timestamp\", t2.timestamphr as \"timestamphr\" from titles t1 left join trackdata t2 on t1.id = t2.trackid WHERE (t1.title like '%" . $SearchTitle . "%' OR t1.fullartist like '%" . $SearchArtist . "%') and t1.StationID = " . $StationID . " ORDER BY t1.fullartist LIMIT " . $MaxResults . "";
         } else { # No simplesearch, No search and no trackid = now playing.
            $sql = "SELECT t1.id as \"trackid\", t2.id as \"titleid\", t1.title, t1.fullartist, t1.Type, t1.CueIn, t1.CueOut, t1.Comments, t1.Album,   t1.totalplays as \"tracktotalplays\", t1.EligibilityTime, t1.ArtistEligibilityTime, t2.compositerating as \"compositerating\", t2.voters as \"tvoters\", t2.about as \"about\",  t2.ProductionNotes as \"ProductionNotes\", t2.PlayList as \"PlayList\", t2.podcast as \"podcast\", t2.image as \"image\",  t2.BroadcastDate as \"BroadcastDate\", t2.timestamp as \"timestamp\", t2.timestamphr as \"timestamphr\", t4.ShortDescription, t4.LongDescription, t4.compositerating as \"artistcompositerating\", t4.voters as \"avoters\", t4.totalplays as \"artisttotalplays\", t4.demozoo, t4.wikipedia, t4.csdb, t4.otherurl, t4.modarchive, t4.bandcamp, t4.soundcloud, t4.youtube, t4.pouet from titles t1 left join trackdata t2 on t1.id = t2.trackid left join artiststitles t3 on t1.Guid = t3.title left join artistdata t4 on t3.artist = t4.artistid WHERE (t1.title like '%" . $SearchTitle . "%' OR t1.fullartist like '%" . $SearchArtist . "%') and t1.StationID = " . $StationID . " ORDER BY t1.fullartist";
         }
      }

      $isql = 0;
      $about = "";
      $compositerating = "";
      $timestamp = "";
      $timestamphr = "";
      $json = "{ \"Tracks\": [";

      # The main loop.

      foreach ($dbht->query($sql) as $row) {

         $trackid = $row["trackid"];
         if (isset($row["titleid"])) {
            $titleid = $row["titleid"];
         } else {
            $titleid = "";
         }
         $title = $row["title"];
         $fullartist = $row["fullartist"];
         if (isset($row["timestamp"])) {
            $timestamp = $row["timestamp"];
         } else {
            $timestamp = "0000000000";
         }
         if (isset($row["timestamphr"])) {
            $timestamphr = $row["timestamphr"];
         } else {
            $timestamphr = "0000-00-00 00:00:00";
         }


         $TrackTotalPlays = $row["tracktotalplays"];
         $EligibilityTime = $row["EligibilityTime"];
         $ArtistEligibilityTime = $row["ArtistEligibilityTime"];


         if (is_numeric($row["CueIn"]) and is_numeric($row["CueOut"])) {
            $PlayTime = ($row["CueOut"] - $row["CueIn"]);

            if ($PlayTime < 259200) {
               if ($PlayTime < 3600) {
                  $PlayTimeHR = gmdate("i:s", $PlayTime);
               } elseif ($PlayTime < 60) {
                  $PlayTimeHR = gmdate("s", $PlayTime);
               } else {
                  $PlayTimeHR = gmdate("H:i:s", $PlayTime);
               }
            } else {
               $PlayTimeHR = "00:00:00";
            }
         } else {
            $PlayTime = 0;
            $PlayTimeHR = "00:00:00";
         }

         $canplay = CheckEligibility($fullartist, $timestamp, $EligibilityTime, $ArtistEligibilityTime, $StationID, $PlayTime);
         if ($canplay[0] == true) {
            $TrackIsEligible = "1";
         } else {
            $TrackIsEligible = "0";
         }
         $Reason = $canplay[1];

         if ($EligibilityFilter == 1 and $TrackIsEligible == 0) {
            continue;
         }

         if ($ReturnArtistDescription == 1) {
            $ArtistDescription = $row["ShortDescription"];
            $ArtistCompositeRating = $row["artistcompositerating"];
            $artisttotalplays = $row["artisttotalplays"];
            $about = $row["about"];
            $compositerating = $row["compositerating"];
            $demozoo = $row["demozoo"];
            $wikipedia = $row["wikipedia"];
            $csdb = $row["csdb"];
            $otherurl = $row["otherurl"];
            $modarchive = $row["modarchive"];
            $bandcamp = $row["bandcamp"];
            $soundcloud = $row["soundcloud"];;
            $youtube = $row["youtube"];
            $pouet = $row["pouet"];
            $podcast = $row["podcast"];
            $image = $row["image"];
            $BroadcastDate = $row["BroadcastDate"];
            $Type = $row["Type"];
            $Avoters = $row["avoters"];
            $Tvoters = $row["tvoters"];
            $PodcastURL = $row["PodcastURL"];

            if ($ReturnPlayList == 1) {
               $PlayList = $row["PlayList"];
            } else {
               $PlayList = "";
            }

            if ($ReturnProductionNotes == 1) {
               $ProductionNotes = $row["ProductionNotes"];
            } else {
               $ProductionNotes = "";
            }

            $Album = $row["Album"];
            $songtype = gettracktype($Album);


            #OriginalName&#37;3A&#37;20glorious-disaster.mod.&#37;20Imported&#37;3A&#37;202021-07-16&#37;20&#37;28TERN-jul2021-03&#37;29.
            $BatchData = "";
            $Album = $row["Album"];

            $bpos = strpos($row["Album"], "TERN-");

            if ($bpos != 0) {
               $BatchData = substr($row["Album"], $bpos, 15);
            } else {
               $BatchData = "";
            }
         } else {
            $ArtistDescription = "";
            $ArtistCompositeRating = 0;
            $artisttotalplays = 0;
            $soundcloud = "";
            $demozoo = "";
            $wikipedia = "";
            $modarchive = "";
            $otherurl = "";
            $csdb = "";
            $pouet = "";
            $youtube = "";
            $wikipedia = "";
            $bandcamp = "";
            $songtype = "Unknown";
            $BatchData = "";
            $Album = "";
            $Avoters = "";
            $Tvoters = "";
            $image = "";
            $BroadcastDate = "";
            $Type = "";
            $podcast = "";
            $ProductionNotes = "";
            $PlayList = "";
            $titleid = "";
            $PodcastURL = "";
         }
         if ($ReturnArtistDescription == 1 and $ReturnArtistLongDescription == 1) {
            $ArtistLongDescription =  $row["LongDescription"];
         } else {
            $ArtistLongDescription = "";
         }




         # Get last xx (Max 15) plays for that track

         if ($LastPlaysToReturn < 15) {
            $limiter = " LIMIT " . $LastPlaysToReturn;
         }

         if ($SimpleSearch == 0) {
            $sql = "SELECT `id`, `trackid`, `timestamp`, `timestamphr`, `artist`, `title` FROM `playoutlog` WHERE trackid = " . $trackid . " and StationID = " . $StationID . " ORDER BY timestamp DESC" . $limiter;
         } else {
            $sql = "SELECT 1=1";
         }

         $ij = 0;

         $jsonpl = "\"Playlog\":[";
         foreach ($dbht->query($sql) as $trow) {

            if (isset($trow["timestamp"])) {
               $timestamp = $trow["timestamp"];
            } else {
               $timestamp = "000000";
            }
            if (isset($trow["timestamphr"])) {
               $timestamphr = $trow["timestamphr"];
            } else {
               $timestamphr = "0000-00-00 00:00:00";
            }
            $ij++;

            $jsonpl .= "{\"timestamphr\":\"" . $timestamphr . "\"},";
         }
         $jsonpl = substr($jsonpl, 0, -1);

         $jsonpl .= "]";


         # Override system.

         if ($Search == "" and $TrackID == 0) {


            # Check for stuff to show, that does not get into the database, but needs to be shown.

            # Need to check the nowplaying again...

            $nowfullartist = "";

            $sql = "SELECT `fullartist`, `title`, `lastplayed`, `trackid`, `Duration` FROM `nowplaying` WHERE StationID=" . $StationID . "";

            $i = 0;


            foreach ($dbht->query($sql) as $ovrow) {
               $i++;
               $nowfullartist = $ovrow["fullartist"];
               $title = $ovrow["title"];
               $trackid = $ovrow["trackid"];
               $Duration = $ovrow["Duration"];
            }

            $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

            # This event will fire if an artist on the RequestPrefix-list is played.
            if (string_search_partial($Stations[$StationIDInArray]["RequestPrefix"], strtolower($nowfullartist)) == true) {


               if (is_numeric($Duration)) {
                  $PlayTime = $Duration;

                  if ($PlayTime < 86400) {
                     if ($PlayTime < 3600) {
                        $PlayTimeHR = gmdate("i:s", $PlayTime);
                     } elseif ($PlayTime < 60) {
                        $PlayTimeHR = gmdate("s", $PlayTime);
                     } else {
                        $PlayTimeHR = gmdate("H:i:s", $PlayTime);
                     }
                  } else {
                     $PlayTimeHR = "00:00:00";
                  }
               } else {
                  $PlayTime = 0;
                  $PlayTimeHR = "00:00:00";
               }


               $fullartist = $nowfullartist;
               $title = $title;
               $songtype = "Listener request";
               $timestamp = "111111";
               $image = "https://radio.ericade.net/radiodb/images/request.jpg";
               $about = "A request made by a listener! Go to \"Request a song\" in the menu on our website ericade.radio to make your own request.";
               $podcast = "";
               $trackid = 0;
               $ArtistLongDescription = "Requests are your way to make the station yours! Remember that great tune you heard yesterday? Now you can hear it again.";
               $ArtistDescription = "Requests are your way to make the station yours! Remember that great tune you heard yesterday? Now you can hear it again.";
               $ProductionNotes = "";
               $artisttotalplays = "";
               $PlayTimeHR = $PlayTimeHR;
               $PlayTime = $Duration;
               $timestamphr = "-";
               $BroadcastDate = "-";
               $isStarable = 0;
               $Reason = "";
               $titleid = "";
               $PodcastURL = "";
            }
         } # $Search == 0.



         $json .= "{\"Artist\": \"" . superentities($fullartist) . "\", \"Title\": \"" . superentities($title) . "\", \"TrackCanBeRequested\": \"" . $TrackIsEligible . "\", \"RequestVerdict\": \"" . $Reason . "\", \"TrackCanBeStarred\": \"" . $isStarable . "\", \"PodcastURL\": \"" . $PodcastURL . "\", \"Image\": \"" . superentities($image) . "\", \"BroadcastDate\": \"" . superentities($BroadcastDate) .  "\", \"TitleID\": \"" . $titleid . "\", \"TrackID\": \"" . superentities($trackid) . "\", \"TrackerType\": \"" . superentities($songtype) .  "\", \"Type\": \"" . superentities($Type) . "\", \"PlayLength\": \"" . superentities($PlayTime) . "\", \"PlayLengthHR\": \"" . superentities($PlayTimeHR) . "\", \"Batch\": \"" . superentities($BatchData) . "\", \"Album\": \"" . superentities($Album) . "\", \"About\": \"" . superentities($about) . "\", \"ProductionNotes\": \"" . superentities($ProductionNotes) . "\", \"PlayList\": \"" . superentities($PlayList) . "\", \"CompositeRating\": \"" . $compositerating . "\", \"TrackVoters\": \"" . $Tvoters . "\",\"ArtistCompositeRating\": \"" . $ArtistCompositeRating . "\", \"ArtistVoters\": \"" . $Avoters .  "\", \"TimeStamp\": \"" . $timestamp . "\", \"TimeStampHumanReadable\":\"" . $timestamphr . "\", \"TrackTotalPlays\": \"" . $TrackTotalPlays . "\", \"ArtistLongDescription\":\"" . superentities($ArtistLongDescription) . "\", \"ArtistShortDescription\":\"" . superentities($ArtistDescription) . "\",\"Demozoo\": \"" . superentities($demozoo) . "\",\"Wikipedia\": \"" . superentities($wikipedia) . "\",\"CSDB\": \"" . superentities($csdb) . "\",\"OtherUrl\": \"" . superentities($otherurl) .  "\",\"ModArchive\": \"" . superentities($modarchive) . "\",\"Bandcamp\": \"" . superentities($bandcamp) . "\",\"SoundCloud\": \"" . superentities($soundcloud) . "\",\"YouTube\": \"" . superentities($youtube) . "\",\"Pouet\": \"" . superentities($pouet) . "\",\"Podcast\": \"" . $podcast . "\",\"ArtistTotalPlays\": \"" . $artisttotalplays . "\", " . $jsonpl . "},";

         $isql++;

         if ($isql >= 50) {
            break;
         } # This is critical in order not to let wide queries kill the DB-layer. :)

      } # ForEach track


      if ($isql == 0) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Track not found.');
      }


      $json = substr($json, 0, -1);
      $json .= "]}";


      #echo "SEQ:".$Sequence;
      #echo "TS:".$timestamp;

      if ($Sequence == $timestamp and $Search == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'I have no new data for you.');
      }


      # Return JSON answer with all the jazz

      return array("result" => 'TRUE', "message" => $json, "Submessage" => 'All done.');
   }

   #************************************************************************************
   # listracks

   /** @return string[]  */
   function listracks()
   {
      # This function supports dumping the entire track database for the report view.

      include '/var/www/html/config.php';
      global $dbht;

      #$guid=checkpassword ($this->Password);
      $token = validatejwt($this->jwt);
      $gtoken = json_decode($token);
      if ($gtoken->message == "Access denied.") {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }
      # $userid=$gtoken->data->id;
      # $useremail=$gtoken->data->email;

      $todaysdate = date("Y-m-d H:i:s");

      # Validate the fields

      $StationID = sanitize_numeric_string($this->StationID, 1, 2);
      if (isset($this->Count)) {
         $Count = sanitize_numeric_string($this->Count, 1, 6);
      } else {
         $Count = null;
      }
      if (isset($this->Skip)) {
         $Skip = sanitize_numeric_string($this->Skip, 1, 6);
      } else {
         $Skip = null;
      }
      //	$CreateTotalCount = sanitize_numeric_string($this->CreateTotalCount, 1, 2);

      $Search = sanitize_paranoid_string($this->Search, 1, 30);

      /*	if ($CreateTotalCount == "") {
			$CreateTotalCount = 0;
		} */

      # Hardcoded skip and count-limits that kick in if the haxx0r has defeated the sanitization.

      if ($Count != "" and ($Count < 0 or $Count > 10000)) {
         return array("result" => 'FALSE', "message" => 'Invalid count range.');
      }

      if ($Skip != "" and ($Skip < 0 or $Skip > 10000)) {
         return array("result" => 'FALSE', "message" => 'Invalid skip range.');
      }


      # Mechanism to build the offset/limiter. 

      $Limiter = "LIMIT 10000";

      # Mechanism to filter queries

      /*	$totalqueries = 0;

		if (isset($this->UserID)) {
			$currCount = count($this->UserID);
			$totalqueries = $totalqueries + $currCount;
			if ($currCount > $max_item_queries) {
				return array("result" => 'FALSE', "message" => 'You have selected too many tracks for this query.');
			}
		}*/

      $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

      if ($StationIDInArray !== false) {
      } else {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Station does not exist of configuration is invalid.');
      }

      $StationName = $Stations[$StationIDInArray]["StationName"];

      /*if ($AllowRequests != 1) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'This station does not allow requests.');
      }*/


      $clause = "StationID = " . $StationID . " AND ";
      $sqlstem = "SELECT id,title,fullartist,CueOut,CueIn,Album,CreationDateHr,Album FROM titles ";
      $searchclause = "(1=1)";
      if ($Search != "") {

         # Convert the title to proper coding.
         $arr = array(" " => "&#37;20", "!" => "&#37;21", "*" => "&#37;2A", "&#37;2F" => "/");
         $stemp = strtr($Search, $arr);

         $searchclause = "(fullartist like '%" . $Search . "%' or title like '%" . $stemp . "%')";
      }

      $Limiter = "";


      if ($Skip != "" and $Count != "") {

         /* $lmtd = $Count - $res;
         $slmtd = $Skip - $dres;

         if ($slmtd < 0) {
            $slmtd = 0;
         }

         if ($lmtd < 0) {
            $lmtd = 0;
         } */
         $Limiter = " LIMIT " . $Skip . "," . $Count;
      }

      $concatstring = " ORDER BY fullartist,title";
      //   $concatstring=" GROUP BY CONCAT(ApplicationName,version) ORDER BY 2 DESC";

      $dres = 0;
      $csql = $sqlstem . "WHERE " . $clause . $searchclause . $concatstring . ";";

      //echo $csql;

      foreach ($dbht->query($csql) as $drow) {
         //$tres = $tres + 1;
         $dres = $dres + 1;
      }

      $TotalCount = $dres;

      # Here's  

      $sql = $sqlstem . "WHERE " . $clause . $searchclause . $concatstring . $Limiter . ";";

      $res = 0;
      //echo $sql;

      $response = "\"Tracks\":[";

      foreach ($dbht->query($sql) as $drow) {
         if ($drow["title"]) {
            $lengthinseconds = ($drow["CueOut"] - $drow["CueIn"]);
            $response .= "{\"ID\":\"" . $drow["id"] . "\",";
            $response .= "\"StationID\":\"" . $StationID . "\",";
            $response .= "\"StationName\":\"" . $StationName . "\",";
            $response .= "\"Title\":\"" . $drow["title"] . "\",";
            $response .= "\"Fullartist\":\"" . $drow["fullartist"] . "\",";
            $response .= "\"Album\":\"" . $drow["Album"] . "\",";
            $response .= "\"Creationdatehr\":\"" . $drow["CreationDateHr"] . "\",";
            $response .= "\"Length\":\"" . ($lengthinseconds) . "\",";
            $response .= "\"Lengthhr\":\"" . TimeToPlain($lengthinseconds) . "\",";
            $response .= "\"Tracktype\":\"" . gettracktype($drow["Album"]) . "\"},";
            $res = $res + 1;
         }
      }


      $response = substr($response, 0, -1);
      $response .= "]}";

      $CountResponse = "{ \"Query\": [{\"ResultCount\": \"" . $res . "\",";
      $CountResponse .= "\"TotalCount\": \"" . $TotalCount  . "\"}],";

      $FullResponse = $CountResponse . $response;

      #				// Empty response.
      if ($res == 0) {
         return array("result" => 'TRUE', "message" => "{ \"Software\":[\"No track information was found.\"]}");
      }



      # Return JSON answer with all the jazz

      return array("result" => 'TRUE', "message" => $FullResponse, "Submessage" => 'All done.');
   }


   #************************************************************************************
   # listnews

   /** @return string[]  */
   function listnews()
   {
      # This function supports dumping the entire track database for the report view.

      include '/var/www/html/config.php';
      global $dbht;

      #$guid=checkpassword ($this->Password);
      $token = validatejwt($this->jwt);
      $gtoken = json_decode($token);
      if ($gtoken->message == "Access denied.") {
         return array("result" => 'FALSE', "message" => 'User token invalid or empty.');
      }
      # $userid=$gtoken->data->id;
      # $useremail=$gtoken->data->email;

      $todaysdate = date("Y-m-d H:i:s");

      # Validate the fields

      $clause = "";

      //     $clause="(" .  . " OR " . $News . ") AND ";


      $StationID = sanitize_numeric_string($this->StationID, 1, 2);
      if (isset($this->Count)) {
         $Count = sanitize_numeric_string($this->Count, 1, 6);
      } else {
         $Count = null;
      }
      if (isset($this->ID)) {
         $ID = sanitize_numeric_string($this->ID, 1, 6);
      } else {
         $ID = null;
      }
      if (isset($this->Skip)) {
         $Skip = sanitize_numeric_string($this->Skip, 1, 6);
      } else {
         $Skip = null;
      }
      if (isset($this->Podcasts)) {
         $Podcasts = sanitize_numeric_string($this->Podcasts, 1, 2);
         if ($Podcasts != 0 and $Podcasts != 1 and $Podcasts != 2) {
            $Podcasts = 0;
         } elseif ($Podcasts == 1) {
         }
      } else {
         $Podcasts = 0;
      }
      if (isset($this->News)) {
         $News = sanitize_numeric_string($this->News, 1, 2);
         if ($News != 0 and $News != 1) {
            $News = "";
         } elseif ($News == 1) {
            $News = 1;
         }
      } else {
         $News = "";
      }


      $clausefinal = "(";
      if ($Podcasts == 1 or $Podcasts == 2) {
         $clausefinal .= "`isPodcast` = 1";
      }
      if ($News == 1 and ($Podcasts == 1 or $Podcasts == 2)) {
         $clausefinal .= " OR `isNews` = 1";
      } else if ($News == 1 and $Podcasts == 0) {
         $clausefinal .= "`isNews` = 1";
      }
      $clausefinal .= ") AND ";

      if ($clausefinal == "() AND ") {
         $clausefinal = "";
      }

      //	$CreateTotalCount = sanitize_numeric_string($this->CreateTotalCount, 1, 2);

      $Search = sanitize_paranoid_string($this->Search, 1, 30);

      /*	if ($CreateTotalCount == "") {
       $CreateTotalCount = 0;
    } */

      # Hardcoded skip and count-limits that kick in if the haxx0r has defeated the sanitization.

      if ($Count != "" and ($Count < 0 or $Count > 10000)) {
         return array("result" => 'FALSE', "message" => 'Invalid count range.');
      }

      if ($Skip != "" and ($Skip < 0 or $Skip > 10000)) {
         return array("result" => 'FALSE', "message" => 'Invalid skip range.');
      }


      # Mechanism to build the offset/limiter. 

      $Limiter = "LIMIT 10000";

      # Mechanism to filter queries

      /*	$totalqueries = 0;

    if (isset($this->UserID)) {
       $currCount = count($this->UserID);
       $totalqueries = $totalqueries + $currCount;
       if ($currCount > $max_item_queries) {
          return array("result" => 'FALSE', "message" => 'You have selected too many tracks for this query.');
       }
    }*/

      $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

      /* if ($StationIDInArray !== false) {
    } else {
       return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Station does not exist of configuration is invalid.');
    } */

      $StationName = $Stations[$StationIDInArray]["StationName"];

      /*if ($AllowRequests != 1) {
       return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'This station does not allow requests.');
    }*/


      $clause = $clausefinal;

      // die("ffg");
      $sqlstem = "t1.`id`, t1.`trackid`, t1.`artist`, t1.`title`, `compositerating`, `explicit`, `voters`, `about`, `trackertype`, `timestamp`, `timestamphr`, t1.`Guid`, t1.`StationID`, `podcast`, `image`, `BroadcastDate`, `Equipment`, `PlayList`, `ProductionNotes`, `Footer`, `isNews`, `EpisodeNumber`, `News`, `isPodcast`, `PodcastURL`, `PodcastFileLength` FROM `trackdata` t1 ";
      $searchclause = "(1=1)";
      if ($Search != "") {

         # Convert the title to proper coding.
         $arr = array(" " => "&#37;20", "!" => "&#37;21", "*" => "&#37;2A", "&#37;2F" => "/");
         $stemp = strtr($Search, $arr);

         $searchclause = "(t1.artist like '%" . $stemp . "%' or t1.title like '%" . $stemp . "%')";
      }

      $idclause = "(1=1)";
      if ($ID != "") {

         $sqlstem = "round((t2.CueOut - t2.CueIn),1) as \"Length\"," . $sqlstem . "left join titles t2 on t1.trackid = t2.id ";

         # Convert the title to proper coding.
         $arr = array(" " => "&#37;20", "!" => "&#37;21", "*" => "&#37;2A", "&#37;2F" => "/");
         $stemp = strtr($Search, $arr);

         $searchclause = "(t1.id = " . $ID . ")";
      }

      if ($Podcasts == 2) {
         $sqlstem = "round((t2.CueOut - t2.CueIn),1) as \"Length\"," . $sqlstem . "left join titles t2 on t1.trackid = t2.id ";
      }

      $sqlstem = "SELECT " . $sqlstem;

      $Limiter = "";


      if ($Skip != "" and $Count != "") {

         /* $lmtd = $Count - $res;
       $slmtd = $Skip - $dres;

       if ($slmtd < 0) {
          $slmtd = 0;
       }

       if ($lmtd < 0) {
          $lmtd = 0;
       } */
         $Limiter = " LIMIT " . $Skip . "," . $Count;
      }

      $concatstring = " ORDER BY BroadcastDate desc,EpisodeNumber desc, timestamp desc, t1.title";
      //   $concatstring=" GROUP BY CONCAT(ApplicationName,version) ORDER BY 2 DESC";

      $dres = 0;
      $csql = $sqlstem . "WHERE " . $clause . $searchclause . $concatstring . ";";

      #echo $csql;

      foreach ($dbht->query($csql) as $drow) {
         //$tres = $tres + 1;
         $dres = $dres + 1;
      }

      $TotalCount = $dres;

      # Here's  

      $sql = $sqlstem . "WHERE " . $clause . $searchclause . $concatstring . $Limiter . ";";

      $res = 0;



      $primepopulation = 0; # Should be at 0, unless you need to feed the database with the <ep class="number">

      $response = "\"Tracks\":[";

      foreach ($dbht->query($sql) as $drow) {

         $episode = "";

         if ($drow["id"]) {


            if ($drow["isPodcast"]) {

               // This logic is for the podcast displaypage in order to provide the file name.

               // What episiode number do we have?

               if ($primepopulation == 1) {
                  // Here we will assume that the EpisodeNumber is empty. Normally it should not be.

                  $pattern = "#^([^\.]*)#";

                  if (preg_match($pattern, $drow["title"], $matches)) {
                  }

                  $psql = "UPDATE `trackdata` SET `EpisodeNumber` = " . $matches[1] . " WHERE `id` = " . $drow["id"];

                  foreach ($dbht->query($psql) as $prow) {
                  }

                  $episode = $matches[1];
               } else {
                  // This is the normal flow operations where we get the episode from the field.

                  $psql = "SELECT `EpisodeNumber` from `trackdata` WHERE `id` = " . $drow["id"];

                  foreach ($dbht->query($psql) as $prow) {

                     $episode = $prow["EpisodeNumber"];
                  }

                  $msql = "SELECT `LastBuild` from `stats` WHERE `id` = 2"; # Ugly fix! Fix betta! L8ta!

                  foreach ($dbht->query($msql) as $mrow) {

                     $LastBuild = $mrow["LastBuild"];
                  }
               }

               // This logic is for the RSS feed
            }

            $response .= "{\"ID\":\"" . $drow["id"] . "\",";
            //$response .= "\"StationID\":\"" . $StationID . "\",";
            //$response .= "\"StationName\":\"" . $StationName . "\",";
            $response .= "\"Title\":\"" . $drow["title"] . "\",";
            $response .= "\"Artist\":\"" . $drow["artist"] . "\",";
            $response .= "\"About\":\"" . superentities($drow["about"]) . "\",";
            $response .= "\"Episode\":\"" . $episode . "\",";
            $response .= "\"Explicit\":\"" . superentities($drow["explicit"]) . "\",";
            if (isset($LastBuild)) {
               $response .= "\"LastBuild\":\"" . $LastBuild . "\",";
            }
            if (isset($drow["Length"])) {
               $response .= "\"Length\":\"" . superentities($drow["Length"]) . "\",";
            }
            if (isset($drow["Length"])) {
               $response .= "\"LengthHR\":\"" . superentities(TimeToPlain($drow["Length"])) . "\",";
            }
            $response .= "\"BroadcastDate\":\"" . $drow["BroadcastDate"] . "\",";
            $response .= "\"Image\":\"" . $drow["image"] . "\",";
            $response .= "\"Equipment\":\"" . superentities($drow["Equipment"]) . "\",";
            $response .= "\"PlayList\":\"" . superentities($drow["PlayList"]) . "\",";
            $response .= "\"ProductionNotes\":\"" . superentities($drow["ProductionNotes"]) . "\",";
            $response .= "\"Footer\":\"" . superentities($drow["Footer"]) . "\",";
            $response .= "\"isNews\":\"" . $drow["isNews"] . "\",";
            $response .= "\"isPodcast\":\"" . $drow["isPodcast"] . "\",";
            $response .= "\"News\":\"" . superentities($drow["News"]) . "\",";
            $response .= "\"PodcastFileLength\":\"" . superentities($drow["PodcastFileLength"]) . "\",";
            $response .= "\"PodcastURL\":\"" . superentities($drow["PodcastURL"]) . "\",";
            $response .= "\"EpisodeNumber\":\"" . $drow["EpisodeNumber"] . "\"},";
            $res = $res + 1;
         }
      }


      $response = substr($response, 0, -1);
      $response .= "]}";

      $CountResponse = "{ \"Query\": [{\"ResultCount\": \"" . $res . "\",";
      $CountResponse .= "\"TotalCount\": \"" . $TotalCount  . "\"}],";

      $FullResponse = $CountResponse . $response;

      #				// Empty response.
      if ($res == 0) {
         return array("result" => 'TRUE', "message" => "{ \"Software\":[\"No news information was found.\"]}");
      }



      # Return JSON answer with all the jazz

      return array("result" => 'TRUE', "message" => $FullResponse, "Submessage" => 'All done.');
   }

   #************************************************************************************
   # getnextrequest

   /** @return string[]  */
   function getnextrequest()
   {
      include '/var/www/html/config.php';
      global $dbht;

      $guid = checkpassword($this->Password);
      if ($guid == FALSE) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Missing or incorrect password.');
      }

      $todaysdate = date("Y-m-d H:i:s");

      $sql = "SELECT `TrackID`, `StationID`, `title`, `fullartist`, `Guid`, `lastplayed`, `Duration`, `greeting`, `nameofrequester`, `Path`, `Source`, `CueIn`, `CueOut` FROM `QueuedRequests` ORDER by ID limit 1";

      $i = 0;

      $jsonpl = "{ \"Requests\":[";
      foreach ($dbht->query($sql) as $row) {

         $i++;

         $jsonpl .= "{\"TrackID\":\"" .  $row["TrackID"] . "\",";
         $jsonpl .= "\"StationID\":\"" .  $row["StationID"] . "\",";
         $jsonpl .= "\"title\":\"" .  $row["title"] . "\",";
         $jsonpl .= "\"fullartist\":\"" .  $row["fullartist"] . "\",";
         $jsonpl .= "\"Guid\":\"" .  $row["Guid"] . "\",";
         $jsonpl .= "\"nameofrequester\":\"" .  $row["nameofrequester"] . "\",";
         $jsonpl .= "\"Path\":\"" .  $row["Path"] . "\",";
         $jsonpl .= "\"Source\":\"" .  $row["Source"] . "\",";
         $jsonpl .= "\"CueIn\":\"" .  $row["CueIn"] . "\",";
         $jsonpl .= "\"CueOut\":\"" .  $row["CueOut"] . "\",";
         $jsonpl .= "\"Duration\":\"" .  $row["Duration"] . "\",";
         $jsonpl .= "\"greeting\":\"" .  $row["greeting"] . "\"},";
      }
      if ($i != 0) {
         $jsonpl = substr($jsonpl, 0, -1);
      }
      $jsonpl .= "] }";

      return array("result" => 'TRUE', "message" => $jsonpl, "Submessage" => 'All done.');
   }

   #************************************************************************************
   # updateplayedrequest

   /** @return string[]  */
   function updateplayedrequest()
   {
      include '/var/www/html/config.php';
      global $dbht;

      $guid = checkpassword($this->Password);
      if ($guid == FALSE) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Missing or incorrect password.');
      }

      $todaysdate = date("Y-m-d H:i:s");


      if (isset($this->TrackID)) {
         $TrackID = sanitize_numeric_string($this->TrackID, 1, 5);
      } else {
         $TrackID =  "";
      }
      if (isset($this->Slot)) {
         $Slot = sanitize_numeric_string($this->Slot, 1, 5);
      } else {
         $Slot =  "";
      }
      if (isset($this->StationID)) {
         $StationID = sanitize_numeric_string($this->StationID, 1, 5);
      } else {
         $StationID =  "";
      }

      if ($TrackID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'TrackID must be specified.');
      }

      if ($Slot == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Slot must be specified.');
      }

      if ($StationID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'StationID must be specified.');
      }

      # Check if the specified track exists on this station.

      $sql = "SELECT id FROM `titles` WHERE StationID = " . $StationID . " AND id = " . $TrackID . "";


      $i = 0;

      foreach ($dbht->query($sql) as $row) {

         $i++;
      }

      if ($i == 0) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Track could not be found.');
      }

      # Load the track data from the Queue. Fail if not there.

      $sql = "SELECT `id`, `TrackID`, `StationID`, `title`, `fullartist`, `Guid`, `lastplayed`, `Duration`, `greeting`, `nameofrequester`, `Path`, `Source`, `CueIn`, `CueOut` FROM `QueuedRequests` WHERE StationID = " . $StationID . " AND TrackID = " . $TrackID . "";

      $i = 0;

      foreach ($dbht->query($sql) as $row) {

         $id = $row["id"];
         $StationID = $row["StationID"];
         $Source = $row["Source"];
         $title = $row["title"];
         $fullartist = $row["fullartist"];
         $Guid = $row["Guid"];
         $lastplayed = $row["lastplayed"];
         $Duration = $row["Duration"];
         $greeting = $row["greeting"];
         $nameofrequester = $row["nameofrequester"];
         $Path = $row["Path"];
         $CueIn = $row["CueIn"];
         $CueOut = $row["CueOut"];

         $i++;
      }

      if ($i == 0) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Track could not be found in queue.');
      }

      # Insert the track into the Played table

      $todaysdate = date("Y-m-d H:i:s");
      $nowdate = time();

      $sql = "INSERT INTO `PlayedRequests`(`TrackID`, `StationID`, `title`, `fullartist`, `Guid`, `lastplayed`, `Duration`, `lastplayedasrequest`, `lastplayedasrequesthr`, `greeting`, `nameofrequester`, `Path`, `Source`, `CueIn`, `CueOut`) VALUES (" . $TrackID . "," . $StationID . ",'" . $title . "','" . $fullartist . "','" . $Guid . "'," . $lastplayed . "," . $Duration . "," . $nowdate . ",'" . $todaysdate . "','" . $greeting . "','" . $nameofrequester . "','" . $Path . "','" . $Source . "'," . $CueIn . "," . $CueOut . ")";

      foreach ($dbht->query($sql) as $row) {
      }

      # Update the ArtistEligibilityTime

      $sql = "UPDATE `titles` SET `ArtistEligibilityTime`=" . $nowdate . " WHERE StationID = " . $StationID . " AND id = " . $TrackID . "";

      foreach ($dbht->query($sql) as $row) {
      }

      # Update the artist data

      # Must be built. Not tested as of yet.

      # Delete it from the queue

      $sql = "DELETE FROM `QueuedRequests` WHERE StationID = " . $StationID . " AND TrackID = " . $TrackID . "";

      foreach ($dbht->query($sql) as $row) {
      }

      # Return ok.

      return array("result" => 'TRUE', "message" => 'TRUE', "Submessage" => 'Successfully moved the entry to the PlayedRequests pile.');
   }



   #************************************************************************************
   # UpdateTrack

   /** @return string[]  */
   function updatetrack()
   {
      include '/var/www/html/config.php';
      global $dbht;

      $fdebug = 0;

      $guid = checkpassword($this->Password);
      if ($guid == FALSE) {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Missing or incorrect password.');
      }

      $todaysdate = date("Y-m-d H:i:s");

      # Validate the fields

      $Title = trim(sanitize_html_string($this->Title, 1, 255));
      $Comments = trim(sanitize_html_string($this->Comments, 1, 8192));
      $Album = trim(sanitize_html_string($this->Album, 1, 255));
      $Genre = trim(sanitize_html_string($this->Genre, 1, 255));
      $Year = trim(sanitize_html_string($this->Year, 1, 255));
      $Guid = trim(sanitize_html_string($this->Guid, 1, 255));
      if (isset($this->Duration) and $this->Duration != "") {
         $Duration = trim(sanitize_float_string(str_replace("%2C", ".", $this->Duration), 1, 20));
      } else {
         $Duration = 0;
      }
      if ($Duration == NULL) {
         $Duration = "NULL";
      }
      if (isset($this->OutCue) and $this->OutCue != "") {
         $OutCue = trim(sanitize_float_string(str_replace("%2C", ".", $this->OutCue), 1, 20));
      } else {
         $OutCue = 0;
      }
      if ($OutCue == NULL) {
         $OutCue = "NULL";
      }

      $Tags = trim(sanitize_html_string($this->Tags, 1, 255));
      $Disabled = trim(sanitize_html_string($this->Disabled, 1, 10));
      $Type = trim(sanitize_html_string($this->Type, 1, 255));
      if (isset($this->Intro) and $this->Intro != "") {
         $Intro = trim(sanitize_float_string(str_replace("%2C", ".", $this->Intro), 1, 20));
      } else {
         $Intro = 0;
      }
      if ($Intro == NULL) {
         $Intro = "NULL";
      }
      if (isset($this->CueIn) and $this->CueIn != "") {
         $CueIn  = trim(sanitize_float_string(str_replace("%2C", ".", $this->CueIn), 1, 20));
      } else {
         $CueIn = 0;
      }
      if ($CueIn == NULL) {
         $CueIn = "NULL";
      }
      if (isset($this->CueOut) and $this->CueOut != "") {
         $CueOut = trim(sanitize_float_string(str_replace("%2C", ".", $this->CueOut), 1, 20));
      } else {
         $CueOut = 0;
      }
      if ($CueOut == NULL) {
         $CueOut = "NULL";
      }
      $Added = trim(sanitize_html_string($this->Added, 1, 255));
      $Sweeper = trim(sanitize_html_string($this->Sweeper, 1, 10));
      $NoFade = trim(sanitize_html_string($this->NoFade, 1, 10));
      $ValidFrom = trim(sanitize_html_string($this->ValidFrom, 1, 255));
      $Expires = trim(sanitize_html_string($this->Expires, 1, 255));
      $Path = trim(sanitize_html_string($this->Path, 1, 255));
      $StationID = trim(sanitize_numeric_string($this->StationID, 1, 2));
      if (isset($this->Segue) and $this->Segue != "") {
         $Segue = trim(sanitize_float_string(str_replace("%2C", ".", $this->Segue), 1, 20));
      } else {
         $Segue = 0;
      }
      if ($Segue == NULL) {
         $Segue = "NULL";
      }

      #  $msg="...".$this->Artist."...";
      #  mail("erik@zalitis.se","Encoding",$msg);

      $patterns = array();
      $patterns[0] = '/&#37;20/';
      $patterns[1] = '/%20/';
      $replacements = array();
      $replacements[0] = ' ';
      $replacements[1] = ' ';
      $Fullartist = preg_replace($patterns, $replacements, $this->Artist);
      $Fullartist = sanitize_html_string($Fullartist, 1, 255);

      if ($StationID == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'StationID field missing.');
      }

      if ($Fullartist == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Artist field missing.');
      }
      if ($Title == "") {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Title field missing.');
      }

      $TS = time(); # Epoch!
      $Timestamphr = date("Y-m-d H:i:s");

      # Logic to filter out tracks that we should store.

      $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

      if ($fdebug == 1) {
         echo "SID:" . $StationIDInArray . "<br>";
      }

      if ($StationIDInArray !== false) {
      } else {
         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Station configuration is not valid or the station does not exist.');
      }

      if ($fdebug == 1) {
         echo "<br>StationID in array: " . $StationIDInArray;
         echo "<br>Station: " . $Stations[$StationIDInArray]["StationName"];
      }

      # Check if the artist is on the FilteredArtists list will automatically fail it.

      if (string_search_partial($Stations[$StationIDInArray]["FilteredArtistsFromIngest"], strtolower($Fullartist)) == true) {

         # Add the track to "Now playing", but NOT to any other Dbs.

         # Add the current playing track to the DB

         $sql = "DELETE FROM nowplaying WHERE StationID = " . $StationID;
         $i = 0;

         foreach ($dbht->query($sql) as $row) {
            $i++;
         }

         $sql = "INSERT INTO nowplaying(title, lastplayed, fullartist, trackid, StationID, Duration) VALUES ('" . $Title . "'," . $TS . ",'" . $Fullartist . "'," . "0" . "," . $StationID . "," . $Duration . ")";
         $i = 0;

         foreach ($dbht->query($sql) as $row) {
            $i++;
         }

         return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'This track should not be added as it is in the FilteredArtistsFromIngest in the configuration for this station.');
      }

      # Flow-chart

      # Definitions
      # Artist => just the artist's name. e.g. KRDN.
      # Title => The title. e.g. "Eternal fredom 2.0"
      # fullartist  => The whole artist field. e.g. "KRDN of Defiance and Shodan of Eternal"
      # Track => a fullartist and title. e.g. Artist: "KRDN of Defiance and Shodan of Eternal", Title: "Eternal fredom 2.0".

      # Cut artist-field
      # The $fullartist variable is the artist field as it comes from the API call. e.g. "KRDN of Defiance". 
      # In this case, the artist is "KRDN" and should be matched as such by replacing the $Artist field.
      # We also have to handle artist-fields like "KRDN of Defiance and Shodan of Eternal".

      # Disect artist-field and make a function multi-dimensional array.

      # Case in point:
      # KRDN of Defiance,EvaCUE and Shodan of Eternal,Defiance

      # Search for and, or, ","
      # First, search for "and". Split it into one array element for every artist.
      # In each array element, search for "or". The first array is the artist.
      # In the second array element, search for ","

      # This: KRDN of Defiance,EvaCUE and Shodan of Eternal,Defiance
      # Will become:
      # [0]KRDN,Defiance,EvaCUE
      # [1]Shodan,Eternal,Defiance

      $fdebug = 0;

      $Artist = (trim(substr($Fullartist, 0, strrpos($Fullartist, ' of '))));
      if ($Artist == "") {
         $Artist = (trim(substr($Fullartist, 0, strrpos($Fullartist, ' and '))));
      }
      if ($Artist == "") {
         $Artist = $Fullartist;
      }

      $Artist = trim($Artist);

      $sceners = preg_split('/\sand\s/', $Fullartist);

      # Create the track first.

      # Check if artist and title exists
      # If true, no more work is needed except updating the playfields.

      $sql = "SELECT id as \"TitleID\", title, `lastplayed`, `totalplays` as \"TitlePlay\", CueIn, CueOut ,`fullartist`, `artist`, Album, `Comments`, `Year`, `Guid` FROM `titles` WHERE guid = '" . $Guid . "' and StationID = " . $StationID;

      $i = 0;

      #  mail("erik@zalitis.se","Encoding",$sql);
      #$msg="...".$this->Artist."...";
      #mail("erik@zalitis.se","Encoding",$msg);

      $ArtistID = "";
      $TitlePlay = "";
      #$ArtistPlay = "";
      $TitleID = "";

      # Those variables are only needed to check if an existing track has changed its data. 
      # They will never be used if the track does not exist.

      $DBGuid = "";
      $DBYear = "";
      $DBComments = "";
      $DBAlbum = "";
      $DBTitle = "";
      $DBCueIn = "";
      $DBCueOut = "";

      foreach ($dbht->query($sql) as $row) {

         # $ArtistID = $row["ArtistID"];
         $TitlePlay = $row["TitlePlay"];
         # $ArtistPlay = $row["ArtistPlay"];
         $TitleID = $row["TitleID"];
         $DBGuid = $row["Guid"];
         $DBYear = $row["Year"];
         $DBFullartist = $row["fullartist"];
         $DBComments = $row["Comments"];
         $DBAlbum = $row["Album"];
         $DBTitle = $row["title"];
         $DBCueIn = $row["CueIn"];
         $DBCueOut = $row["CueOut"];


         $i++;
      }

      # Track does not exists

      if ($i == 0) {

         if ($fdebug == 1) {
            echo "\nTrack does NOT exist.";
         }
         $TrackExists = 0;

         # Create the track.

         # Create the title

         $i = 0;

         $todaysdate = date("Y-m-d H:i:s");
         $todaysepoch = time();

         $sql = "INSERT INTO titles(title, lastplayed, totalplays, fullartist, CreationDate, CreationDateHR, lastplayedhr,Comments,Album,Genre,`Year`,`Guid`,`Duration`,`OutCue`,`Tags`,`Disabled`,`Type`,`Intro`,`CueIn`,`CueOut`,`Added`,`Sweeper`,`NoFade`,`ValidFrom`,`Expires`,`Path`,`Segue`,`StationID`,`EligibilityTime`) VALUES ('" . $Title . "'," . $TS . ",1,'" . $Fullartist . "'," . $todaysepoch . ",'" . $todaysdate . "','" . $Timestamphr . "','" . $Comments . "','" . $Album . "','" . $Genre . "','" . $Year . "','" . $Guid . "'," . $Duration . "," . $OutCue . ",'" . $Tags . "','" . $Disabled . "','" . $Type . "'," . $Intro . "," . $CueIn . "," . $CueOut . ",'" . $Added . "','" . $Sweeper . "','" . $NoFade . "','" . $ValidFrom . "','" . $Expires . "','" . $Path . "','" . $Segue . "'," . $StationID . "," . $TS . ")";
         # mail("erik@zalitis.se","Encoding",$sql);
         #  echo $sql;
         #mail("erik@zalitis.se","Encoding",$sql);

         foreach ($dbht->query($sql) as $row) {

            $i++;
         }

         # Lookup the newly created title to get the ID to map the artist to.

         $i = 0;
         $ArtistID = "";

         $sql = "SELECT id FROM titles WHERE title = '" . $Title . "' and StationID = " . $StationID;

         foreach ($dbht->query($sql) as $row) {

            $TitleID = $row["id"];

            $i++;
         }

         # Code to create the trackdata
         # This will only be done once for a track and then never be deleted.
         # We will have to insert the track name in order to reconnect the Data if we
         # at a later data have to delete the tracks and rebuild the database
         # Update 2022-08-16: we now purge old tracks. But the trackdata will not be deleted, and will as stated above, reconnect.

         $sql = "INSERT INTO `trackdata`(`trackid`, `artist`, `title`,`timestamp`,`timestamphr`,`Guid`,`StationID`) VALUES (" . $TitleID . ",'" . $Fullartist . "','" . $Title . "'," . $TS . ",'" . $Timestamphr . "','" . $Guid . "'," . $StationID . ")";

         $i = 0;
         $ArtistID = "";

         #mail("erik@zalitis.se","Encoding",$sql);

         foreach ($dbht->query($sql) as $row) {
            $i++;
         }

         # Update track play.

         $rc = updateplayout($TitleID, $Fullartist, $Title, $TS, $Timestamphr, $Guid, $StationID);

         # Increment total tracks played
         addtotalplay("track", $StationID);

         # New track!
         updatechangelog($TitleID, $Fullartist, $Title, $TS, $Timestamphr, $Guid,  "New Track", ("The track " . $Title . " just appeared in the database."), $StationID);
      } else {

         # The track actually DOES exist.

         if ($fdebug == 1) {
            echo "\nTrack alredy exists.";
         }
         # Just update the artist and track timestamp.

         $TrackExists = 1;
         $ArtistExists = 1;

         # Update the artist timestamp.
         # Update the title's timestamp.

         $i = 0;

         $sql = "UPDATE titles SET lastplayed=" . $TS . ",totalplays=" . ($TitlePlay + 1) . ",lastplayedhr='" . $Timestamphr . "', `EligibilityTime`=" . $TS . " WHERE id = " . $TitleID . " and StationID = " . $StationID;
         #mail("erik@zalitis.se","Encoding",$sql);
         #echo $sql;

         foreach ($dbht->query($sql) as $row) {

            $i++;
         }

         # Update track play.

         $rc = updateplayout($TitleID, $Fullartist, $Title, $TS, $Timestamphr, $Guid, $StationID);

         # Increment total tracks played.

         addtotalplay("track", $StationID);

         # Check for changes. If the track has changed, we need to update.
         # We're looking for change of Title and Album.
         # The album change is meant to allow a total update so I can change the album or title in order to force a change.
         # Artistchange is not yet supported.

         $ChangeDetected = 0;

         # Compare the album-field from the API with the same from the DB

         if ($DBAlbum != $Album) {
            $ChangeDetected = 1;
            updatechangelog($TitleID, $Fullartist, $Title, $TS, $Timestamphr, $Guid,  "Track change", ("The Album(Metadata) for " . $Title . ":" . $Album . " (API), " . $DBAlbum . " (DB) changed its data and was updated"), $StationID);
         }

         # Compare the title-field from the API with the same from the DB

         if ($DBTitle != $Title) {
            $ChangeDetected = 1;
            updatechangelog(
               $TitleID,
               $Fullartist,
               $Title,
               $TS,
               $Timestamphr,
               $Guid,
               "Track change",
               ("The track " . $Title . " (API), " . $DBTitle . " (DB) changed its data and was updated"),
               $StationID
            );
         }

         # Compare the Fullartist-field from the API with the same from the DB

         if ($DBFullartist != $Fullartist) {
            $ChangeDetected = 1;
            updatechangelog(
               $TitleID,
               $Fullartist,
               $Title,
               $TS,
               $Timestamphr,
               $Guid,
               "Track change",
               ("The full artist " . $Fullartist . " (API), " . $DBFullartist . " (DB) data for the track " . $Title . " (API), " . $DBTitle . " (DB) changed its data and was updated"),
               $StationID
            );
         }

         # Compare the CueIn(True startpoint)-field from the API with the same from the DB

         if ($DBCueIn != $CueIn) {
            $ChangeDetected = 1;
            updatechangelog(
               $TitleID,
               $Fullartist,
               $Title,
               $TS,
               $Timestamphr,
               $Guid,
               "Track change",
               ("The CueIn for track " . $Title . ":" . $CueIn . " (API), " . $DBCueIn . " (DB) changed its data and was updated"),
               $StationID
            );
         }

         # Compare the CueOut(True fadepoint)-field from the API with the same from the DB

         if ($DBCueOut != $CueOut) {
            $ChangeDetected = 1;
            updatechangelog(
               $TitleID,
               $Fullartist,
               $Title,
               $TS,
               $Timestamphr,
               $Guid,
               "Track change",
               ("The CueOut for track " . $Title . ":" . $CueOut . " (API), " . $DBCueOut . " (DB) changed its data and was updated"),
               $StationID
            );
         }

         if ($ChangeDetected == 1) # At this time we NEED to actually start changing the DB and the changelog.
         {

            $sql = "UPDATE `titles` SET `title`='" . $Title . "',`fullartist`='" . $Fullartist . "',`Comments`='" . $Comments . "',`Album`='" . $Album . "',`Genre`='" . $Genre . "',`Year`='" . $Year . "',`Duration`=" . $Duration . ",`OutCue`=" . $OutCue . ",`Tags`='" . $Tags . "',`Disabled`='" . $Disabled . "',`Type`='" . $Type . "',`Intro`=" . $Intro . ",`CueIn`=" .  $CueIn .  ",`CueOut`=" .  $CueOut .  ",`Added`='" .  $Added .  "',`Sweeper`='" .   $Sweeper .  "',`NoFade`='" .  $NoFade .  "',`ValidFrom`='" .  $ValidFrom .  "',`Path`='" .  $Path . "',`Segue`='" .  $Segue . "',`Expires`='" . $Expires . "' WHERE guid = '" . $Guid . "' and StationID = " . $StationID;

            $i = 0;

            foreach ($dbht->query($sql) as $row) {

               $i++;
            }

            if ($fdebug == 1) {
               echo "\nTrack updated its data.";
            }


            $action = "";
            foreach ($sceners as $scener) {
               # Now we get Name and groups per scener

               $scenergroups = preg_split('/of/', $scener);
               $handle = trim($scenergroups[0]);
               if (isset($scenergroups[1])) {
                  $allgroups = $scenergroups[1];
               }

               # Check the scener to ascertain their existence in the database.

               #echo "checking CheckArtist...";
               $ArtistID = CheckArtist($TS, $StationID, $Fullartist);
               #echo "check done...";

               # Checking if the artist IS linked to the track

               $sql = "Select id from artiststitles where (title = '" . $Guid . "' and artist = " . $ArtistID . " and StationID = " . $StationID . ")";
               $i = 0;

               # echo $sql;

               foreach ($dbht->query($sql) as $row) {


                  $i++;
               }

               # Link the title to the respective artist

               if ($i != 0) {
                  if ($fdebug == 1) {
                     echo "\n" . $Artist . " for " . $Title . " already exists. No linking needed.";
                  }
               }

               if ($i == 0) {


                  # Link the title to the respective artist

                  if ($fdebug == 1) {
                     echo "\nLinking " . $Artist . " to " . $Title . ".";
                  }

                  $sql = "INSERT INTO artiststitles(artist, title, StationID) VALUES (" . $ArtistID . ",'" . $Guid . "'," . $StationID . ")";
                  $i = 0;

                  #echo $sql;

                  foreach ($dbht->query($sql) as $row) {


                     $i++;
                  }

                  updatechangelog($TitleID, $Fullartist, $Title, $TS, $Timestamphr, $Guid,  "Artist change", ("The artist " . $Artist . " for track " . $Title . " (API), " . $DBTitle . " (DB) changed its data and was updated"), $StationID);
               }
               #    echo "<br>";
            }
         }

         #  return array("result" => 'TRUE', "message" => 'TRUE', "Submessage" => 'Artist and Track was updated.'); 

      }

      # Add the current playing track to the DB

      $sql = "DELETE FROM nowplaying WHERE StationID = " . $StationID;
      $i = 0;


      foreach ($dbht->query($sql) as $row) {


         $i++;
      }

      $sql = "INSERT INTO nowplaying(title, lastplayed, fullartist, trackid, StationID, Duration) VALUES ('" . $Title . "'," . $TS . ",'" . $Fullartist . "'," . $TitleID . "," . $StationID . "," . $Duration . ")";
      $i = 0;


      foreach ($dbht->query($sql) as $row) {


         $i++;
      }


      # If track does not exist.

      if ($TrackExists == 0) {

         # Loop through artists, and make sure they exist, or if not, create them.

         # Loop through groups, and make sure they exist, or if not, create them.

         # Loop through artists and link them to the respective groups
         $action = "";
         foreach ($sceners as $scener) {
            # Now we get Name and groups per scener

            $scenergroups = preg_split('/of/', $scener);
            $handle = trim($scenergroups[0]);
            if (isset($scenergroups[1])) {
               $allgroups = $scenergroups[1];
            }

            # Check the scener to ascertain their existence in the database.

            #echo "checking CheckArtist...";
            $ArtistID = CheckArtist($TS, $StationID, $Fullartist);
            #echo "check done...";

            # Link the title to the respective artist

            if ($fdebug == 1) {
               echo "\nLinking " . $Artist . " to " . $Title . " for station " . $StationID . ".";
            }

            $sql = "INSERT INTO artiststitles(artist, title, StationID) VALUES (" . $ArtistID . ",'" . $Guid . "'," . $StationID . ")";
            $i = 0;

            foreach ($dbht->query($sql) as $row) {
               $i++;
            }

            #echo "Scener: ".$handle;
            #echo " Member of the following groups: ";


         return array("result" => 'TRUE', "message" => 'TRUE', "Submessage" => 'All done');
      }
      #echo "Track exists? ".$TrackExists;
      if ($TrackExists == 1) {

         # We will have to check the artist every playtime, otherwise EligibilityTime does not update.
         # This means the request system cannot filter out artists properly.
         # It's a bit demanding on the backend SQL. So, if you don't use request, feel free to comment the next 15 lines out
         # don't comment out the end bracket, though! :)

         #  if ($ChangeDetected == 1) {

         # Convergence patch!

         $action = "";
         foreach ($sceners as $scener) {
            # Now we get Name and groups per scener

            $scenergroups = preg_split('/of/', $scener);
            $handle = trim($scenergroups[0]);
            if (isset($scenergroups[1])) {
               $allgroups = $scenergroups[1];
            }

            # Check the scener to ascertain their existence in the database.

            $ArtistID = CheckArtist($TS, $StationID, $Fullartist);

            #echo "Scener: ".$handle;
            #echo " Member of the following groups: ";

            # Update the track's timestamp

            #      $i=0;

            #$sql="UPDATE artists SET lastplayed=".$TS.",totalplays=".($ArtistPlay + 1)." WHERE id = ".$ArtistID."";

            #echo $sql;

            #        foreach($dbht->query($sql) as $row) { 

            #       $i++;

            #   }

            #    echo "<br>";
         }
         #  }

         return array("result" => 'TRUE', "message" => 'TRUE', "Submessage" => 'All done.');
      }

      return array("result" => 'TRUE', "message" => 'TRUE', "Submessage" => 'Unknown failure.');
   } # Function: updatetracks


} # Class: Tracks
}