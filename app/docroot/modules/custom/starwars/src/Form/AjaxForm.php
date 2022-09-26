<?php
namespace Drupal\starwars\Form;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AjaxForm extends FormBase {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('email'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::validateEmailAjax',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Verifying email..'),
        ),
      ],
      '#suffix' => '<div class="email-validation-message"></div>'
    ];
    $form['site'] = [
      '#type' => 'textfield',
      '#title' => $this->t('site'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::validateSiteAjax',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Verifying url..'),
        ),
      ],
      '#suffix' => '<div class="url-validation-message""></div>'
    ];
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
    );
    return $form;
  }

  public function getFormId() {
    return 'starwars_ajax';
  }
  public function validateSiteAjax(array &$form, FormStateInterface $form_state){
    $url = $form_state->getValue('site');
    $response = new AjaxResponse();
    if (filter_var($url, FILTER_VALIDATE_URL)) {
      $url = parse_url($url);
      $tru1 = preg_match('#.ru#',$url['host']);
      $tru2 = preg_match('#.рф#',$url['host']);
      if($tru1 or $tru2) {
        $url = 1;
      }else{
        $response->addCommand(new HtmlCommand('.email-validation-message', 'Поддерживается тольк ru и рф домены'));
        return $response;
      }
    }else{
      $response->addCommand(new HtmlCommand('.email-validation-message', 'Некорректный адрес электронной почты'));
      return $response;
    }
  }
  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $response = new AjaxResponse();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $response->addCommand(new HtmlCommand('.email-validation-message', 'Некорректный адрес электронной почты'));
    }
    else {
      $response->addCommand(new HtmlCommand('.email-validation-message', ''));
    }
    return $response;
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $date = $form_state->getValue('name');
  }
}
