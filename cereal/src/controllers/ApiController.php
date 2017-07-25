<?php

namespace craft\cereal\controllers;

use Craft;

use craft\element\Entry;
use craft\web\Controller;
use craft\elements\db\ElementQuery;

class ApiController extends Controller
{
  protected $allowAnonymous = true;

  public function actionGet()
  {
    $schema = Craft::$app->request->get("schema");
    return $this->asJson($schema);

    //$this->returnJson($this->schemaToArray($schema));
  }

  function schemaToArray(array $schema, $relatedEntryModel = null)
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
    $criteria = new ElementQuery($schema->section);
    $relatedSchemas = array_key_exists("related", $schema) ? $schema["related"] : null;
    if($relatedSchemas)
      unset($schema->related);
    foreach($schema as $key => $value)
    {
      $criteria->$key = $value;
    }
    if($relatedEntryModel)
      $criteria->relatedTo = $relatedEntryModel;
    
    $entryModels = $criteria->all();
    $arrays = array();

    foreach($entryModels as $entryModel)
    {
      $array = $this->entryModelToArray($entryModel);
      if($relatedSchemas)
      {
        foreach($relatedSchemas as $relatedSchema)
        {
          $relatedSection = $relatedSchema["section"];
          $array[$relatedSection] = $this->schemaToArray($relatedSchema, $entryModel);
        }
      }
      $arrays[] = $array;
    }
    
    return $arrays;
  }

  function entryModelToArray(EntryModel $entryModel)
  {
    $array = array();
    $array["title"] = $entryModel->title;
    $array["id"] = $entryModel->id;
    foreach ($entryModel->getFieldLayout()->getFields() as $fieldLayout)
    {
      $field = $fieldLayout->getField();
      $handle = $field->getAttributes()["handle"];
      $type = $field->getFieldType()->getName();
      switch ($type) {
        case "Entries":
          // need to handle multiple
          $entries = $entryModel->$handle->find();
          if (count($entries))
          {
            $array[$handle] = array_map(
              function($entry) {return $this->entryModelToArray($entry);}, 
              $entries);
          }
          else
          {
            $array[$handle] = null;
          }
          break;
        case "Assets":
          $assets = $entryModel->$handle->find();
          if (count($assets))
          {
            $array[$handle] = array_map(
              function($asset) {return $asset->getUrl();},
              $assets);
          }
          else
          {
            $array[$handle] = null;
          }
          break;
        case "Tags":
          $tags = $entryModel->$handle->find();
          if (count($tags))
          {
            $array[$handle] = array_map(
              function($tag) {return $tag->getTitle();},
              $tags);
          }
          else
          {
            $array[$handle] = null;
          }
          break;
        case "Categories":
          $categories = $entryModel->$handle->find();
          if (count($categories))
          {
            $array[$handle] = array_map(
              function($category) {return $category->getTitle();},
              $categories);
          }
          else
          {
            $array[$handle] = null;
          }
          break;
        case "Date/Time":
          $array[$handle] = $entryModel->$handle;
          break;
        case "Plain Text":
          $array[$handle] = $entryModel->$handle;
          break;
        default: 
          $array[$handle] = $type;
      }
    }
    return $array;
  }
}