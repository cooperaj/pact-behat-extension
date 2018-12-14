test:                                                                                             ## Run phpunit tests
	vendor/bin/phpspec run

test-coverage:                                                                                             ## Run phpunit tests
	phpdbg -qrr  vendor/bin/phpspec run

phpstan:
	vendor/bin/phpstan analyse -l max src	

