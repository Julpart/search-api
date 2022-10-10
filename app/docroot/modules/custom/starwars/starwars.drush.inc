<?php

/**
 * Implements hook_drush_command().
 */

function starwars_drush_command(){
  $items = array();
  $items['starwars'] = array(
    'description' => 'Publish content',
    'callback' => 'drush_starwars_content',
    'aliases' => array('star'),
    'options' => array(
      'date' => 'Nodes of this type will be published',
    ),
  );
  return $items;
}

function drush_starwars_content(){
  $date = drush_get_option('date', 1);
  drush_print($date);
}
