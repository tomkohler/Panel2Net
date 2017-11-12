# Protoype for Panel2Net
# Reads serial data and pushes it via HTTP POST onto the net
# runs on Raspberry Pi
# Thomas Kohler (C) 2017

# imports
# import threading
import serial
import http.client
import logging
import urllib.request, urllib.parse, urllib.error
import time

# Configuration Data (later to be put in Panel2Net.conf)
# SerialPort: Name of RPi serial port receiving the panel data
SerialPort = '/dev/ttyUSB0'
# BaudRate: Serial port speed (Baud)
BaudRate = 9600
# DataBits: Number of DataBits (7, 8)
DataBits = 8
# StopBits: Number of StopBits
StopBits = 1
# Parity Check: (NONE, EVEN, ODD, SPACE)
Parity = 'NONE'
# FlowControl: FlowControl Mechanism
FlowControl = 'NFC'
# PackageByTime: Time Duration until a package is closed
# and sent off (seconds)
PackageByTime = 0.1
# PackageByLength*	Length (in bytes) of data input
# until a package is closed and sent off
PackageByLength = 128

# WebPort: Port over which the HTTP data is sent
WebPort = 'wlan0'
# RequestMode: GET or POST
RequestMode = 'POST'
# RequestServer: Server IP or Name
RequestServer = 'swb.world'
# RequestPort: Port over which HTTP request is being placed
RequestPort = 80
# RequestURL	URL to be put after the Server IP or Name to construct URL
RequestURL = '/abcd/index.php'
# RequestHeader: HTTP Customer Headers
RequestHeader = 'Content-Type: text/plain; Connection: keep-alive'
# RequestTimeOut: Duration for the HTTP request to wait
# for an answer before aborting (seconds)
RequestTimeOut = 10

# LogFileName: Name of LogFile
LogFileName = '/tmp/Panel2Net.log'
# LogFileSize Maximum Size of LogFile (in Mbytes)
LogFileSize = 10
# LogLevel	Minimum Severity Level to be logged (see severity in Logs)
LogLevel = 'E'

# Note: Logging not implemented, serial data hardcoded
# Step 0: Interface initialisation

logging.basicConfig(level=logging.ERROR,
                    format='%(asctime)s %(levelname)s %(message)s',
                    filename=LogFileName,
                    filemode='w')

ser = serial.Serial()
ser.port = SerialPort
ser.baudrate = BaudRate
ser.bytesize = serial.EIGHTBITS
# number of bits per bytes
ser.parity = serial.PARITY_NONE
# set parity check: no parity
ser.stopbits = serial.STOPBITS_ONE
# number of stop bits
ser.timeout = PackageByTime
# non-block read
ser.xonxoff = False
# disable software flow control
ser.rtscts = False
# disable hardware (RTS/CTS) flow control
ser.dsrdtr = False
# disable hardware (DSR/DTR) flow control
ser.writeTimeout = 2
# timeout for write

try:
    ser.open()
except Exception as e:
    print("error open serial port: " + str(e))
    logging.error("error open serial port: " + str(e))
    exit()

# Step 1: Read -> Write Loop

if ser.isOpen():

    try:
        ser.flushInput()
        # flush input buffer, discarding all its contents
        ser.flushOutput()
        # flush output buffer, aborting current output
        # and discard all that is in buffer
        RequestCount = 0
        
        while True:
            response = ser.read(PackageByLength).decode('utf-8')
            if len(response) > 0:
                logging.debug(response)
                StarterTime = time.time() * 1000
                headers = {"Content-type": "application/x-www-form-urlencoded",
                "Accept": "text/plain", "Device_ID": "TEST2017"}
                conn = http.client.HTTPConnection(RequestServer,RequestPort)
                conn.request("POST", RequestURL, response, headers)
                httpreply = conn.getresponse()
                if httpreply.status == 200:
                    logging.debug(str(httpreply.status) + " " + str(httpreply.reason))
                else:
                    logging.error(str(httpreply.status) + " " + str(httpreply.reason))    
                RequestCount = RequestCount + 1    
                logging.debug("RequestCount: " + str(RequestCount))
                EnderTime = time.time() * 1000
                ElapserTime = int(EnderTime - StarterTime)
                print("RequestCount: " + str(RequestCount) + ": " + str(ElapserTime)
                      + " ms -> " + str(httpreply.status))
                logging.debug(str(ElapserTime) + " ms")
  
            # ser.close()
    except Exception as e1:
        print("error communicating...: " + str(e1))
        logging.error("error communicating...: " + str(e1))

else:
    print("cannot open serial port ")
