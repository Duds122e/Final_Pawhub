@echo off
set DATABASE_URL=mysql://paw_user:paw_password@localhost:3310/paw_db?serverVersion=8.0
php -S 0.0.0.0:8001 index.php
