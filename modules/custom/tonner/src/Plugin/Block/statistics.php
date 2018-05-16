<?php

namespace Drupal\tonner\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'statistics' block.
 *
 * @Block(
 *  id = "statistics",
 *  admin_label = @Translation("Statistics"),
 * )
 */
class statistics extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        //Get all the Available taxonomy Tones
        $tones = $this->getTones();
        //Total COunt
        $headlineTotal = 0;
        $currentHeadlineTotal = 0;
        //Set up historical capture
        $histCollect = [];
        //Loop through Tones to get the ALL COUNT!
        foreach($tones as $key => $tone){
            //entity query using storage manager
            $result = \Drupal::entityQuery('node');
            $result->condition('type', 'news_headline');
            $result->condition('field_tone',$tone['tid'],'=');
            $result->condition('field_publishedat',strtotime('-2 day'),'>=');
            //------------------------------------------------------------------
            //Country
//            if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
//              if(!empty($_SESSION['tonner']['sel_country_tid'])  && $_SESSION['tonner']['sel_country_tid']!=='All'){
//                $result->condition('field_country',$_SESSION['tonner']['sel_country_tid'],'=');
//              }
//            }
//            //------------------------------------------------------------------
//            //Industry
//            if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
//              if(!empty($_SESSION['tonner']['sel_industry_tid'])  && $_SESSION['tonner']['sel_industry_tid']!=='All'){
//                $result->condition('field_article_industry',$_SESSION['tonner']['sel_industry_tid'],'=');
//              }
//            }
            //------------------------------------------------------------------
            $result->sort('field_publishedat');
            $result->range(0,2000);
            $res = $result->execute();
            $nodes = \Drupal\Node\Entity\Node::loadMultiple($res);
//            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
            //Loop for historical
            foreach($nodes as $node){
                //Test
                if(!isset($histCollect[date('Y-m-d',$node->field_publishedat->value)])){
                    $histCollect[date('Y-m-d',$node->field_publishedat->value)] = [];
                    foreach($tones as $k => $t){
                        $histCollect[date('Y-m-d',$node->field_publishedat->value)][$t['name']] = 0;
                    }
                    ksort($histCollect[date('Y-m-d',$node->field_publishedat->value)]);
                }
                if(!isset($histCollect[date('Y-m-d',$node->field_publishedat->value)][$tone['name']])){
                    $histCollect[date('Y-m-d',$node->field_publishedat->value)][$tone['name']] = 0;
                }
                //Add
                $histCollect[date('Y-m-d',$node->field_publishedat->value)][$tone['name']] = $histCollect[date('Y-m-d',$node->field_publishedat->value)][$tone['name']] + 1;
            }
            //set the count
            $tones[$key]['total_headline_count'] = count($nodes);
            $headlineTotal = $headlineTotal + count($nodes);
            //Load the nodes by tone and restrict to today
            $result = \Drupal::entityQuery('node')
                ->condition('type', 'news_headline')
                ->condition('field_tone',$tone['tid'],'=')
                ->condition('field_publishedat',strtotime(date('Y-m-d')),'>=')
                ->execute();
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
            //set the current count
            $tones[$key]['current_headline_count'] = count($nodes);
            $currentHeadlineTotal = $currentHeadlineTotal + count($nodes);
        }
        //Key sort Historical data
        ksort($histCollect);
        //Send to Template
        $build = [
            '#theme' => 'tonner',
            '#searchform' => \Drupal::formBuilder()->getForm('Drupal\tonner\Form\graphsearchform'),
            '#tonescollect' => $tones,
            '#total_headlines' => $headlineTotal,
            '#attached' => [
                'library' => [
                    'tonner/tonner_worker',
                ],
                'drupalSettings' => [
                    'tonescollect' => $tones,
                    'total_headlines' => $headlineTotal,
                    'current_total_headlines' => $currentHeadlineTotal,
                    'historialdata' => $histCollect
                ]
            ],
            '#cache' => array('max-age' => 0)
        ];

        return $build;
    }

    /**
     * Gets tones names and tids
     * @return array
     */
    public function getTones(){
        //Open Collection
        $collect = [];
        //Vocab
        $vocabulary_name = 'tones'; //name of your vocabulary
        //Entity qyery on taxonomy
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', $vocabulary_name);
        //------------------------------------------------------------------
        //Industry
//        if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
//          if(!empty($_SESSION['tonner']['sel_sentiment_tid'])  && $_SESSION['tonner']['sel_sentiment_tid']!=='All'){
//            //Entity qyery on taxonomy
//            $query = \Drupal::entityQuery('taxonomy_term');
//            $query->condition('vid', $vocabulary_name);
//            $query->condition('tid',$_SESSION['tonner']['sel_sentiment_tid'],'=');
//            $query->sort('name');
//            $tids = $query->execute();
//          }
//        }
        //------------------------------------------------------------------
        $query->sort('name');
        $tids = $query->execute();
        //Load Terms
        $terms = Term::loadMultiple($tids);
        foreach($terms as $term) {
            //Add
            $collect[$term->getName()] = [];
            $collect[$term->getName()]['tid'] = $term->id();
            $collect[$term->getName()]['name'] = $term->getName();
        }
        //return
        return $collect;
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
