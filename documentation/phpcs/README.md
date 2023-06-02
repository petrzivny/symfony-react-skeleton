## How setup and use PHP_CodeSniffer
What is PHP_CodeSniffer why it is essential in quality php projects? Read more [here](https://github.com/squizlabs/PHP_CodeSniffer).
You can use out of the box PHP_CodeSniffer via CLI. After setup in you IDE, you can use PHP_CodeSniffer in your IDE too.

### CLI usage
```bash
# as always all CLI commands should be run in the php docker container. (docker exec -it symfony-react-skeleton_php sh -l) 
composer phpcs
# or
composer test
```

### IDE usage
Yes, you can use PHP_CodeSniffer to see static php errors in your PhpStorm.

#### IDE setup
1. Go to File | Settings | PHP | [Quality Tools](jetbrains://PhpStorm/settings?name=PHP--Quality+Tools) and click "PHPStan"
2. Click on "...", check PHPStan path: `/your/path/to/repo/api/vendor/bin/phpcs` and click on "validate"
3. You should see something like `OK, PHP_CodeSniffer version ...`
4. Click on "OK"
5. Switch button from OFF to ON
6. Select Custom for Coding standard select
7. Enter Configuration file: `/your/path/to/repo/api/phpcs.xml.dist`
8. Click on "OK"

**You can now see all static php errors in your IDE**
