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
    //Holder
    $display_build = [];
    //Hold the Top Sentiment for the Country
    $display_build['countries'] = [];
    $display_build['countries']['total_articles'] = 0;
    $display_build['countries']['top'] = [];
    $display_build['countries']['top']['country_name'] = null;
    $display_build['countries']['top']['sentiment'] = null;
    $display_build['countries']['top']['total'] = 0;
    $display_build['countries']['top']['percentage'] = 0;
    //Loop Countries
    foreach($terms as $country) {
      //Load country object
      $countryTerm = \Drupal\taxonomy\Entity\Term::load($country->Id());
      //Add to total count
      $display_build['countries']['total_articles'] = $display_build['countries']['total_articles'] + $countryTerm->field_total_number_of_articles->value;
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
          if($display_build['countries']['top']['total'] < $sentiment_count){
            //Set
            $display_build['countries']['top']['country_name'] = $countryTerm->getName();
            $display_build['countries']['top']['sentiment'] = $fc->field_sentiment->entity->getName();
            $display_build['countries']['top']['total'] = $sentiment_count;
          }
        }
      }
    }
    //Get percentage for Top Country
    $display_build['countries']['top']['percentage'] = $this->percentageOf($display_build['countries']['top']['total'],$display_build['countries']['total_articles']);
    
    $this->printer($display_build);

    $build['stats_overview']['#markup'] = 'Implement StatOverview.';

    return $build;
  }


  /**
   * Calculate percetage between the numbers
   */

  function percentageOf( $number, $everything, $decimals = 2 ){
    return round( $number / $everything * 100, $decimals );
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
