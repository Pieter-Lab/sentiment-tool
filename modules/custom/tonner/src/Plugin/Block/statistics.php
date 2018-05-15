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
            $result = \Drupal::entityQuery('node')
                ->condition('type', 'news_headline')
                ->condition('field_tone',$tone['tid'],'=')
                ->condition('field_publishedat',strtotime('-1 day'),'>=')
                ->sort('title')
                ->execute();
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
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
            ]
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
