Objective of the project: Upload data from Serial Interface to Web for Mobatime, SwissTiming, Stramatel basketball or multisports panels using a low-cost approach 

Required Hardware (all available on Amazon or Aliexpress):
* RPi3
* RS232 or RS422 cable
* Handmade Cable fitting the panel-maker specifications (see documentation)
* LAMP Server (I use a Droplet from Digital Ocean, but theoretically can sit also on RPi)

Software Installation:
On Raspberry Pi
a) create directory Panel2Net in /home/pi
b) copy Panel2Net.py into /home/pi/Panel2Net
c) enable autostart of python script as described in autostart

On LAMP Server:
a) install apache server "apt-get install apache2 -y" then "a2enmod rewrite" then "service apache2 restart"
b) install php with "apt-get install php libapache2-mod-php -y"
c) create directory /www/abcd/
d) copy all .php files into that directory

List of Files

Panel2Net.py - Python script to push data from the Panels to the Apache Server
Panel2Net.id - Hardware Identification (unique name of the device)

Mobatime.php - PHP script to decode Mobatime serial info and compile an XML history and last action
Stramatel.php - PHP script to decode Mobatime serial info and compile an XML history and last action
SwissTiming.php - PHP script to decode Mobatime serial info and compile an XML history and last action

mobatime_lausanne_massagno.txt - Mobatime test data
SwissTiming_NEU_PUL_20171125 (1).txt - SwissTiming test data
Stramatel_GEN_HEL_20171125.txt - Stramatel test data

Spec Basketball Panel Data Handling - Specification of Interfaces
Scorebug Installation and Usage Manual - Manual for Installationa and Usage (to be handed to the clubs)
SB_Scorebug Support Document - Documentation for System Administrators

SBL_Scoreboard 2017_Autoscore5.BPres - XSplit Broadcaster Presentation configuration allowing XSplit to
take the results and put it into lower thirds automatically

