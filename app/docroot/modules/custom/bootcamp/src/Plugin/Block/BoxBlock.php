<?php

/**
 * @file
 * Contains \Drupal\Bootcamp\Plugin\Block\BoxBlock.
 */

namespace Drupal\Bootcamp\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Component\Plugin\PluginAwareInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormBase;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use PhpParser\Node\Stmt\Global_;

/**
 * @block(
 *   id = "boxblock",
 *   admin_label = @Translation("BoxBlock"),
 * )
 */

class BoxBlock extends BlockBase implements BlockPluginInterface
{

    /**
     * {@inheritdoc}
     */


    public function blockForm($form, FormStateInterface $form_state)
    {
        $form = parent::blockForm($form, $form_state);
        $config = $this->getConfiguration();
        $form['text_title'] = [
            '#type' => 'text_format',
            '#allowed_formats' => ['plain_text' => 'plain_text'],
            '#title' => $this->t('Content'),
            '#default_value' => isset($config['text_title']['value']) ? $config['text_title']['value'] : 'Card Title',
        ];
        $form['background_image'] = [
            '#type' => 'media_library',
            '#allowed_bundles' => ['image'],
            '#title' => t('Background image'),
            '#default_value' => !empty($config['background_image'])
                ? $config['background_image']
                : '',
            '#weight' => 999,
        ];
        $form['small_image'] = [
            '#type' => 'media_library',
            '#allowed_bundles' => ['image'],
            '#title' => t('small image'),
            '#default_value' => !empty($config['small_image'])
                ? $config['small_image']
                : '',
            '#weight' => 999,
        ];

        $form['text'] = [
            '#type' => 'text_format',
            '#allowed_formats' => ['plain_text' => 'plain_text'],
            '#title' => $this->t('Content'),
            '#default_value' => isset($config['text']['value']) ? $config['text']['value'] : 'This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.',
        ];
        $form['last_update'] = [
            '#type' => 'text_format',
            '#allowed_formats' => ['plain_text' => 'plain_text'],
            '#title' => $this->t('Last update'),
            '#default_value' => isset($config['last_update']['value']) ? $config['last_update']['value'] : 'Last updated 3 mins ago',
        ];




        return $form;
    }
    /**
     * {@inheritdoc}
     */

    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->configuration['text'] = $form_state->getValue('text');
        $this->configuration['last_update'] = $form_state->getValue('last_update');
        $this->configuration['text_title'] = $form_state->getValue('text_title');
        $this->setConfigurationValue('background_image', $form_state->getValue('background_image'));
        $this->setConfigurationValue('small_image', $form_state->getValue('small_image'));
    }

    /**
     * {@inheritdoc}
     */


    public function build()
    {
        $config = $this->getConfiguration();
        $text = '';
        $text = $config['text']['value'];
        $text_title = '';
        $text_title = $config['text_title']['value'];
        $last = '';
        $last = $config['last_update']['value'];
        $image = $config['small_image'];
        $background_image = $config['background_image'];
        $test_image = Media::load($image);
        $test_background_image = Media::load($background_image);
        $fid = ImageStyle::load('thumbnail')->buildUrl($test_image->field_media_image->entity->getFileUri());
        $fid2 = ImageStyle::load('large')->buildUrl($test_background_image->field_media_image->entity->getFileUri());
        return [
            '#theme' => 'my_template',
            '#image' => $fid,
            '#background_image' => $fid2,
            '#text' => $text,
            '#text_title' => $text_title,
            '#last' => $last,
        ];
    }

    public function blockAccess(AccountInterface $account)
    {
        return \Drupal\Core\Access\AccessResult::allowed();
    }
}
