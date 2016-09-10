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

