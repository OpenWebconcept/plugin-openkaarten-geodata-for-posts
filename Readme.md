# OpenKaarten Geodata Plugin

This plugin adds GeoData fields to the OpenPub Items post type and creates a REST endpoint to retrieve OpenPub Items with geodata.

## Requirements

### OpenKaarten Geodata

In order to make the OpenKaarten Geodata Plugin work, you will need to have a WordPress installation with at least the following installed (and activated):

* [WordPress](https://wordpress.org/)
* [OpenPub Base](https://github.com/OpenWebconcept/plugin-openpub-base/)
* [CMB2](https://wordpress.org/plugins/cmb2/)

On this WordPress installation you will have to enable pretty permalinks (Settings > Permalinks > Select any of the options that is not plain).

There are two possible setups for the OpenKaarten Geodata, this can be:

1. On the WordPress installation of an existing website.
2. On a completely new WordPress installation.

In all scenarios the OpenKaarten Geodata plugin needs to have the following installed (and activated):

* [WordPress](https://wordpress.org/)
* [OpenPub Base](https://github.com/OpenWebconcept/plugin-openpub-base/)
* [CMB2](https://wordpress.org/plugins/cmb2/)
* [OpenKaarten Geodata](https://github.com/OpenWebconcept/plugin-openkaarten-geodata-for-posts)
* [OpenKaarten Base Functions](https://github.com/OpenWebconcept/package-owc-openkaarten-functions/)

With this installed you can use the OpenKaarten Geodata plugin in your WordPress website.

If you chose for option 2 (new WordPress installation), you will probably need to install a WordPress theme. Since the OpenKaarten plugin is a REST API, it can be used in any WordPress theme.

## Works best with

The OpenKaarten Geodata plugin works best with the following plugins, which can be installed on a different WordPress installation:

- [OpenKaarten Base](https://github.com/openwebconcept/plugin-openkaarten-base): This plugin adds Datalayers and Locations to WordPress which can be retrieved via the OpenKaarten REST API.
- [OpenKaarten Frontend](https://github.com/OpenWebconcept/plugin-openkaarten-frontend-plugin): This plugin adds a map to your WordPress website where you can show the locations of the datalayers.

## Installation

### Manual installation

At this point manual installation is not supported, because of composer dependencies. We are working on this.

### Composer installation

1. `composer source git@github.com:OpenWebconcept/plugin-openkaarten-geodata-for-posts.git`
2. `composer require acato/openkaarten-geodata-for-posts`
3. `cd /wp-content/plugins/openkaarten-geodata-for-posts`
4. `npm install && npm run build`
5. Activate the OpenKaarten Geodata Plugin through the 'Plugins' menu in WordPress.

## Usage

### Add geodata to OpenPub Items
Add geodata to OpenPub Items by editing an OpenPub Item and filling in the geodata fields. You can either add geodata by clicking one or multiple points on the map or by filling in an address, which generates a latitude and longitude for 1 specific point.

## Development

### Coding Standards

Please remember, we use the WordPress PHP Coding Standards for this plugin! (https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/) To check if your changes are compatible with these standards:

*  `cd /wp-content/plugins/openkaarten-geodata-for-posts`
*  `composer install` (this step is only needed once after installing the plugin)
*  `./vendor/bin/phpcs --standard=phpcs.xml.dist .`
*  See the output if you have made any errors.
    *  Errors marked with `[x]` can be fixed automatically by phpcbf, to do so run: `./vendor/bin/phpcbf --standard=phpcs.xml.dist .`

N.B. the `composer install` command will also install a git hook, preventing you from committing code that isn't compatible with the coding standards.

### NPM
The plugin uses NPM for managing the JavaScript dependencies and building the leaflet map for showing locations within a datalayer. To install the dependencies, run the following command:
```
npm install
```

To deploy the JavaScript files, run the following command:
```
npm run build
```

To watch the JavaScript files for changes, run the following command:
```
npm run watch
```

## REST API Endpoints
This plugin adds the following REST API GET-endpoints:
- `/wp-json/owc/openkaarten/v1/openpub-items`
