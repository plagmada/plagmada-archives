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

(Assuming you are in the plagmada-archives folder) Copy the file nginx/collectiveaccess.plagmada.org into the Nginx configuration folder. On Ubuntu, this is typically at /etc/nginx/sites-available. The following lines will do this, and also try to restart nginx with the new configuration:

On Ubuntu 15.04 (assumption):

```
sudo cp nginx/php5/collectiveaccess.plagmada.org /etc/nginx/sites-available/collectiveaccess.plagmada.org
sudo ln -s /etc/nginx/sites-available/collectiveaccess.plagmada.org /etc/nginx/sites-enabled/collectiveaccess.plagmada.org
sudo ln -s /home/ubuntu/plagmada/plagmada-archives/providence-1.6.1 /var/www/collectiveaccess
sudo chown -R ubuntu.www-data providence-1.6.1
sudo service nginx restart
```

On Ubuntu 16.04

```
sudo cp nginx/php5/collectiveaccess.plagmada.org /etc/nginx/sites-available/collectiveaccess.plagmada.org
sudo ln -s /etc/nginx/sites-available/collectiveaccess.plagmada.org /etc/nginx/sites-enabled/collectiveaccess.plagmada.org
sudo mv /home/ubuntu/plagmada/plagmada-archives/providence-1.6.1 /var/www/collectiveaccess
sudo chown -R ubuntu.www-data /var/www/collectiveaccess
sudo service nginx restart
```
