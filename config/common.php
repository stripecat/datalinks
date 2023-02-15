<?php
include '/var/www/html/config.php';

# Initialize JWT.

include_once $syspath . 'inc/BeforeValidException.php';
include_once $syspath . 'inc/ExpiredException.php';
include_once $syspath . 'inc/SignatureInvalidException.php';
include_once $syspath . 'inc/JWT.php';

use \Firebase\JWT\JWT;

//Db conn and instances
try {
  $dbht = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname . '', $dbuser, $dbpasswd, array(PDO::ATTR_PERSISTENT => true));
} catch (PDOException $e) {
  if ($debugmode == 1) {
    echo 'Connection failed: ' . $e->getMessage();
  } else {
    die("Couldn't connect to the database. Please contact Moleant.com");
  }
}


# Common stuff


# Security sanitization

function sanitize_paranoid_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
    return FALSE;
  }

  $string = preg_replace("/[\'\<\>\=\;]/i", "", $string);

  return $string;
}

// Email sanitization -- Lets through email
function sanitize_email_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  if (!filter_var($string, FILTER_VALIDATE_EMAIL))
    return FALSE;

  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// Normal sanitization -- Lets trough alphanumeric + åäöÅÄÖ and _-%!?"
// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_sql_string($string, $min = '', $max = '')
{
  $len = strlen($string);
  //$string = preg_replace("/'/", " ", $string); // Ta bort ' 
  $pattern[0] = '/(\\\\)/';
  $pattern[1] = "/\"/";
  $pattern[2] = "/'/";
  $replacement[0] = '\\\\\\';
  $replacement[1] = '\"';
  $replacement[2] = "\\'";

  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return preg_replace($pattern, $replacement, $string);
}
// Numeric sanitization -- Lets through 0-9
function sanitize_numeric_string($string, $min = '', $max = '')
{
  $len = strlen($string);
  $string = preg_replace("/[^0-9]/", "", $string);
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

// Floating decimal sanitization -- Lets through 0-9
function sanitize_float_string($string, $min = '', $max = '')
{
  $len = strlen($string);
  $string = preg_replace("/[^0-9\,\.]/", "", $string);
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}


function sanitize_date_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
    return FALSE;
  } # Is the string longer or shorter than accepted ranges of characters?

  if (preg_match("/[^0-9\:\-\,\/ T]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?


  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even then.

  $string = preg_replace("/[^0-9\:\-\,\/ T]/i", "", $string);

  return $string;
}

// Hexadecimal sanitization -- Lets through 0-9a-f
function sanitize_hexdec_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
    return FALSE;
  } # Is the string longer or shorter than accepted ranges of characters?

  if (preg_match("/[^0-9a-f\-]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?


  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even then.

  $string = preg_replace("/[^0-9a-f\-]/i", "", $string);

  return $string;
}

// Hexadecimal sanitization -- Lets through 0-9a-f
function sanitize_adguid_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
    return FALSE;
  } # Is the string longer or shorter than accepted ranges of characters?

  if (preg_match("/[^0-9a-f\-]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?


  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even then.

  $string = preg_replace("/[^0-9a-f\-]/i", "", $string);

  return $string;
}

// Base64 sanitization -- Lets through 0-9a-f
function sanitize_basesixtyfour_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
    return FALSE;
  } # Is the string longer or shorter than accepted ranges of characters?'

  if (preg_match("/[^0-9a-zA-Z\=\/\+]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?


  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even then.

  $string = preg_replace("/[^0-9a-zA-Z\=\/\+]/i", "", $string);

  return $string;
}


// Hexadecimal sanitization -- Lets through 0-9a-f
function sanitize_jwt_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) # Is the string longer or shorter than accepted ranges of characters?
    return FALSE;

  if (preg_match("/[^0-9a-zA-Z\-\.\_]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?

  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even then.

  $string = preg_replace("/[^0-9a-zA-Z\-\.\_]/i", "", $string);

  return $string;
}

function sanitize_dns_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) # Is the string longer or shorter than accepted ranges of characters?
    return FALSE;
  if (preg_match("/[^a-zA-Z\-0-9\.]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?


  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even rthen.

  $string = preg_replace("/[^a-zA-Z\-0-9\.]/i", "", $string);

  return $string;
}

function sanitize_url_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
    return FALSE;
  } # Is the string longer or shorter than accepted ranges of characters?

  if (preg_match("/[^a-zA-Z\-0-9\.\/]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?


  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even rthen.

  $string = preg_replace("/[^a-zA-Z\-0-9\.\/]/i", "", $string);

  return $string;
}

function sanitize_filename_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
    return FALSE;
  } # Is the string longer or shorter than accepted ranges of characters?
  if (preg_match("/[\'\/\:\*\"\<\>\|\\\\]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?


  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even rthen.

  $string = preg_replace("/[\'\/\:\*\"\<\>\|\\\\]/i", "", $string);

  return $string;
}

function sanitize_alpha_string($string, $min = '', $max = '')
{
  $len = strlen($string);

  # Stage 1 - Validation. Failure here will invalidated further checking.
  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) {
    return FALSE;
  } # Is the string longer or shorter than accepted ranges of characters?

  if (preg_match("/[^a-zA-Z0-9]/i", $string) > 0) {
    return FALSE;
  } # Does the string contain anything except accepted characters?


  # Stage 2 - Sanitization. Returns only valid characters.
  # In cases where whitelisting has been done above, it may be a bit superfluous.
  # But should a programmer change the above lines, sanitization will keep it secure
  # even rthen.

  $string = preg_replace("/[^a-zA-Z0-9]/i", "", $string);

  return $string;
}

// sanitize a string for HTML (make sure nothing gets interpreted!)
function sanitize_html_string($string, $min = '', $max = '')
{
  if (empty($string)) {
    return FALSE;
  }
  $len = strlen($string);
  $pattern[0] = '/\&/';
  $pattern[1] = '/</';
  $pattern[2] = "/>/";
  $pattern[3] = '/\n/';
  $pattern[4] = '/"/';
  $pattern[5] = "/'/";
  $pattern[6] = "/%/";
  $pattern[7] = '/\(/';
  $pattern[8] = '/\)/';
  $pattern[9] = '/\+/';
  $pattern[10] = '/\{/';
  $pattern[11] = '/\}/';
  $pattern[12] = '/\|/';
  $pattern[13] = '/\~/';
  $pattern[14] = '/�/';
  $pattern[15] = '/�/';
  $pattern[16] = '/�/';
  $pattern[17] = '/�/';
  $pattern[18] = '/�/';
  $pattern[19] = '/�/';
  $pattern[20] = '/\\\/';
  $replacement[0] = '&amp;';
  $replacement[1] = '&lt;';
  $replacement[2] = '&gt;';
  $replacement[3] = '<br>';
  $replacement[4] = '&quot;';
  $replacement[5] = '&#39;';
  $replacement[6] = '&#37;';
  $replacement[7] = '&#40;';
  $replacement[8] = '&#41;';
  $replacement[9] = '&#43;';
  $replacement[10] = '&#123;';
  $replacement[11] = '&#125;';
  $replacement[12] = '&#124;';
  $replacement[13] = '&#126;';
  $replacement[14] = '&aring;';
  $replacement[15] = '&auml;';
  $replacement[16] = '&ouml;';
  $replacement[17] = '&Aring;';
  $replacement[18] = '&Auml;';
  $replacement[19] = '&Ouml;';
  $replacement[20] = '&#92;';



  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return preg_replace($pattern, $replacement, $string);
}
// sanitize a string for HTML (make sure nothing gets interpreted!)
function sanitize_htmlallowsome_string($string, $min = '', $max = '')
{
  $len = strlen($string);
  $pattern[0] = '/</';
  $pattern[1] = "/>/";
  $pattern[2] = '/\n/';
  $pattern[3] = '/"/';
  $pattern[4] = "/'/";
  $pattern[5] = "/%/";
  $pattern[6] = '/\(/';
  $pattern[7] = '/\)/';
  $pattern[8] = '/\+/';
  $pattern[9] = '/\{/';
  $pattern[10] = '/\}/';
  $pattern[11] = '/\|/';
  $pattern[12] = '/\~/';
  $pattern[13] = '/�/';
  $pattern[14] = '/�/';
  $pattern[15] = '/�/';
  $pattern[16] = '/�/';
  $pattern[17] = '/�/';
  $pattern[18] = '/�/';
  $pattern[19] = '/\\\/';
  $replacement[0] = '&lt;';
  $replacement[1] = '&gt;';
  $replacement[2] = '<br>';
  $replacement[3] = '&quot;';
  $replacement[4] = '&#39;';
  $replacement[5] = '&#37;';
  $replacement[6] = '&#40;';
  $replacement[7] = '&#41;';
  $replacement[8] = '&#43;';
  $replacement[9] = '&#123;';
  $replacement[10] = '&#125;';
  $replacement[11] = '&#124;';
  $replacement[12] = '&#126;';
  $replacement[13] = '&aring;';
  $replacement[14] = '&auml;';
  $replacement[15] = '&ouml;';
  $replacement[16] = '&Aring;';
  $replacement[17] = '&Auml;';
  $replacement[18] = '&Ouml;';
  $replacement[19] = '&#92;';

  if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return preg_replace($pattern, $replacement, $string);
}

# Character conversion

/**
 * @param mixed $str 
 * @return string 
 */
function superentities2($str)
{
  // get rid of existing entities else double-escape
  $str = html_entity_decode(stripslashes($str), ENT_QUOTES, 'UTF-8');
  $ar = preg_split('/(?<!^)(?!$)/u', $str);  // return array of every multi-byte character
  $str2 = "";
  foreach ($ar as $c) {
    $o = ord($c);
    if ((strlen($c) > 1) || /* multi-byte [unicode] */
      ($o < 32 || $o > 126) || /* <- control / latin weirdos -> */
      ($o > 33 && $o < 40) ||/* quotes + ambersand */
      ($o > 59 && $o < 63) /* html */
    ) {
      // convert to numeric entity
      $c = mb_encode_numericentity($c, array(0x0, 0xffff, 0, 0xffff), 'UTF-8');
    }
    $str2 .= $c;
  }
  return $str2;
}

function superentities($str)
{
  $str=str_replace("\n", "\\\\n", $str);
  $str=str_replace("\r", "", $str);
  $str=str_replace("\"", "\\\"", $str);
  $str=str_replace("\t", "\\\\t", $str);
  $str=mb_convert_encoding($str, 'HTML-ENTITIES', "UTF-8");

  return $str;
  
  /* // get rid of existing entities else double-escape
  $str = html_entity_decode(stripslashes($str), ENT_QUOTES, 'UTF-8');
  $ar = preg_split('/(?<!^)(?!$)/u', $str);  // return array of every multi-byte character
  $str2 = "";
  print_r($ar);
  foreach ($ar as $c) {
    $o = ord($c);
    if ((strlen($c) > 1) || /* multi-byte [unicode] */
  #  ($o < 32 || $o > 126) || /* <- control / latin weirdos -> */
  #  ($o > 33 && $o < 40) ||/* quotes + ambersand */
  #  ($o > 59 && $o < 63) /* html */
  #) {
  // convert to numeric entity
  #  $c = mb_encode_numericentity($c, array(0x0, 0xffff, 0, 0xffff), 'UTF-8');
  #}
  #$str2 .= $c;
  #} */
  # return $str2;
}


# Common functions

function TimeToPlain($timeinseconds, $colonseparated = 1)
{

  if ($colonseparated == 0
  ) {
    if ($timeinseconds < 86400 ) 
    {
      if ($timeinseconds < 60) {
        $PlayTimeHR = round($timeinseconds, 0) . " seconds";
      } elseif ($timeinseconds  < 3600) {
        $PlayTimeHR = gmdate("i", $timeinseconds) . " minutes " . gmdate("s", $timeinseconds) . " seconds";
      } elseif ($timeinseconds < 7200) {
        $PlayTimeHR = gmdate("h", $timeinseconds) . " hour " . gmdate("i", $timeinseconds) . " minutes";
      } else {
        $PlayTimeHR = gmdate("H", $timeinseconds) . " hours " . gmdate("i", $timeinseconds) . " minutes";
      }
    } elseif ($timeinseconds < 172800
    ) {
      $PlayTimeHR =  gmdate("d", $timeinseconds) - 1 . " day " . gmdate("H", $timeinseconds) . " hours " . gmdate("i", $timeinseconds) . " minutes";
    } else {
      $PlayTimeHR =  gmdate("d", $timeinseconds) - 1 . " days " . gmdate("H", $timeinseconds) . " hours " . gmdate("i", $timeinseconds) . " minutes";
    }
  } else {

    

    if (gmdate("s",$timeinseconds) < 10) {

      $PlayTimeSeconds = "0" . round(gmdate("s",$timeinseconds),0);
    } else {
      $s = gmdate("s",$timeinseconds);
      $PlayTimeSeconds = round($s,0);
    }

    if ($timeinseconds < 86400) 
    {
      if ($timeinseconds < 60) {
        $PlayTimeHR = '00:' . $PlayTimeSeconds;

      } elseif ($timeinseconds  < 3600) {
        $PlayTimeHR = gmdate("i", $timeinseconds) . ":" . $PlayTimeSeconds . "";
      } elseif ($timeinseconds <= 86399) {
        $PlayTimeHR = gmdate("h", $timeinseconds) . ":" . gmdate("i", $timeinseconds) . ":" . $PlayTimeSeconds . "";
      }
    } elseif ($timeinseconds < 172800
    ) {
      $PlayTimeHR =  gmdate("d", $timeinseconds) - 1 . " day " . gmdate("H", $timeinseconds) . " hours " . gmdate("i", $timeinseconds) . " minutes";
    } else {
      $PlayTimeHR =  gmdate("d", $timeinseconds) - 1 . " days " . gmdate("H", $timeinseconds) . " hours " . gmdate("i", $timeinseconds) . " minutes";
    }
  }

  return $PlayTimeHR;
}

function gettracktype($Album)
{
  if (stripos($Album, ".mod", 0) != false) {
    $songtype = "Amiga 4-channel module";
  } elseif (stripos($Album, "mod.", 0) != false) {
    $songtype = "Amiga 4-channel module";
  } elseif (stripos($Album, ".xm", 0) != false) {
    $songtype = "Fasttracker";
  } elseif (stripos($Album, ".it", 0) != false) {
    $songtype = "Impulsetracker";
  } elseif (stripos($Album, ".s3m", 0) != false) {
    $songtype = "Screamtracker";
  } elseif (stripos($Album, ".ahx", 0) != false) {
    $songtype = "AHX Synth-tracker";
  } elseif (stripos($Album, ".med", 0) != false) {
    $songtype = "OctaMed/MED";
  } elseif (stripos($Album, ".rmx", 0) != false) {
    $songtype = "Modern remix";
  } elseif (stripos($Album, ".ogg", 0) != false) {
    $songtype = "Modern remix (demo scene)";
  } elseif (stripos($Album, ".mp3", 0) != false) {
    $songtype = "Modern remix (demo scene)";
  } elseif (stripos($Album, ".okt", 0) != false) {
    $songtype = "Oktalyzer";
  } elseif (stripos($Album, ".wav", 0) != false) {
    $songtype = "Modern remix (demo scene)";
  } elseif (stripos($Album, ".stn", 0) != false) {
    $songtype = "StationID";
  } elseif (stripos($Album, ".mptm", 0) != false) {
    $songtype = "Multitracker";
  } elseif (stripos($Album, ".mo3", 0) != false) {
    $songtype = "MO3";
  } elseif (stripos($Album, ".mt2", 0) != false) {
    $songtype = "MadTracker";
  } elseif (stripos($Album, ".symmod", 0) != false) {
    $songtype = "Symphonie module";
  } elseif (stripos($Album, ".hvl", 0) != false) {
    $songtype = "HivelyTracker Audio";
  } elseif (stripos($Album, ".sid", 0) != false) {
    $songtype = "C64 Sidtune";
  } elseif (stripos($Album, ".sfx", 0) != false) {
    $songtype = "SoundFX / MultiMedia Sound";
  } elseif (stripos($Album, ".sfx2", 0) != false) {
     $songtype = "SoundFX / MultiMedia Sound";
  } elseif (stripos($Album, ".mms", 0) != false) {
     $songtype = "SoundFX / MultiMedia Sound";
  } elseif (stripos($Album, ".pod", 0) != false) {
    $songtype = "Podcast episode";
  } elseif (stripos($Album, ".live", 0) != false) {
    $songtype = "Live broadcast rerun";
  } elseif (stripos($Album, ".amf", 0) != false) {
    $songtype = "Asylum Music Format";
  } elseif (stripos($Album, ".news", 0) != false) {
    $songtype = "News cast";
  } elseif (stripos($Album, ".mtm", 0) != false) {
    $songtype = "Multitracker";
  } elseif (stripos($Album, ".prt", 0) != false) {
    $songtype = "Pretracker";
  } elseif (stripos($Album, ".pre", 0) != false) {
    $songtype = "Pretracker";
  } elseif (stripos($Album, ".digi", 0) != false) {
    $songtype = "Digi Booster";
  } elseif (stripos($Album, ".dbm", 0) != false) {
    $songtype = "Digi Booster Pro";
  } elseif (stripos($Album, ".adv", 0) != false) {
    $songtype = "Advertisment";
  } else {
    $songtype = "Unknown";
  }

  return $songtype;
}

function PurgeOldEpisodes($days)
{

  # Only supported on station 1 for obvious reasons.

  require '/var/www/html/config.php';
  global $dbht;

  $i = 0;

  $dyndata = "";
  $maxdays=31;
  $stated="Unknown";
  $nowepoch=time();
  
  if ($days < 21) {
    $days = 21;
  } # Mistakedly nuking the database is a less than stellar idea.

  $sql = "SELECT `id`, `totalplays`, `guid`, `fullartist`, `title`, `lastplayed`, `lastplayedhr`, `StationID` FROM `titles` WHERE (lastplayed < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $days . " DAY))) and stationid = 1 ORDER BY lastplayedhr DESC";
  
  #$sql = "SELECT `id`, `totalplays`, `guid`, `fullartist`, `title`, `lastplayedhr`, `StationID` FROM `titles` WHERE Album like '%.xm%' and stationid = 1 ORDER BY lastplayedhr DESC";
  


  foreach ($dbht->query($sql) as $prow) {

    $state="Deleted";
    $fullartist = $prow["fullartist"];
    $title = $prow["title"];
    $lastplayedhr = $prow["lastplayedhr"];
    $lastplayed = $prow["lastplayed"];
    $totalplays = $prow["totalplays"];
    $guid = $prow["guid"];
    $StationID = $prow["StationID"];
    $id = $prow["id"];

    $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

    # Check if the artist is on the PurgeProtectedArtists list will automatically fail it. 

    if (string_search_partial($Stations[$StationIDInArray]["PurgeProtectedArtists"], strtolower($fullartist)) == true) {

      # Do nothing for this track of this artist. It should not be touched.

    } 
    else if ($lastplayed < ($nowepoch - 2678400)) {

      # After a longer station downtime, we shouldn't destroy the whole DB.
      # This part spares files older than a month.

      $state = "Not deleted - Too old for purge";

      $dyndata = $dyndata . "
      <tr>
      <td>" .  $fullartist . " - " . $title . "</td>
      <td>" . $lastplayedhr . "</td>
      <td>" . $totalplays . "</td>
      <td>" . $guid . "</td>
      <td>" . $state . "</td>
      </tr>";
  

    }
    else {

      # Remove this track for this artist.
      # The artist can never be deleted.

      $i++; # Increment only on deletion.

      $dyndata = $dyndata . "
    <tr>
    <td>" .  $fullartist . " - " . $title . "</td>
    <td>" . $lastplayedhr . "</td>
    <td>" . $totalplays . "</td>
    <td>" . $guid . "</td>
    <td>" . $state . "</td>
    </tr>";

      # Delete the title

      $sql = "delete FROM `titles` where `guid` = '" . $guid . "'";

      $rc = $dbht->query($sql);

      # Delete the title to artist link

      $sql = "delete FROM `artiststitles` WHERE `title` = '" . $guid . "'";

      $rc = $dbht->query($sql);

      # Delete the title to trackdata link
      
      # Not implemented to to risks of data loss.

    #  $sql = "delete FROM `trackdata` WHERE `Guid` = '" . $guid . "'";

     # $rc = $dbht->query($sql);

    }
  }


  $to = "sitemaster@ericade.net";
  $subject = "ericade-radio - Tunes to be deleted.";

  $message = "
  <html>
  <head>
  <title>Tracks to be deleted</title>
  </head>
  <body>
  <p>Please make sure those are not in use</p>
  <table>
  <tr>
  <th>Tune</th>
  <th>Lastplayed</th>
  <th>Total plays</th>
  <th>Global identifier</th>
  <th>Status</th>
  </tr>
" . $dyndata . "
  </table>
  </body>
  </html>
  ";

  // Always set content-type when sending HTML email
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

  // More headers
  $headers .= 'From: <sitemaster@bahnhof.se>' . "\r\n";



  if ($i > 0) {
    mail($to, $subject, $message, $headers);
  }
}

function PopulateTrackerTypes()
{

  require '/var/www/html/config.php';
  global $dbht;


  $PossibleStations = array_column($Stations, 'stationid');


  #if ($StationIDInArray !== false) {
  #} else {
  #   return array("result" => 'FALSE', "message" => 'FALSE', "Submessage" => 'Station does not exist of configuration is invalid.');
  #}

  #$AllowRequests = $Stations[$StationIDInArray]["AllowRequests"];

  # Empty the table

  $sql = "DELETE FROM `TrackerTypes`;";

  $rc = $dbht->query($sql);

  # All the stations

  foreach ($PossibleStations as $StationID) {

    $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

    $NonCalculatedArtists = $Stations[$StationIDInArray]["NonCalculatedArtists"];

    if (count($NonCalculatedArtists) > 0) { $NonCountWhere = ""; } else { $NonCountWhere = " ";}

    foreach ($NonCalculatedArtists as $ArtistNotCounted)
    {
      $NonCountWhere = $NonCountWhere . " and fullartist not like '%" . $ArtistNotCounted . "' ";
    }

    # Remove the last space.
    $NonCountWhere = substr($NonCountWhere, 0, -1);

    # We must know the exact number of tracks on the station to calculate the percentage.

    $sql = "SELECT count(album) as \"TotalTracks\" FROM `titles` WHERE album <> '' and StationID = " . $StationID . "" . $NonCountWhere;

    foreach ($dbht->query($sql) as $irow) {

      $TotalTracks = $irow["TotalTracks"];
    }

    # The sum of all music (better than that Tom Clancy novel)
    
    $sql = "select (sum(CueOut-CueIn)/60/60) as \"TotalLength\" from titles where album <> '' and StationID = " . $StationID . "" . $NonCountWhere;

    foreach ($dbht->query($sql) as $irow) {

      $TotalLength = $irow["TotalLength"];

      $TotalLength = round($TotalLength, 2);
    }

    $sql = "UPDATE `stats` SET `totaltracks`=" . $TotalTracks . " WHERE StationID = " . $StationID . "";

    $rc = $dbht->query($sql);

    $sql = "UPDATE `stats` SET `TotalLength`=" . $TotalLength . " WHERE StationID = " . $StationID . "";

    #mail("erik@zalitis.se", "Testa", $sql);

    $rc = $dbht->query($sql);

    $arrTrackerTypes = array();

    # We must have all albums that are available.
    $sql = "SELECT album, `id`, `fullartist`,`title`,`lastplayedhr` FROM `titles` WHERE album <> '' and StationID = " . $StationID . "" . $NonCountWhere;

    $totalstations = 0;
    foreach ($dbht->query($sql) as $row) {

      $album = $row['album'];
      $gtt = gettracktype($album);

      # Dumb code to gimme a list.

      # if ($gtt == "Unknown") { mail("sitemaster@ericade.net","Another unknown track",($row['id'] . " - " . "-" . $row['title'] . "-" . $StationID . "." . $row['lastplayedhr'])); }

      # Check if we have the tracktype in the array

      $inc = 0;

      if (array_key_exists($gtt, $arrTrackerTypes)) {
        $inc = $arrTrackerTypes[$gtt];
        $inc++;
        $arrTrackerTypes[$gtt] = $inc;
      } else {
        $arrTrackerTypes[$gtt] = 1; // add a entry
      }
      # If we do, 

    }

    #echo print_r($arrTrackerTypes);
    # Done looping through the albums for this station.

    foreach ($arrTrackerTypes as $key => $value) {
      #echo "Typ: ". $key;
      #echo "Spelningar: " . $value;

      if ($TotalTracks != 0) # Protection against division by zero. Universe may collapse then.
      {
        $Percent = ($value / $TotalTracks) * 100;
      }

      $sql = "INSERT INTO `TrackerTypes`(`TrackerType`, `Extension`, `Plays`, `Comment`, `StationID`, `Percent`) 
        VALUES ('" . $key . "','.'," . $value . ",''," . $StationID . "," . $Percent . ")";

      $rc = $dbht->query($sql);
    }
  }
}

function convertplain($in)
{

  #  $out = str_replace("&#37;", "&#", $in);


  #$pattern = '/(\w+) (\d+), (\d+)/i';
  $pattern = '/&#37;(\d+)/i';

  # &#37;C3&#37;A5


  $replacement = '%${1}';
  $out = preg_replace($pattern, $replacement, $in);


  return urldecode($out);
  #return $out;
}


/**
 * @param mixed $password 
 * @return bool 
 */
function checkpassword($password)
{

  include '/var/www/html/config.php';
  if ($password == $apipwd) {
    return TRUE;
  } else {
    return FALSE;
  }
}

function validateaccess($token)
{
  global $guid, $db, $databsase;
  # Get the token and sanitize it.

  $token = sanitize_hexdec_string($token, 24, 24);

  if (!$token) {
    http_response_code(403);
    print('{"message": "Invalid token."}');
    return FALSE;
  }

  # Check it against the database.

  if (!isset($database)) {
    $database = new Database();
    $db = $database->getConnection();
  }

  $i = 0;
  $sql = "SELECT DISTINCT guid,customername FROM customers WHERE orgid = '" . $token . "'";
  foreach ($db->query($sql) as $row) {
    $i++;
    $guid = $row['guid'];
  }

  # http_response_code(403);print ('{"message": "Customer not found."}'); 

  if ($i == 0) {
    return FALSE;
  }

  return $guid;
  # Return access denied|granted. 



}

function validatejwt($token)
{
  #global $guid,$db,$database;
  require '/var/www/html/config.php';
  # Get the token and sanitize it.

  $token = sanitize_jwt_string($token, 1, 900);
  if (!$token) {
    return json_encode(array(
      "message" => "Access denied.",
      "data" => false
    ));
  } else {

    // if decode succeed, show user details
    try {
      // decode jwt
      $decoded = JWT::decode($token, $apikeyseed, array('HS256'));

      # Get the nonce-attribute first.
      if (isset($decoded->data->nonce)) {
        $tnonce = sanitize_hexdec_string($decoded->data->nonce, 1, 50);
      } else {
        return json_encode(array(
          "message" => "Access denied.",
          "data" => false
        ));
      }

      $userid = sanitize_html_string($decoded->data->id, 1, 200);
      $posd = strpos($userid, "bm9uY2U=");
      $vnonce = substr($userid, ($posd + 8));
      if ($vnonce == $tnonce) {
        $userid2 = substr($userid, 0, $posd);
      } else {
        return json_encode(array(
          "message" => "Access denied.",
          "data" => false
        ));
      }

      $useremail = sanitize_html_string($decoded->data->email, 1, 255);

      $posd = strpos($useremail, "bm9uY2U=");
      $vnonce = substr($useremail, ($posd + 8));
      if ($vnonce == $tnonce) {
        $useremail2 = substr($useremail, 0, $posd);
      } else {
        $useremail2 = "unknown@soldier.com";
      }

      if (isset($decoded->data->ip)) {
        $ip = sanitize_html_string($decoded->data->ip, 1, 255);
      } else {
        $ip = "";
      }
      $posd = strpos($ip, "bm9uY2U=");
      $vnonce = substr($ip, ($posd + 8));
      if ($vnonce == $tnonce) {
        $ip2 = substr($ip, 0, $posd);
      } else {
        $ip2 = "unknown@soldier.com";
        #	return json_encode(array("message" => "Access denied.",
        #     "data" => false
        #  ));
      }

      $ts = sanitize_html_string($decoded->data->timestamp, 1, 255);

      $posd = strpos($ts, "bm9uY2U=");
      $vnonce = substr($ts, ($posd + 8));
      if ($vnonce == $tnonce) {
        $ts2 = substr($ts, 0, $posd);
      } else {
        return json_encode(array(
          "message" => "Access denied.",
          "data" => false
        ));
      }

      # Anti replay protection

      $ts4 = (int)$ts2;
      $ts3 = time();

      $elapsed = ($ts3 - $ts4);
      #echo "Now: ".$ts3." Then:".$ts4." Elapsed: ".$elapsed." seconds";
      #die("rr");

      if ($maxticketoffset != 0 && $maxticketoffset != "") {
        if ($elapsed > $maxticketoffset || $elapsed < (-$maxticketoffset)) {

          return json_encode(array(
            "message" => "Access denied.",
            "data" => "Ticket is not valid anymore or yet. Seconds elapsed " . $elapsed
          ));
        }
      }




      #echo "IP: ".$ip2."...";


      $arrRes = array(
        "id" => $userid2,
        "email" => $useremail2,
        "ip" => $ip2
      );


      // show user details
      return json_encode(array(
        "message" => "Access granted.",
        "data" => $arrRes
      ));
    } catch (Exception $e) {

      // set response code
      //http_response_code(401);

      // tell the user access denied  & show error message
      /* echo json_encode(array(
        "message" => "Access denied.",
        "error" => $e->getMessage()
    ));*/
      return json_encode(array(
        "message" => "Access denied.",
        "data" => false
      ));
    }
  }
}

function CheckFileExtension($filename)
{
  global $dbht;
  
  preg_match('/[^\.]*$/', $filename, $matches);
  $ext = $matches[0];
  $i = 0;
  $sql = "SELECT mimetype,icon FROM Allowed_FileTypes WHERE lower(extension) like '%" . $ext . "%'";
  foreach ($dbht->query($sql) as $row) {
    $mimetype = $row["mimetype"];
    $icon = $row["icon"];
    $i++;
  }

  if ($i <> 0) {
    return array("result" => 'TRUE', "mimetype" => $mimetype, "icon" => $icon);
  } else {
    return array("result" => 'FALSE');
  }
}

function CheckUserPermission($userid, $CGuid, $perm)
{
  global $dbht;
  if ($perm == 1) {
    $eperm = "roles, 1, 1";
  } /* Read */ elseif ($perm == 2) {
    $eperm = "roles, 2, 1";
  } /* Write */ elseif ($perm == 3) {
    $eperm = "roles, 3, 1";
  } /* Admin */ else {
    return FALSE;
  }

  # Check the permissions for this user and to this customer.
  $i = 0;
  $sql = "SELECT * FROM roles WHERE (objectid='" . $userid . "' and guid='" . $CGuid . "' and SUBSTR(" . $eperm . ")=1)";

  foreach ($dbht->query($sql) as $row) {
    $i++;
  }

  if ($i <> 0) {
    return TRUE;
  } else {
    return FALSE;
  }
}

function AddRandomNumber($num)
{

  return bin2hex(random_bytes($num));
}

function GenerateGUID($num)
{

  return bin2hex(random_bytes($num));
}

# Functions for the tracks class.

function addtotalplay($type, $StationID)
{
  global $dbht;

  $newstat = 0;

  $fdebug = 1;


  if ($type ==  "artist") {

    $newstat = 0;

    # Read current totalartistplays

    $i = 0;

    $sql = "SELECT totalartistplays FROM stats WHERE StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {

      $totalartistplays = $row["totalartistplays"];
      $i++;
    }

    # If we get no result, we must create an entry

    if ($i == 0) {

      $sql = "INSERT INTO `stats`(`totalartistplays`, `totaltrackplays`, `totalgroupplays`, `StationID`) VALUES (0,0,0," . $StationID . ")";
      $totalartistplays = 0;

      if ($fdebug == 1) {
        echo "\nNew stats file was created..";
      }

      foreach ($dbht->query($sql) as $row) {
      }
    }

    # increment by 1

    $newstat = $totalartistplays + 1;

    # Push it back


    $sql = "UPDATE stats SET totalartistplays = " . $newstat . " WHERE StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {
    }
  } elseif ($type == "track") {

    $newstat = 0;

    # Read current totaltrackplays

    $sql = "SELECT totaltrackplays FROM stats WHERE StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {

      $totaltrackplays = $row["totaltrackplays"];
    }

    # increment by 1

    $newstat = $totaltrackplays + 1;

    # Push it back


    $sql = "UPDATE stats SET totaltrackplays = " . $newstat . " WHERE StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {
    }
  } elseif ($type == "group") {

    $newstat = 0;

    # Read current totalgroupplays

    $sql = "SELECT totalgroupplays FROM stats WHERE StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {

      $totalgroupplays = $row["totalgroupplays"];
    }

    # increment by 1

    $newstat = $totalgroupplays + 1;

    # Push it back


    $sql = "UPDATE stats SET totalgroupplays = " . $newstat . " WHERE StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {
    }
  } else {
    return false;
  }
}

function CheckArtist($TS, $StationID, $fullartist)
{
  include '/var/www/html/config.php';
  global $dbht;


  $fdebug = 0;

  if ($fdebug == 1) {
    echo "\nArtist to check: " . $fullartist . ".";
  }

  # Check if the artist exists

  $sql = "SELECT t1.id as \"ArtistID\", t1.totalplays as \"ArtistPlay\" FROM artists t1 WHERE t1.artist = '" . $fullartist . "' and t1.StationID = " . $StationID;

  $i = 0;

  $ArtistID = "";
  $TitlePlay = "";
  $ArtistPlay = "";

  foreach ($dbht->query($sql) as $row) {

    $ArtistID = $row["ArtistID"];
    $ArtistPlay = $row["ArtistPlay"];

    $i++;
  }


  if ($i == 0) {
    $ArtistExists = 0;
  } else {
    $ArtistExists = 1;
  }

  if ($ArtistExists == 0) {
    # Create the Artist

    if ($fdebug == 1) {
      echo "\nArtist was NOT found...";
    }

    $i = 0;

    $sql = "INSERT INTO artists(artist, lastplayed, totalplays, StationID, EligibilityTime) VALUES ('" . $fullartist . "','" . $TS . "',1," . $StationID . "," . $TS . ")";

    foreach ($dbht->query($sql) as $row) {

      $i++;
    }

    if ($fdebug == 1) {
      echo "\nArtist was created...";
    }

    usleep(250000); # Just to make sure the DB returns a record. Don't want no race-conditions thank you very much.

    # Lookup the newly created artist to get the ID to map the song to.

    $i = 0;
    $ArtistID = "";

    $sql = "SELECT id FROM artists WHERE artist = '" . $scener . "' and StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {

      $ArtistID = $row["id"];

      $i++;
    }

    if ($fdebug == 1) {
      echo "\nArtist id became " . $ArtistID . ".";
    }

    # Create the artists datablob

    $sql = "INSERT INTO `artistdata`(`artistid`, `artist`, `lastplayed`, `totalplays`, `StationID`, `EligibilityTime`) VALUES (" . $ArtistID . ",'" . $fullartist . "','" . $TS . "',1," . $StationID . "," . $TS . ")";

    foreach ($dbht->query($sql) as $row) {
    }

    # Increment total artists played
    addtotalplay("artist", $StationID);
  }



  # If the artist DOES exist, update the timestamps.


  if ($ArtistExists == 1) {

    if ($fdebug == 1) {
      echo "\nArtist already exists...";
    }
    $i = 0;

    # Update the artist timestamp.

    $i = 0;

    $sql = "UPDATE artists SET lastplayed=" . $TS . ",totalplays=" . ($ArtistPlay + 1) . ",`EligibilityTime`=" . $TS . " WHERE id = " . $ArtistID . " and StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {

      $i++;
    }

    $sql = "UPDATE artistdata SET lastplayed=" . $TS . ",totalplays=" . ($ArtistPlay + 1) . ",`EligibilityTime`=" . $TS . " WHERE artistid = " . $ArtistID . " and StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {
    }

    if ($fdebug == 1) {
      echo "\nArtist lastplayed field was updated...";
    }

    $sql = "UPDATE titles SET ArtistEligibilityTime=" . $TS . " WHERE fullartist = '" . $fullartist . "' and StationID = " . $StationID;

    foreach ($dbht->query($sql) as $row) {
    }

    #return array("result" => 'TRUE', "message" => 'TRUE', "Submessage" => 'Artist updated and Track was created.'); 

    # Increment total artists played
    addtotalplay("artist", $StationID);
  }

  if ($fdebug == 1) {
    echo "\nDone. Returning the artistid:" . $ArtistID . ".";
  }
  return $ArtistID;
}

function updateplayout($TrackID, $Fullartist, $Title, $TS, $Timestamphr, $Guid, $StationID)
{
  include '/var/www/html/config.php';
  global $dbht;

  $i = 0;

  $sql = "INSERT INTO `playoutlog`(`trackid`, `timestamp`, `timestamphr`, `artist`, `title`, `Guid`, `StationID`) VALUES (" . $TrackID . "," . $TS . ",'" . $Timestamphr . "','" . $Fullartist . "','" . $Title . "','" . $Guid . "', " . $StationID . ")";

  # echo $sql;

  foreach ($dbht->query($sql) as $row) {

    $i++;
  }
}

function updatechangelog($TrackID, $Fullartist, $Title, $TS, $Timestamphr, $Guid,  $action, $logtext, $StationID)
{
  include '/var/www/html/config.php';
  global $dbht;

  $i = 0;

  $sql = "INSERT INTO `changelog`(`trackid`, `timestamp`, `timestamphr`, `artist`, `title`, `Guid`, `action`, `logtext`, `StationID`) VALUES (" . $TrackID . "," . $TS . ",'" . $Timestamphr . "','" . $Fullartist . "','" . $Title . "','" . $Guid . "','" . $action . "','" . $logtext . "'," . $StationID . ")";

  # echo $sql;

  foreach ($dbht->query($sql) as $row) {

    $i++;
  }
}


function calculateartistcr($TrackID)
{

  include '/var/www/html/config.php';
  global $dbht;

  # First we must list all artist connected to a certain track. 

  # 8814 => Belle Helene


  $sql = "SELECT t1.Guid,t3.artist,t3.artistid as \"artistid\" FROM `titles` t1 LEFT JOIN artiststitles t2 on t1.Guid=t2.title LEFT JOIN artistdata t3 on t2.artist=t3.artistid WHERE t1.id = " . $TrackID . "";

  $i = 0;
  $fdebug = 0;

  $arrArtists = array();

  foreach ($dbht->query($sql) as $row) {

    $i++;
    array_push($arrArtists, $row["artistid"]);
  }

  # We must then re-calculate the composite rating for all those.

  foreach ($arrArtists as $arrArtist) {



    if ($fdebug == 1) {
      echo "\n Artist to check " . $arrArtist . ".";
    }

    # We need to trigger a full recalculation of this artists all stars.

    $i = 0;
    $sumrating = 0;
    $sql = "SELECT t1.artist, t2.title, t3.stars as \"stars\" FROM `artiststitles` t1 join titles t2 on t1.title = t2.guid join starlog t3 on t2.id = t3.trackid WHERE t1.artist = " . $arrArtist . "";

    if ($fdebug == 1) {
      echo "\nSQL: ".$sql;
    }

    foreach ($dbht->query($sql) as $row) {
      $sumrating = $sumrating + $row["stars"];
      $i++;
    }

    $compositerating = round(($sumrating / $i), 2);

    if ($fdebug == 1) {
      echo "\nCompositerating: ".$compositerating;
    }


    $sql = "UPDATE `artistdata` SET `compositerating`=" . $compositerating . ", `voters`=" . $i . " WHERE artistid=" . $arrArtist . ";";

    if ($fdebug == 1) {
      echo "\nSQL: ".$sql;
    }

    $i = 0;
    foreach ($dbht->query($sql) as $row) {
      $i++;
    }
  }




  return true;
}

function CheckEligibility($fullartist, $TitleLastPlayed, $EligibilityTime, $ArtistEligibilityTime, $StationID, $Duration)
{

  include '/var/www/html/config.php';
  global $dbht;

  $fdebug = 0;

  /*
  The important fields:
  TitleLastPlayed => When a title was last played EXCLUDING when the last request was made.
  EligibilityTime => When a title was last played INCLUDING when the last request was made.
  ArtistEligibilityTime  => When a artist was last played INCLUDING when the last request was made.

  Rationale: TitleLastPlayed is used to determine last playdate and time of a title. A requested track
  will not affect this timestamp. This makes sure the times can be safely be displayed. EligibilityTime will 
  show when a title was played OR when one was added as a request. Whichever comes latest.
  
  Configurations to consider:

  $SameArtistLimit #  How long after an artist's been played on the station many any of that artist's songs may be requested.
  $SameSongLimit # How long after a song's been played on the station may it be requested?
  $SameRequestLimit # How often may one song be requested?

  $SameArtistLimit is validated against $ArtistEligibilityTime
  $SameSongLimit is validated against $TitleLastPlayed
  $SameRequestLimit is validated against EligibilityTime

  That thing must be noted may sound a bit weird. But if a song may be played once per 2 hours and requested
  once per 10 hours, it makes sense that playing it would make it invalidate it for playing even as a request.
  Requesting a song does NOT mean that it has already been played, as requests take time before playing.
    
  */

  # Get the station data
  # Sanity-check: does the station even exist?

  $nowdate = time();

  $StationIDInArray = array_search($StationID, array_column($Stations, 'stationid'));

  $fdebug = 0;

  if ($fdebug == 1) {
    echo "SID:" . $StationIDInArray . "<br>";
  }

  if ($StationIDInArray !== false) {
  } else {
    return array(false, "Station not found");
  }

  if ($fdebug == 1) {
    echo "<br>StationID in array: " . $StationIDInArray;
    echo "<br>Station: " . $Stations[$StationIDInArray]["StationName"];
  }

  # Check the duration of the song.

  $RequestMaxDuration = $Stations[$StationIDInArray]["RequestMaxDuration"];

  if ($Duration > $RequestMaxDuration) {
    return array(false, "Song is too long");
  }

  # Check if the artist is on the FilteredArtists list will automatically fail it.

  if (in_array(strtolower($fullartist), $Stations[$StationIDInArray]["FilteredArtists"]) == true) {

    if ($fdebug == 1) {
      echo "Fail at FilteredArtists";
    }

    return array(false, "Artist filtered");
  }

  # Check if the Title can be played. Fail if not.

  $SameSongLimit = $Stations[$StationIDInArray]["SameSongLimit"];

  if (($nowdate - $TitleLastPlayed) <= ($SameSongLimit * 60)) {

    if ($fdebug == 1) {
      echo "Fail at SameSongLimit (LastPlay: " . $TitleLastPlayed . ". Now: " . $nowdate . ". SameSongLimit: " . $SameSongLimit . "";
    }

    return array(false, "Song was played recently");
  }

  # Check if the artist is on the AlwaysAllowedArtists

  if (in_array(strtolower($fullartist), $Stations[$StationIDInArray]["AlwaysAllowedArtists"]) == true) {
    # It is. Then we will not check ArtistEligibilityTime. This automatically passed this check.
    if ($fdebug == 1) {
      echo "Artistcheck bypassed (AlwaysAllowedArtists)";
    }
  } else {
    # Then check if the artist can be played. Fail if not.

    $SameArtistLimit = $Stations[$StationIDInArray]["SameArtistLimit"];

    if (($nowdate - $ArtistEligibilityTime) <= ($SameArtistLimit * 60)) {

      if ($fdebug == 1) {
        echo "Fail at ArtistEligibilityTime";
      }


      return array(false, "Artist was played recently");
    }
  }

  # Check if the request can be made. That is the request limit.

  $SameRequestLimit = $Stations[$StationIDInArray]["SameRequestLimit"];

  if (($nowdate - $EligibilityTime) <= ($SameRequestLimit * 60)) {

    if ($fdebug == 1) {
      echo "Fail at SameRequestLimit";
    }

    return array(false, "Song has been requested recently");
  }

  # Lastly, we must pass if nothing else has complained.

  if ($fdebug == 1) {
    echo "Pass";
  }


  return array(true, "Song can be requested");
}


function string_search_partial($arr, $keyword)
{
  foreach ($arr as $index => $string) {

    # echo "Does the " . $string  . " exist in the " . $keyword . "?";
    if (strpos($keyword, $string) !== FALSE)
      return true;
  }
}



function array_search_partial($arr, $keyword)
{
  foreach ($arr as $index => $string) {
    if (strpos($string, $keyword) !== FALSE)
      return true;
  }
}

/**
 * By tott (https://gist.github.com/tott/7684443)
 * Check if a given ip is in a network
 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return boolean true if the ip is in this range / false if not.
 */
function ip_in_range($ip, $range)
{
  if (strpos($range, '/') == false) {
    $range .= '/32';
  }
  // $range is in IP/CIDR format eg 127.0.0.1/24
  list($range, $netmask) = explode('/', $range, 2);
  $range_decimal = ip2long($range);
  $ip_decimal = ip2long($ip);
  $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
  $netmask_decimal = ~$wildcard_decimal;
  return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
}

function getip($ip)
{
  include '/var/www/html/config.php';
  global $dbht;

  # This function is meant to lookup IPs from the IP-cache.

  # Check if the ip exists in the lookup table.

  $sql = "SELECT `id`, `IP`, `Country`, `Regionname`, `Isp`, `City`, `Zip`, `Timestamp`, `Timestamphr` FROM `ips` WHERE IP = '" . $ip . "'";

  $i = 0;
  foreach ($dbht->query($sql) as $row) {
    $Country = $row["Country"];
    $Regionname = $row["Regionname"];
    $Isp = $row["Isp"];
    $City = $row["City"];
    $Zip = $row["Zip"];
    $Timestamp = $row["Timestamp"];
    $Timestamphr = $row["Timestamphr"];
    $i++;
  }

  # If it exists, return city and country.

  if ($i == 1) {

    $ipdetails = array(
      ("Country") => $Country,
      ("Regionname") => $Regionname,
      ("Isp") => $Isp,
      ("City") => $City,
      ("Zip") => $Zip,
      ("Timestamp") => $Timestamp,
      ("Timestamphr") => $Timestamphr
    );

    return $ipdetails;
  } else {

    $ipdetails = array(
      ("Country") => '',
      ("Regionname") => '',
      ("Isp") => '',
      ("City") => '',
      ("Zip") => '',
      ("Timestamp") => '',
      ("Timestamphr") => ''
    );
    return $ipdetails;
  }
}


function checkip($ip, $Country, $Regionname, $Isp, $City, $Timestamp, $Timestamphr, $Zip)
{
  include '/var/www/html/config.php';
  global $dbht;

  # This function is meant to cache ip to City/Country mappings as these are 
  # "expensive" to lookup. The API services are limited to number of lookup they allow per minute.

  # Check if the ip exists in the lookup table.

  $sql = "SELECT `id`, `IP`, `Country`, `Regionname`, `Isp`, `City`, Zip, `Timestamp`, `Timestamphr`, `Hits` FROM `ips` WHERE IP = '" . $ip . "'";

  $i = 0;
  foreach ($dbht->query($sql) as $row) {
    $Country = $row["Country"];
    $Regionname = $row["Regionname"];
    $Isp = $row["Isp"];
    $City = $row["City"];
    $Zip = $row["Zip"];
    $Timestamp = $row["Timestamp"];
    $Timestamphr = $row["Timestamphr"];
    $Hits = $row["Hits"];
    $i++;
  }

  # If it exists, return city and country

  if ($i == 1) {

    $ipdetails = array(
      ("Country") => $Country,
      ("Regionname") => $Regionname,
      ("Isp") => $Isp,
      ("City") => $City,
      ("Zip") => $Zip,
      ("Timestamp") => $Timestamp,
      ("Timestamphr") => $Timestamphr
    );

    # Increments the hits ("Heat map")

    $Hits++;

    $sql = "UPDATE `ips` SET `Hits` = $Hits WHERE IP = '" . $ip . "'";
    $i = 0;
    foreach ($dbht->query($sql) as $row) {
    }

    return $ipdetails;
  } else {

    # If it does not, add it.
    $sql = "INSERT INTO `ips`(`IP`, `Country`, `Regionname`, `Isp`, `City`, `Zip`, `Timestamp`, `Timestamphr`,`Hits`)
       VALUES ('" . $ip . "','" . $Country . "','" . $Regionname . "','" . $Isp . "','" . $City . "','" . $Zip . "'," . $Timestamp . ",'" . $Timestamphr . "',0)";

    #echo $sql;

    $i = 0;
    foreach ($dbht->query($sql) as $row) {
    }

    $ipdetails = array(
      ("Country") => "",
      ("Regionname") => "",
      ("Isp") => "",
      ("City") => "",
      ("Zip") => "",
      ("Timestamp") => "",
      ("Timestamphr") => ""
    );

    return $ipdetails;
  }

  # In the future, we might want to add a "Stale"-check to update the information if it changes.

}
