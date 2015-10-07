This repository contains the Omeka source and surrounding configurations used by the plagmada.org archives.

# Installation

## Install Required Packages

(Ubuntu 14.04)

```
sudo apt-get update
sudo apt-get install mysql-server php5-fpm php5-imagick php5-mysqlnd nginx nginx-extras imagemagick
```

## Cleanup

```
sudo update-rc.d remove apache2
```


## Create Omeka Database

Log into the database as root:

```
mysql -u root -p
```

Create the database and grant privileges. *IMPORTANT:* replace 'examplepass' with a good password!:

```
CREATE DATABASE omeka;
CREATE USER 'omeka'@'localhost' IDENTIFIED BY 'examplepass';
GRANT ALL ON omeka.* to 'omeka'@'localhost';
```

## Install Omeka

Modify the file omeka/db.ini to match the MySQL credentials set up above.

```
sudo ln -s /home/ubuntu/plagmada-archives/omeka /var/www/omeka
sudo chown -R ubuntu.www-data omeka
sudo chmod -R go-w omeka
sudo chmod -R ug+rw omeka/files
sudo chmod o-rwx omeka/db.ini
```


## Configure PHP5-FPM

```
sudo cp php5-fpm/php.ini /etc/php5/fpm/
sudo php5-fpm restart
```

## Configure Nginx

```
sudo rm -f /etc/nginx/sites-enabled/default

sudo cp nginx/omeka.plagmada.org /etc/nginx/sites-available/

sudo ln -s /etc/nginx/sites-available/omeka.plagmada.org /etc/nginx/sites-enabled/omeka.plagmada.org

sudo service nginx restart
```

Now go to http://omeka.plagmada.org/install/install.php and follow the instructions.