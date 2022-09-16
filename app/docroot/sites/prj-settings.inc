<?php

/**
 * @file
 * Custom settings common for all multi-sites of the project.
 */

use Drupal\Component\Assertion\Handle;

$databases = [];
$settings['hash_salt'] = 'HASH_SALT';

$settings['update_free_access'] = FALSE;
// @codingStandardsIgnoreStart
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
// @codingStandardsIgnoreEnd
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

// Setup path to basic config files. The rest will be added based on env.
$settings['config_sync_directory'] = '../config/general';
$settings['file_temp_path'] = 'sites/default/files/private/temp';

$settings['trusted_host_patterns'] = array(
  '^(www\.)?(((feature-\d*|task-\d*|dev|stage|preprod)\.)?balzhinimaev)\.(loc|com|mob|(ci2\.)?qajedi.ru)',
);
$settings['file_private_path'] = 'sites/default/files/private';

// Include env settings file for certain site
// (because DB could be different and etc).
// Structure of settings folder — folder per project
// and per site settings file inside.
$env_settings_path = '/var/www/site-php/balzhinimaev';
// If PROJECT_ENV_MULTI is TRUE,
// it means we have several envs on the same machine.
// I.e. same real instance for dev and stage. Or stage and prod.
// It means we need to include file per env type.
if (!empty($_ENV['PROJECT_ENV_MULTI']) && !empty($_ENV['PROJECT_ENV_TYPE'])) {
  $env_settings_path .= '-' . $_ENV['PROJECT_ENV_TYPE'];
}

// @codingStandardsIgnoreStart
if (is_readable($env_settings_path) && isset($site_settings_filename)) {
  require $env_settings_path . '/' . $site_settings_filename;
}
// @codingStandardsIgnoreEnd

define('PROJECT_ENV_PROD', 'prod');
define('PROJECT_ENV_STAGE', 'stage');
define('PROJECT_ENV_DEV', 'dev');
define('PROJECT_ENV_LOCAL', 'local');

/**
 * Return a name of environment we are deployed at.
 *
 * @return string
 *   Environment identification string.
 */
function project_get_environment() {
  static $project_env = NULL;

  if (is_null($project_env)) {
    // It's example of detection but could be used as is if hosting the same.
    if (!empty($_ENV['AH_SITE_ENVIRONMENT'])) {
      $acquia_env_mapping = array(
        'test' => PROJECT_ENV_STAGE,
      );

      $project_env = isset($acquia_env_mapping[$_ENV['AH_SITE_ENVIRONMENT']])
        ? $acquia_env_mapping[$_ENV['AH_SITE_ENVIRONMENT']]
        : $_ENV['AH_SITE_ENVIRONMENT'];
    }
    // Self-hosted projects.
    elseif (getenv('PROJECT_ENV_TYPE')) {
      $project_env = getenv('PROJECT_ENV_TYPE');
    }
    // Default env is local.
    else {
      $project_env = PROJECT_ENV_LOCAL;
    }
  }

  return $project_env;
}

// For separate balancers we need to forward is original request was an HTTPS.
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
  $_SERVER['HTTPS'] = 'on';
}

$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'] = [
  '10.0.0.0/8',
  '172.16.0.0/12',
  '192.168.0.0/16',
];

// Environment-specific settings overrides.
$current_env = project_get_environment();
$config['config_split.config_split.' . $current_env]['status'] = TRUE;
switch ($current_env) {
  case PROJECT_ENV_PROD:
    // @TODO: Add prod specific settings.
    break;

  case PROJECT_ENV_STAGE:
    // @TODO: Add stage specific settings.
    break;

  case PROJECT_ENV_DEV:
    assert_options(ASSERT_ACTIVE, TRUE);
    Handle::register();
    break;

  case PROJECT_ENV_LOCAL:
    assert_options(ASSERT_ACTIVE, TRUE);
    Handle::register();

    $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/local.services.yml';
    // Allow any hosts for local usage.
    $settings['trusted_host_patterns'] = [];

    // Disable caching during development.
    $settings['cache']['bins']['render'] = 'cache.backend.null';
    $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
    $settings['cache']['bins']['page'] = 'cache.backend.null';
    break;
}
