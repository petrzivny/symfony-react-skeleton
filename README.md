[PHPStan usage](documentation%2Fphpstan%2FREADME.md)

## How to start local development
```bash
git clone
cd .docker && docker compose --env-file ../api/.env up -d

```

### What is included out of the box
1. Docker to run complete dev environment (php + nginx + mysql)
2. Symfony framework as a backed REST api
    - [x] Xdebug setup to debug both html requests and CLI commands
    - [x] Phpstan in a very strict level

### XDEBUG setup in PphStorm (with first debug call)
Xdebug is configured out of the box in container for all CLI commands, for browser I recommend to install [Xdebug helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc), or other similar tool to add XDEBUG_SESSION cookie to requests.

1. Click on "Start listening for PHP Debug Connection"
2. Perform any request from your browser (with a XDEBUG_SESSION cookie, see description few lines before)
3. If asked by PhpStorm to provide server mappings, do it like this ![xdebug-mappings.png](documentation%2Fimages%2Fxdebug-mappings.png)
4. If you want to xdebug CLI calls use `xphp` alias defined in docker container
```bash
docker exec -it symfony-react-skeleton_php sh -l
xphp bin/console debug:dotenv
```


### PHPStan - CLI usage
```bash
# Alias to detect bugs in a code. It scans all paths from phpstan-strict.neon
composer phpstan:strict

# Alias to detect bugs in a given path.
composer phpstan:strict path/to/my/files
```
***These commands should be always run inside docker container***

### PHPStan - IDE usage
Yes, you can use phpstan to see static php errors in your IDE.

#### PhpStorm setup
1. Go to File | Settings | PHP | [Quality Tools](jetbrains://PhpStorm/settings?name=PHP--Quality+Tools) and click "PHPStan"
2. Click on "..."
3. Enter PHPStan path: `/your/path/to/repo/server/vendor-composer/bin/phpstan` and click on "validate"
4. You should see something like `OK, PHPStan - PHP Static ...`
5. Click on "Apply"
6. Click on "PHPStan inspection" link (it opens File | Settings | Editor | [Inspections](jetbrains://PhpStorm/settings?name=Editor--Inspections))
7. Click on "Quality tools"
8. Check the PHPStan validation checkbox
9. Enter Configuration file: `/your/path/to/repo/server/phpstan-strict.neon`

**You can now see all static php errors in your IDE**


#### Prerequisites
1. docker installed
2. git installed

#### Recommended prerequisites
1. Crete `.gitignore` file in a parent directory for your project and include `.idea` line in it. This directory created by PhpStorm in every project should not be versioned but is not included in project scope .gitignore file. (best-practice)
