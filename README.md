- [PHPStan usage](documentation%2Fphpstan%2FREADME.md)
- [PHP_CodeSniffer usage](documentation%2Fphpcs%2FREADME.md)

## How to start local development
```bash
git clone git@github.com:petrzivny/symfony-react-skeleton.git
cd symfony-react-skeleton
mv api/.env.local.dist api/.env.local
cd .docker && docker compose --env-file ../api/.env.local up -d
```

### Run production (deployment) version of php and nginx
```bash
cd .docker && docker compose --env-file ../api/.env.local -f docker-compose-prod.yaml up -d
```

### What is included out-of-the-box
1. Docker to run complete dev environment (php + nginx + mysql)
2. Symfony framework as a backed REST api
    - [x] Symfony opcache preloading with JIT in prod (_performance_ ⏩).
    - [x] Xdebug setup to debug both html requests and CLI commands.
    - [x] Phpstan in a very strict level.
    - [x] PHP_CodeSniffer in a very strict level (lots of rules are my personal "taste", feel free to change/remove them).
    - [x] Psalm.
    - [x] Roave/SecurityAdvisories to prevent using dependencies with known security vulnerabilities.
    - [x] PHPUnit Unit tests.
    - [x] PHPUnit Functional tests (including smoke tests).
    - [x] Other linters (Composer, Yaml, Symfony container).
3. DevOps: CI pipeline to build both test and prod images
    - [x] Run all tests from point 2 on final (test) docker image (_best-practice_ 👍).
    - [x] If everything passes there are php and nginx environment agnostic (_best-practice_ 👍) containers ready to be shipped into any environment (including prod of course).
    - [x] Pipeline expects self-hosted GitHub runner(s). [See](https://docs.github.com/en/actions/hosting-your-own-runners/managing-self-hosted-runners/adding-self-hosted-runners) for more information.

### XDEBUG setup in PphStorm (with first debug call)
Xdebug is configured out-of-the-box in container for all CLI commands, for browser I recommend to install [Xdebug helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc), or other similar tool to add XDEBUG_SESSION cookie to requests.

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
1. Crete global `.gitignore` file in a parent directory for your project and add `.idea` line in it. This directory created by PhpStorm in every project should not be versioned but should not be included in project's scope .gitignore file either (_best-practice_ 👍).
2. GitHub repository.
3. At least one self-hosted GitHub runner to fully enjoy benefits of out-of-the-box CI pipeline.
