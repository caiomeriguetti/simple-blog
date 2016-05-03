#Intro

This repo consists of two symfony apps, one for the ui and other for the api.

The ui calls the api server using regular http calls for consuming the services. I Should have implemented some kind of authentication on the api side but the time wasnt enough to do so.

All the ui is javascript based using the mustache template engine. I also used jquery for dealing with dom and mootools for providing oo features. Also used the bootstrap ui components for building the ui. All the dependencies are managed using npm.

There is some api tests: just enter the simple-blog folder and type the command "phpunit". You need phpunit installed (https://phpunit.de/manual/current/pt_br/installation.html)

#Deploy:

To install everything needed to run the app execute

```bash
./install.sh
```

To deploy the app into apache just execute:
```bash
./deploy.sh local
```
or

```bash
./deploy.sh prod
```
for production

The deploy.sh script makes averything related to the deployment process. Basically it sets up the virtualhosts, renames some files, execute migrations and so on.

The ui is here: http://ec2-54-187-107-64.us-west-2.compute.amazonaws.com:9090/ and the api endpoint is here: http://ec2-54-187-107-64.us-west-2.compute.amazonaws.com:9091/

#Bonus points: Scaling the app

What i would probably do is to configure multiple ui servers as well as multiple api servers. After that i would put a load balancer in front of all api/ui servers to balance the traffic. To do so we can use apache proxy balancer module.

Additionally, i would host all static file in s3 or sets up a dedicated apache server to serve the files with some caching policy.

To ilustrate i created 2 load balancers: one for the ui and other for the api. The ui load balancer is configured to cache static files for 1 minute( /app/* and /node_modules/*). Both load balancers redirects the requests to the app servers using the bybusiness balancing strategy.



