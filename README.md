[![Maintainability](https://api.codeclimate.com/v1/badges/df8cbc772601e9169865/maintainability)](https://codeclimate.com/github/LykaJ/api/maintainability)

# Before starting the installation #

Before staring, you need to generate the SSH keys. 
To do so, paste the following in your terminal:
```
$ mkdir -p config/jwt # For Symfony3+, no need of the -p option
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```
*Note your password_jwt to paste it in the .env later.*

# Installation #

1. Clone or download the project.
1. Install dependencies with composer: `$ composer install -o -a`
1. Edit the .env file:
`DATABASE_URL=mysql://user:pass@127.0.0.1:8889/database_name`
1. Edit the JWT passphrase:
`JWT_PASSPHRASE=password_jwt`
1. Create database `php bin/console doctrine:database:create`.
1. Run the command `php bin/console doctrine:schema:create` to create the schema in the database.
1. Load the fixtures with `php bin/console doctrine:fixtures:load --append`.
1. Run the Web server with `php bin/console server:start`.
1. Use Postman to navigate through the API.

# API Documentation #

To access the API documentation, request the route '/doc' (example: http://127.0.0.1:8000/doc).

# How to login to the app #

To access the secured routes of the api, you need to be authentified. 
To do so:
1. Request the following route via the POST method: http://127.0.0.1:8000/login_check
1. In the **Body** of the request, add your username and password like so: 
  ```
  {
	   "username": "your_username",
	   "password": "your_password"
  }
  ```
1. Send the request. 
1. The API sends back a token. Copy that token and paste it in the header key Authorization under the value *Bearer* (example: Bearer YOUR_TOKEN).

You can now access all the secured routes of the api.

