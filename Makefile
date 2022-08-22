start:
	php -S localhost:8080 -t public public/index.php

lint:
	composer exec --verbose phpcs -- --standard=PSR12 public src templates tests

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 public src templates tests

test:
	composer exec --verbose phpunit tests

test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

deploy:
	git push heroku main
