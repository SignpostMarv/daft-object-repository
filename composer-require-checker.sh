if [ ! -f phive.phar ]; then
    php -r 'file_put_contents("./phive.phar", file_get_contents("https://phar.io/releases/phive.phar"));'
    php -r 'file_put_contents("./phive.phar.asc", file_get_contents("https://phar.io/releases/phive.phar.asc"));'
    gpg --keyserver hkps.pool.sks-keyservers.net --recv-keys 0x9B2D5D79
    gpg --verify phive.phar.asc phive.phar
fi
php phive.phar install --trust-gpg-keys D2CCAC42F6295E7D composer-require-checker

if [ -f ./tools/composer-require-checker.bat ]; then
    ./tools/composer-require-checker.bat check ./composer.json
else
    ./tools/composer-require-checker check ./composer.json
fi
