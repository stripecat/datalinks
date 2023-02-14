# ezdatalinks
A backend database and request system for PlayIT Live.

![How it works as a diagram](https://ericade.radio/assets/img/datalinksdiagram.png)

1. The user loads the page with the request form.
2. The www-site returns the page. User types a name of an artist or song.
3. The webbrowser first obtains a token from the www-site for the user and asks the api for a list matching the search. The token is then sent along with the request to the API.
4. A list of song is sent back from the API.
5. The user selects the song and clicks the "request song"-button. The webbrowser then calls the API after obtaining a token by the www-site.
The API either rejects the request or accepts it. On acceptance, it is put in the QueuedRequests table.
6. On every hour at 9 and 39 minutes past the hour, the Powershellscript asks if there are any new requests in the queue. If a request exists it will be handled. The script merges an stationID (e.g. "This is a brand new request from an Internet user) with the song requested. When done, it instructs the API that the song has been put in the station.
7. The API moves the data from the QueuedRequests to the PlayedRequest table.

WARNING:
This software is published "as is". I cannot provide support for it. It requires an intermediate skill in system administration and preferably some programming skills. Expect no "Next, next finish".

I have a very long time to go before this "web module" has a its excentricies ironed out. The shoutcast module is still in, even though I intend to remove it in the future.

What you need

- A Linux box (Ubuntu recommended)
- PHP 7.2 or better
- A virtual host to run it on.
- A MariaDB or MySQL instance.
- DNS-name. Recommended: api.yourdomain.com.
- A busload of patience.

How it works
PlayIT Live will not allow you to reach its database, nor is there an API for it. This means, all data has to be exported. EZDatalinks gets its data from the "Now Playing" plugin. Everytime a song is played, the "Now playing"-plugin will send its data to EZDatalinks via the API-endpoint /radio/updatetrack/. The endpoint will add songs it does not know anything about or update the last played times for songs it knows.

Please note: the api.domain.tld must be its own domain. It can off course be hosted on the same box as the frontend. But it needs to be its own VHOST in Apache or NGinx.

==Pre-requisites==


You will need one system running Windows for your PlayIT Live instance. 

You will need another system running Linux. I have tested all this on Ubuntu 22.04. But I believe you can use any
modern distribution. This system needs a vhost for the front and one for the api. If you own a domain for your station, it's 
recommended you create a subdomain called api, so the API can be reached on api.yourdomain.com. 

If you have PlayIT Live running and a website for your station, you're probably already setup this way.

===The Internet facing API ===

Import the table-file call extras/RadioAPI.sql from the Github clone into MariaDB or Mysql. This will create an empty tablestructure in a database called RadioAPI. Create a user with full permissions to the database with mysql -u root. No superdbuserprivileges is needed for the API. Just all commands (SELECT, INSERT, UPDATE and DELETE).

Make sure you have a vhost for the API under Apache2 or Nginx. This vhost must have a DNS setup, pointing to it. Mine is called api.ericade.net and I use Certbot to get free certificates from Letsencrypt. How to set this up is out of the scope of this discussion. You don't have to have https enabled, but it's not recommended to run only http. In reality, HTTPS is pretty mandatory as Google lowers rankings for site without it.

Unpack the zipfile from Github. Omit the folder called "Frontend" and the file called "templatedata.sql". The index.htm should be in the root. Now open the config.php and configure the database-settings. The configfile is self-explanatory. Please go through the config-file and set it as needed.

Recommendation: make sure your IDE (development console) knows about PHP. This makes it easy not to break stuff.

Search for "/var/www/html/api.ericade.net/" in all .php-files and replace it with your own path on all php-files. If you're not sure what the path is, navigate to the folder that you put all the files for the api. Then type in pwd. This will give you information. I will try to fix a better solution for this in the future. Sorry, a bit of lazy coding :)

Set a good password in the config-file.

Copy the text hereunder and use it in the "Now playing"-plugin to create the caller function for the whole setup. 
To do so, select the HTTPWebRequest tab, set it to send POST and past it into POST-body. No need to fill in any login information.

{
	"Password": "<the password you typed into the config-file>",
	"Artist": "{{artist}}",
	"Title": " {{title}}",
	"Comments": "{{comments}}",
	"Album": "{{album}}",
	"Genre": "{{genre}}",
	"Year": "{{year}}",
	"Duration": "{{duration}}",
	"OutCue": "{{outcue}}",
	"Tags": "{{tags}}",
	"Disabled": "{{disabled}}",
	"Segue": "{{segue}}",
	"Path": "{{path}}",
	"Type": "{{type}}",
	"Intro": "{{intro}}",
	"CueIn": "{{cueIn}}",
	"CueOut": "{{cueOut}}",
	"Added": "{{added}}",
	"Sweeper": "{{sweeper}}",
	"NoFade": "{{nofade}}",
	"ValidFrom": "{{validfrom}}",
	"Expires": "{{expires}}",
	"Guid": "{{guid}}",
	"StationID": "1"
}

Set it up to send this to the API-endpoint (api.youdomain.tld/radio/updatetrack/). This field is called "URL:".

The PlayIt live machine and the EZDataLinks do not have to be the same. I run the base plaform on Windows 11 and my original version of EZDataLinks runs on a Linux box under Hyper-V.

Now test that the database is filling up with songs from the station.

=======================
Windows request loader
=======================

Location: the server running PlayIt Live.

Please go to the main server for PlayIt Live. 

Next up is the requestloader. It´s a Powershell script, that will check if there are any files to play and also notify the API that a song have been added to the slot. There are two slots, so every hour allows for two requests. It´s ill-adviced to try to use more than 2. Also it´s not supported, so you will have to program that yourself to make it work.

Install the webserver-role on the main server for PlayIt Live. This will only be used by PlayIt Live, so it should not be set to listen on any ip-adress other than 127.0.0.1.

C:\inetpub\wwwroot\ is the root path. Create the folders radio\request in the webroot.
Copy the file requestloader.ps1 (From folder called "Powershell") to the PIL broadcast-machine under c:\scripts\, then open it there:

Set $Destination_dir to "C:\inetpub\wwwroot\radio\request".

Edit the declarations to match your settings. Download the latest version of FFMPEG from here (https://www.gyan.dev/ffmpeg/builds/). Put it in the c:\scripts.

Go to C:\inetpub\wwwroot\radio\request and create \templatefiles\. Put the announcing ident ("You are listening to KRUD 95.7, here is a request from a listener") under it and call it intreq.mp3.

The script needs to run once per minute. It will the do housekeeping or load the next waiting request. At start, it checks the minute. If it's 9 or 39 minutes past the hour, it will check with the API to see if there is a request to play. If the api responds with pending request, it will create a file in "C:\inetpub\wwwroot\radio\request" called slotx.wav. Requests playing a 10 past the hour are bound to "slot1" and those playing at 40 minutes past the hour are called "slot2". The file is thus either slot1.wav or slot2.wav.

Next, create a scheduled task and call it "RequestLoader". Make it run the script %SystemRoot%\system32\WindowsPowerShell\v1.0\powershell.exe and this as the arguments -NoProfile -NoLogo -NonInteractive -ExecutionPolicy Bypass -File "C:\Users\Erik Zalitis Admin\Desktop\Scripts\RequestLoader.ps1" 

Set it to stop if its still running after and hour. This should not happen, but rather safe than sorry.

============
PlayIt Live
============

You need to create two "Scheduled events" in playit live. They are there to look after a requested tune. If there is no file, the events wont fire. This means that the playout will work as normal. One event should fire at 10 past the hour and the other one at 40 minutes past the hour.

1. Call the first "Scheduled event" "Play request from Internet - Slot 1"
2. Make it run daily on every hour and at 09 minutes past the hour.
3. Click "Add action" (green plus sign in the bottom of the Scheduled event).
4. Add action to "Insert track" and select From: File and point to the the filename C:\inetpub\wwwroot\radio\request\request1.wav.
   Remember that this file and the whole webserver is just for PlayIt live. The API and front should run on an Internet facing server.
5. Call the second "Scheduled event" "Play request from Internet - Slot 2"
6. Click "Add action" (green plus sign in the bottom of the Scheduled event).
7. Add action to "Insert track" and select From: File and point to the the filename C:\inetpub\wwwroot\radio\request\request2.wav.

With this the backend is setup properly.

============
Web frontend
============

The front end needs the ticket generator and code. Those are located under the folder "Frontend" in the package.

