#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>

#define MAX_TRYING 3

uint8_t mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xEE };
uint8_t arduinoIP[] = { 192, 168, 28, 191 };
uint8_t gatewayIP[] = { 192, 168, 29, 254 };
uint8_t subnetIP[] = { 255, 255, 254, 0 };
uint8_t dnsIP[] = { 8, 8, 8, 8 };
IPAddress server_addr(192, 168, 1, 13);

char user[] = "root";
char password[] = "Bc8574";

EthernetClient client;
MySQL_Connection conn((Client *)&client);

const int buttonPin = 2;
const int relayPin1 = 3;
const int relayPin2 = 4;
const int relayPin3 = 5;
const int relayPin4 = 6;

const int lcdColumns = 16; // Sesuaikan dengan jumlah kolom LCD Anda
const int lcdRows = 2;     // Sesuaikan dengan jumlah baris LCD Anda

int buttonState = 0;
int lastButtonState = HIGH;
boolean isTiming = false;
boolean relayTurnedOff = false;
boolean relayTurnedOff2 = false;

unsigned long startTime = 0;
unsigned long elapsedMillis = 0;

// Inisialisasi objek LiquidCrystal_I2C
LiquidCrystal_I2C lcd(0x27, lcdColumns, lcdRows); // Alamat I2C dan ukuran LCD

void setup() {
  pinMode(buttonPin, INPUT_PULLUP);
  pinMode(relayPin1, OUTPUT);
  pinMode(relayPin2, OUTPUT);
  pinMode(relayPin3, OUTPUT);
  pinMode(relayPin4, OUTPUT);

  digitalWrite(relayPin1, HIGH);
  digitalWrite(relayPin2, HIGH);
  digitalWrite(relayPin3, HIGH);
  digitalWrite(relayPin4, HIGH);

  Serial.begin(9600);

  Ethernet.begin(mac_addr, arduinoIP, dnsIP, gatewayIP, subnetIP);
  delay(1000);

  if (conn.connect(server_addr, 3306, user, password))
  {
    Serial.println("Connected to MySQL server!");
  }
  else
  {
    Serial.println("Connection failed.");
  }

  // Inisialisasi LCD
  lcd.begin(lcdColumns, lcdRows);
  lcd.backlight(); // Hidupkan backlight LCD
  lcd.setCursor(0, 0);
  lcd.print("Tekan START");
  lcd.setCursor(0, 1);
  lcd.print("untuk mulai");
}

void loop(){
  // Logika tombol
  int reading = digitalRead(buttonPin);

  if (reading == LOW && lastButtonState == HIGH) {
    isTiming = !isTiming;

    if (isTiming) {
      startTime = millis();
      Serial.println("Button pressed! Weighing started.");
      digitalWrite(relayPin1, LOW);
      digitalWrite(relayPin2, LOW);
      digitalWrite(relayPin3, LOW);
      digitalWrite(relayPin4, LOW);
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Mulai Menimbang");
    } 
    // else {
    //   digitalWrite(relayPin1, HIGH);
    //   digitalWrite(relayPin2, HIGH);
    //   digitalWrite(relayPin3, HIGH);
    //   digitalWrite(relayPin4, HIGH);
    //   Serial.println("Button released. Weighing stopped.");
    //   lcd.clear();
    //   lcd.setCursor(0, 0);
    //   lcd.print("Berhenti Menimbang");
    // }
  }

  lastButtonState = reading;

  // Logika untuk menghitung berat setelah tombol ditekan
  if (isTiming) {
    if (Serial.available()) {
      String raw_data = Serial.readStringUntil('\n');
      int space_position = raw_data.indexOf(' ');

      if (space_position != -1) {
        String data = raw_data.substring(space_position + 1);
        data.trim();
        data.replace("kg", "");
        data.replace(",", "");

        Serial.println("Weight from scale: " + data + " kg");
        lcd.clear();
        lcd.setCursor(0, 1);
        lcd.print("Berat: " + data + " kg");

        int weight = data.toInt();

        // Matikan relay jika berat mencapai 400 kg
        if (weight >= 400 && !relayTurnedOff) {
          digitalWrite(relayPin1, HIGH);
          digitalWrite(relayPin2, HIGH);
          digitalWrite(relayPin3, HIGH);
          digitalWrite(relayPin4, HIGH);
          Serial.println("Relays turned off due to high weight!");
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Berat = 400kg");
          // insertDataToDatabase(weight);
          relayTurnedOff = true;
        }

        // Matikan relay jika berat mencapai 700 kg
        if (weight >= 700 && !relayTurnedOff2) {
          digitalWrite(relayPin1, HIGH);
          digitalWrite(relayPin2, HIGH);
          digitalWrite(relayPin3, HIGH);
          digitalWrite(relayPin4, HIGH);
          Serial.println("Relays turned off due to high weight!");
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Berat = 700kg");
          // insertDataToDatabase(weight);
          relayTurnedOff2 = true;
        }

        // Matikan relay jika berat mencapai 855 kg
        if (weight >= 855) {
          digitalWrite(relayPin1, HIGH);
          digitalWrite(relayPin2, HIGH);
          digitalWrite(relayPin3, HIGH);
          digitalWrite(relayPin4, HIGH);
          Serial.println("Relays turned off due to high weight!");
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Berat Tercapai");
          insertDataToDatabase(weight);
          isTiming = false;
          relayTurnedOff = false;  // Reset status relay
          relayTurnedOff2 = false;  // Reset status relay2
          lastButtonState = HIGH;
        }
      }
    }
  }

  // Logika untuk menyalakan relay kembali jika tombol start ditekan
  if (isTiming && (relayTurnedOff || relayTurnedOff2) && reading == LOW && lastButtonState == LOW) {
    relayTurnedOff = false;
    relayTurnedOff2 = false;
    Serial.println("Button pressed! Relays turned on.");
    digitalWrite(relayPin1, LOW);
    digitalWrite(relayPin2, LOW);
    digitalWrite(relayPin3, LOW);
    digitalWrite(relayPin4, LOW);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Mulai Menimbang");
  }
}

void insertDataToDatabase(float weight) {
  MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
  
  String INSERT_SQL = "INSERT INTO db_sensor.t_pelet (pos, area, kg, waktu, created_at, plant) VALUES ('1', 'pelet', '" + String(weight) + "', NOW(), NOW(),'2201')";
  
  cur_mem->execute(INSERT_SQL.c_str());
}
