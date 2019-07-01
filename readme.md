# README

This repository contains all the required files may need to work.

### Run the project

First you need to make a copy of the `config.example.php` with the name `config.php`.
Edit the file and add the right values.

#### Create database structure

The file `contact_manager.sql` contains the required structure of the database,
you need to restore in a MySQL Server, preferred the `5.7.26` version.

#### Create and run the docker image

To create the image you just need to run the next command:

```
docker build -t contact-manager:1 --no-cache .
```

To run the image you just need to run the next command:

```
docker run --restart unless-stopped -d -p 3000:3000 --name contact-manager contact-manager:1
```

After this command the API will be listening on port `3000`.

#### Postman request collection

In this repo is the postman collection, so you could restore in postman and start
making request to the API.

## TODO

- Add comments
- Write a documentation
- Add logs to file for errors