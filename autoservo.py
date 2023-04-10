import RPi.GPIO as GPIO
import time
import mysql.connector
from apscheduler.schedulers.background import BackgroundScheduler

#needs libraries installed on pi:
#sudo pip install apscheduler
#sudo pip install mysql-connector-python

# Set up PIR sensor on pin 18.
GPIO.setmode(GPIO.BCM)
GPIO.setup(18, GPIO.IN)
motion_cooldown = 120  # 2 minutes is 120 seconds.

# Set up GPIO Servo on pin 24.
GPIO.setmode(GPIO.BCM)
GPIO.setup(24, GPIO.OUT)

# Define servo control function.
def move_servo(angle):
    duty = angle / 18 + 2
    GPIO.output(24, True)
    pwm.ChangeDutyCycle(duty)
    time.sleep(1)
    GPIO.output(24, False)
    pwm.ChangeDutyCycle(0)

# Set up PWM pin 24 to 50hz.
pwm = GPIO.PWM(24, 50)
pwm.start(0)

# Set up scheduled job.
scheduler = BackgroundScheduler()
scheduler.start()

# Connect to the Cloud SQL database.
cnx = mysql.connector.connect(user='root', password='password',
                              host='34.132.166.21', database='csi4160-grot-db')

# Create a cursor to execute SQL statements
cursor = cnx.cursor()

# Initialize last_motion_time variable to 0.
last_motion_time = 0

# Define motion detection callback function.
def motion_detected(channel):
    global last_motion_time
    # Check cooldown period.
    current_time = time.time()
    # If motion detected and current time minus last motion time is greater than 2 minute cooldown...
    if current_time - last_motion_time > motion_cooldown:
        print("Motion detected, Logged to DB!")
        # Log Pet Movement time to the database.
        now = time.strftime('%Y-%m-%d %H:%M:%S')
        sql = "INSERT INTO servo_log (timestamp, description) VALUES (%s, %s)"
        cursor.execute(sql, (now, "Pet Movement"))
        cnx.commit()

        # Set last motion time to current time to restart the timer.
        last_motion_time = current_time

# Add motion detection callback to the PIR sensor pin.
GPIO.add_event_detect(18, GPIO.RISING, callback=motion_detected)



def scheduled_job():
    # Define scheduled job function, move servo 90 degrees and back to 0. Add timestamps.
    now = time.strftime('%Y-%m-%d %H:%M:%S')
    sql = "INSERT INTO servo_log (timestamp, description) VALUES (%s, %s)"
    cursor.execute(sql, (now, "Servo Opened"))
    cnx.commit()
    move_servo(90)
    now = time.strftime('%Y-%m-%d %H:%M:%S')
    sql = "INSERT INTO servo_log (timestamp, description) VALUES (%s, %s)"
    cursor.execute(sql, (now, "Food dispensed"))
    time.sleep(1)
    move_servo(0)
    now = time.strftime('%Y-%m-%d %H:%M:%S')
    sql = "INSERT INTO servo_log (timestamp, description) VALUES (%s, %s)"
    cursor.execute(sql, (now, "Servo Closed"))
    cnx.commit()


# Schedule job for 10am and 10pm.
scheduler.add_job(scheduled_job, 'cron', hour='10, 0', minute='22,23,24')

# Wait for scheduled jobs to run.
try:
    while True:
        time.sleep(1)
except KeyboardInterrupt:
    print("Keyboard cancel, stopping program.")
finally:
    # Clean up resources.
    cursor.close()
    cnx.close()
    GPIO.cleanup()

