# Sending a Postback Request

At this point, it is most reliable to sent requests to postback-agent.com/ingest.php

The way I have it set up to view with a browser is to add these lines to your etc/hosts file
(If you're on windows: C:\Windows\System32\drivers\etc\hosts)

45.55.35.202    postback-agent.com
45.55.35.202    postback-monitor.com

then click to browse to <a href="http://postback-agent.com/post-request.html" target="_blank">http://postback-agent.com/post-request.html</a>
and click to browse to <a href="http://postback-monitor.com" target="_blank">http://postback-monitor.com</a>

Use post-request to post to the server and see the response.
Watch the result at postback-monitor.

At this point postback-monitor shows the test url (enabled when post-request has stopwatch "on").
This url has the following timestamps added to it:

post-request.html
- send request with timestamp (S1) in object
ingest.php
- receive and set another timestamp (R1)
push to redis with
- stopwatch = 'on' if S1 was sent - otherwise stopwatch = 'off'
- S1,R1 and another sent timestamp (S2)
popper node
- pop from redis and set another timestamp (R2)
- IF stopwatch = 'on' send S1,R1,S2,R2 and another timestamp (S3)
receiver-one.com
- receive and set another timestamp (R3)


Stopwatch "off" will emulate a user request and not include time data in the postback.


#Observing the server logs
If you want to log in to the server and check out the logs while throwing requests at it
login as user www with password test.test

then run
sudo pm2 logs

Each action is labeled with the service that logged it:
pop-from-redis is the node that pulls data from the redis queue and sends to the endpoint
receiver-one is the endpoint - it adds the received timestamp (down to milliseconds) in the response but does not see the timestamps unless stopwatch is 'on' (stopwatch is described in the next section)
postback-monitor is the observer that gets timestamp data for visualization


pm2 starts/manages apps on restart
Use the following commands show streaming log data
sudo pm2 logs
and monitor server activity
sudo pm2 monit

you can check out node health here:
<a href=https://app.keymetrics.io>https://app.keymetrics.io</a>
login with ryan.dirks@yahoo.com and test.test



