# socket_echo_client.py
import socket
import sys
import time
import binascii

def StringRightSize(inputstring):

    inputstring = inputstring.strip()
    
    if inputstring[0] == ' ':
        inputstring = inputstring[1:]
    elif inputstring[1] == ' ':
        inputstring = inputstring[2:]

    if inputstring[-1] == ' ':
        inputstring = inputstring[:-1]
    elif inputstring[-2] == ' ':
        inputstring = inputstring[:-2]

    inputstring = inputstring.replace(' ', '')
    inputstring = inputstring.replace('92', '')
    
    return(inputstring)

# Configuration Section
hostname = 'localhost'
sockPort = 4000
readlength = 128
testfile = 'mobatime.txt'

with open(testfile, "r") as f:
    # read first message
    testmessage = f.read(readlength)
    counter = 0
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

    # Connect the socket to the port where the server is listening
    server_address = ('localhost', sockPort)
    print('connecting to {} port {}'.format(*server_address))

    # send message
    sock.connect(server_address)
    
    # check of EOF
    while testmessage:

        testmessage2 = StringRightSize(testmessage)
        testmessage2 = testmessage2.strip()
       
        if (len(testmessage2) % 2 == 0):
            # make sure this becomes hex
            testmessage3 = binascii.unhexlify(testmessage2)
            print(testmessage2)
        
            #send message to socket and increment counter
            sock.sendall(testmessage3)
            counter = counter + 1
            
        time.sleep(0.2)
        testmessage = f.read(readlength)
              
# final statement
print('closing file')
f.close()
sock.close()
        

