#!/bin/bash

#node
wget https://nodejs.org/dist/v4.3.2/node-v4.3.2-linux-x64.tar.xz -O tmp/node-v4.3.2-linux-x64.tar.xz
sudo mkdir -p /opt/node
tar -xf tmp/node-v4.3.2-linux-x64.tar.xz -C /opt/node
update-alternatives --install /usr/bin/node node /opt/node/node-v4.3.2-linux-x64/bin/node 2110
update-alternatives --install /usr/bin/npm npm /opt/node/node-v4.3.2-linux-x64/bin/npm 2110

#migrations
export LANGUAGE=en_US.UTF-8
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8
export LC_CTYPE="en_US.UTF-8"
locale-gen en_US.UTF-8
sudo dpkg-reconfigure locales
wget https://bootstrap.pypa.io/get-pip.py
python get-pip.py
sudo rm get-pip.py
sudo pip install yoyo-migrations
sudo pip install pymysql

#PHP
sudo add-apt-repository ppa:ondrej/php5-5.6
sudo apt-get update
sudo apt-get install -y php5
sudo apt-get install -y php5-mysql
sudo apt-get install -y php5-gd
sudo apt-get install -y php5-curl

#PHPUnit
wget https://phar.phpunit.de/phpunit.phar
chmod +x phpunit.phar
sudo mv phpunit.phar /usr/local/bin/phpunit

#composer
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
php -r "if (hash_file('SHA384', 'composer-setup.php') === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=/usr/local/bin
php -r "unlink('composer-setup.php');" 

#Apache2
sudo apt-get install -y apache2
sudo apt-get install -y libapache2-mod-proxy-html libxml2-dev
sudo a2enmod rewrite proxy proxy_http proxy_balancer headers lbmethod_bybusyness
sudo service apache2 restart

#mysql
sudo apt-get install -y mysql-server

