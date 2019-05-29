[![Maintainability](https://api.codeclimate.com/v1/badges/df8cbc772601e9169865/maintainability)](https://codeclimate.com/github/LykaJ/api/maintainability)

# Installation #
***

1. Clone or download the project.
1. Install dependencies with composer: `$ composer install`
1. Edit the .env file:
`DATABASE_URL=mysql://user:pass@127.0.0.1:8889/database_name`
1. Edit the JWT passphrase:
`JWT_PASSPHRASE=password_jwt`
1. Create database `bin/console doctrine:database:create`
1. Run the Web server with `bin/console server:start`
1. Use Postman to navigate through the API.
