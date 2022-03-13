# AOT ESHOP

An ecommerce web app made for school using Symfony 6 featuring basic usual functionalities

## Installation

1. Install dependencies

```bash
composer install
npm install
```

2. Configure your .env with the correct database and mailer credentials
3. Create the database and run migrations using doctrine
```
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```
4. Launch Symfony and webpack server (seperate terminals)

```
symfony serve -D
npm run watch
```
