<?php

namespace craft\cereal\controllers;

use Craft;

use craft\elements\Entry;
use craft\web\Controller;
use craft\elements\db\EntryQuery;

class ApiController extends Controller
{
  protected $allowAnonymous = true;

  public function actionGet()
  {
    $schema = Craft::$app->request->get("schema");

    return $this->asJson($this->schemaToArray($schema));
  }

  function schemaToArray(array $schema, $relatedEntry = null)
  /* example schema:
    {
      section: "series",
      related: [
        {
          section: "sermons",
        }
      ]
    }
  */
  {
    $entryQuery = Entry::find();
    unset($schema["section"]);
    $relatedSchemas = array_key_exists("related", $schema) ? $schema["related"] : null;
    if($relatedSchemas)
      unset($schema->related);
    foreach($schema as $key => $value)
    {
      $entryQuery->$key = $value;
    }
    if($relatedEntry)
      $entryQuery->relatedTo = $relatedEntry;
    
    $entries = $entryQuery->all();
    // $entries = Entry::find()->configure($schema);
    $arrays = array();

    foreach($entries as $entry)
    {
      $array = $this->entryModelToArray($entry);
      if($relatedSchemas)
      {
        foreach($relatedSchemas as $relatedSchema)
        {
          $relatedSection = $relatedSchema["section"];
          $array[$relatedSection] = $this->schemaToArray($relatedSchema, $entry);
        }
      }
      $arrays[] = $array;
    }
    
    return $arrays;
  }

  function entryModelToArray(Entry $entry)
  {
    $array = array();
    $array["title"] = $entry->title;
    $array["id"] = $entry->id;
    foreach ($entry->getFieldLayout()->getFields() as $fieldLayout)
    {
      $array[$fieldLayout->name] = $fieldLayout;
      // $field = $fieldLayout->getField();
      // $handle = $field->getAttributes()["handle"];
      // $type = $field->getFieldType()->getName();
      // switch ($type) {
      //   case "Entries":
      //     // need to handle multiple
      //     $entries = $entry->$handle->find();
      //     if (count($entries))
      //     {
      //       $array[$handle] = array_map(
      //         function($entry) {return $this->entryModelToArray($entry);}, 
      //         $entries);
      //     }
      //     else
      //     {
      //       $array[$handle] = null;
      //     }
      //     break;
      //   case "Assets":
      //     $assets = $entry->$handle->find();
      //     if (count($assets))
      //     {
      //       $array[$handle] = array_map(
      //         function($asset) {return $asset->getUrl();},
      //         $assets);
      //     }
      //     else
      //     {
      //       $array[$handle] = null;
      //     }
      //     break;
      //   case "Tags":
      //     $tags = $entry->$handle->find();
      //     if (count($tags))
      //     {
      //       $array[$handle] = array_map(
      //         function($tag) {return $tag->getTitle();},
      //         $tags);
      //     }
      //     else
      //     {
      //       $array[$handle] = null;
      //     }
      //     break;
      //   case "Categories":
      //     $categories = $entry->$handle->find();
      //     if (count($categories))
      //     {
      //       $array[$handle] = array_map(
      //         function($category) {return $category->getTitle();},
      //         $categories);
      //     }
      //     else
      //     {
      //       $array[$handle] = null;
      //     }
      //     break;
      //   case "Date/Time":
      //     $array[$handle] = $entry->$handle;
      //     break;
      //   case "Plain Text":
      //     $array[$handle] = $entry->$handle;
      //     break;
      //   default: 
      //     $array[$handle] = $type;
      // }
    }
    return $array;
  }
}