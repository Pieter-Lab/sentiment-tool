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
    //Countries-----------------------------------------------------------------
    //Container
    $options = [];
    //Get the List of Countries
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "country");
    $tids = $query->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
    //Loop Countries
    foreach($terms as $country) {
      $countryTerm = \Drupal\taxonomy\Entity\Term::load($country->Id());
      $options[$country->Id()] = $countryTerm->getName();
    }
    //Add to select list
    $form['countries'] = [
      '#type' => 'select',
      '#title' => $this->t('Countries'),
      '#description' => $this->t('Select a Country'),
      '#options' => $options,
      '#weight' => '1',
    ];
    //Industries-----------------------------------------------------------------
    //Container
    $options = [];
    //Get the List of Terms
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "industry");
    $tids = $query->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
    //Loop Terms
    foreach($terms as $t) {
      $Term = \Drupal\taxonomy\Entity\Term::load($t->Id());
      $options[$t->Id()] = $Term->getName();
    }
    //Add to select list
    $form['industries'] = [
      '#type' => 'select',
      '#title' => $this->t('Industries'),
      '#description' => $this->t('Select an Industry'),
      '#options' => $options,
      '#weight' => '1',
    ];
    //Sentiment-----------------------------------------------------------------
    //Container
    $options = [];
    //Get the List of Terms
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "tones");
    $tids = $query->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
    //Loop Terms
    foreach($terms as $t) {
      $Term = \Drupal\taxonomy\Entity\Term::load($t->Id());
      $options[$t->Id()] = $Term->getName();
    }
    //Add to select list
    $form['sentiment'] = [
      '#type' => 'select',
      '#title' => $this->t('Sentiment'),
      '#description' => $this->t('Select a Sentiment'),
      '#options' => $options,
      '#weight' => '1',
    ];
    //--------------------------------------------------------------------------
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
    $this->printer($form_state->getValues());
    exit("Peter Testing!");
//    foreach ($form_state->getValues() as $key => $value) {
//      drupal_set_message($key . ': ' . $value);
//    }

  }
  /**
   * Prints out variables
   */
  public function printer($val)
  {
    if (is_array($val) || is_object($val)) {
      echo '<pre>';
      print_r($val);
      echo '</pre>';
    } else {
      echo '<br />';
      var_dump($val);
      echo '<br />';
    }
  }


}
