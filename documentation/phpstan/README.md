## How setup and use PHPStan
What is PHPStan why it is essential in quality php projects? Read more [here](https://phpstan.org/).
You can use out of the box PHPStan via CLI. After setup in you IDE, you can use PHPStan in your IDE too.

### CLI usage
```bash
# as always all CLI commands should be run in the php docker container. (docker exec -it symfony-react-skeleton_php sh -l) 
composer phpstan
# or
composer test
```

### IDE usage
Yes, you can use PHPStan to see static php errors in your PhpStorm.

#### IDE setup
1. Go to File | Settings | PHP | [Quality Tools](jetbrains://PhpStorm/settings?name=PHP--Quality+Tools) and click "PHPStan"
2. Click on "...", check PHPStan path: `/your/path/to/repo/api/vendor/bin/phpstan` and click on "validate"
3. You should see something like `OK, PHPStan - PHP Static ...`
4. Click on "OK"
5. Switch button from OFF to ON
6. Enter Configuration file: `/your/path/to/repo/api/phpstan.neon`
7. Click on "OK"

**You can now see all static php errors in your IDE**
