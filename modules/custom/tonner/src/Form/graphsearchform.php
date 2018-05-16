<?php

namespace Drupal\tonner\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class graphsearchform.
 */
class graphsearchform extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'graphsearchform';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['country'] = [
      '#type' => 'Country',
      '#title' => $this->t('Country'),
      '#description' => $this->t('Select Country'),
      '#weight' => '0',
    ];
    $form['countries'] = [
      '#type' => 'autocomplete_deluxe',
      '#title' => $this->t('Countries'),
      '#description' => $this->t('Select either Country'),
      '#weight' => '1',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
