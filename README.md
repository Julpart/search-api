# Composer template for Drupal projects
## Usage
### Project initialization
1. [Install docker](https://git.i20.biz/drupaljedi/localenv).
1. Clone the project: `git clone git@git.i20.biz:drupaljedi/project-template.git -b 9.x <project_name>`
1. Enter to the created project directory: `cd <project_name>`
1. Remove the old git repo: `rm -rf .git`
1. cd provision/local
1. cp ../.env.example ./.env
1. Edit .env file. Replace all entries of "project_name" with proper project name.
1. Optionally run "sudo gedit /etc/hosts". Add row 127.0.0.1	project_name.loc. But normally latest localenv leads all .loc domains to 127.0.0.1
1. Make sure that network is up in localenv project.
1. docker-compose up -d
1. sudo cp ../settings.inc.example ./docker-runtime/php-settings/project_name-settings.inc. "project_name" is the name of your project.
1. Create folder app/docroot/sites/default/files
1. chown -R 82:82 app/docroot/sites/default/files
1. run "docker-compose exec --user 1000 cli /bin/bash" from the provision/local folder.
1. run "composer install"
1. Edit sites/default/settings.php. Make sure there is proper path to the settings file specified in point 11.
1. Edit sites/prj-settings.inc. Replace all entries of "project_name" with your project name.
1. visit site by url http://project_name.loc
1. Install using "Import DrupalJedi configuration" (not standard)

### Joining to the existing project
1. [Install docker](https://git.i20.biz/drupaljedi/localenv).
1. Clone the your project
1. Execute the `make join project_name=example` command to join the project.

## What does the template do?

When installing the given `composer.json` some tasks are taken care of:

* Drupal will be installed in the `docroot`-directory.
* Autoloader is implemented to use the generated composer autoloader in `vendor/autoload.php`,
  instead of the one provided by Drupal (`drupal/vendor/autoload.php`).
* Modules (packages of type `drupal-module`) will be placed in `docroot/modules/contrib/`
* Theme (packages of type `drupal-theme`) will be placed in `docroot/themes/contrib/`
* Profiles (packages of type `drupal-profile`) will be placed in `docroot/profiles/contrib/`
* Libraries (packages of type `drupal-library`) will be placed in `docroot/libraries/`

## Updating Drupal Core

This project will attempt to keep all of your Drupal Core files up-to-date; the 
project [drupal-composer/drupal-scaffold](https://github.com/drupal-composer/drupal-scaffold) 
is used to ensure that your scaffold files are updated every time drupal/core is 
updated. If you customize any of the "scaffolding" files (commonly .htaccess), 
you may need to merge conflicts if any of your modified files are updated in a 
new release of Drupal core.

Follow the steps below to update your core files.

1. Run `composer update` to update Drupal core, contrib modules and its dependencies.
1. Run `git diff` to determine if any of the scaffolding files have changed. 
   Review the files for any changes and restore any customizations to 
  `.htaccess` or `robots.txt`.
1. Commit everything all together in a single commit, so `docroot` will remain in
   sync with the `core` when checking out branches or running `git bisect`.

## CI Initialization
To initialize CI you need to:

1. Replace the placeholders in `tools/config.yml` file and check other settings in that file.
1. Check the configuration inside `tools/pipeline.j2` file.
1. Ask your STO to finish the initialization.

## FAQ
### How can I apply patches to downloaded modules?

To add a patch to drupal module foobar insert the patches section in the extra 
section of composer.json:
```json
"extra": {
    "patches": {
        "drupal/foobar": {
            "Patch description": "URL to patch"
        }
    }
}
```
