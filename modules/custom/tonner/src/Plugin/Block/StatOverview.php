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
    $display_build['countries']['list'] = [];
    //Loop Countries
    foreach($terms as $country) {
      //Load country object
      $countryTerm = \Drupal\taxonomy\Entity\Term::load($country->Id());
      //Get ISO to get country Information
      $countryInfo = current(json_decode(file_get_contents('https://restcountries.eu/rest/v2/name/'.$countryTerm->getName().'?fullText=true')));
      //Add to totals
      $display_build['countries']['total_articles'] = $display_build['countries']['total_articles'] + $countryTerm->field_total_number_of_articles->value;
      //get the sentiment Totals
      $sentiment = $countryTerm->field_sentiment_totals->getValue();
      if($sentiment && !empty($sentiment)){
        //holder
        $sent_max_name = null;
        $sent_max_count = 0;
        //get precentage
        $totaler = 0;
        //loop
        foreach($sentiment as $fcV){
          //Load Field Collection
          $fc = \Drupal\field_collection\Entity\FieldCollectionItem::load($fcV['value']);
          //Get values
          $sentiment_count = (int) $fc->field_total->value;
          //Test
          if($sent_max_count < $sentiment_count){
            $sent_max_count = $sentiment_count;
            $sent_max_name = $fc->field_sentiment->entity->getName();
          }
          //add
          $totaler = $totaler + $sentiment_count;
        }
        if($sent_max_count > 0){
          //Add to List
          $display_build['countries']['list'][$sent_max_count] = [
            'sentiment' => $sent_max_name,
            'country' => $countryTerm->getName(),
            'sentiment_percentage' => $this->percentageOf($sent_max_count,$totaler)
          ];
          if(isset($countryInfo) && !empty($countryInfo)){
            if(isset($countryInfo->flag) && !empty($countryInfo->flag)){
              $display_build['countries']['list'][$sent_max_count]['flag'] = $countryInfo->flag;
            }
          }
        }
      }
    }
    //Key sort
    krsort($display_build['countries']['list']);
    $this->printer($display_build);
    //Setup the render array
    $build = [
      '#theme' => 'tonneroverview',
      '#data' => $display_build
    ];
    //return render array
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
