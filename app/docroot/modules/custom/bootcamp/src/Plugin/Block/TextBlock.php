<?php

/**
 * @file
 * Contains \Drupal\Bootcamp\Plugin\Block\TextBlock.
 */

namespace Drupal\Bootcamp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Block(
 *   id = "textblock",
 *   admin_label = @Translation("TextBlock"),
 * )
 */

class TextBlock extends BlockBase
{

    public function blockForm($form, FormStateInterface $form_state)
    {
        $form = parent::blockForm($form, $form_state);
        $config = $this->getConfiguration();

        $form['text'] = [
            '#type' => 'text_format',
            '#allowed_formats' => ['full_html' => 'full_html'],
            '#title' => $this->t('Content'),
            '#default_value' => isset($config['text']['value']) ? $config['text']['value'] : '',
        ];


        return $form;
    }

    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->configuration['text'] = $form_state->getValue('text');
    }

    /**
     * {@inheritdoc}
     */


    public function build()
    {
        $config = $this->getConfiguration();
        $text = '';
        $text = $config['text']['value'];
        $block = [
            '#type' => 'markup',
            '#markup' => $text,
        ];
        return $block;
    }
}
