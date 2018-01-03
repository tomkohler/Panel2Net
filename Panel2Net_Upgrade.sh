echo "Checking if there is a new version..."
sudo curl -s -o /home/pi/Panel2Net/Panel2Net.py https://raw.githubusercontent.com/tomkohler/Panel2Net/master/Panel2Net.py
sudo chmod 777 /home/pi/Panel2Net/Panel2Net.py
sudo /usr/bin/python3 /home/pi/Panel2Net/Panel2Net.py
