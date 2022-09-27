<?php
namespace Drupal\starwars\Form;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
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
      '#suffix' => '<div class="url-validation-message"></div>'
    ];
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
      '#ajax' => [
      'callback' => '::ajaxSubmitCallback',
      'event' => 'click',
      'progress' => [
        'type' => 'throbber',
      ],
        ],
    );
    $form['submit-message'] = [
      '#markup' => "<div class='submit-message'></div>",
      '#weight' => -100,
    ];
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
      $condition1 = preg_match('#[.]ru#',$url['host']);
      $condition2 = preg_match('#//[.]рф#',$url['host']);
      if ($condition1 or $condition2) {
        $response->addCommand(new CssCommand('#edit-site', ['border' => '2px solid black']));
        $response->addCommand(new HtmlCommand('.url-validation-message', ''));
        return $response;
      } else{
        $response->addCommand(new CssCommand('#edit-site', ['border' => '2px solid red']));
        $response->addCommand(new CssCommand('.url-validation-message', ['color' => 'red']));
        $response->addCommand(new HtmlCommand('.url-validation-message', 'Поддерживается тольк ru и рф домены'));
        return $response;
      }
    } else {
      $response->addCommand(new CssCommand('#edit-site', ['border' => '2px solid red']));
      $response->addCommand(new CssCommand('.url-validation-message', ['color' => 'red']));
      $response->addCommand(new HtmlCommand('.url-validation-message', 'Некорректный адрес'));
      return $response;
    }
  }
  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $response = new AjaxResponse();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $response->addCommand(new CssCommand('#edit-email', ['border' => '2px solid red']));
      $response->addCommand(new CssCommand('.email-validation-message', ['color' => 'red']));
      $response->addCommand(new HtmlCommand('.email-validation-message', 'Некорректный адрес электронной почты'));
    }
    else {
      $response->addCommand(new HtmlCommand('.email-validation-message', ''));
      $response->addCommand(new CssCommand('#edit-email', ['border' => '2px solid black']));
    }
    return $response;
  }
public function validateForm(array &$form, FormStateInterface $form_state)
{
  $email = $form_state->getValue('email');
  $url = $form_state->getValue('site');
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $form_state->setErrorByName('email');
  }
  if (filter_var($url, FILTER_VALIDATE_URL)) {
    $url = parse_url($url);
    $condition1 = preg_match('#[.]ru#',$url['host']);
    $condition2 = preg_match('#[.]рф#',$url['host']);
    if (!($condition1 or $condition2)) {
      $form_state->setErrorByName('site');
    }
  } else {
    $form_state->setErrorByName('site');
  }
}

  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state)
  {
    $response = new AjaxResponse();
    $email = $form_state->getValue('email');
    $url = $form_state->getValue('site');
    if(empty($form_state->getErrors())){
      $response->addCommand(new HtmlCommand('.submit-message', 'Форма отправлена'));
    }else{
      $response->addCommand(new HtmlCommand('.submit-message', 'Ошибка валидации'));
    }
    return $response;
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if(empty($form_state->getErrors())){
      $name = $form_state->getValue('name');
      $email = $form_state->getValue('email');
      $url = $form_state->getValue('site');
      $url = parse_url($url);
      $result_url =str_replace('www.','',$url['host']);
      $result_url .= '/';
      $path =trim($url['path'], "/" );
      $result_url .= $path;

      $query = \Drupal::database()->insert('starwars.starwars');
      $query->fields(array(
        'name',
        'email',
        'url',
      ));
      $query->values(array(
        $name,
        $email,
        $url,
      ));
      $query->execute();
    }

  }
}
