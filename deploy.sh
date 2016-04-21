#!/bin/bash
env=$1
vhostfile='virtual-hosts.'${env}
sudo cp php.ini /etc/php5/apache2
sudo cp ${vhostfile} /etc/apache2/sites-available/insided.conf
sudo a2ensite insided.conf

cd /home/ubuntu/phpapps/insided/ui/
sudo cp app/config/parameters.prod.yml app/config/parameters.yml.dist
sudo mkdir vendor
sudo chmod 777 -R web
sudo chmod 777 -R vendor
sudo chmod 777 -R app/config
sudo composer install --optimize-autoloader 
sudo chmod 777 -R var/cache
sudo chmod 777 -R var/logs
cd web
sudo npm install

cd /home/ubuntu/phpapps/insided/simple-blog/
sudo chmod 777 -R web
sudo cp app/config/parameters.prod.yml app/config/parameters.yml.dist
sudo mkdir vendor
sudo chmod 777 -R vendor
sudo chmod 777 -R app/config
sudo composer install --optimize-autoloader
sudo chmod 777 -R var/cache
sudo chmod 777 -R var/logs

sudo service apache2 reload  