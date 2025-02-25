test:                                                                                             ## Run phpunit tests
	vendor/bin/phpunit

test-mutator:
	infection --threads=4 --min-msi=60 --only-covered --log-verbosity=2 --test-framework=phpunit

phpstan:
	vendor/bin/phpstan analyse -l max src
