<?php

namespace Drupal\starwars\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use \Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "cron_node",
 *   title = @Translation("My queue worker"),
 *   cron = {"time" = 10}
 * )
 */
class QueueWorkerApi extends QueueWorkerBase implements ContainerFactoryPluginInterface{

  protected $serviceQueue;
  protected $serviceApi;
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->serviceQueue = $container->get('queue');
    $instance->serviceApi = $container->get('starwars.sources');
    return $instance;
  }

  public function processItem($data) {
    $url = $data['url'];
    $type = $data['type'];
    $service = $this->serviceApi;
    $result = $service->getApiByUrl($url);
    $queue_factory = $this->serviceQueue;
    if(isset($result->next)){
      $queuePage = $queue_factory->get('cron_node');
      $dataItem = [
        'url' => $result->next,
        'type' => $type,
      ];
    $queuePage->createItem($dataItem);
    }
    foreach ($result->results as $item){//исправить ошибку с условиями
      $edit = strtotime($item->edited);
      if(isset($data['drushMode']) and $edit <= $data['lastUpdate']){
          $item->type = $type;
          $queue = $queue_factory->get('cron_node_publisher');
          $queue->createItem($item);

      }else if(!isset($data['drushMode']) and $edit >= $data['lastUpdate']) {
        $item->type = $type;
        $queue = $queue_factory->get('cron_node_publisher');
        $queue->createItem($item);
      }
    }



  }
}
