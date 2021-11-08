#!/bin/bash
php artisan config:clear
php artisan view:clear
php artisan optimize
php artisan cache:clear