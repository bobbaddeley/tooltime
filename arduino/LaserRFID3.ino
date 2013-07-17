// Laser cutter auth system
// By Bob Baddeley <bob.baddeley@gmail.com>
// Joe Kerman <jkerman@gmail.com>

//This code talks to a python script running on a linux computer inside of the laser cutter (Raspberry Pi)
//It polls the RFID reader for data, sends the tag ID to the serial port and waits for a response.
//It also polls for an active laser power supply, and reports job times to the same serial port. 
//
//USER MANUAL:
//
//RED LED= Logged off, laser will not fire
//YELLOW LED=waiting for response from PC
//GREEN LED=Logged in, laser ready to fire
//
//
//LOGIN
//Swipe RFID tag until status lights change to yellow
//
//LOGOUT
//Swipe RFID tag of currently logged in user until light turns RED
//
//ENROLL NEW USER
//Swipe enrollment RFID tag. When lights change to 
//


//
//
//CHANGELOG:
//
//
//v3.0
//-added billing tag
//-fixed supervisor override tag
//-added user enrollment
//
//v2.1
//-cleaned up code, total reorg
//
//v2.0 
//-Jobs shorter than 15 seconds ignored
//-Job time reported in seconds
//-Tags are double-read for accuracy
//-Supervisor tag works even if serial port comms die



#include <SoftwareSerial.h>


#define DISABLEDPIN 5   // Red LED
#define ENABLEDPIN 7    // Green LED
#define BUSYPIN 6       // Yellow LED
#define RFIDENABLEPIN 3 // RFID enable
#define RFIDDATAPIN 4   // RFID RX
#define LASERPIN 8      // Laser power supply ACTIVE LOW
// #define JOBDONEPIN 10   // Laser "job complete" pin 
#define LASERENABLEPIN1 11 // Using two pins to trigger the relay to ensure enough current
#define LASERENABLEPIN2 12 // Using two pins to trigger the relay to ensure enough current



#define MIN_REPORT_TIME 5     //Minimum job length to generate a usage report
#define CODE_LEN 10           //Max length of RFID tag
#define START_BYTE 0x0A       //Parallax specific reader header
#define STOP_BYTE 0x0D        //Parallax specific reader footer
#define VALIDATE_LENGTH  200  //maximum reads b/w tag read and validate
#define ITERATION_LENGTH 2000 //time, in ms, given to the user to move hand away
#define JOB_END_TIME 5000     //Time beam must be off before a job is ended and reported
#define VERIFY_TIMEOUT 5000   //Time to wait for a response from the serial port for verifying tags


// int  val = 0;
int jobtime;
char tag[11]; // the RFID tag being submitted for processing
char response[11];
int bytesread = 0;
char currentcode[11]; // the verified valid RFID tag of the currently logged in user
char billingcode[11]; // verified RFID tag of the user to bill for the job
char override[11] =  "84000711FB"; // Emergency override tag, that works even if the PC is down
char enrolltag[11] = "FFFFFFFFFF"; // Special tag to enable enrollment mode
long verifystarttime = 0;
boolean laseron = false;
long laserstarttime = 0;
long lastlaserontime = 0;



enum state {
  DISABLED,
  VERIFYING,
  ENABLED,
  ENROLLING
};
state currentstate = DISABLED; // Startup in disabled mode
SoftwareSerial mySerial( RFIDDATAPIN, 12 ); // Define the serial port to the RFID reader

void setup() {
  
  pinMode(RFIDENABLEPIN,OUTPUT);   
  pinMode(DISABLEDPIN,OUTPUT);
  pinMode(ENABLEDPIN,OUTPUT);
  pinMode(BUSYPIN,OUTPUT);
  pinMode(LASERPIN,INPUT);
  //pinMode(JOBDONEPIN, INPUT);
  digitalWrite(2, LOW);           // Activate the RFID reader
  
  disable(); // Startup in disabled mode
  mySerial.begin( 2400  ); // Open the serial port to the RFID reader
  Serial.begin(   9600   ); // Open the USB serial port to the computer
} 

// The main loop is a state machine, and must NEVER block!! The subroutines react depending on state, as well
// as update the state. 

void loop() {
  // Check and process laser beam status
     CheckBeam();
  // Make sure we arent stuck in verifying mode
     VerifyTimeout();
  // Check for and process waiting RFID data
     CheckRFID();
  // Check for and process any responses from the computer
     CheckResponse();
} 





// If the laser beam is firing, update the last beam fired time and start a new job counter. 
// If a job has recently ended, report it
void CheckBeam() {
  if (digitalRead(LASERPIN)==LOW){
   if (laseron==false){
      laseron = true;
      laserstarttime = millis();
   }
    lastlaserontime = millis();
  }
  if (millis()-lastlaserontime>JOB_END_TIME && laseron == true){
    laseron = false;
    jobtime = (((millis()-laserstarttime-JOB_END_TIME))/1000);
    if (jobtime>MIN_REPORT_TIME) { // discard any jobs under the time limit
     ReportJob();
    }
  }
}


// Report the completed job time to the serial port
void ReportJob() {
      Serial.print("T:");
      Serial.print(jobtime);
      Serial.print(",C:");
      Serial.print(currentcode); 
      Serial.print(",B:");
      Serial.println(billingcode);
}
// Make sure we arent waiting for verification that will never arrive


void VerifyTimeout() {
  if (millis()-verifystarttime>VERIFY_TIMEOUT && currentstate == VERIFYING){ //timeout in case verification takes too long
   errorblink();
   boolean overrideflag = true;
       for(int x = 0;x<10;x++){         
        if (tag[x]!=override[x]){
          overrideflag = false;
          break;
        }
       }
    if (overrideflag==true) {
      enable();
      digitalWrite(DISABLEDPIN,HIGH);
      digitalWrite(BUSYPIN, HIGH);
      digitalWrite(ENABLEDPIN,HIGH);
    } else {
    disable();
    }
  } 
}

void CheckRFID() {
  enableRFID(); //RFID is disabled after a successful tag, make sure its re-enabled
  if ((currentstate == DISABLED) || ((currentstate == ENABLED) && (mySerial.available() > 0))) {  
    getRFIDTag();
    if(isCodeValid()) {
      digitalWrite(BUSYPIN,HIGH);
      disableRFID();
      
      
          
      
      if (currentstate == ENABLED){
       //if the person is swiping their card again and they are currently enabled, then disable.
       boolean current = true;
       for(int x = 0;x<10;x++){         
        if (tag[x]!=currentcode[x]){
          current = false;
          break;
        }
      }
        if (current==true) { 
        disable();
        }
        else { // a different tag when a user is already logged in, means we assign it to a billing tag
         for(int x=0;x<10;x++){ 
          billingcode[x]=tag[x];
         }
         billingblink();
        }
      }
      
      if (currentstate == DISABLED) { // Check for the enrollment tag, and enter enrollment mode if so.  
         currentstate = ENROLLING;
         for(int x = 0;x<10;x++){         
          if (tag[x]!=enrolltag[x]){
           currentstate = DISABLED;
          break;
        }
      }
         
        if (currentstate == ENROLLING) {
          boolean enrolled = false;
          while (enrolled==false) { // We are going to sit here until we get a valid tag to enroll
            digitalWrite(ENABLEDPIN,HIGH);
            digitalWrite(DISABLEDPIN,HIGH);
            digitalWrite(BUSYPIN,LOW);
            if (mySerial.available() > 0) {
              getRFIDTag();
              if(isCodeValid()) {
                disableRFID();                
                enrolled == true;
                ReportEnroll();
               }
            
          }
          digitalWrite(ENABLEDPIN,LOW);
          digitalWrite(DISABLEDPIN,LOW);
          digitalWrite(BUSYPIN,HIGH);
          delay(100);
          //  
        }
        }       
       if (currentstate == DISABLED) { // if we are still disabled, we must be verifying a user for login
          ReportRFID(); // Otherwise, send the tag off for verification 
       }
      }
      delay(ITERATION_LENGTH); // chill out for a second, to prevent insta-logout
    } 
    else {
      disableRFID();
      // Serial.println("Got some noise");  
    }
    mySerial.flush();
    clearCode(); // reset the currently being processed tag to all 0's
  } 
  
}

// Send the RFID auth request to the serial port
void ReportRFID() {
        currentstate = VERIFYING;
        verifystarttime = millis();
        Serial.print("V:");
        Serial.println(tag);            // print the TAG code        
}

void ReportEnroll() {
        currentstate = VERIFYING;
        verifystarttime = millis();
        Serial.print("E:");
        Serial.println(tag);            // print the TAG code        
}

// Check for a response from the computer
void CheckResponse() {
  int val;
  if (Serial.available() > 0) {
    if (currentstate==VERIFYING && (val = Serial.read()==10)){ // see if we have a response 
      // Clear memory for response to 0
      for(int i=0; i<20; i++) {
        response[i] = 0; 
      }
      while(bytesread<1) {              // read 10 digit code
        if( Serial.available() > 0) {
          val = Serial.read();
          response[bytesread] = val;         // add the digit          
          bytesread++;                   // ready to read next digit 
        }
      }
      bytesread = 0;
      if(response[0] == 121) { // "y"
        enable();
        // Since its verified, save the tag ID, so they can logout
       for(int x = 0;x<10;x++){
        currentcode[x]=tag[x];
        }
      }
      
      if(response[0] == 98) { // "b"
      specialblink();
      disable();
      }
      
      else {
        errorblink();
        disable();
      }
    }
  }  
}

// Enable the laser cutter for use
void enable(){
  Serial.println("ENABLING");
  currentstate = ENABLED;
  digitalWrite(DISABLEDPIN,LOW);
  digitalWrite(BUSYPIN, LOW);
  digitalWrite(ENABLEDPIN,HIGH);
  digitalWrite(LASERENABLEPIN1,HIGH);
  digitalWrite(LASERENABLEPIN2,HIGH);
  digitalWrite(RFIDENABLEPIN, LOW);   // Activate the RFID reader
   for(int x = 0;x<10;x++){
        currentcode[x]=tag[x];
      }
}

// Disable the laser cutter for use
void disable(){
  Serial.println("DISABLING");
  currentstate = DISABLED;  
  digitalWrite(DISABLEDPIN,HIGH);
  digitalWrite(BUSYPIN, LOW);
  digitalWrite(ENABLEDPIN,LOW);
  digitalWrite(LASERENABLEPIN1,LOW);
  digitalWrite(LASERENABLEPIN2,LOW);
  digitalWrite(RFIDENABLEPIN, LOW);                  // Activate the RFID reader
  for(int x = 0;x<10;x++){
    currentcode[x]=0;
  }
}

void enableRFID() {
  digitalWrite(RFIDENABLEPIN, LOW);    
}

void disableRFID() {
  digitalWrite(RFIDENABLEPIN, HIGH);  
}

void getRFIDTag() {
  byte next_byte; 
  while(mySerial.available() <= 0) {
  }
  if((next_byte = mySerial.read()) == START_BYTE) {      
    byte bytesread = 0; 
    while(bytesread < CODE_LEN) {
      if(mySerial.available() > 0) { //wait for the next byte
        if((next_byte = mySerial.read()) == STOP_BYTE) break;       
        tag[bytesread++] = next_byte;                   
      }
    }                
  }    
}


// Read the tag again quickly, to see if it matches. to eliminate noise and RF interference
boolean isCodeValid() {
  byte next_byte; 
  int count = 0;
  while (mySerial.available() < 2) {  //there is already a STOP_BYTE in buffer
    delay(1); //probably not a very pure millisecond
    if(count++ > VALIDATE_LENGTH) return false;
  }
  mySerial.read(); //throw away extra STOP_BYTE
  if ((next_byte = mySerial.read()) == START_BYTE) {  
    byte bytes_read = 0; 
    while (bytes_read < CODE_LEN) {
      if (mySerial.available() > 0) { //wait for the next byte      
        if ((next_byte = mySerial.read()) == STOP_BYTE) break;
        if (tag[bytes_read++] != next_byte) return false;                     
      }
    }                
  }
  
  return true;   
}


// Manually set the memory for the current tag to all 0's
void clearCode() {
  for(int i=0; i<CODE_LEN; i++) {
    tag[i] = 0; 
  }
}

// Pulse LED's for error reporting
void errorblink() {
  for (int x = 0;x<5;x++) {
    digitalWrite(DISABLEDPIN,LOW);
    delay(200);
    digitalWrite(DISABLEDPIN,HIGH);
    delay(200);
    }
}

void billingblink() {
 for (int x = 0;x<5;x++) {
  digitalWrite(ENABLEDPIN,LOW);
  delay(100);
  digitalWrite(ENABLEDPIN,HIGH);
  delay(100); 
 }
 digitalWrite(DISABLEDPIN,HIGH);
}

void specialblink() {
  digitalWrite(ENABLEDPIN,LOW);
  digitalWrite(DISABLEDPIN,LOW);
  digitalWrite(BUSYPIN,LOW);
  for (int x = 0;x<3;x++) {
  digitalWrite(ENABLEDPIN,HIGH);
  delay(100);
  digitalWrite(BUSYPIN,HIGH);
  delay(100); 
  digitalWrite(DISABLEDPIN,HIGH);
  delay(100);
  digitalWrite(DISABLEDPIN,LOW);
  delay(100);
  digitalWrite(BUSYPIN,LOW);
  delay(100);
  digitalWrite(DISABLEDPIN,LOW);
 }
 delay (1000);
 digitalWrite(DISABLEDPIN,HIGH);
  
}

void debugblink() {
  
  for (int x = 0;x<3;x++) {
  digitalWrite(ENABLEDPIN,HIGH);
  digitalWrite(DISABLEDPIN,HIGH);
  digitalWrite(BUSYPIN,HIGH);
  delay(100);
  digitalWrite(ENABLEDPIN,LOW);
  digitalWrite(DISABLEDPIN,LOW);
  digitalWrite(BUSYPIN,LOW);
  delay(100);

  }
}




