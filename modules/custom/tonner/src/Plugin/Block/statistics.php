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
        //Collect Tags for tag CLoud
        $tagsTopics = [];
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
//            $result->condition('field_publishedat',strtotime('-14 day'),'>=');
            //------------------------------------------------------------------
            //Country
            if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
              if(!empty($_SESSION['tonner']['sel_country_tid'])  && $_SESSION['tonner']['sel_country_tid']!=='all'){
                $result->condition('field_country',$_SESSION['tonner']['sel_country_tid'],'=');
              }
            }
            //------------------------------------------------------------------
            //Industry
            if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
              if(!empty($_SESSION['tonner']['sel_industry_tid'])  && $_SESSION['tonner']['sel_industry_tid']!=='all'){
                $result->condition('field_article_industry',$_SESSION['tonner']['sel_industry_tid'],'=');
              }
            }
            //------------------------------------------------------------------
            //Topics
            if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
              if(!empty($_SESSION['tonner']['sel_tag_tid'])  && $_SESSION['tonner']['sel_tag_tid']!=='all'){
                $result->condition('field_topics',$_SESSION['tonner']['sel_tag_tid'],'=');
              }
            }
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
                //Get tags
                $tags = $node->field_topics->getValue();
                if(!empty($tags)){
                  foreach($tags as $tag){
                    $topic = \Drupal\taxonomy\Entity\Term::load($tag['target_id']);
                    $tagsTopics[$topic->getName()] = $topic->getName();
                  }
                }
            }
            //set the count
            $tones[$key]['total_headline_count'] = count($nodes);
            $headlineTotal = $headlineTotal + count($nodes);
            //Load the nodes by tone and restrict to today
            $result = \Drupal::entityQuery('node');
            $result->condition('type', 'news_headline');
            $result->condition('field_tone',$tone['tid'],'=');
            $result->condition('field_publishedat',strtotime(date('Y-m-d')),'>=');
            //Country
            if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
              if(!empty($_SESSION['tonner']['sel_country_tid'])  && $_SESSION['tonner']['sel_country_tid']!=='all'){
                $result->condition('field_country',$_SESSION['tonner']['sel_country_tid'],'=');
              }
            }
            //Industry
            if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
              if(!empty($_SESSION['tonner']['sel_industry_tid'])  && $_SESSION['tonner']['sel_industry_tid']!=='all'){
                $result->condition('field_article_industry',$_SESSION['tonner']['sel_industry_tid'],'=');
              }
            }
            //------------------------------------------------------------------
            //Topics
            if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
              if(!empty($_SESSION['tonner']['sel_tag_tid'])  && $_SESSION['tonner']['sel_tag_tid']!=='all'){
                $result->condition('field_topics',$_SESSION['tonner']['sel_tag_tid'],'=');
              }
            }
            //------------------------------------------------------------------
            $nres = $result->execute();
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nres);
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
            '#cache' => array('max-age' => 0),
            '#topicsCloud'=> implode(', ',$tagsTopics)
        ];
        if(isset($this->searchSentiment) && !empty($this->searchSentiment)){
          $build['#attached']['drupalSettings']['searchSentiment'] = ucfirst($this->searchSentiment);
        }
        //Check for Headline
        if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
          //Headline
          $headline = false;
          if(isset($_SESSION['tonner']['sel_country_tid']) && !empty($_SESSION['tonner']['sel_country_tid'])){
            //Get All the Terms in the Countries Vocab
            $query = \Drupal::entityQuery('taxonomy_term');
            $query->condition('vid', "country");
            $query->condition('tid', $_SESSION['tonner']['sel_country_tid'],"=");
            $tids = $query->execute();
            if($tids && !empty($tids)){
              $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
              foreach($terms as $country) {
                $countryTerm = \Drupal\taxonomy\Entity\Term::load($country->Id());
                $headline .= $countryTerm->getName().' : ';
              }
            }
          }
          if(isset($_SESSION['tonner']['sel_industry_tid']) && !empty($_SESSION['tonner']['sel_industry_tid'])){
            //Get All the Terms in the Tones Vocab
            $query = \Drupal::entityQuery('taxonomy_term');
            $query->condition('vid', "industry");
            $query->condition('tid', $_SESSION['tonner']['sel_industry_tid'],"=");
            $tids = $query->execute();
            $industryTerms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
            foreach($industryTerms as $industry) {
              //load term
              $industryTerm = \Drupal\taxonomy\Entity\Term::load($industry->Id());
              $headline .= $industryTerm->getName().' : ';
            }
          }
          if(isset($_SESSION['tonner']['sel_sentiment_tid']) && !empty($_SESSION['tonner']['sel_sentiment_tid'])){
            $toneTerms = \Drupal\taxonomy\Entity\Term::loadMultiple($_SESSION['tonner']['sel_sentiment_tid']);
            if($toneTerms && !empty($toneTerms)){
              foreach($toneTerms as $tone) {
                //load term
                $toneTerm = \Drupal\taxonomy\Entity\Term::load($tone->Id());
                $headline .= $toneTerm->getName().' : ';
              }
            }
          }
          if(isset($_SESSION['tonner']['sel_tag_tid']) && !empty($_SESSION['tonner']['sel_tag_tid'])){
            //Get All the Terms in the Tones Vocab
            $query = \Drupal::entityQuery('taxonomy_term');
            $query->condition('vid', "tags");
            $query->condition('tid', $_SESSION['tonner']['sel_tag_tid'],"=");
            $tids = $query->execute();
            $industryTerms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
            foreach($industryTerms as $industry) {
              //load term
              $industryTerm = \Drupal\taxonomy\Entity\Term::load($industry->Id());
              $headline .= $industryTerm->getName().' : ';
            }
          }
          //Check if Headlines was created
          if($headline && !empty($headline)){
            $build['#attached']['drupalSettings']['graphheadline'] = ucfirst($headline);
            $build['#graphheadline'] = ucfirst($headline);
          }
        }
        //return the build
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
        //Sentiment
        if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
          if(!empty($_SESSION['tonner']['sel_sentiment_tid'])){
            //Entity qyery on taxonomy
//            $this->printer($_SESSION['tonner']['sel_sentiment_tid']);
//            exit();
            $and_condition_1 = $query->orConditionGroup();
            $count = 0;
            foreach($_SESSION['tonner']['sel_sentiment_tid'] as $k => $v){
              if($k!=="all"){
                $count++;
                $and_condition_1->condition('tid',$k,'=');
              }
            }
            if($count>0){
              $query->condition($and_condition_1);
            }
          }
        }
        //------------------------------------------------------------------
        $query->sort('name');
        $tids = $query->execute();
//        $this->printer($tids);
        //Load Terms
        $terms = Term::loadMultiple($tids);
        foreach($terms as $term) {
            //Add
            $collect[$term->getName()] = [];
            $collect[$term->getName()]['tid'] = $term->id();
            $collect[$term->getName()]['name'] = $term->getName();
          //Sentiment
//          if(isset($_SESSION['tonner']) && !empty($_SESSION['tonner'])){
//            if(!empty($_SESSION['tonner']['sel_sentiment_tid'])){
//              if($_SESSION['tonner']['sel_sentiment_tid']==$term->id())
//              //Entity qyery on taxonomy
//              $this->searchSentiment = $term->getName();
//            }
//          }
        }
//        exit();
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
