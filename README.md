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

## Testing the App's React Components
To test the functionality of the app's frontend components and pages in the correct Docker environment, run the following command

Bash
```
docker exec laravel-app npm test
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

## NOTE
See Records Project Design.xlsx for details on my high level plans for the various frontend, backend, and database components of this project. See Judith Records request.pdf for an example of a legacy retention request
