<?php

namespace Drupal\starwars\Ajax;
use Drupal\Core\Ajax\CommandInterface;

class starwarsAjaxCommand implements CommandInterface {

  protected $message;
  protected $url;
  # Constructs
  public function __construct() {
    $this->message = 'Спасибо за заполнение!';
    $this->url = '/';
    $blockManager = \Drupal::service('plugin.manager.block');
    $contextRepository = \Drupal::service('starwars_dialog');
    $definitions = $blockManager->getDefinitionsForContexts($contextRepository->getAvailableContexts());
    $this->content = $definitions;

  }

  public function render() {
    return array(
      'command' => 'starwarsAjaxCommand',
      'message' => $this->message,
      'url' => $this->url,
      'content' => $this->content,
    );
  }
}
