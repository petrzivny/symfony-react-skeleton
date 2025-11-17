## How setup and use Xdebug
Xdebug is configured out-of-the-box in container for all CLI commands. Try it:
### CLI usage
To use xdebug in CLI, you need to use `xphp` alias inside docker container. Eg.:
1. Open api/src/Command/SecretsExternalDecryptToFileCommand.php in PhpStorm (or other IDE) and place a xdebug breakpoint on the first line of execute method.
2. Check if Listening for PHP Debug Connections icon is in listening mode. 
3. Start shell inside php docker container
```sh 
docker exec -it ${PROJECT_NAME}_php sh -l
```
4. Debug any php script by using alias `xphp`
```sh
xphp bin/console status
```
5. If asked by PhpStorm to provide server mappings, do it like this ![xdebug-mapping.png](xdebug-mapping.png)
6. Debugger should stop at your breakpoint, and you should see all debug info.
![breakpoint.png](breakpoint-new.png)

### BROWSER usage
I recommend to install [Browser debugging extension](https://www.jetbrains.com/help/phpstorm/2025.2/browser-debugging-extensions.html?php.debugging.browser_extensions&keymap=GNOME), or other similar tool to add XDEBUG_SESSION cookie to requests.
1. Open api/src/Controller/HealthController.php in PhpStorm (or other IDE) and place a xdebug breakpoint on the first line of index method.
2. Check if Listening for PHP Debug Connections icon is in listening mode.
3. Perform http://localhost:81/health request from your browser (with a XDEBUG_SESSION_START cookie, see description few lines before)
4. If asked by PhpStorm to provide server mappings, do it like this ![xdebug-mapping.png](xdebug-mapping.png)
5. Debugger should stop at your breakpoint, and you should see all debug info.
#### Troubleshooting 
Try to disable in PHPStorm: `Settings | PHP | Debug | External connections | Ignore external connections through unregistered server configurations`. It can help you with different port for example.
Try `http://localhost:81/api/status?XDEBUG_SESSION_START=WHATEVER` to simulate Browser debugging extension.
