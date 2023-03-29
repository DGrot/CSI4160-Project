import RPi.GPIO as GPIO
import time
import mysql.connector
from apscheduler.schedulers.background import BackgroundScheduler

#needs libraries installed on pi:
#sudo pip install apscheduler
#sudo pip install mysql-connector-python

# Set up GPIO Servo on pin 24.
GPIO.setmode(GPIO.BCM)
GPIO.setup(24, GPIO.OUT)

# Define servo control function
def move_servo(angle):
    duty = angle / 18 + 2
    GPIO.output(24, True)
    pwm.ChangeDutyCycle(duty)
    time.sleep(1)
    GPIO.output(24, False)
    pwm.ChangeDutyCycle(0)

# Set up PWM pin 24 to 50hz
pwm = GPIO.PWM(24, 50)
pwm.start(0)

# Set up scheduled job
scheduler = BackgroundScheduler()
scheduler.start()

# Connect to the Cloud SQL database
cnx = mysql.connector.connect(user='postgres', password='postgres',
                              host='34.123.187.107', database='csi4160-grot-db')

# Create a cursor to execute SQL statements
cursor = cnx.cursor()

# Define scheduled job function, move servo 90 degrees and back to 0.
def scheduled_job():
    now = time.strftime('%Y-%m-%d %H:%M:%S')
    sql = "INSERT INTO servo_log (timestamp) VALUES (%s)"
    cursor.execute(sql, (now,))
    cnx.commit()
    move_servo(90)
    time.sleep(1)
    move_servo(0)

# Schedule job for 10am and 10pm
scheduler.add_job(scheduled_job, 'cron', hour='15', minute='45,47')

# Wait for scheduled jobs to run
while True:
    time.sleep(1)

# Clean up resources
cursor.close()
cnx.close()
