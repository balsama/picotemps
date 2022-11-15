# This file should be renamed to `main.py` and save to the Pico W alongside `tempbotconfig.py`.
# Requires rp2-pico > 1.19.1 (which as of the time of writing this is a dev snapshot)
import network
import socket
import time
import dht

from machine import Pin
import uasyncio as asyncio

# Requires a `tempbotconfig.py` file to be saved on the pi with values for ssid, password, and tbid.
from tempbotconfig import *

led = Pin(15, Pin.OUT)
onboard = Pin("LED", Pin.OUT, value=0)

def webpage(temperature, humidity, tbid):
    #Template HTML
    html = f"""
            {{"temperature": {temperature},"humidity": {humidity},"id": "{tbid}"}}
            """
    return str(html)

wlan = network.WLAN(network.STA_IF)

def connect_to_network():
    wlan.active(True)
    wlan.config(pm = 0xa11140)  # Disable power-save mode
    wlan.connect(ssid, password)

    max_wait = 10
    while max_wait > 0:
        if wlan.status() < 0 or wlan.status() >= 3:
            break
        max_wait -= 1
        print('waiting for connection...')
        time.sleep(1)

    if wlan.status() != 3:
        raise RuntimeError('network connection failed')
    else:
        print('connected')
        status = wlan.ifconfig()
        print('ip = ' + status[0])

async def serve_client(reader, writer):
    print("Client connected")
    request_line = await reader.readline()
    print("Request:", request_line)
    # We are not interested in HTTP request headers, skip them
    while await reader.readline() != b"\r\n":
        pass
        
    sensor = dht.DHT22(Pin(2))
    sensor.measure()
    temperature = sensor.temperature()
    humidity = sensor.humidity()
    
    response = webpage(temperature, humidity, tbid)
    writer.write('HTTP/1.0 200 OK\r\nContent-type: text/html\r\n\r\n')
    writer.write(response)

    await writer.drain()
    await writer.wait_closed()
    print("Client disconnected")

async def main():
    print('Connecting to Network...')
    connect_to_network()

    print('Setting up webserver...')
    asyncio.create_task(asyncio.start_server(serve_client, "0.0.0.0", 80))
    print('Ready')
    while True:
        onboard.on()
        await asyncio.sleep(0.25)
        onboard.off()
        await asyncio.sleep(3)
        
try:
    asyncio.run(main())
finally:
    asyncio.new_event_loop()