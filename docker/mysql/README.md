# Willow CMS MySQL Database Setup
init.sql is the initial SQL script that sets up the database schema and initial data for Willow CMS. It is executed when the MySQL container is started for the first time.

init_cords_custom.sql is a custom SQL script that sets up the database schema and initial data for the CORDS inventory management system. It is a custom database that can be used alongside Willow CMS to isolate the CORDS data from the main Willow CMS database.



NEEDS VERIFICATION THAT IT WILL STILL CONNECT TO THE WILLOW CMS DATABASE
<!-- init_new_database.sql is a SQL script that sets up a new database for the CORDS inventory management system. It is executed when the MySQL container is started for the first time. -->


# Usage - Setting Up MySQL with Docker: 

FIRST TEST RUN BEFORE USING THE NEW DATABASE

1. Place the `init.sql` and `init_cords_custom.sql` files in the `docker/mysql` directory of your Willow CMS project.
2. Ensure that your `docker-compose.yml` file includes the MySQL service with the following configuration:
```yaml
services:
  mysql:
    image: mysql:latest
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: cms
      MYSQL_USER: willowcms
      MYSQL_PASSWORD: willowcms
    volumes:
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
      - ./docker/mysql/init_cords_custom.sql:/docker-entrypoint-initdb.d/init_cords_custom.sql
    ports:
      - "3306:3306"
```
3. Start your Docker containers using `docker-compose up`.
4. The MySQL container will automatically execute the `init.sql` and `init_cords_custom.sql` scripts to set up the database schema and initial data.



# Migrating the Database to a New MySQL Instance
If you need to migrate the database to a new MySQL instance, follow these steps:
1. Export the current database schema and data using the following command:
```bash
docker exec -i <mysql_container_name> mysqldump -u root -p<mysql_root_password> cms > cms_backup.sql
```
or 
use the './manage.sh' script to backup the database (option number 3)
2. Copy the `init_cords_custom_into_cms.sql` file to your new MySQL instance.
3. Import the database schema and data into the new MySQL instance using the following command:
```bash
docker exec -i <new_mysql_container_name> mysql -u root -p<mysql_root_password> cms < cms_backup.sql
```
or
use the phpMyAdmin interface to run the `init_cords_custom_into_cms.sql` file into the new MySQL instance.

4. Verify that the database has been migrated successfully by checking the tables and data in the new MySQL instance.
5. Update your `docker-compose.yml` file to point to the new MySQL instance if necessary.
6. now migrate using cakephp migrations
```bash
docker exec -it <willow_container_name> bin/cake migrations migrate
```