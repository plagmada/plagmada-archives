This repository contains the Omeka source and surrounding configurations used by the plagmada.org archives.

# Installation

## Install Required Packages

(Ubuntu 15.04)

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

Locally, copy the file `omeka/db.example.ini` to `omeka/db.ini`, and modify to `omeka/db.ini` match the MySQL credentials set up above.

Then, upload the plagmada-archives source to the server.

```
cp omeka-2.4.1/db.example.ini omeka-2.4.1/db.ini
cd ../
rsync -rptvv plagmada-archives user@plagmadahost:~/
```

Remotely (on the EC2 instance):

```
sudo ln -s /home/ubuntu/plagmada-archives/omeka-2.4.1 /var/www/omeka
sudo chown -R ubuntu.www-data plagmada-archives/omeka-2.4.1
sudo chmod -R go-w plagmada-archives/omeka-2.4.1
sudo chmod -R ug+rw plagmada-archives/omeka-2.4.1/files
sudo chmod o-rwx plagmada-archives/omeka-2.4.1/db.ini
sudo chown ubuntu.www-data plagmada-archives/omeka-2.4.1/db.ini
```


## Configure PHP5-FPM

```
sudo cp plagmada-archives/php5-fpm/php.ini /etc/php5/fpm/
sudo service php5-fpm restart
```

## Configure Nginx

```
sudo rm -f /etc/nginx/sites-enabled/default

sudo cp plagmada-archives/nginx/omeka.plagmada.org /etc/nginx/sites-available/

sudo ln -s /etc/nginx/sites-available/omeka.plagmada.org /etc/nginx/sites-enabled/omeka.plagmada.org

sudo service nginx restart
```

## Finish installation through browser

Go to http://omeka.plagmada.org/install/install.php and follow the instructions.

NOTE: Subsequent to successful completion of the installation step just above, you should be able to log into Omeka at http://omeka.plagmada.org/admin/users/login

## Current issues

* Updating (rsync'ing) requires re-setting of ownership and permission files.

# Upgrading
See [Omeka Upgrading Documentation](https://omeka.org/codex/Upgrading)

# Troubleshooting

* Error logs involving web service and PHP will be at `/var/log/nginx/omeka.plagmada.org.error.log`
* Error logs for the PHP process manager (php5-fpm) are at `/var/log/php5-fpm.log`