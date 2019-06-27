test:                                                                                             ## Run phpunit tests
	vendor/bin/phpspec run

test-coverage:                                                                                    ## Run phpunit tests
	phpdbg -qrr  vendor/bin/phpspec run

test-mutator:
	infection --threads=4 --min-msi=60 --only-covered --log-verbosity=2 --test-framework=phpspec

phpstan:
	vendor/bin/phpstan analyse -l max src
