infection.phar:
	wget https://github.com/infection/infection/releases/download/0.29.12/infection.phar
	chmod a+x infection.phar

PHONY: test
test:
	vendor/bin/phpunit
	vendor/bin/behat -f progress

PHONY: test-mutator
test-mutator: infection.phar
	./infection.phar

PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse -l max src
