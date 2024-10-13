docker compose down
docker compose up -d
docker compose exec php composer install
docker compose exec php bin/cake migrations migrate
docker compose exec php bin/cake create_user -u admin -p password -e admin@test.com -a 1
docker compose exec php bin/cake default_data_import --all
docker compose exec php vendor/bin/phpunit
docker compose exec php vendor/bin/phpcs --standard=vendor/cakephp/cakephp-codesniffer/CakePHP src/ tests/
docker compose exec php bin/cake cache clear_all
chmod 777 -R webroot/ tmp/ logs/
