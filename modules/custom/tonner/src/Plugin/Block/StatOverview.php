<?php

namespace Drupal\tonner\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'StatOverview' block.
 *
 * @Block(
 *  id = "stats_overview",
 *  admin_label = @Translation("Stats Overview"),
 * )
 */
class StatOverview extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    //Open build holder
    $build = [];
    //Get All the Terms in the Countries Vocab
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "country");
    $tids = $query->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
    //Loop Countries
    foreach($terms as $country) {
      //Load country object
      $countryTerm = \Drupal\taxonomy\Entity\Term::load($country->Id());
      //get the sentiment Totals
      $sentiment = $countryTerm->field_sentiment_totals->getValue();
      if($sentiment && !empty($sentiment)){
        //loop
        foreach($sentiment as $fcV){
          //Load Field Collection
          $fc = \Drupal\field_collection\Entity\FieldCollectionItem::load($fcV['value']);
          $this->printer($fc->field_sentiment->entity->getName());
          $this->printer($fc->field_total->value);
        }
      }
    }
    $build['stats_overview']['#markup'] = 'Implement StatOverview.';

    return $build;
  }

  /**
   * Prints out variables
   */
  public function printer($val){
    if(is_array($val) || is_object($val)){
      echo '<pre>';
      print_r($val);
      echo '</pre>';
    }else{
      echo '<br />';
      var_dump($val);
      echo '<br />';
    }
  }

}
