This repo consists of two symfony apps, one for the ui and other for the api.

The ui calls the api server using regular http calls for consuming the services. I Should have implemented some kind of authentication on the api side but the time wasnt enough to do so.

All the ui is javascript based using the mustache template engine. I also used jquery for dealing with dom and mootools for providing oo features. Also used the bootstrap ui components for building the ui. All the dependencies are managed using npm.

There is some api tests: just enter the simple-blog folder and type the command "phpunit". You need phpunit installed (https://phpunit.de/manual/current/pt_br/installation.html)

Deploy:

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