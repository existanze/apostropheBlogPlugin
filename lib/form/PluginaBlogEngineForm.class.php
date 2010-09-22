<?php

class PluginaBlogEngineForm extends aPageForm
{
  public function setup()
  {
    parent::setup();

    $this->useFields();
    $q = Doctrine::getTable('aBlogPost')->addCategories();
    $this->setWidget('blog_categories_list', new sfWidgetFormDoctrineChoice(array('multiple' => true, 'query' => $q, 'model' => 'aCategory')));
    $this->widgetSchema->setLabel('blog_categories_list', 'Categories');
    $this->widgetSchema->setHelp('blog_categories_list','(Defaults to All Cateogories)');
    $this->setValidator('blog_categories_list', new sfValidatorDoctrineChoice(array('multiple' => true, 'query' => $q, 'model' => 'aCategory', 'required' => false)));
    $this->widgetSchema->setNameFormat('enginesettings[%s]');
    $this->widgetSchema->setFormFormatterName('aAdmin');
  }
}