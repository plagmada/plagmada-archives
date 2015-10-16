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
sudo service apache2 stop
```

NOTE: This no longer appears to be necessary.

## Create Omeka Database

Log into the database as root:

```
mysql -u root -p
```

Create the database and grant privileges. *IMPORTANT:* (Replace SQL_INJECTION below with a really good password surrounded in quotes!)

```
CREATE DATABASE omeka;
CREATE USER 'omeka'@'localhost';
SET PASSWORD FOR 'omeka'@'localhost' = PASSWORD(SQL_INJECTION);
GRANT ALL ON omeka.* to 'omeka'@'localhost';
```

Test database creation by trying to log in:

```
mysql -u omeka -p
```

## Copy over the PlaGMaDA archive repository

```
rsync -rptvv plagmada-archives ubuntu@HOSTNAME:~/
```

## Install Omeka

Modify the file omeka/db.ini to match the MySQL credentials set up above.

```
mv /home/ubuntu/plagmada-archives/omeka/db.example.ini /home/ubuntu/plagmada-archives/omeka/db.ini
```

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