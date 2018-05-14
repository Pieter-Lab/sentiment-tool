<?php

namespace Drupal\tonner\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;
use Drupal\node\Entity\Node;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class TonnerCommands extends DrushCommands {

    //IBM WATSON KEYS
    public $username = '6c0222b3-4d01-467e-b0f1-89ec5910d6b1';
    public $password = 'ePNrLyfsiJDW';
    public $url = 'https://gateway.watsonplatform.net/tone-analyzer/api/v3/tone?version=2017-09-21';

  /**
   * Tally the Countries Total Articles and Total Sentiment Scores.
   *
   * @usage tonner-tallyCountries
   *   Usage description
   *
   * @command tonner:tallyCountries
   * @aliases TCountries
   */
    public function tallyCountriesSentimentTotals(){
      //Get the display date
      $display_date_string = date('Y-m-d h:i:s a',time());
      //Talk
      echo '************************* '.$display_date_string.': Tally Countries Process Started *************************'.PHP_EOL;
      //--------------------------------------------------------------------------------------------------------------------------
      //Get All the Terms in the Tones Vocab
      $query = \Drupal::entityQuery('taxonomy_term');
      $query->condition('vid', "tones");
      $tids = $query->execute();
      $toneTerms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
      //--------------------------------------------------------------------------------------------------------------------------
      //Get All the Terms in the Countries Vocab
      $query = \Drupal::entityQuery('taxonomy_term');
      $query->condition('vid', "country");
      $tids = $query->execute();
      $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
      //Loop Countries
      foreach($terms as $country){
        $countryTerm = \Drupal\taxonomy\Entity\Term::load($country->Id());
        //Entity Query to get all articles listed
//        echo $countryTerm->getName().PHP_EOL;
//        echo $countryTerm->id().PHP_EOL;
        $query = \Drupal::entityQuery('node')
          ->condition('type', 'news_headline')
          ->condition('field_country', $countryTerm->id(), '=');
        $nids = $query->execute();
        //Get and set the Total Article count
        $ct_total_articles = count($nids);
        $countryTerm->field_sentiment_totals->setValue([]);//Clear out previous sentiments
        $countryTerm->field_total_number_of_articles->setValue($ct_total_articles);
        //save
        $countryTerm->save();
        //Talk
        echo "####### Date Stamp: ".$display_date_string.': '.$countryTerm->getName().': Total Articles: '.$ct_total_articles.' #######'.PHP_EOL;
        //----------------------------------------------------------------------
          //Loop Tones
          foreach($toneTerms as $tone) {
            //load term
            $toneTerm = \Drupal\taxonomy\Entity\Term::load($tone->Id());
            //Count
            $tQuery = \Drupal::entityQuery('node')
              ->condition('type', 'news_headline')
              ->condition('field_country', $countryTerm->id(), '=')
              ->condition('field_tone', $toneTerm->id(), '=');
            $tNids = $query->execute();
            //set
            $fc = \Drupal\field_collection\Entity\FieldCollectionItem::create(['field_name' => 'field_sentiment_totals']);
            $fc->field_sentiment->setValue(['target_id'=>$toneTerm->id()]);
            $fc->field_total->setValue(count($tNids));
            $fc->setHostEntity($countryTerm);
            $fc->save();
            //Talk
            echo "####### Date Stamp: ".$display_date_string.': '.$countryTerm->getName().': Total Tone "'.$toneTerm->getName().'" Articles: '.count($tNids).' #######'.PHP_EOL;
          }
          //save
          $countryTerm->save();
        exit();
        //----------------------------------------------------------------------
      }

      //--------------------------------------------------------------------------------------------------------------------------
      echo '************************* '.$display_date_string.': Tally Countries Process Ended *************************'.PHP_EOL;
    }

    /**
     * Import News From Reuter.
     *
     * @usage tonner-importNews
     *   Usage description
     *
     * @command tonner:importNews
     * @aliases INews
     */
    public function importNews() {
        //https://newsapi.org/sources
        echo '************************* '.date('y-m-d h:i:s a',time()).': News Import Process Has started.*************************'.PHP_EOL;
        //Import News Headlines for each Country
        $this->import('us','United States');
        $this->import('gb','United Kingdom');
        $this->import('au','Australia');
        $this->import('ca','China');
        $this->import('in','India');
        $this->import('ie','Ireland');
        $this->import('nz','New Zealand');
        $this->import('za','South Africa');

        echo '************************* '.date('y-m-d h:i:s a',time()).': News Import Process Has Ended.*************************'.PHP_EOL;
    }

    /**
     * Import Tunner
     * @param $counrty_code
     * @param $country_title
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function import($counrty_code,$country_title) {
        //talk
        $this->logger()->success(dt('News Import Process Has started.'));
        echo(dt('News Import Process Has started.')).PHP_EOL;
        //--------------------------------------------------------------------------------------------------------------
            # An HTTP GET request example
            //Api headlines endpoint
            $url = 'https://newsapi.org/v2/top-headlines?country='.$counrty_code.'&apiKey=451ed6d47caf4d52b8e867b97a2f76ee';
            $country_name = $country_title;
            //----------------------------------------------------------------------------------------------------------------------
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            curl_close($ch);
            //----------------------------------------------------------------------------------------------------------------------
            //transform to array
            $newsArr = json_decode($data);
            //Test that we have data
            if($newsArr->status==="ok"){
                //Talk
                $this->logger()->success(dt('News API Says GO!'));
                echo(dt('News API Says GO!')).PHP_EOL;
                //get the total results
                $total = $newsArr->totalResults;
                //Test that we have a res
                if($total>0){
                    //talk
                    $this->logger()->success(dt('Found News Articles: total: '.$total));
                    echo(dt('Found News Articles: total: '.$total)).PHP_EOL;
                    //Loop through the articles
                    foreach ($newsArr->articles as $article){
                        //Test for duplicates
                        $query = \Drupal::entityQuery('node')
                            ->condition('type', 'news_headline')
                            ->condition('title', $article->title, '=');
                        $nids = $query->execute();
                        //test
                        if(empty($nids)){
                            echo ''.PHP_EOL;
                            $this->logger()->success(dt('Processing Article: '.$article->title));
                            echo(dt('Processing Article: '.$article->title)).PHP_EOL;
                            //Get the tones
                            $tones = $this->interpret($article->title,$article->description);
                            //----------------------------------------------------------------------------------------------------------
                            if(!empty($tones->document_tone->tones)){
                                $this->logger()->success(dt('Generating Article: '.$article->title));
                                echo(dt('Generating Article: '.$article->title)).PHP_EOL;
                                //Insert
                                $edge_name = Node::create(['type' => 'news_headline']);
                                $edge_name->set('title', $article->title);
                                $edge_name->set('body', $article->description);
                                //Author
                                if(!empty($article->author)){
                                    $author_term_id = $this->get_vocabulary_term($article->author,'author');
                                    if(!empty($author_term_id)){
                                        //update term
                                        $edge_name->set('field_author', $author_term_id);
                                    }
                                }
//                                //Published
                                $edge_name->set('field_publishedat',strtotime($article->publishedAt));
                                //Source
                                $source_term_id = $this->get_vocabulary_term($article->source->name,'source');
                                if(!empty($source_term_id)){
                                    //update term
                                    $edge_name->set('field_source', $source_term_id);
                                }
                                $edge_name->set('field_url', $article->field_url);
                                //Image
                                    //image file_name
                                    $ImageFileName = basename($article->urlToImage);
                                    //Set the Image directory
                                    $ImageDirectory = 'public://headline_images/';
                                    if(file_prepare_directory($ImageDirectory, FILE_CREATE_DIRECTORY)){
                                        // Create file object from a remotely copied file.
                                        $data = file_get_contents($article->urlToImage);
                                        if($data && !empty($data)){
                                            $file = file_save_data($data, $ImageDirectory.$ImageFileName, FILE_EXISTS_REPLACE);;
                                            //add to image array holder
                                            $edge_name->set('field_urltoimage', [
                                                'target_id' => $file->id()
                                            ]);
                                        }
                                    }
                                //Country
                                $country_term_id = $this->get_vocabulary_term($country_name ,'country');
                                if(!empty($country_term_id)){
                                    //update term
                                    $edge_name->set('field_country', $country_term_id);
                                }
                                //loop the tones
                                $ton = [];
                                foreach($tones->document_tone->tones as $tone){
                                    if(!empty($tone->tone_name) && isset($tone->tone_name)){
                                        //Tone
                                        $tone_term_id = $this->get_vocabulary_term($tone->tone_name,'tones');
                                        if(!empty($tone_term_id)){
                                            //update term
                                            $ton[]['target_id'] = $tone_term_id;
                                        }
                                    }
                                }
                                $edge_name->set('field_tone', $ton);
                                $edge_name->enforceIsNew();
                                $edge_name->save();
                                //talk
                                $this->logger()->success(dt('********* New Article Inserted *********'));
                                echo(dt('********* New Article Inserted *********')).PHP_EOL;
                            }else{
                                //Talk
                                $this->logger()->error(dt('No Tones Found for Article: '.$article->title));
                                echo(dt('No Tones Found for Article: '.$article->title)).PHP_EOL;
                            }
                            //----------------------------------------------------------------------------------------------------------
                        }else{
                            //Talk
                            echo '.'.PHP_EOL;
                            $this->logger()->error(dt('Article Present: '.$article->title));
                            echo(dt('Article Present: '.$article->title)).PHP_EOL;
                        }
                    }
                }
            }else{
                //Talk
                $this->logger()->error(dt('News API Says NO!'));
                echo(dt('News API Says NO!')).PHP_EOL;
            }
        //--------------------------------------------------------------------------------------------------------------
        //Talk
        $this->logger()->success(dt('News Import Process Has ended.'));
        echo(dt('News Import Process Has ended.')).PHP_EOL;
    }

    /**
     * Send text which is url en coded to the IBM Toner to retrieve emotional val
     * @param $text
     * @return mixed
     */
    public function interpret($text, $description){
        //st the content
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode($this->username.":".$this->password)
            )
        ));
        //call
        $data = file_get_contents($this->url.'&text='.urlencode($text), false, $context);
        //Convert to JSON
        $json = json_decode($data);
        //Test
        if(empty($json->document_tone->tones)){
            //Try on description
            //call
            $data = file_get_contents($this->url.'&text='.urlencode($description), false, $context);
            //Convert to JSON
            $json = json_decode($data);
        }
        //convert and return
        return $json;
    }

    /** Niffty way to find and create Taxonomy Terms
     * @param $term_value
     * @param $vocabulary
     * @return int|mixed|null|string
     */
    public function get_vocabulary_term($term_value, $vocabulary){
        if ($terms = taxonomy_term_load_multiple_by_name($term_value, $vocabulary)) {
            $term = reset($terms);
        }else {
            $term = \Drupal\taxonomy\Entity\Term::create([
                'name' => $term_value,
                'vid' => $vocabulary,
            ]);
            $term->save();
        }
        return $term->id();
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

  /**
   * Command description here.
   *
   * @param $arg1
   *   Argument description.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage tonner-commandName foo
   *   Usage description
   *
   * @command tonner:commandName
   * @aliases foo
   */
  public function commandName($arg1, $options = ['option-name' => 'default']) {
    $this->logger()->success(dt('Achievement unlocked.'));
  }



  /**
   * An example of the table output format.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @field-labels
   *   group: Group
   *   token: Token
   *   name: Name
   * @default-fields group,token,name
   *
   * @command tonner:token
   * @aliases token
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function token($options = ['format' => 'table']) {
    $all = \Drupal::token()->getInfo();
    foreach ($all['tokens'] as $group => $tokens) {
      foreach ($tokens as $key => $token) {
        $rows[] = [
          'group' => $group,
          'token' => $key,
          'name' => $token['name'],
        ];
      }
    }
    return new RowsOfFields($rows);
  }
}
