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
    //Hold the Top Sentiment for the Country
    $sentiment_max_country = null;
    $sentiment_max_sentiment = null;
    $sentiment_max_total = 0;
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
          //Get values
          $sentiment_count = (int) $fc->field_total->value;
          //Test
          if($sentiment_max_total < $sentiment_count){
            //Set
            $sentiment_max_country = $countryTerm->getName();
            $sentiment_max_sentiment = $fc->field_sentiment->entity->getName();
            $sentiment_max_total = $sentiment_count;
          }
        }
      }
    }
    $this->printer($sentiment_max_country);
    $this->printer($sentiment_max_sentiment);
    $this->printer($sentiment_max_total);

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
