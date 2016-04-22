This repo consists of two symfony apps, one for the ui and other for the api.

The ui call the api server using regular http calls for consuming the services. We could have some kind of authentication on the api side but the time wasnt enough to do so.

All the ui is javascript based using the mustache template engine. I also used jquery for dealing with dom and mootools for providing oo features. Also used the bootstrap ui components for building the ui.

There is some api tests: just enter the simple-blog folder and type the command "phpunit". You need phpunit installed (https://phpunit.de/manual/current/pt_br/installation.html)

