[![Build](https://github.com/fey/slim-php-application/actions/workflows/main.yml/badge.svg?branch=main)](https://github.com/fey/slim-php-application/actions/workflows/main.yml) [![codecov](https://codecov.io/gh/fey/slim-php-application/branch/main/graph/badge.svg?token=N8RO2YOQ75)](https://codecov.io/gh/fey/slim-php-application)

# Slim Application

## Requirements

* PHP 8.1+
* PostgreSQL

## Local development

1. Run PostgreSQL database.
2. Install dependencies and prepare *.env*

```bash
make setup
```

3. Edit *.env* file and set variables for application

## Production

1. Create app on Heroku
2. Add PostgreSQL database

  ```bash
  heroku addons:create heroku-postgresql:hobby-dev
  ```

3. Go to [rollbar.com](https://rollbar.com/), register and set token on Heroku

  ```bash
  heroku config:set ROLLBAR_TOKEN=<token>
  ```

4. Deploy application

  ```bash
  make deploy
  ```
