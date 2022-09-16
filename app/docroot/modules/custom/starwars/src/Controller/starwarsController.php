<?php
namespace Drupal\starwars\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

class starwarsController extends ControllerBase {
  public function content(){

    $result = \Drupal::service('starwars.delete');
    $result->do();
    $build = [
     '#markup' => $this->t('1'),
    ];
   // return var_dump($resultStr);
    return $build;
  }
}
