# Money Backend

```bash
# 1. Start the docker containers
docker-compose up -d

# 2. Install composer packages
docker-compose exec php composer install

# 3. Create the database
docker-compose exec php bin/console doctrine:database:create --no-interaction

# 4. Load the migrations
docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
```
