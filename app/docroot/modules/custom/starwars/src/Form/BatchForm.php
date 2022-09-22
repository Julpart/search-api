<?php

namespace Drupal\starwars\Form;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BatchForm extends FormBase {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Date'),
    ];
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Import',
    );
    return $form;
  }

  public function getFormId() {
    return 'starwars_batch';
  }
  public function submitForm(array &$form, FormStateInterface $form_state){
    $date = $form_state->getValue('date');
    if(!isset($date)){
      $date = 0;
    }
    $console = false;
    $operations = [
      ['batch_process_items', [$date,$console]],
    ];
    $batch = [
      'title' => $this->t('Batch process'),
      'operations' => $operations,
      'finished' => 'batch_starwars_finished',
    ];
    batch_set($batch);
  }
}
