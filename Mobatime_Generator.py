import binascii

concat = b''

f = open('test_bodet.txt', 'wb')

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
    
    timestring = str(s1) + str(s2) + 'D' + str(ff)
    xstring = b'\x01\x7f\x02G18\x825'+ timestring.encode('iso-8859-1') + b'11  1 \x037'
    #print (xstring)
    response_hex = binascii.hexlify(xstring)
    response_iso = xstring.decode('iso-8859-1')
    #concat = concat + response_iso

    print (response_hex)
    print (response_iso)
    print (response_iso.encode('iso-8859-1'))
    f.write(response_iso.encode('iso-8859-1'))
    #print ("\n")
    


f.close()