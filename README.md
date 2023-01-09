# ezdatalinks
A backend database and request system for PlayIT Live.

WARNING:
This software is published "as is". I cannot provide support for it. It requires an intermediate skill in system administration and preferably some programming skills. Expect no "Next, next finish".

I have a very long time to go before this "web module" has a its excentricies ironed out. The shoutcast module is still in, even though I intend to remove it for an instance.

What you need

- A Linux box (Ubuntu recommended)
- PHP 7.2 or better
- A virtual host to run it on.
- DNS-name. Recommended: api.yourdomain.com.
- A busload of patience.

How it works
PlayIT Live will not allow you to reach its database, nor is there an API for it. This means, all data has to be exported. EZDatalinks gets its data from the "Now Playing" plugin. Everytime a song is played, the "Now playing"-plugin will send its data to EZDatalinks via the API-endpoint /radio/updatetrack/. The endpoint will add songs it does not know anything about or update the last played times for songs it knows.

To install
Import the table-file call templatedata.sql from the Github clone. This will create an empty instance. Create a user with full permissions to the database. No superdbuserprivileges needed.

Make sure you have a vhost under Apache2 or Nginx. Unpack the zipfile from Github. The index.htm should be in the root. Now open the config.php and configure the database-settings. The configfile is self-explanatory. Please go through the config-file and set it as needed.

Recommendation: make sure your IDE (development console) knows about PHP. This makes it easy not to break stuff.

Set a good password in the config-file. Open the file JSON-REQ.txt in the root and use it in the "Now playing"-plugin to create the caller function for the whole setup. The PlayIt live machine and the EZDataLinks do not have to be the same. I run the base plaform on Windows 11 and my original version of EZDataLinks runs on a Linux box under Hyper-V.

Now test that the database is filling up with songs from the station.

=======================
Windows request loader
=======================

Please go to the main server for PlayIt Live. 

Next up is the requestloader. It´s a Powershell script, that will check if there are any files to play and also notify the API that a song have been added to the slot. There are two slots, so every hour allows for two requests. It´s ill-adviced to try to use more than 2. Also it´s not supported, so you will have to program that yourself to make it work.

Install the webserver-role on the main server for PlayIt Live. This will only be used by PlayIt Live, so it should not be set to listen on any ip-adress other than 127.0.0.1.

C:\inetpub\wwwroot\ is the root path. Create the folders radio\request in the webroot.
Copy the file requestloader.ps1 to the PIL broadcast-machine under c:\scripts\, then open it there:

Set $Destination_dir to "C:\inetpub\wwwroot\radio\request".

Edit the declarations to match your settings. Download the latest version of FFMPEG from here (https://www.gyan.dev/ffmpeg/builds/). Put it in the c:\scripts.

Go to C:\inetpub\wwwroot\radio\request and create \templatefiles\. Put the announcing ident ("You are listening to KRUD 95.7, here is a request from a listener") under it and call it intreq.mp3.



