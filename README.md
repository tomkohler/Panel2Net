Installation:

On Raspberry Pi
a) create directory Panel2Net in /home/pi
b) copy Panel2Net.py into /home/pi/Panel2Net
c) enable autostart of python script as described in autostart

On Apache Server either on Raspberry Pi or on any other Linux Server:
a) install apache server "apt-get install apache2 -y" then "a2enmod rewrite" then "service apache2 restart"
b) install php with "apt-get install php libapache2-mod-php -y"
c) create directory /www/abcd/
d) copy all 4 .php files into that directory

List of Files

Panel2Net.py - Python script to push data from the Panels to the Apache Server

Mobatime.php - PHP script to decode Mobatime serial info and compile an XML history and last action

Stramatel.php - PHP script to decode Mobatime serial info and compile an XML history and last action

SwissTiming.php - PHP script to decode Mobatime serial info and compile an XML history and last action

2017-10-09 Spec Basketball Panel Data Handling V8 - Specification of Interfaces

2017-12-10 Scorebug Installation and Usage Manual V1
