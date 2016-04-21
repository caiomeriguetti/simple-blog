#!/bin/bash

#PHP
sudo add-apt-repository ppa:ondrej/php5-5.6
sudo apt-get update
sudo apt-get install -y php5
sudo apt-get install -y php5-mysql
sudo apt-get install -y php5-gd
sudo apt-get install -y php5-curl

#composer
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
php -r "if (hash_file('SHA384', 'composer-setup.php') === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=/usr/local/bin
php -r "unlink('composer-setup.php');" 

#Apache2
sudo apt-get install -y apache2
sudo a2enmod rewrite
sudo service apache2 reload

#mysql
sudo apt-get install -y mysql-server