This repository contains the Omeka and CollectiveAccess source and surrounding configurations used by the plagmada.org archives.

PLEASE NOTE: This is a work in progress! Check out the Issues to see what we're working on.

Omeka is [here on GitHub](https://github.com/omeka/Omeka)

CollectiveAccess's Providence is [here on GitHub](https://github.com/collectiveaccess/providence)

CollectiveAccess's Pawtucket2 is [here on GitHub](https://github.com/collectiveaccess/pawtucket2)

# Base Installation

## Base Server Packages

(Ubuntu 15.04)

```
sudo apt-get update
sudo apt-get install mysql-server php5-fpm php5-imagick php5-mysqlnd php-xml php-curl nginx nginx-extras imagemagick
```

(Ubuntu 16.04, which introduced PHP7)

```
sudo apt-get update
sudo apt-get install mysql-server php7.0-fpm php-imagick php7.0-mysql php-xml php-curl nginx nginx-extras imagemagick
```

Depending on the state of the machine on 16.04, you may encounter some nonsense with the MySQL 5.7 setup, apparently the thing doesn't know how to set up it's own password. After [fixing the problem](https://mirzmaster.wordpress.com/2009/01/16/mysql-access-denied-for-user-debian-sys-maintlocalhost/), make sure you have set up a root password too. Valid instructions are right at the end on [this page](https://bugs.launchpad.net/ubuntu/+source/mysql-5.7/+bug/1571668)

## Cleanup

```
sudo update-rc.d remove apache2
sudo service apache2 stop
```

NOTE: This no longer appears to be necessary.

# Proceed

For Omeka instructions, read docs/omeka.md

For CollectiveAccess instructions, read docs/collectiveaccess.md