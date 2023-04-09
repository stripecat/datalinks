# ezdatalinks
A backend database and request system for PlayIT Live.

![How it works as a diagram](https://ericade.radio/assets/img/datalinksdiagram.png)

1. The user loads the page with the request form.
2. The www-site returns the page. User types a name of an artist or song.
3. The webbrowser first obtains a token from the www-site for the user and asks the api for a list matching the search. The token is then sent along with the request to the API.
4. A list of songs is sent back from the API.
5. The user selects the song and clicks the "request song"-button. The webbrowser then calls the API after obtaining a token by the www-site.
The API either rejects the request or accepts it. On acceptance, it is put in the QueuedRequests table.
6. On every hour at 9 and 39 minutes past the hour, the Powershellscript asks if there are any new requests in the queue. If a request exists it will be handled. The script merges an stationID (e.g. "This is a brand new request from an Internet user) with the song requested. When done, it instructs the API that the song has been put in the station.
7. The API moves the data from the QueuedRequests to the PlayedRequest table.

WARNING:
This software is published "as is". I cannot provide support for it. It requires an intermediate skill in system administration and preferably some programming skills. Expect no "Next, next finish".

It will take a while until the "web module" has a its excentricies ironed out.

What you need

- A Linux box (Ubuntu recommended)
- PHP 7.2 or better
- A virtual host to run it on. (Not the same as the one you run the station web site on)
- A MariaDB or MySQL instance.
- DNS-name. Recommended: api.yourdomain.tld. (create it as a subdomain called api. If your station is called ericade.radio, then it should be api.ericade.radio)
- A busload of patience.

# How it works
PlayIT Live will not allow you to reach its database, nor is there an API for it. This means, all data has to be exported. EZDatalinks gets its data from the "Now Playing" plugin. Everytime a song is played, the "Now playing"-plugin will send its data to EZDatalinks via the API-endpoint /radio/updatetrack/. The endpoint will add songs it does not know anything about or update the last played times for songs it knows.

Please note: the api.domain.tld must be its own domain. It can off course be hosted on the same box as the frontend. But it needs to be its own VHOST in Apache or NGinx.

# Pre-requisites

You will need one system running Windows for your PlayIT Live instance. 

You will need another system running Linux. I have tested all this on Ubuntu 22.04. But I believe you can use any
modern distribution. This system needs a vhost for the front and one for the api. If you own a domain for your station, it's 
recommended you create a subdomain called api, so the API can be reached on api.yourdomain.com.

If you have PlayIT Live running and a website for your station, you're probably already setup this way with two different servers.

Running the frontend and the api on different computers is possible. But the computer using the api must have Maridb or mysql on the same server as the API. If you have them on different computers, you may get performance problems.


# The Internet facing API

Import the table-file call extras/RadioAPI.sql from the Github clone into MariaDB or Mysql. 

```
Easiest way:
mysql -u root < RadioAPI.sql
```

This will create an empty tablestructure in a database called RadioAPI. Create a user with full permissions to the database with mysql -u root. 
 
```
CREATE USER radio@localhost IDENTIFIED BY 'cdE#4rFVbgT%';
```

(Don't use that particular password. Also remember it must be added to config.php in the API-website.)

Then grant the permissions to the user.
No superdbuser privileges are needed for the API. Just all commands (SELECT, INSERT, UPDATE and DELETE).

```
GRANT ALL ON RadioAPI.* TO radio@localhost;
```

 And to activate the privileges in the database.

```
FLUSH PRIVILEGES;
```

Remember to go into config.php and update the database settings.

## The Virtual host

Make sure you have a vhost for the API under Apache2 or Nginx. This vhost must have a DNS setup, pointing to it. Mine is called api.ericade.net and I use Certbot to get free certificates from Letsencrypt. How to set this up is out of the scope of this discussion. You don't have to have https enabled, but it's not recommended to run only http. In reality, HTTPS is pretty mandatory as Google lowers rankings for site without it. Also the API cannot be httponly, if the site generating the calls has https.

Unpack the zipfile from Github. Omit the folder called "Frontend" and the file called "templatedata.sql". The index.htm should be in the root. Now open the config.php and configure the database-settings. The configfile is self-explanatory. Please go through the config-file and set it as needed.

Recommendation: make sure your IDE (development console) knows about PHP. This makes it easy not to break stuff.

Search for "/var/www/html/" in all .php-files and replace it with your own path on all php-files. If you're not sure what the path is, navigate to the folder that you put all the files for the api. Then type in pwd. This will give you information. I will try to fix a better solution for this in the future. Sorry, a bit of lazy coding :)

Set a good password in the config-file.

# Setting on your PlayIT Live host

Copy the text hereunder and use it in the "Now playing"-plugin to create the caller function for the whole setup. 
To do so, select the HTTPWebRequest tab, set it to send POST and past it into POST-body. No need to fill in any login information.

Please make sure the password you use for the API-calls and for the database are not the same.

```
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
```

Set it up to send this to the API-endpoint (api.yourdomain.tld/radio/updatetrack/). This field is called "URL:".

The PlayIt live machine and the EZDataLinks do not (should not) have to be the same. Running the API on Windows should be possible, but is not recommended. I run the base plaform on Windows 11 and my original version of EZDataLinks runs on a Linux box under Hyper-V.

Please note that running the radio www-page and the API on the same machine but as different VHOSTs is probably the best solution. Remember that machine needs to have MariaDB or MySQL.

Now test that the database is filling up with songs from the station.

# Windows request loader

Location: the server running PlayIt Live.

Please go to the main server for PlayIt Live. 

Next up is the requestloader. It´s a Powershell script, that will check if there are any files to play and also notify the API that a song have been added to the slot. There are two slots, so every hour allows for two requests. It´s ill-adviced to try to use more than 2. Also it´s not supported, so it will need some extra coding to work.

C:\ezdatalinks\ is the root path.
Copy the file the folder ezdatalinks (From the folder called "Extras") to the PIL broadcast-machine so it becomes C:\ezdatalinks\.

Now open C:\ezdatalinks\RequestLoader.ps1.

Make sure $Destination_dir is set to "C:\ezdatalinks\RequestLoader".

If you haven't run scripts on this server, execution may be prohibited. If so, open a Powershell-prompt in administrative mode and run this command "set-executionpolicy remotesigned".

Run the script once to create all necessary folders.

Under c:\ezdatalinks\request\templatefiles you will find the file intreq.mp3. That is the station id that will play before the requested song. Please replace that with your own stationid. The one provided is for my station. You can experiment with it will testing that everything works.

The script needs to run once per minute. It will the do housekeeping or load the next waiting request. At start, it checks the minute. If it's 9 or 39 minutes past the hour, it will check with the API to see if there is a request to play. If the api responds with pending request, it will create a file in "C:\ezdatalinks\request" called requestx.wav. Requests playing a 10 past the hour are bound to "slot1" and those playing at 40 minutes past the hour are called "request2". The file is thus either request1.wav or request2.wav.

## Make sure the script runs every minute (with Microsoft Task Scheduler)

Create a scheduled task and call it "RequestLoader". 
Make sure it runs every minute of the hour.

![scheduled task time](https://ericade.radio/assets/img/scheduler.png)

Make it run the script %SystemRoot%\system32\WindowsPowerShell\v1.0\powershell.exe and this as the arguments -NoProfile -NoLogo -NonInteractive -ExecutionPolicy Bypass -File "C:\ezdatalinks\RequestLoader.ps1". 

![scheduled task script](https://ericade.radio/assets/img/scheduler-ps.png)

Set it to stop if its still running after an hour. This should not happen, but rather safe than sorry.

Set it to run with the Windows-account use to login when you run PlayIt Live.

![scheduled task credentials](https://ericade.radio/assets/img/scheduler-cred.png)

# PlayIt Live

You need to create two "Scheduled events" in playit live. They are there to look after a requested tune. If there is no file, the events wont fire. This means that the playout will work as normal. One event should fire at 10 past the hour and the other one at 40 minutes past the hour.

1. Create two empty files: C:\ezdatalinks\request\request1.wav and C:\ezdatalinks\request\request2.wav
2. Go to PlayIt live and create a scheduled event.Call the first scheduled event "Play request from Internet - Slot 1"
3. Make it run daily on every hour and at 09 minutes past the hour.
4. Click "Add action" (green plus sign in the bottom of the Scheduled event).
5. Add action to "Insert track" and select From: File and point to the the filename C:\ezdatalinks\request\request1.wav.
   Remember that this file and the whole webserver is just for PlayIt live. The API and front should run on an Internet facing server.
6. Call the second "Scheduled event" "Play request from Internet - Slot 2"
7. Click "Add action" (green plus sign in the bottom of the Scheduled event).
8. Add action to "Insert track" and select From: File and point to the the filename C:\ezdatalinks\request\request2.wav.

With this the backend is setup properly.

# Web frontend

The front end needs the ticket generator and code. Those are located under the folder "Extras/Frontend" in the package. Upload the structure to your frontend. Request.php is the page that you use to request tunes. It's rather raw, so you can use it with your own CSS-structure.
