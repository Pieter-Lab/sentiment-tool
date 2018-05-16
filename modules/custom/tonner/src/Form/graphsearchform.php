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
    $form['countries'] = [
      '#type' => 'select',
      '#title' => $this->t('Countries'),
      '#description' => $this->t('Select either Country'),
      '#value' => ['gb'=>'United Kingdom','us'=>'United States'],
      '#weight' => '1',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#weight' => '2',
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
