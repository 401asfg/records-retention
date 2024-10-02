## Hosted with the permission of VCC

## Running the App
Run the following command to launch a nginx server to locally host the app, a mysql database to store the apps data, and the app itself

Bash
```
docker compose up --build -d
```

Go to the url 127.0.0.1:8000

## Testing the App's API
To test the app's api in the correct Docker environment, run the following command

Bash
```
docker exec laravel-app php artisan test
```

## Access the MySQL Database
To access the MySQL database, after running the app in docker, run the following command

Bash
```
docker exec -it mysql-db /bin/bash
```

Shutting Down the App
To shutdown the app, running in the background, run the following command

Bash
```
docker compose down
```
