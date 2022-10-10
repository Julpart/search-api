<?php
namespace Drupal\starwars\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use \Drupal\node\NodeInterface;
use Drupal\Core\Session\AccountInterface;


class starwarsTaskController extends ControllerBase {

  public function exampleTabContent(NodeInterface $node) {
    $swapi_id = current(current($node->get('field_swapi_id')->getValue()));
    $type = $node->bundle();
    $request = \Drupal::httpClient()->request('GET',"https://swapi.dev/api/$type/$swapi_id");
    $requestContent = $request->getBody()->getContents();
    $requestObj = json_decode($requestContent);
    ob_start();
    print_r($requestObj);
    $result = ob_get_clean();
    return ['#markup' => $result];
  }

  public function exampleTabAccess(NodeInterface $node,AccountInterface $account) {
    $check = $node->access('update', $account);
    if ($check) {
      return AccessResult::allowed();
    }else{
      return AccessResult::forbidden();
    }
    return AccessResult::allowedIf($node->bundle() === 'people');
  }

}
