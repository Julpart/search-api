<?php

namespace Drupal\starwars\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use \Drupal\node\Entity\Node;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "cron_node_publisher",
 *   title = @Translation("My queue worker"),
 *   cron = {"time" = 10}
 * )
 */
class QueueWorkerStar extends QueueWorkerBase implements ContainerFactoryPluginInterface{//exception

  /**
   * {@inheritdoc}
   */
  protected $entityManager;
  protected $serviceApi;
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityManager = $container->get('entity_type.manager');
    $instance->serviceApi = $container->get('starwars.sources');
    return $instance;
  }

  protected function getId($url){
    $result = parse_url($url);
    preg_match('#\\/[0-9]+\\/#',$url,$found);
    $result = trim( array_pop($found), "/" );
    return $result;
  }
  protected function createArr($data,$swapi_id){
    if($data->type == 'films'){
      $name = $data->title;
    }else{
      $name = $data->name;
    }
    $result = [
      'type' => $data->type,
      'title' => "$name",
      'field_swapi_id' => $swapi_id,
    ];
    foreach ($data as $key =>&$item) {
      if(filter_var($item, FILTER_VALIDATE_URL)){
          $item = array($item);
      }
      if ($key != 'url' and $key != 'type') {
        if (is_array($item)) {
          $result["field_$key"] = $this->makeNode($item);
        }
        else {
          $result["field_$key"] = $item;
        }
      }
    }
    return $result;
  }
  protected function updateNode($id,$data){
    $node = Node::load($id);

    $title = ($data->type == 'films' ? $data->title : $data->name);
    $node->set('title', $title);

    foreach ($data as $key =>$item) {
      $key = strtolower($key);
      if (filter_var($item, FILTER_VALIDATE_URL)) {
        $item = [$item];
      }
      if ($key != 'url' and $key != 'type' ) {//!in_array
        if(is_array($item)){
          $node->set("field_$key",  $this->makeNode($item));
        }else {
          $node->set("field_$key", $item);
        }
      }
    }
    $node->set('status',1);
    $node->save();
  }
  protected function createNode($data){
    $query = $this->entityManager->getStorage('node');
    $swapi_id = $this->getId($data->url);
    $nods = $query->loadByProperties(['type' => $data->type, 'field_swapi_id' => $swapi_id]);
    if (empty($nods)){
      $arr = $this->createArr($data,$swapi_id);
      $node = Node::create($arr)->save();
    }else{
      $this->updateNode(array_pop($nods)->id(),$data);//current
    }
  }
  protected function makeNode($arr){
    $nidsArr = [];
    if(!isset($arr[0])){
      return $nidsArr = [];
    }
    $type = $this->serviceApi->getType($arr[0]);
    $query = $this->entityManager->getStorage('node');
    foreach ($arr as $value) {
      $id = $this->getId($value);
      $nods = $query->loadByProperties(['type' => $type, 'field_swapi_id' => $id]);
      if(empty($nods)){
        $node = Node::create([
          'type' => $type,
          'title' => $id,
          'field_swapi_id' => $id,
          'status' => 0,
        ])->save();
        $nidsArr = $node;
      }else{
        $nidsArr[] = array_pop($nods)->id();
      }
      }
    return $nidsArr;
  }



  public function processItem($data) {
      $this->createNode($data);
  }

}
