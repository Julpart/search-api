<?php

/**
 * @file
 * Contains \Drupal\Bootcamp\Plugin\Block\TextBlock.
 */

namespace Drupal\starwars\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Block(
 *   id = "starwars_dialog",
 *   admin_label = @Translation("StarWars"),
 * )
 */

class ModuleDialogBlock extends BlockBase
{

  public function build()
  {
    $config = $this->getConfiguration();
    $text = 'Hello from block';
    $block = [
      '#type' => 'markup',
      '#markup' => $text,
    ];
    return $block;
  }
}
