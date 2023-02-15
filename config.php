<?php

# Database connection details.

$dbms = 'mssql';
$dbhost = 'localhost';
$dbport = '';
$dbname = 'RadioAPI';
$dbuser = 'radio';
$dbpasswd = '<password for db>';
$debugmode = 1; # Probably not in use.
$syspath = "/var/www/html/api.moleant.com/";

# Password authentication for the PlayIt Live JSON.

$apipwd="<password>"; # Only for ingest!
# JWT
# This is used for external communication to identify the browser and ensure its
# temporary id without storing cookies. Not a ID or security mechanism.
# Only used to deter people from cheating with stars and requests.
# What? You don't think people cheat? What planet are you from?

$apikeyseed = '<<Password>>'; # Super-secret password for the ticket encryption. Change before deploying.
$iss = 'http://example.org';
$aud = 'http://example.com';
$iat = 1356999524;
$nbf = 1357000000;

# Throttling for SQL-queries for the report-APIs.

$max_total_queries=50000; # Probably not in use. Need to check before removing. Code stolen from Discover API.
$max_item_queries=5000; # Probably not in use. Need to check before removing. Code stolen from Discover API.
$maxticketoffset = 0; # Probably not needed. Ticket replay protection is kinda pointless, as it's not a security feature as implemented here. But don't remove. Something could break.

# Inclusion

$InternalIPRanges = array("192.168.74.0/24","192.168.1.0/24","192.168.42.0/24"); # IPs considered internal to the station's physical location. Used by some filters.
$InternalIngestIPRanges = array("192.168.74.0/24"); # IPs allowed to send track updates to the API.

# Exclusion

$InternalExcludeShoutcastIPRanges = array("192.168.74.20/32"); # ips that will not be returned by the getlistener service. Only valid for current listeners, not the log.

# SpecialEvents

$ForceGroupEvaluation = 0; # Set to 1 to force the scener <-> group mapping to be active. It will put more pressure on the DB as
                          # as group membership will be checked even for tracks that already exists. Please use sparingly. Default: 0.

# Stream login to Shoutcast

$sc_username = "admin";
$sc_password = "<<Password>>";
$sc_remote_url = 'http://192.168.74.20:8000/';
$sc_server="192.168.74.20";

# Station specific settings

$Stations = array(
    (0) => array(
        ("stationid") => 1, # A number, defining the station. Must be unique, but no need to have it in a specific order. Use numbers between 1-99.
        ("AllowRequests") => 1, # If the station will allow requests. 0 = No, 1 = Yes.
        ("RequestPrefix") => array("listener-request"), # Prefix for the request song. Will be shown before the Artistname in the artistfield. It must specified in FilteredArtistsFromIngest as well.
        ("StationName") => '24/7 tracked music', # The name of the station.
        ("StreamingOffset") => 10, # The feed to the shoutcast-system can have the change of songs delayed in order to compensate for the buffering. In seconds.
        ("TrackPurgeAllowed") => 1, # Allow tracks not played to be purged. Not implemented yet.
        ("PurgeDays") => 21, # How many days a track must not have been played to be purged. Not implemented yet.
        ("FilteredArtistsFromIngest") => array("listener-request"), # Entries will not be stored in the database. Will still show as "now playing". Please note each entry allows for partial matching. All artists must be in lower case.
        ("SameArtistLimit") => 60, #  How long after an artist's been played on the station many any of that artist's songs may be requested.
        ("SameSongLimit") => 60, # How long after a song's been played on the station may it be requested?
        ("SameRequestLimit") => 300, # Seconds before the same song can be requested again.
        ("SlotsPerHour") => 2, # Requests that are possible per hour. 2 is probably what you want. Should be carefully considered.
        ("RequestMaxDuration") => 650, # Longest song that may be requested. In seconds.
        ("MaxPendingRequests") => 10, # Max number of requests that may exist on the station at any given moment.
        ("ValidityDays") => 1, # Days that must pass before a track may be requested again from one identified browser. 
        ("FilteredArtists") => array("jingles","ericade.radio","announcement","advert"), # Filtered from searches.  All artists must be in lower case.
        ("NonCalculatedArtists") => array("jingles","ericade.radio","announcement","advert"), # Will not be considered when calculating total playtime. All artists must be in lower case.
        ("AlwaysAllowedArtists") => array("trackerartist"), #  Artists that can always be requested. All artists must be in lower case. Removed2022-10-28:"trackerartist","allister brimble","dr. awesome","xyce"
        ("PurgeProtectedArtists") => array("jingles","ericade.radio","announcement","advert") # Will not ever have their tracks removed due to not being played. All artists must be in lower case.
    ),
    (1) => array(
        ("stationid") => 2, # A number, defining the station. Must be unique, but no need to have it in a specific order. Use numbers between 1-99.
        ("AllowRequests") => 0, # If the station will allow requests. 0 = No, 1 = Yes.
        ("RequestPrefix") => array("listener-request"), # Prefix for the request song. Will be shown before the Artistname in the artistfield. It must specified in FilteredArtistsFromIngest as well.
        ("StationName") => 'Best of ERICADE.radio', # The name of the station.
        ("StreamingOffset") => 10, # The feed to the shoutcast-system can have the change of songs delayed in order to compensate for the buffering. In seconds.
        ("TrackPurgeAllowed") => 0, # Allow tracks not played to be purged. Not implemented yet.
        ("PurgeDays") => 21, # How many days a track must not have been played to be purged. Not implemented yet.
        ("FilteredArtistsFromIngest") => array("listener-request"), # Entries will not be stored in the database. Will still show as "now playing". Please note each entry allows for partial matching. All artists must be in lower case.
        ("SameArtistLimit") => 60, #  How long after an artist's been played on the station many any of that artist's songs may be requested.
        ("SameSongLimit") => 60, # How long after a song's been played on the station may it be requested?
        ("SameRequestLimit") => 300, # Seconds before the same song can be requested again.
        ("SlotsPerHour") => 2, # Requests that are possible per hour. 2 is probably what you want. Should be carefully considered.
        ("RequestMaxDuration") => 420, # Longest song that may be requested. In seconds.
        ("MaxPendingRequests") => 10, # Max number of requests that may exist on the station at any given moment.
        ("ValidityDays") => 1, # Days that must pass before a track may be requested again from one identified browser. 
        ("FilteredArtists") => array(""), # All artists must be in lower case.
        ("NonCalculatedArtists") => array("jingles","ericade.radio","announcement","advert"), # Will not be considered when calculating total playtime. All artists must be in lower case.
        ("AlwaysAllowedArtists") => array(""), # Artists that can always be requested. All artists must be in lower case.
        ("PurgeProtectedArtists") => array("jingles","ericade.radio","announcement","advert") # Will not ever have their tracks removed due to not being played. All artists must be in lower case.
    )

);

# Misc stuff.

# The different paths for the podcast episodes.

$newstem = "https://radio.ericade.net/Flashback/";
$oldstem = "https://radio.ericade.net/AmigaFlashback/";
$newfstem = "/var/www/html/radio.ericade.net/Flashback/";
$oldfstem = "/var/www/html/radio.ericade.net/AmigaFlashback/";

?>