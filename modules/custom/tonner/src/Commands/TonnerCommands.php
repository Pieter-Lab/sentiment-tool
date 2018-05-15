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
    private $prefix = "########:";
    private $suffix = " :########".PHP_EOL;
    public $username = '497762ee-4aa8-4f67-b64b-f81adfcb0e28';
    public $password = 'Io5q2s8XMz2O';
    public $url = 'https://gateway.watsonplatform.net/tone-analyzer/api/v3/tone?version=2017-09-21';
    private $indusrties = ['business','entertainment','general','health','science','sports','technology'];
    private $import_countries = [
      'gb'=>'United Kingdom',
      'us'=>'United States',
      'au'=>'Australia',
      'in'=>'India',
      'ca'=>'China',
      'ie'=>'Ireland',
      'nz'=>'New Zealand',
      'za'=>'South Africa'];

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
      echo $this->prefix.' '.$display_date_string.': Tally Countries Process Started *************************'.PHP_EOL;
      //Get All the Terms in the Tones Vocab
      $query = \Drupal::entityQuery('taxonomy_term');
      $query->condition('vid', "industry");
      $tids = $query->execute();
      $industryTerms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
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
//        echo $this->prefix.$countryTerm->getName().PHP_EOL;
//        echo $this->prefix.$countryTerm->id().PHP_EOL;
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
        echo  $this->prefix." Date Stamp: ".$display_date_string.': '.$countryTerm->getName().': Total Articles: '.$ct_total_articles.' #######'.PHP_EOL;
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
            $tNids = $tQuery->execute();
            //set
            $fc = \Drupal\field_collection\Entity\FieldCollectionItem::create(['field_name' => 'field_sentiment_totals']);
            $fc->field_sentiment->setValue(['target_id'=>$toneTerm->id()]);
            $fc->field_total->setValue(count($tNids));
            $fc->setHostEntity($countryTerm);
            $fc->save();
            //Talk
            echo  $this->prefix." Date Stamp: ".$display_date_string.': '.$countryTerm->getName().': Total Tone "'.$toneTerm->getName().'" Articles: '.count($tNids).' #######'.PHP_EOL;
          }
          //Loop Industry
          foreach($industryTerms as $industry) {
            //load term
            $industryTerm = \Drupal\taxonomy\Entity\Term::load($industry->Id());
            //Count
            $iQuery = \Drupal::entityQuery('node')
              ->condition('type', 'news_headline')
              ->condition('field_country', $countryTerm->id(), '=')
              ->condition('field_article_industry', $industryTerm->id(), '=');
            $iNids = $iQuery->execute();
            //set
            $fc = \Drupal\field_collection\Entity\FieldCollectionItem::create(['field_name' => 'field_industry_totals']);
            $fc->field_industry->setValue(['target_id'=>$industryTerm->id()]);
            $fc->field_industry_total->setValue(count($iNids));
            $fc->setHostEntity($countryTerm);
            $fc->save();
            //Talk
            echo  $this->prefix." Date Stamp: ".$display_date_string.': '.$countryTerm->getName().': Total Industry "'.$industryTerm->getName().'" Articles: '.count($iNids).' #######'.PHP_EOL;
          }
          //save
          $countryTerm->save();
        //----------------------------------------------------------------------
      }
      //--------------------------------------------------------------------------------------------------------------------------
      echo  $this->prefix.' '.$display_date_string.': Tally Countries Process Ended *************************'.PHP_EOL;
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
        echo  $this->prefix.'  '.date('y-m-d h:i:s a',time()).': News Import Process Has started.*************************'.PHP_EOL;
        //Import News Headlines for each Country per industry
        foreach($this->import_countries as $cCode => $cName){
          foreach($this->indusrties as $industry){
            //Talk
            echo  $this->prefix.' '.date('y-m-d h:i:s a',time()).': '.$cName.': Importing for - '.$industry.'.*************************'.PHP_EOL;
            $this->import($cCode,$cName,$industry);
          }
        }
        echo  $this->prefix.' '.date('y-m-d h:i:s a',time()).': News Import Process Has Ended.*************************'.PHP_EOL;
    }

  /**
   * Main Importer for Reuters
   * @param $counrty_code
   * @param $country_title
   * @param $industry
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
    public function import($counrty_code,$country_title,$industry) {
        //talk
      echo  $this->prefix.' '.(dt('News Import Process Has started.')).$this->suffix;
        //--------------------------------------------------------------------------------------------------------------
            # An HTTP GET request example
            //Api headlines endpoint
            $url = 'https://newsapi.org/v2/top-headlines?country='.$counrty_code.'&category='.$industry.'&apiKey=451ed6d47caf4d52b8e867b97a2f76ee';
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
              echo  $this->prefix.' '.(dt('News API Says GO!')).$this->suffix;
                //get the total results
                $total = $newsArr->totalResults;
                //Test that we have a res
                if($total>0){
                    //talk
                  echo  $this->prefix.' '.(dt('Found News Articles: total: '.$total)).$this->suffix;
                    //Loop through the articles
                    foreach ($newsArr->articles as $article){
                        //Test for duplicates
                        $query = \Drupal::entityQuery('node')
                            ->condition('type', 'news_headline')
                            ->condition('title', $article->title, '=');
                        $nids = $query->execute();
                        //test
                        if(empty($nids)){
                          echo  $this->prefix.' '.(dt('Processing Article: '.$article->title)).$this->suffix;
                            //Get the tones
                            $tones = $this->interpret($article->title,$article->description);
                            //----------------------------------------------------------------------------------------------------------
                            if(!empty($tones->document_tone->tones)){
                              echo  $this->prefix.' '.(dt('Generating Article: '.$article->title)).$this->suffix;
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
                                $edge_name->set('field_url', $article->url);
                                //Image
                                    //image file_name
                                    $ImageFileName = basename($article->urlToImage);
                                    if(!empty($ImageFileName) && strlen($ImageFileName) < 200){
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
                                    }
                                //Industry
                                $industry_term_id = $this->get_vocabulary_term($industry ,'industry');
                                if(!empty($industry_term_id)){
                                  //update term
                                  $edge_name->set('field_article_industry', $industry_term_id);
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
                              echo  $this->prefix.' '.(dt(' --------------- New Article Inserted --------------- ')).$this->suffix;
                            }else{
                                //Talk
                              echo  $this->prefix.' '.(dt('No Tones Found for Article: '.$article->title)).$this->suffix;
                            }
                            //----------------------------------------------------------------------------------------------------------
                        }else{
                            //Talk
                          echo  $this->prefix.' '.(dt('Article Present: '.$article->title)).$this->suffix;
                        }
                    }
                }
            }else{
                //Talk
              echo  $this->prefix.' '.(dt('News API Says NO!')).$this->suffix;
            }
        //--------------------------------------------------------------------------------------------------------------
        //Talk
      echo  $this->prefix.' '.(dt('News Import Process Has ended.')).$this->suffix;
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
        if(empty($json->document_tone->tones) && !empty($description)){
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
