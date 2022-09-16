<?php

/**
 * @file
 * Custom single site-specific settings.
 *
 * @see prj-settings.inc for default settings.
 */

// It's not in the common settings file because for multi-site we need
// to specify different settings, i.e. DB connection.
$site_settings_filename = 'balzhinimaev-settings.inc';

require_once DRUPAL_ROOT . '/sites/prj-settings.inc';
