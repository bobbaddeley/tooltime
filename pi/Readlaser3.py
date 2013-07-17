#!/usr/bin/env python
# This reads characters from an arduino connected to the laser, over usbserial
# Joe Kerman jkerman@gmail.com 11/2012


import serial
import urllib
import time
import os


port = '/dev/ttyACM0'
currentcode = ''
debug = True
# open serial port
try:
        ser = serial.Serial(port, 9600, timeout=0.1)
except serial.SerialException, e:
        print e
        exit()
ser.flush()

while(1): # read all lines on serial interface
        try:
                line = ser.readline()
                if(line): # line available?
                        if debug == True:
                                print "Raw:" + line
                        if line[0] == 'T':
                                try:
                                        #mytime=str(time.time())
                                        mytime = time.strftime("%m%d%Y-%H%M")
                                        inputstring=line.strip()
                                        inputstring=inputstring.split(",")
                                        jobtime=inputstring[0][2:]
                                        tag=inputstring[1][2:12]
                                        billing=inputstring[2][2:12]
                                        fdata = open('/home/pi/lasertime.txt','a')
                                        fdata.write("T:" + mytime + ",L:" + jobtime + ",U:" + tag + ",B:" + billing + '\n')
                                        fdata.close()
                                        #grab the parameters and upload to the time service
                                        f = urllib.urlopen("http://test.wyzgyz.com/tooltime/lasertime.php?machine=1&tag="+currentcode+"&data="+line.strip())
                                        junk = os.system("/home/pi/takeandsendpic.sh /home/pi/laserpics/" + mytime + ".jpg T:" + jobtime + ",U:" + tag + ",B:" + billing )
                                        results = f.read().strip()
                                        if debug == True:
                                                print "T:" + mytime + ",L:" + jobtime + ",U:" + tag  +'\n'
                                                print "Got:" + results + "\n"
                                except Exception, e:
										print e
                        elif line[0] == 'V':
                                try:
                                        #mytime=str(time.time());
                                        mytime = time.strftime("%m%d%Y-%H%M")

                                        if debug == True:
                                                print line[2:12]
                                        currentcode = line[2:12]
                                        fusers = open('/home/pi/laserusers.txt','r')
                                        for user in fusers:
                                                if user[:10] == currentcode:
                                                        flogin = open('/home/pi/laserlogin.txt','a')
                                                        flogin.write("T:" + mytime +',U:' + currentcode +'\n')
                                                        flogin.close()
                                                        ser.write("\n" + "y")
                                                        #grab the parameters and try to verify with the verify service
                                                        f = urllib.urlopen("http://test.wyzgyz.com/tooltime/verifytag.php?machine=1&tag="+currentcode)
                                        #results = f.read().strip()
                                        #if debug == True:
                                        #       print results
                                        #ser.write("\n"+results)


                                except Exception, e:
                                        print e

                        elif line[0] == 'E':
                                try:
                                        #print ""
                                        if debug == True:
                                                print "Enrolling:" + line[2:12]
                                        fusers = open('/home/pi/laserusers.txt','a')
                                        fusers.write(line[2:12] + "\n")
                                        fusers.close()
                                        #f = urllib.urlopen("http://test.wyzgyz.com/tooltime/adduser.php?machine=1&tag="+line[2:12])
                                        ser.write("\n" + "b")


                                except Exception, e:
                                        print e


                        else:
                                if debug == True:
                                        print "Unknown line:" + line + "\n"
        except Exception, e:
				print e
