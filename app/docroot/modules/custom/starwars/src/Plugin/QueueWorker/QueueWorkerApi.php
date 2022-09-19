<?php

namespace Drupal\starwars\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use \Drupal\node\Entity\Node;

/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "cron_node",
 *   title = @Translation("My queue worker"),
 *   cron = {"time" = 10}
 * )
 */
class QueueWorkerApi extends QueueWorkerBase {
  public function processItem($data) {
    $url = $data['url'];
    $type = $data['type'];
    $service = \Drupal::service('starwars.sources');
    $result = $service->getApiByUrl($url);
    $queue_factory = \Drupal::service('queue');
    if(isset($result->next)){
      $queuePage = $queue_factory->get('cron_node');
      $dataItem = [
        'url' => $result->next,
        'type' => $type,
      ];
    $queuePage->createItem($dataItem);
    }
    foreach ($result->results as $item){
      $edit = mktime($item->edited);
      if($edit <= $data['lastUpdate']) {
        $item->type = $type;
        $queue = $queue_factory->get('cron_node_publisher');
        $queue->createItem($item);
      }
    }



  }
}
