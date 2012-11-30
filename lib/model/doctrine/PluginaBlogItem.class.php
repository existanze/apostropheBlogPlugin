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
  protected $engineSlug;
  public $engine = 'aBlog';

  /**
   * Doctrine_Record overrides
   */

  public function construct()
  {
    if ($this->isNew() && sfConfig::get('app_aBlog_allow_comments_individually', false))
    {
      $this->setAllowComments(sfConfig::get('app_aBlog_allow_comments_initially', true));
    }
    $default = $this->getTable()->getDefaultTemplate();
    $this->setTemplate($this->getTable()->getDefaultTemplate());
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
    if ($this->userHasPrivilege('delete'))
    {
      return parent::delete($conn);
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Suitable in tasks and other environments where you don't have a user
   */
  public function deleteWithoutPermissionsCheck(Doctrine_Connection $conn = null)
  {
    return parent::delete($conn);
  }
  
  public function postDelete($event)
  {
    $this->Page->delete();
  }

  public function preInsert($event)
  {
    // If the slug hasn't been altered slugify the title to create the slug.
    if (!strlen($this['slug']))
    {
      $this['slug'] = $this->uniqueSlugFromTitle($this->_get('title'));
    }
  }
  
  /**
   * Listener to setup blog item and its virtual page
   * @param <type> $event
   */
  public function postInsert($event)
  {
    if ($this->page_id)
    {
      // TODO: how is it possible for the page to already exist at this point?
      // It definitely does. We were wasting every other page before this change,
      // and also crashing "New Event"
      $page = $this->Page;
    }
    else
    {
      // Create a virtual page for this item
      $page = new aPage();
    }
    // Hold all search updates until we're through triggering
    // saves of the virtual page
    $page->blockSearchUpdates();
    // In virtual pages the engine column is used to figure out which engine should 
    // be asked for extra fields before search indexing of the page
    $page['engine'] = get_class($this);
    $page['slug'] = $this->getVirtualPageSlug();
    // Search is good, let it happen
    $page['view_is_secure'] = false;
    // ... But not if we're unpublished
    $page->archived = !($this->status === 'published');

    // Make default values for this item
    
    $this['slug_saved'] = false;
    if (is_null($this['published_at']))
    {
      $this['published_at'] = date('Y-m-d H:i:s');
    }
    $page->published_at = $this['published_at'];

    $this->Page = $page;
    
    // For consistency with the way the page creation form does it, and to fix a bug
    // in generate-test-posts, we save the page before setting its title slot
    
    $this->Page->save();
    // Create a slot for the title and add to the virtual page.
    // We now have an actual title right off owing to the special form for
    // creating a new post which won't let you slide by without one
    $this->Page->setTitle($this->_get('title'));

    // This prevents post preupdate from running after the next save
    $this->update = false;
    $this->save();
    // Now that we're definitely done with the page we can
    // let just one search index update happen
    $page->flushSearchUpdates();
  }

  /**
   * preUpdate function used to do some slugification.
   * @param <type> $event
   */
  public function preUpdate($event)
  {
    if($this->update)
    {
      // If the slug was altered by the user we no longer want to attempt to sluggify
      // the title to create the slug
      if(array_key_exists('slug', $this->getModified()))
      {
        $this['slug_saved'] = true;
      }

      if($this['slug_saved'] == false && array_key_exists('title', $this->getModified()))
      {
        // If the slug hasn't been altered slugify the title to create the slug.
        $this['slug'] = $this->uniqueSlugFromTitle($this->_get('title'));
      }
      else
      {
        // Otherwise slugify the user entered value. (Why is this happening here and not in a form?)
        $this['slug'] = $this->uniqueifySlug(aTools::slugify($this['slug']));
      }
    }

    $this->Page['view_is_secure'] = ($this['status'] == 'published') ? false: true;

  }

  protected function uniqueSlugFromTitle($title)
  {
    return $this->uniqueifySlug(aTools::slugify(html_entity_decode($title, ENT_COMPAT, 'UTF-8')));
  }
  
  protected function uniqueifySlug($slug)
  {
    $cslug = $slug;
    // Check if a blog post or event already has this slug
    $i = 1;
    while($this->findConflictingItem($slug))
    {
      $slug = $cslug.'-'.$i;
      $i++;
    }
    return $slug;
  }
  
  public function findConflictingItem($slug)
  {
    // Slugs are unique at the database level, so we have to uniqueify them
    // across both posts and events. If we use the posts or events table we
    // will always miss those conflicts
    $q = Doctrine::getTable('aBlogItem')->createQuery()
        ->addWhere('slug = ?', $slug);
    // Don't fail to return anything when id is null
    if ($this['id'])
    {
      $q->addWhere('id != ?', $this['id']);
    }
    // Don't hydrate objects just for a test like this. With 500 "Monday Fourteen"
    // posts (to take an example of my own) you'd wind up running out of memory
    // due to Doctrine's memory leaks
    $result = $q->fetchOne(array(), Doctrine::HYDRATE_NONE);
    return $result;
  }

  /**
   * Post update function to update the title slot that is saved for search indexing
   * and internationalization purposes.
   * @param <type> $event
   */
  public function postUpdate($event)
  {
    if ($this->update)
    {
      $this->Page->setTitle($this->_get('title'));
    }
    $this->Page->archived = !($this->status === 'published');
    $this->Page->published_at = $this->published_at;
    // Search is good, let it happen
    $this->Page->view_is_secure = false;
    $this->Page->save();
  }

  // We were calling this from postSave but some subtlety of Doctrine forms
  // made it fail in fascinating ways - categories with null names popping into
  // existence was my favorite symptom, personally... now we call it after all the
  // excitement is over in the blog form update action. 
  public function updatePageTagsAndCategories()
  {
    $this->Page->setTags($this->getTags());
    $categories = $this->getCategories();
    $ids = aArray::getIds($categories);
    $this->Page->unlink('Categories');
    $this->Page->link('Categories', $ids);
    $this->Page->save();
  }

  /**
   * These date methods are used in the routing of the permalink
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
    return $this['slug'];
  }

  public function getTitle()
  {
    // See if the index action has already cached the associated page
    // (This is a big speedup)
    $slug = $this->getVirtualPageSlug();
    if (aTools::isPageCached($slug))
    {
      $titleSlot = aTools::getCachedPage($slug)->getSlot('title');
    }
    else
    {
      $titleSlot = $this->Page ? $this->Page->getSlot('title') : null;
    }
    if ($titleSlot)
    {
      $result = $titleSlot->value;
    }
    else
    {
      $result = $this['slug'];
    }
    $title = trim($result);
    if (!strlen($result))
    {
      // Don't break the UI, return something reasonable
      $slug = $this->slug;
      $title = substr(strrchr($slug, "/"), 1);
    }
    return $title;
  }
  
  public function getFeedTitle()
  {
    return html_entity_decode($this->getTitle(), ENT_COMPAT, 'UTF-8');
  }

  public function setTitle($value)
  {
    $this->_set('title', htmlentities($value, ENT_COMPAT, 'UTF-8'));
  }

  /**
   * Slot content convenience methods
   */

  /**
   * Gets text that should show up in an rss feed
   * @return <type>
   */
  public function getFeedText()
  {
    /**
     * Due to the design of the feed plugin we have to render a partial here even though
     * we are technically in the model layer. RSS needs templating and customizing like everything else
     * we present to the end user
     */
    
    sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
    return get_partial($this->engine . '/' . $this->template . '_rss', array(get_class($this) => $this));
  }

  public function getThumbnailMarkup()
  {
    $result = '';
    $images = $this->Page->getMediaForArea('blog-body', 'image', 1);
    $image = reset($images);
    if ($image)
    {
      $styles = htmlspecialchars(sfConfig::get('app_aBlog_feedThumbnailStyles', 'display: block; clear: left; float: left; width: 100px; margin: 10px;'));
      $width = sfConfig::get('app_aBlog_feedThumbnailWidth', 100);
      $height = sfConfig::get('app_aBlog_feedThumbnailHeight', false);
      $crop = sfConfig::get('app_aBlog_feedThumbnailCrop', 's');
      $url = $image->getImgSrcUrl($width, $height, $crop);
      $result .= <<<EOM
    <img style="$styles" 
      src="$url"
    />
EOM
;
     }
     return $result;
  }

  /**
   * Gets the text for the areas in this item
   * @param int $limit
   * @return string
   */
  public function getText($limit = null)
  {
    return $this->getTextForAreas($this->getAreas(), $limit);
  }

  /**
   *
   * @param string $area Name of an area
   * @param int $limit Number of characters to restrict retrieval to
   * @return string
   */
  public function getTextForArea($area, $limit = null, $options = null)
  {
    return $this->getTextForAreas(array($area), $limit, $options);
  }

  /**
   *
   * @param string $areas Array of areas to retrieve text for
   * @param int $limit Number of characters to restrict retrieval to
   * @return string
   */
  public function getTextForAreas($areas = array(), $limit = null, $options = null)
  {
    if (is_null($options))
    {
      // getText returns actual plaintext, not entity-escaped text, so we
      // should specify an actual ellipsis instead of the &hellip; entity
      // escape sequence.
      $options = array('append_ellipsis' => true, 'ellipsis' => '…');
    }
    $text = '';
    foreach($areas as $area)
    {
      foreach($this->Page->getArea($area) as $slot)
      {
        if(method_exists($slot, 'getText'))
        {
          $text .= $slot->getText();
        }
      }
    }
    if(!is_null($limit))
    {
      $text = aString::limitWords($text, $limit, $options);
    }

    return $text;
  }

  /**
   *
   * @param string $area Name of an area
   * @param int $limit Number of characters to restrict retrieval to
   * @return string
   */
  public function getRichTextForArea($area, $limit = null, $types = array())
  {
    return $this->getRichTextForAreas(array($area), $limit, $types);
  }

  /**
   *
   * @param string $areas Array of areas to retrieve text for
   * @param int $limit Number of characters to restrict retrieval to
   * @return string
   */
  public function getRichTextForAreas($areas = array(), $limit = null)
  {
		
    $text = '';
		
		if(!is_array($areas))
			$areas = array($areas);

    foreach($areas as $area)
    {
			$text .= $this->Page->getAreaBasicHtml($area);
		}
    if(!is_null($limit))
    {
      $text = aHtml::limitWords($text, $limit, array('append_ellipsis' => true));
    }

    return $text;
  }
  
  /**
   * Returns media for all areas that are specified in app.yml as being valid for
	 * this blog template. 
   * @param string $type Kind of media to select from (image, video, pdf)
   * @param int $limit
   * @return Array aMediaItem
   */
  public function getMedia($type = 'image', $limit = 5)
  {
    return $this->getMediaForAreas($this->getAreas(), $type, $limit);
  }

  /**
   * Checks if this item hasMedia
   * @param string $type Kind of media to select from (image, video, pdf)
   * @return bool
   */
  public function hasMedia($type = 'image', $areas = array())
  {
    if(count($areas))
    {
      return count($this->getMediaForAreas($areas, $type, 1));
    }
    else
    {
      return count($this->getMedia($type, 1));
    }
  }

  /**
   * Returns media for a given area attached to this items page.
   * @param string $area
   * @param string $type Kind of media to select from (image, video, pdf)
   * @param int $limit
   * @return Array aMediaItem
   */
  public function getMediaForArea($area, $type = 'image', $limit = 5)
  {
    return $this->getMediaForAreas(array($area), $type, $limit);
  }

  /**
   * Given an array of areas this function returns the mediaItems in those areas.
   * @param  aArea $areas
   * @param  $type Set the type of media to return (image, video, pdf, etc...)
   * @param  $limit Limit the number of mediaItems returned
   * @return array aMediaItems
   */
  public function getMediaForAreas($areas, $type = 'image', $limit = 5)
  {
    return $this->Page->getMediaForAreas($areas, $type, $limit);
  }

  /**
   * Gets the areas for this item as defined in app.yml
   * @return array $areas
   */
  public function getAreas()
  {
    $templates = sfConfig::get('app_'.$this->engine.'_templates', $this->getTemplateDefaults());
    return $templates[$this['template']]['areas'];
  }

  public function getTemplateDefaults()
  {
    return $this->getTable()->getTemplateDefaults();
  }

  /**
   * Publishes a blog post or event if user has permission
   */
  public function publish()
  {
    
    if($this->userHasPrivilege('publish'))
    {
      $this['status'] = 'published';
      if(is_null($this['published_at']))
      {
        $this['published_at'] = date('Y-m-d H:i:s');
      }
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
   * Permission methods
   */


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

    if(!$user->isAuthenticated())
      return false;
    
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
   * Checks if a user can edit this post
   * @param sfGuardUser $user
   * @return <type>
   */
  public function userCanEdit(sfGuardUser $user)
  {
    $q = $this->getTable()->createQuery()
      ->addWhere('id = ?', $this['id']);
    Doctrine::getTable('aBlogItem')->filterByEditable($q, $user['id']);
    return count($q->execute());
  }

  /**
   * This function attempts to find the "best" engine to route a given person to.
   * based on the categories that are used on various engine pages.
   *
   * @return aPage the best engine page
   */
  public function findBestEngine()
  {
    $page = Doctrine::getTable('aPage')->findOneBy('slug', $this->getEngineSlug());
    return $page;
  }

  public function getEngineSlug()
  {    
    if (!isset($this->engineSlug))
    {
      $this->engineSlug = aEngineTools::getEngineSlug($this);
    }

    return $this->engineSlug;
  }


  public function getRoutingParams($bestEngine = true)
  {
    $params = array('year' => $this->getYear(), 'month' => $this->getMonth(), 'day' => $this->getDay(), 'slug' => $this->getSlug());
    if($bestEngine)
      $params['engine-slug'] = $this->getEngineSlug();

    return $params;
  }
  
  // This allows sfFeed2Plugin to provide valid publication dates
  public function getPubdate()
  {
    return aDate::normalize($this->published_at);
    return $this->published_at;
  }
  
  public function getTemplate()
  {
    $template = $this->_get('template');
    $defaults = $this->getTemplateDefaults();
    $templates = sfConfig::get('app_'.$this->engine.'_templates', $defaults);
    // If the template is no longer valid, return the first template in the hardcoded
    // set of default templates (which are guaranteed to exist because they are in the plugin)
    if (!isset($templates[$template]))
    {
      foreach ($defaults as $key => $value)
      {
        return $key;
      }
    }
    return $template;
  }
}
