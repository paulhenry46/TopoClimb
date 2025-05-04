## TopoClimb

TopoClimb is a web-app to manage routes of indoor climbing sites and generate topo.

## Features
- Upload map of area in svg format
- Draw lines on map of area
- Create routes, lines, sectors
- Create public page for each indoor site
- Comment routes and propose a new cotation
- Generate topo of the site

## Stack
- Laravel 12
- Livewire 3
- Tailwindcss 4
- Inkscape

## Setup

You will need a LAMP stack. In this process, we use Apache, MariaDB and PHP 8.3
- Install PHP and extensions required by Laravel : `apt install php8.3 php8.3-ctype php8.3-bcmath php8.3-curl php8.3-common php8.3-mbstring php8.3-xml php8.3-imagick`
- Install Inkscape (required for processing map of areas) : `apt install inkscape`
- Create and set up BDD :
    - `mysql -u root -p`
    - `CREATE DATABASE TopoClimbDB;`
    - `CREATE USER 'TopoClimbUser'@'localhost' IDENTIFIED BY 'YourPassword';`
    - `GRANT ALL PRIVILEGES ON TopoClimbDB.* TO 'TopoClimbUser'@'localhost';`
> [!WARNING]
> You will need to store the credential in the .env file !

- Install Imagick and Ghostscript
    - `apt install ghostscript`

- Clone the app and move it to where you want
- run `composer install`
- run `npm install` and `npm run build`
- edit .env file and put the Database infos
- run `php artisan key:generate`
- run `php artisan migrate`
- run `php artisan db:seed`
- run `php artisan storage:link`

An user with the hightest permissions is created, so you can log in with : Mail : `admin@system.localhost` / Password : `d4d5ehdp785pd81 `

> [!CAUTION]
> Don't forget to change the password after the first login, or better, create a new admin account and remove the default !

- Create the Apache2 site file :
```
<VirtualHost *:80>
    DocumentRoot /path/to/public_folder
    ServerName topoclimb.test
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
    <Directory /path/to/public_folder>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
- You can active https with certbot.
## Contributing

- Clone the app and move it to where you want
- run `composer install`
- edit .env file and put the Database infos
- run `php artisan key:generate`
- run `php artisan migrate`
- run `php artisan db:seed`
- run `php artisan storage:link`
- run `npm install`
- run `npm run dev`
- run `php artisan serve`
- It's ready. Thank you for your contributions !