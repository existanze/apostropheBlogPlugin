<?php

/**
 * PluginaBlogItem
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class PluginaBlogItem extends BaseaBlogItem
{
  protected $update = true;
  public $engine = 'aBlog';

  /**
   * These date methods are use in the routing of the permalink
   */
  public function getYear()
  {
    return date('Y', strtotime($this->getPublishedAt()));
  }

  public function getMonth()
  {
    return date('m', strtotime($this->getPublishedAt()));
  }

  public function getDay()
  {
    return date('d', strtotime($this->getPublishedAt()));
  }
  
  public function getFeedSlug()
  {
    return $this->slug;
  }
  
  public function postInsert($event)
  {
    $page = new aPage();
    $page['slug'] = $this->engine.'/'.$this['id'];
    $page->save();

    $slot = $page->createSlot('aRichText');
    $slot->value = 'This is your body.';
    $slot->save();
    $page->newAreaVersion('blog-body', 'add',
      array(
        'permid' => 1, 
        'slot' => $slot));
    
    $title = $page->createSlot('aText');
    $title->value = "Untitled";
    $title->save();
    $page->newAreaVersion('title', 'add',
      array(
        'permid' => 1,
        'slot' => $title));

    $this->Page = $page;
    $this->slug = 'untitled-'.$this['id'];
    $this->title = 'untitled';
    $this['slug_saved'] = false;
    $this->update = false;
    $this->save();
  }

  public function preUpdate($event)
  {
    if($this->update)
    {
      if(array_key_exists('slug', $this->getModified()))
      {
        $this['slug_saved'] = true;
      }
      if($this['slug_saved'] == false && array_key_exists('title', $this->getModified()))
      {
        $this['slug'] = aTools::slugify($this['title']);
      }
      else
      {
        $this['slug'] = aTools::slugify($this['slug']);
      }
    }
  }

  public function postUpdate($event)
  {
    $title = $this->Page->createSlot('aText');
    $title->value = $this['title'];
    $title->save();
    $this->Page->newAreaVersion('title', 'add',
      array(
        'permid' => 1,
        'slot' => $title));
  }
  
  public function postDelete($event)
  {
    $this->Page->delete();
  }

  /**
   *
   * @param string $areas Array of areas to retrieve text for
   * @param int $limit Number of characters to restrict retrieval to
   * @return string
   */
  public function getTextForAreas($areas = array(), $limit = null)
  {
    $text = '';
    foreach($areas as $area)
    {
      foreach($this->Page->getArea($area) as $slot)
      {
        if(method_exists($slot, 'getText'))
        {
          $text .= strip_tags($slot->getText());
        }
      }
    }
    if(!is_null($limit))
    {
      $text = substr($text, 0, $limit);
    }

    return $text;
  }

  /**
   *
   * @param string $area Name of an area
   * @param int $limit Number of characters to restrict retrieval to
   * @return string
   */
  public function getTextForArea($area, $limit = null)
  {
    return $this->getTextForAreas(array($area), $limit);
  }

  public function getMediaForArea($area, $type = 'image', $limit = 5)
  {
    return $this->getMediaForAreas(array($area), $type, $limit);
  }

  /**
   * Given an array of array this function returns the mediaItems in those areas.
   * @param  aArea $areas
   * @param  $type Set the type of media to return (image, video, pdf, etc...)
   * @param  $limit Limit the number of mediaItems returned
   * @return array aMediaItems
   */
  public function getMediaForAreas($areas, $type = 'image', $limit = 5)
  {
    $aMediaItems = array();
    foreach($areas as $area)
    {
      foreach($this->Page->getArea($area) as $slot)
      {
        foreach($slot->MediaItems as $aMediaItem)
        {
          if(is_null($type) || $aMediaItem['type'] == $type)
          {
            $limit = $limit - 1;
            $aMediaItems[] = $aMediaItem;
            if($limit == 0) return $aMediaItems;
          }
        }
      }
    }
    return $aMediaItems;
  }

  public function getAreas()
  {
    $templates = sfConfig::get('app_'.$this->engine.'_templates');
    return $templates[$this['template']]['areas'];
  }

  public function getMedia($type = 'image', $limit = 5)
  {
    return $this->getMediaForAreas($this->getAreas(), $type, $limit);
  }

  public function hasMedia($type = 'image')
  {
    return count($this->getMedia($type, 1));
  }

  public function getFeedText()
  {
    $text = '';
    foreach($this->Page->getArea('blog-body') as $slot)
    {
      if(method_exists($slot, 'getText'))
      {
        $text .= $slot->getText();
      }
    }
    return $text;
  }

  public function userCanEdit(sfGuardUser $user)
  {
    $q = $this->getTable()->createQuery()
      ->addWhere('id = ?', $this['id']);
    Doctrine::getTable('aBlogItem')->filterByEditable($q, $user['id']);
    return count($q->execute());
  }


  /**
   * Deletes a blog item after checking if the user has permission to perform
   * the delete.
   * @param Doctrine_Connection $conn
   * @return boolean
   */
  public function delete(Doctrine_Connection $conn = null)
  {
    $user = sfContext::getInstance()->getUser()->getGuardUser();
    if($this->userHasPrivilege($user, 'delete'))
    {
      return parent::delete($conn);
    }
    else
      return false;
  }

  /**
   * Publishes a blog post or event if user has permission
   */
  public function publish()
  {
    if($this->userHasPrivilege('publish'))
    {
      $this['status'] = 'published';
      $this->save();
    }
  }


  /**
   * Unpublishes a blog post or event if the user has permission
   */
  public function unpublish()
  {
    if($this->userHasPrivilege('publish'))
    {
      $this['status'] = 'draft';
      $this->save();
    }
  }


  /**
   * Checks whether a user has permission to perform various actions on blog
   * post or event.
   *
   * @param string $privilege
   * @return boolean
   */
  public function userHasPrivilege($privilege = 'publish')
  {
    $user = sfContext::getInstance()->getUser();
    if($user->hasCredential('admin'))
      return true;

    if($user->getGuardUser()->getId() == $this['author_id'])
      return true;
    
    if($privilege == 'edit')
    {
      return $this->userCanEdit($user->getGuardUser());
    }

    return false;
  }
  
  /**
   * This function attempts to find the "best" engine to route a given post to.
   * based on the categories that are used on various engine pages.
   *
   * @return aPage the best engine page
   */
  public function findBestEngine()
  {
    $engines = Doctrine::getTable('aPage')->createQuery()
      ->addWhere('engine = ?', $this->engine)
      ->execute();

    if(count($engines) == 0)
      return '';
    else if(count($engines) == 1)
      return $engines[0];

    //When there are more than one engine page we need to use some heuristics to
    //guess what the best page is.
    $catIds = array();
    foreach($this->Categories as $category)
    {
      $catIds[$category['id']] = $category['id'];
    }

    if(count($catIds) < 1)
      return $engines[0];

    $best = array(0, '');

    foreach($engines as $engine)
    {
      $score = 0;
      foreach($engine->BlogCategories as $category)
      {
        if(isset($catIds[$category['id']]))
          $score = $score + 1;
      }
      if($score > $best[0])
      {
        $best = $engine;
      }
    }

    return $best;
  }
}