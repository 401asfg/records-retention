## Setting up the .env file
Copy the .env.example file and name it ".env".
Fill out the variables (such as DB_HOST=) with the values you wish to use for this project.

## Running the App
Run the following command to launch a nginx server to locally host the app, a mysql database to store the apps data, and the app itself

Bash
```
docker compose up --build -d
```

### Setting up the Development Database
In order for this application to function in this Docker environment, the app's database needs to have several tables. These tables can be added to the database using the following command:

Bash
```
docker exec laravel-app php artisan migrate:fresh
```

Furthermore, in order for certain aspects of the app to be observable (such as the request form's searchable dropdown), the database tables should be populated using seeders.

NOTE: while the User, Box, Department, and Retention Request tables are seeded with dummy values for testing, the values seeded into the Role table are the only values that table should have even in production (unless new roles are added to the project or redundant roles are removed).

Seed the Role database:

Bash
```
docker exec laravel-app php artisan db:seed --class=RoleSeeder
```

The rest of the tables can be seeded in the same way, simply by substituting RoleSeeder for UserSeeder, BoxSeeder, DepartmentSeeder, or RetentionRequestSeeder.

## Viewing the Docker Database
You can view the Docker environment's development MySQL database at any time with the commands:

Bash
```
docker exec -it mysql-db /bin/bash
mysql -u root -p
```

NOTE: The password for this database is defined in the .env file as the variable: DB_PASSWORD.

## Viewing the Application
Go to the url 127.0.0.1:8000 in any browser.

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
