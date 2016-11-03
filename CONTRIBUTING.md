Contributing
------------

To contribute to this project, clone the repository, then install its dependencies using composer:

    composer install

Before submitting your code, please make sure the code conforms to to the PHPCS coding standard and that all the tests pass.
To do this, run the following commands:

    ./vendor/bin/phpcs
    ./vendor/bin/phpunit

To automatically fix some coding style issues, use phpcbf:

     ./vendor/bin/phpcbf
