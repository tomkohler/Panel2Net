# Protoype for Panel2Net
# Reads serial data and pushes it via HTTP POST onto the net
# runs on Raspberry Pi
# Thomas Kohler (C) 2017/2018
# Version 1.1 / 31-12-2017

# imports
import serial
from serial import SerialException

import http.client
import logging
import urllib.request, urllib.parse, urllib.error
import time
import binascii
import random

# THIS IS THE UNIQUE DEVICENAME
Device_ID = 'SB_ZURICH'

if Device_ID == '':
    Device_ID = "SB_" + str(random.randint(1000,9999))

# Configuration Data (later to be put in Panel2Net.conf)
# SerialPort: Name of RPi serial port receiving the panel data
SerialPort = '/dev/ttyUSB0'
# BaudRate: Serial port speed (Baud, Default)
BaudRate = 9600
# PackageByTime: Time Duration until a package is closed
# and sent off (seconds)
PackageByTime = 0.1
# PackageByLength*	Length (in bytes) of data input
# until a package is closed and sent off
PackageByLength = 64

# RequestMode: GET or POST
RequestMode = 'POST'
# RequestServer: Server IP or Name
RequestServer = 'swb.world'
# RequestPort: Port over which HTTP request is being placed
RequestPort = 80
# RequestURL (Default)
RequestURL = '/abcd/mobatime.php'
# for an answer before aborting (seconds)
RequestTimeOut = 10
# Number of Retries before attempting to switch baudrate  
ReadRetry = 3
# MaxBuffer before flushing (in order to avoid buffer overflow)
BufferMax = 1000

# LogFileName: Name of LogFile
LogFileName = '/tmp/Panel2Net.log'
# LogFileSize Maximum Size of LogFile (in Mbytes)
LogFileSize = 10
# LogLevel	Minimum Severity Level to be logged (see severity in Logs)
LogLevel = 'E'

logging.basicConfig(level=logging.DEBUG,
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

while True:
    try:
        print ("\nInitializing")
        ser.close()
        ser.open()
        if ser.isOpen():
            try:
                ser.flushInput()
                # flush input buffer, discarding all its contents
                ser.flushOutput()
                # flush output buffer, aborting current output
                # and discard all that is in buffer
                RequestCount = 0
                print ("\nPort Opening")
                # Initialise RetryCounter
                RetryCount = 0
        
                while True:
                    # If the BufferSize is larger than defined BufferMax then flush to avoid buffer overflow
                    # print("BufferSize: " + str(ser.inWaiting()) + ", BufferMax: " + str(BufferMax))
                    if ser.inWaiting() > BufferMax:
                        print ("\n>>> Buffer limit exceeded. Flushing buffer")
                        ser.flushInput()
                        ser.flushOutput()

                    # Read from Serial Interface
                    response = ser.read(PackageByLength)
    
                    if len(response) > 0:
                        # In case there is something coming down the serial path
                        logging.debug(response)

                        # Calculate Request Start Time
                        StarterTime = time.time() * 1000

                        response_hex = response.replace(b' ', b'')
                        # print("\nResponse_Hex: " + str(response_hex))

                        # Evaluate if the received data is HEX or needs conversion to HEX
                        try:
                            int(response_hex,16)    
                        except ValueError:
                            # not hex, needs conversion
                            response_hex = binascii.hexlify(response)
                            response_hex = response_hex.upper()
                            # print("\nResponse_Raw: " + str(response))    
                            # print("\nResponse_Hex: " + str(response_hex))

                        # Evaluate if the received data matches the panel format or not
                        if response_hex.find(b'017F0247') != -1:
                            # found mobatime panel data
                            # print("Mobatime: " + str(response_hex) + " - " + str(response_hex.find(b'017F0247')))
                            RequestURL = '/abcd/mobatime.php'
                            RetryCount = 0
                        elif (response_hex.find(b'F83320') != -1) or (response_hex.find(b'E8E8E4') != -1):
                            # found stramatel panel data
                            # print("Stramatel: " + str(response_hex) + " - " + str(response_hex.find(b'F83320')))
                            RequestURL = '/abcd/stramatel.php'
                            RetryCount = 0
                        elif (response_hex.find(b'0254') != -1) or (response_hex.find(b'0244') != -1):
                            # found SwissTiming panel data
                            # print("SwissTiming: " + str(response_hex) + " - " + str(response_hex.find(b'0244')))
                            RequestURL = '/abcd/swisstiming.php'
                            RetryCount = 0
                        else:
                            # if not known format found, then retry as long there are retries left, otherwise change baudrate
                            if RetryCount < ReadRetry:
                                RetryCount += 1
                                print("\n>>> Panel not recognized. Retry #" + str(RetryCount))
                            else:    
                                # Closing Serial Interface to Change Baudrate
                                ser.close()
                                # Switch Baudrate
                                if BaudRate == 9600:
                                    BaudRate = 19200
                                elif BaudRate == 19200:
                                    BaudRate = 38400
                                elif BaudRate == 38400:
                                    BaudRate = 57600
                                elif BaudRate == 57600:
                                    BaudRate = 115200
                                elif BaudRate == 115200:
                                    BaudRate = 1200
                                elif BaudRate == 1200:
                                    BaudRate = 2400
                                elif BaudRate == 2400:
                                    BaudRate = 4800
                                elif BaudRate == 4800:
                                    BaudRate = 9600
                                else:
                                    BaudRate = 9600

                                # Set new Baudrate and re-open Serial Interface
                                ser.baudrate = BaudRate
                                ser.open()
                                print("\n>>> Panel not recognized. Changing Baudrate to " + str(BaudRate))

                            # Flush Buffers and Read to clean previous data
                            ser.flushInput()
                            ser.flushOutput()
                            response = ser.read(PackageByLength)
                            response = ser.read(PackageByLength)
                            response = b''        

                        # End Evaluation Block

                        if response != b'':
                            # Make and Evaluate HTTP Request
                            headers = {}
                            headers['Content-type'] = 'application/x-www-form-urlencoded'
                            headers['Accept'] = 'text/plain'
                            headers['Content-Type'] = 'text/plain'
                            headers['Connection'] = 'keep-alive'
                            headers['Device_ID'] = Device_ID

                            conn = http.client.HTTPConnection(RequestServer,RequestPort)
                            conn.request("POST", RequestURL, response, headers)
                            httpreply = conn.getresponse()
                            if httpreply.status == 200:
                                logging.debug(str(httpreply.status) + " " + str(httpreply.reason))
                            else:
                                logging.error(str(httpreply.status) + " " + str(httpreply.reason))    
                            RequestCount = RequestCount + 1    
                            logging.debug("RequestCount: " + str(RequestCount))
                            
                            # Calculate End Time
                            EnderTime = time.time() * 1000
                            
                            # Calculate Time used for Request Handling
                            ElapserTime = int(EnderTime - StarterTime)
                            print("\r#: " + str(RequestCount) + ", Bd: " + str(BaudRate) + ", Panel: " + str(RequestURL[6:-4]) + ", Len#: "
                             + str (PackageByLength) + ", HT: " + str(ElapserTime)
                             + " ms: " + str(httpreply.status) + ", Buf: " + str(ser.inWaiting()), end='       ', flush=True)
                            logging.debug("\rRequestCount: " + str(RequestCount) + ", Package Length: "
                             + str (PackageByLength) + ", Handling Time: " + str(ElapserTime)
                             + " ms -> " + str(httpreply.status) + ", BufferSize: " + str(ser.inWaiting()))        
                            
                            # Adjust PackageByLength based on Handling Time
                            if ElapserTime > 2000:
                                PackageByLength = 4096
                            elif ElapserTime > 1000:
                                PackageByLength = 2048
                            elif ElapserTime > 500:
                                PackageByLength = 1048
                            elif ElapserTime > 250:
                                PackageByLength = 512
                            elif ElapserTime > 125:
                                PackageByLength = 256
                            elif ElapserTime > 60:
                                PackageByLength = 128
                            else:
                                PackageByLength = 128
                            # print("New PackageLength: " + str(PackageByLength))
                    
                    else:
                        # In case nothing is coming down the serial interface
                        print ("\rWaiting for serial input...", end='', flush=True)
  
            # in case that the Serial Read or HTTP request fails        
            except Exception as e1:
                print("error communicating...: " + str(e1))
                logging.error("error communicating...: " + str(e1))

        else:
            print("Port Opening Failed... trying again in 5 seconds")
            time.sleep(5)
            ser.close()
    
    except SerialException:
        print("No port connected... trying again in 5 seconds")
        time.sleep(5)
