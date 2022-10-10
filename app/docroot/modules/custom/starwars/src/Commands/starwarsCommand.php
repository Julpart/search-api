<?php
namespace Drupal\starwars\Commands;
use Drush\Commands\DrushCommands;


class starwarsCommand extends DrushCommands {

  /**
   * @var Drupal\starwars\Services\APIService
   */
  protected $starService;
  protected $queueService;

  public function __construct($starService,$queueService) {
    parent::__construct();
    $this->starService = $starService;
    $this->queueService = $queueService;
  }

/**
 * Custom drush command to import swapi data.
 *
 * @command custom:starwars
 * @aliases star
 * @param string $date
 */
  public function starwarsCommand($date=false){
    if(!$date) {
   //   $this->importSwapi();
      $this->importSwapiBatch();
      return 1;
    }

    $date = strtotime($date);
    if(!$date) {
      $this->output()->writeln('data entry error');
      return 0;
    }
   // $this->importSwapi($date);
    $this->importSwapiBatch($date);


  }

  protected function importSwapi($date=0){
    $result =$this->starService;
    $queue_factory = $this->queueService;
    $queue = $queue_factory->get('cron_node');
    $apiUrl = $result->getUrl();
    $lastUpdateTime = $date;
    foreach ($apiUrl as $key => $item) {
      $data = [
        'type' => $key,
        'url' => $item,
        'lastUpdate' => $lastUpdateTime,
      ];
      $queue->createItem($data);
      $this->output()->writeln('Создана очередь типа ' . $key);
    }
    $this->output()->writeln('Все очереди созданы.');
  }


  protected function  importSwapiBatch($date=0){
    $console = true;
    $operations = [
      ['batch_process_items', [$date,$console]],
    ];
    $batch = [
      'title' => 'Batch process',
      'operations' => $operations,
      'finished' => 'batch_starwars_finished',
    ];
    batch_set($batch);
    $this->output()->writeln('Запущен batch process');
    drush_backend_batch_process();
    $this->output()->writeln('Batch завершил работу');
  }
}
