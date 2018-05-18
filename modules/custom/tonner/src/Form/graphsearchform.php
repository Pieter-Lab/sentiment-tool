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
//    $this->printer($_SESSION['tonner']['sel_country_tid']);
//    $this->printer($_SESSION['tonner']['sel_industry_tid']);
//    $this->printer($_SESSION['tonner']['sel_sentiment_tid']);
    //Container
    $options = ['all'=>'All'];
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
      '#cache' => array('max-age' => 0)
    ];
    if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
      if(!empty($_SESSION['tonner']['sel_country_tid'])){
        $form['countries']['#value'] = $_SESSION['tonner']['sel_country_tid'];
//        $form['countries']['#value'] = [$_SESSION['tonner']['sel_country_tid']=>$options[$_SESSION['tonner']['sel_country_tid']]];
//        $form['countries']['#value'] = $options[$_SESSION['tonner']['sel_country_tid']];

//        $form['countries']['#default_value'] = [$_SESSION['tonner']['sel_country_tid']=>$options[$_SESSION['tonner']['sel_country_tid']]];
      }
    }
    //Industries-----------------------------------------------------------------
    //Container
    $options = ['all'=>'All'];
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
    if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
      if(!empty($_SESSION['tonner']['sel_industry_tid'])){
        $form['industries']['#default_value'] = [$_SESSION['tonner']['sel_industry_tid']=>$options[$_SESSION['tonner']['sel_industry_tid']]];
      }
    }
    //Sentiment-----------------------------------------------------------------
    //Container
    $options = ['all'=>'All'];
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
      '#multiple' => TRUE,
      '#title' => $this->t('Sentiment'),
      '#description' => $this->t('Select a Sentiment'),
      '#options' => $options,
      '#weight' => '1',
    ];
    if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
      if(!empty($_SESSION['tonner']['sel_sentiment_tid']) && $_SESSION['tonner']['sel_sentiment_tid']==='All'){
        $form['sentiment']['#default_value'] = $_SESSION['tonner']['sel_sentiment_tid'];
      }
    }
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
    // Collect
    $values = $form_state->getValues();
    $_SESSION['tonner'] = [];
    $_SESSION['tonner']['sel_country_tid'] = $values['countries'];
    $_SESSION['tonner']['sel_industry_tid'] = $values['industries'];
    $_SESSION['tonner']['sel_sentiment_tid'] = $values['sentiment'];
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
