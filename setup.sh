docker compose exec php bin/cake migrations migrate
docker compose exec php bin/cake create_user -u admin -p password -e admin@test.com -a 1
docker compose exec php bin/cake default_data_import --all
docker compose exec php bin/cake cache clear_all

