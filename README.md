- [PHPStan usage](documentation%2Fphpstan%2FREADME.md)
- [PHP_CodeSniffer usage](documentation%2Fphpcs%2FREADME.md)

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
    - [x] PHP_CodeSniffer in a very strict level (lots of rules are my personal "taste", feel free to change/remove them)
    - [x] Roave/SecurityAdvisories to prevent using dependencies with known security vulnerabilities

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

#### Prerequisites
1. docker installed
2. git installed

#### Recommended prerequisites
1. Crete `.gitignore` file in a parent directory for your project and include `.idea` line in it. This directory created by PhpStorm in every project should not be versioned but is not included in project scope .gitignore file. (best-practice)
