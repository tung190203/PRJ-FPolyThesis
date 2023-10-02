
# Getting started
## Environmental requirements
Laravel 10 (PHP 8.1 + MariaDB 10.4)
Link xampp for windows: https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.1.17/xampp-windows-x64-8.1.17-0-VS16-installer.exe/download
Link composer for windows: https://getcomposer.org/Composer-Setup.exe
Link nodejs for windows: https://nodejs.org/dist/v18.18.0/node-v18.18.0-x64.msi
## Installation

Clone the repository

    git clone https://github.com/Manhnd10803/PRJ-FPolyThesis.git

Switch to the repo folder

    cd PRJ-FPolyThesis_Backend

Install all the dependencies using composer and npm

    composer install
    npm install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

Run the database migrations (**Set the database connection in .env before migrating**)

    php artisan migrate (tạm thời chưa cần chạy vì chưa có db chuẩn)

Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000

