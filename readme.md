# Legacy Tasking System

A simple legacy tasking system build with laravel framework version 5.3.24

## Software requirement

* [Composer](https://getcomposer.org/)
* [Vagrant](https://www.vagrantup.com/)
* [Virtualbox](https://www.virtualbox.org/wiki/Downloads)
* [Git Bash](https://git-for-windows.github.io/)

## Configuration
Edit hosts file :

* You can find the file under -> C:/Windows/System32/drivers/etc
* Add the code below at the bottom of host file.
```
192.168.10.10	tasking.dev
```

## Installation && Setup
Ps. For "*first time*" installation, it may take some time to download and configure.  

At project root explorer, right click and select "Git Bash Here",
this it will open the git bash terminal.

```
$ composer install
$ vendor/bin/homestead make
```
Edit Homestead.yaml :
* At line 19, change "homestead.app" to :
```
- map: tasking.dev
```
* At line 23, change "homestead" to :
```
    - legacy_task
```
Then continue on the get bash terminal :
```
$ vagrant up
$ vagrant ssh
$ cd legacy-tasking-system
$ php artisan migrate
```

Lastly, visit the system with chrome/IE/firefox by url :
```
http://tasking.dev/
```

## Database

For database management system, I use [HeidiSQL](http://www.heidisql.com/).

Please feel free to use any DBMS, for database configuration

```

Network type    : mysql
Hostname/IP     : 127.0.0.1
User            : homestead
Password        : secret
Port            : 3306

```
Ps. For further details, please check on ".env" file.

## Framework
* [Laravel](https://laravel.com/)
* [Bootstrap](http://getbootstrap.com/)
* [Jquery](https://jquery.com/)
