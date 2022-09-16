<?php

namespace Drupal\starwars\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use \Drupal\node\Entity\Node;

/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "cron_node_publisher",
 *   title = @Translation("My queue worker"),
 *   cron = {"time" = 10}
 * )
 */
class QueueWorkerStar extends QueueWorkerBase {//exception

  /**
   * {@inheritdoc}
   */
  protected function getId($url){
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
    foreach ($data as $key =>$item) {
      if ($key != 'url' and $key != 'type') {
        if (is_array($item) or $key == 'homeworld') {// проверка по значению приводить к массиву
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
    if($data->type == 'films'){
      $node->set('title',$data->title);
    }else{
      $node->set('title',$data->name);
    }
    foreach ($data as $key =>$item){
      $key = strtolower($key);
      if($key != 'url' and $key != 'type' ) {
        if(is_array($item) or $key == 'homeworld'){
          $node->set("field_$key",  $this->makeNode($item));
        }else {
          $node->set("field_$key", $item);
        }
      }
    }
    $node->save();
  }
  protected function createNode($data){
    $query = \Drupal::entityTypeManager()->getStorage('node');//инъекция зависимости
    $swapi_id = $this->getId($data->url);
    $nods = $query->loadByProperties(['type' => $data->type, 'field_swapi_id' => $swapi_id]);
    if (empty($nods)){
      $arr = $this->createArr($data,$swapi_id);
      $node = Node::create($arr)->save();
    }else{
      $this->updateNode(array_pop($nods)->id(),$data);
    }
  }
  protected function makeNode($item){
    $nidsArr = [];
    $itemArr = [];
    if(is_array($item)){
      preg_match('#api\\/[a-z]+\\/#',$item[0],$found);
      $itemArr = array_merge($itemArr,$item);
    }elseif(isset($item)){
      preg_match('#api\\/[a-z]+\\/#',$item,$found);//убрать parseUrl
      $itemArr[] = $item;
    }else{
      return $nidsArr;
    }
    $type = array_pop($found);
    $type= str_replace('api', "", $type);
    $type= trim( $type , "/" );
    $query = \Drupal::entityTypeManager()->getStorage('node');
    foreach ($itemArr as $item) {
      preg_match('#\\/[0-9]+\\/#',$item,$foundid);
      $id = trim(array_pop($foundid), "/" );
      $nods = $query->loadByProperties(['type' => $type, 'field_swapi_id' => $id]);
      if(empty($nods)){
        $node = Node::create([
          'type' => $type,
          'title' => $id,
          'field_swapi_id' => $id,//unpublished
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
