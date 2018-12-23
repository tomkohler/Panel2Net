# Injector to test mobatime last minute
import http.client
import time
import binascii

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
# Number of Retries before attempting to switch baudrate  
ReadRetry = 3
# MaxBuffer before flushing (in order to avoid buffer overflow)
BufferMax = 2000

Device_ID = "SB_WINTERTHUR"
RequestCount = 0
response = b''
remainder_hex = ""
response_hex = ""

s1 = 5
s2 = 9
ff = 10
while True:
    ff = ff - 1
    if ff < 0:
        ff = -1
    if ff == -1:
        ff = 9
        s2= s2 - 1
    if s2 < 0:
        s2 = -1
    if s2 == -1:
        ff = 9
        s2 = 9
        s1 = s1 - 1
    if s1 < 0:
        break
    
    # Calculate Request Start Time
    StarterTime = time.time() * 1000
   
    print (s1,s2,ff)
    hex1 = 30 + s1
    hex2 = 30 + s2
    hex3 = 30 + ff
    
    response_hex = '01 7F 02 47 31 38 82 35 '+str(hex1)+' '+str(hex2)+' 44 '+str(hex3)+' 31 32 80 80 34 80 03 54'
    # this is what normally comes in through the serial interface
    response_hex2 = response_hex.replace(' ', '')
    response2 = bytes.fromhex(response_hex2).decode('iso-8859-1')
    response = response2.encode()
    print (response2)
    print (response)
    
     # Evaluate if the received data is HEX or needs conversion to HEX
    try:
        int(response_hex,16)    
    except ValueError:
        # not hex, needs conversion
        response_hex = binascii.hexlify(response)
        response_hex = response_hex.upper()
        # print("\nResponse_Raw: " + str(response))    
        # print("\nResponse_Hex: " + str(response_hex))
    
 
    
    if ((response_hex.find(b'017F0247') != -1) and (response_hex.rfind(b'03') != -1)):
        # found mobatime panel data
        # print("Mobatime: " + str(response_hex) + " - " + str(response_hex.find(b'017F0247')))
        # Get First and Last Usable Sequence, extract Usable String and put rest in Remainder
        StartToken = response_hex.find(b'017F0247')
        EndToken = response_hex.rfind(b'03')
        # End Token + 4 because after the EndToken there is a checksum byte
        remainder_hex = response_hex[EndToken + 4:]
        response_hex = response_hex[StartToken:EndToken + 4] + b'017F0247'
        # print("Mobatime: ST:" + str(StartToken) + " - ET: " + str(EndToken) + "\n" + str(response_hex) + "\n" + str(remainder_hex))
        RequestURL = '/abcd/mobatime.php'
        RetryCount = 0
    
    print (response_hex)   
    
    if response != b'':
        headers = {}
        headers['Content-type'] = 'application/x-www-form-urlencoded'
        headers['Accept'] = 'text/plain'
        headers['Content-Type'] = 'text/plain'
        headers['Connection'] = 'keep-alive'
        headers['Device_ID'] = Device_ID

        conn = http.client.HTTPConnection(RequestServer,RequestPort)
        conn.request("POST", RequestURL, response2, headers)
        httpreply = conn.getresponse()
        if httpreply.status == 200:
            print ("Request OK")
        else:
            print ("Request Failed")
        
        RequestCount = RequestCount + 1
                    
        # Calculate End Time
        EnderTime = time.time() * 1000

        # Calculate Time used for Request Handling
        ElapserTime = int(EnderTime - StarterTime)
        print("\r#: " + str(RequestCount) + ", Panel: " + str(RequestURL[6:-4]) + ", Len#: "
         + ", HT: " + str(ElapserTime)
         + " ms: " + str(httpreply.status), end='          ', flush=True)
    else:
        print ("Non binary input")
