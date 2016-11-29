There is already full documentation for Collective Access [here](http://docs.collectiveaccess.org/wiki/Installing_Providence):

The docs recommend Apache 2x with PHP5x, but we are going to try an nginx + php-fpm setup. See base readme.md file to install requirements.

# Providence

## Create Providence Database

Log into the database as root:

```
mysql -u root -p
```

Create the database and grant privileges. *IMPORTANT:* (Replace SQL_INJECTION below with a really good password surrounded in quotes!)

```
CREATE DATABASE collectiveaccess;
CREATE USER 'collectiveaccess'@'localhost';
SET PASSWORD FOR 'collectiveaccess'@'localhost' = PASSWORD(SQL_INJECTION);
GRANT ALL ON collectiveaccess.* to 'collectiveaccess'@'localhost';
```

Test database creation by trying to log in:

```
mysql -u collectiveaccess -p
```

## Configure Nginx and set up wwwroot

(Assuming you are in the plagmada-archives folder) Copy the file nginx/collectiveaccess.plagmada.org into the Nginx configuration folder. On Ubuntu, this is typically at /etc/nginx/sites-available. The following lines will do this, and also try to restart nginx with the new configuration:

On Ubuntu 15.04 (assumption):

```
sudo cp nginx/php5/collectiveaccess.plagmada.org /etc/nginx/sites-available/collectiveaccess.plagmada.org
sudo ln -s /etc/nginx/sites-available/collectiveaccess.plagmada.org /etc/nginx/sites-enabled/collectiveaccess.plagmada.org
sudo ln -s /home/ubuntu/plagmada/plagmada-archives/providence-1.6.1 /var/www/ca-providence
sudo ln -s /home/ubuntu/plagmada/plagmada-archives/pawtucket2-develop /var/www/ca-pawtucket
sudo chown -R ubuntu.www-data providence-1.6.1
sudo chown -R ubuntu.www-data pawtucket2-develop
sudo service nginx restart
```

On Ubuntu 16.04

```
sudo cp nginx/php5/collectiveaccess.plagmada.org /etc/nginx/sites-available/collectiveaccess.plagmada.org
sudo ln -s /etc/nginx/sites-available/collectiveaccess.plagmada.org /etc/nginx/sites-enabled/collectiveaccess.plagmada.org
sudo mv /home/ubuntu/plagmada/plagmada-archives/providence-1.6.1 /var/www/ca-providence
sudo mv /home/ubuntu/plagmada/plagmada-archives/pawtucket2-develop /var/www/ca-pawtucket
sudo chown -R ubuntu.www-data /var/www/ca-providence
sudo chown -R ubuntu.www-data /var/www/ca-pawtucket
sudo service nginx restart
```

## Set up CollectiveAccess Pawtucket (Frontend) and Providence (Main app)

Go into /var/www/ca-pawtucket (`cd /var/www/ca-pawtucket`)

Copy the file setup.php-dist to setup.php and modify values to match database and paths above.

Go into /var/www/collectiveaccess (`cd /var/www/collectiveaccess`)

Copy the file setup.php-dist to setup.php and modify values to match database and paths above.

(While remaining in the same folder) Update file permissions:

```
sudo chown ubuntu.www-data setup.php
sudo chmod o-rwx setup.php
mkdir app/tmp
mkdir vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer
sudo chown ubuntu.www-data app/tmp vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer media
sudo chmod ug+rw app/tmp vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer media
```