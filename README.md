# eDirectory
---
## Requirement
*Production requirements*

**`Apache`** `(2.2 or 2.4)` with mod-rewrite enabled and env module

**`PHP`** `5.6`

**`MySql`** `5.6` or **`MariaDB`** `(10.0.27 or 10.1)`

**`ElasticSearch`** `2.3.4`

PHP standard libraries + additional modules: mcrypt, gd, intl, mbstring, pdo, pdo_mysql, exif, apcu/apc.

cURL enabled

JSON needs to be enabled

OpenSSL

Set on php.ini:

- date.timezone
- Safemode Disabled
- Openbase_dir Disabled
- Open_short_tag must be "ON"


*Development Requirements*
**`Node`** `8.11.*` | **`Gulp-CLI`** `2.0.*`

## Instalation
***To install  eDirectory, follow the [Install Guide](https://netuno.arcasolutions.com/basecode/edirectory/wikis/v11000-install-guide)***

***For development, follow this tutorial***

1) Run the command bellow to install the dependencies
    ```
    npm install
    ```
2) Copy the file `gulpfile.sample.js` and rename it to `gulpfile.js`
3) Configure the `host` in the `gulpfile.js` with your **eDirectory** host

## Tasks
### Compile files
***FRONT-END***
```
gulp frontend
```
*Or*
```
gulp frontend --theme <theme name>
```

***SITEMGR***
```
gulp sitemgr
```

### Run web-server to compile in real-time
***FRONT-END***
```
gulp frontend-watch
```
*Or*
```
gulp frontend-watch --theme <theme name>
```

***SITEMGR***
```
gulp sitemgr-watch
```

## Technology

* [NPM](https://www.npmjs.com/) - Package Manager
* [NODEJS](https://nodejs.org/) - Javascript Engine
* [GULP](https://gulpjs.com/) - Workflow Automation
* [LESS](http://lesscss.org/) - CSS Preprocessor
* [SASS](https://sass-lang.com/) - CSS Preprocessor

## Authors

* **João Vitor Deroldo** - *Dev Front-End* - [Site](https://joaov.com.br)
