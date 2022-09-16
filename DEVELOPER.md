# Rules and conditions to development process

1. Read the [README.md](README.md) first.

## Basic rules

1. Develop the functionality not under the administrator and check under the anonymous.
2. Use [services and dependency injection](https://www.drupal.org/docs/8/api/services-and-dependency-injection/services-and-dependency-injection-in-drupal-8) in own modules.
3. Always wrap text in templates with translation twig function `{{ 'text'|t }}`.
4. Use [twig](https://www.drupal.org/docs/8/theming/twig) functions in templates instead of preprocess hooks in theme.
5. ...

## Development flow
1. Start your work from `develop` branch.
2. Every `feature`, `task`, `bug` and `hotfix` must be forked in own branch named with their number in task manager. `task-223344`, for example.
3. ...

## Useful links
- [Drupal 8 project initialization (RU)](https://sites.google.com/a/i20.biz/i20-terrim/cod-holding/standards/development/specializirovannye-standarty/drupal-8-razrabotka/inicializacia-proekta).
- [Using composer with Drupal 8 (RU)](https://sites.google.com/a/i20.biz/i20-terrim/cod-holding/standards/development/specializirovannye-standarty/drupal-8-razrabotka/ispolzovanie-composer).

## Best practices in Drupal 8 Development (Links)
- [DRUPAL 8 DEVELOPMENT BEST PRACTICES By Greg Boggs](http://www.gregboggs.com/drupal-development-best-practices/).
- [WONDROUS Drupal 8 Best Practice 2016](https://github.com/WondrousLLC/drupal-8-best-practices).
- [DRUPAL 8 BREADCRUMBS - ADD THE CURRENT PAGE](http://www.gregboggs.com/drupal8-breadcrumbs/).
- ...


### Task completion checklist

- [ ] I have exported configuration and added it to the project.
- [ ] My code did not spawn an invalid layout.
- [ ] My code corresponds to code-style, all debugging is removed, all TODO are applied.
- [ ] Browser console don't have JS errors and debug info.
- [ ] Your code works in other browsers like FF and IE.
- [ ] Think about the time of the tester! Describe how-to test your task.

## Project specific rules

*This part of document should be added by TL of project.*

See below as example:

### Working with theme and templates

1. Project use static assets (css, js, images, libraries) and html-code prepared by FE-team. All of these generate into `<theme_path>/bundles/` folder by `make build-html`  command using Makefile from project root folder.
 - Use generated html as a base for theme templates.
 - Describe all libraries and css in THEME.libraries file.
