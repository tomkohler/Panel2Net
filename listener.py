# Panel2Net with Ethernet TCP Listener

import socket
import sys
import binascii
import http.client

def hexspace(string, length):
    return ' '.join(string[i:i+length] for i in range(0,len(string),length))

Device_ID = 'SB_STARWINGS'
response = ''
# RequestMode: GET or POST
RequestMode = 'POST'
# RequestServer: Server IP or Name (Amazon EC2)
RequestServer = '3.120.213.249'
# RequestPort: Port over which HTTP request is being placed
RequestPort = 80
# RequestURL (Default)
RequestURL = '/abcd/mobatime.php'
# for an answer before aborting (seconds)
RequestTimeOut = 10

# Create a TCP/IP socket
sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# Open Debug File
# f = open("/tmp/debug.txt", "ab")

# Bind the socket to the port
server_address = ('', 4000)
print('starting up on {} port {}'.format(*server_address))
sock.bind(server_address)

# Listen for incoming connections
sock.listen(1)

while True:
    # Wait for a connection
    print('waiting for a connection')
    connection, client_address = sock.accept()
    try:
        print('connection from', client_address)

        # Receive the data in small chunks and retransmit it
        while True:
            
            data = connection.recv(256)
            
            # debug line
            # f.write(data)
            # f.write("\n")
            
            cdata = binascii.hexlify(data)
            ddata = cdata.decode('utf-8')
            ddata = ddata.upper()
            #print (ddata)
            edata = hexspace(ddata, 2)
            #print (edata)
	    # Make and Evaluate HTTP Request
            headers = {}
            headers['Content-type'] = 'application/x-www-form-urlencoded'
            headers['Accept'] = 'text/plain'
            headers['Content-Type'] = 'text/plain'
            headers['Connection'] = 'keep-alive'
            headers['Device_ID'] = Device_ID

            conn = http.client.HTTPConnection(RequestServer,RequestPort)
            conn.request("POST", RequestURL, edata, headers)
            httpreply = conn.getresponse()
            if httpreply.status == 200:
             print ("OK")
             print (edata)
            else:
            	print ("NOK")



    finally:
        # Clean up the connection
        connection.close()
        # close debug file
        # f.close()
