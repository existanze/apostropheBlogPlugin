<h3 class="a-blog-item-title"><?php echo link_to($aEvent['title'], 'a_event', $aEvent) ?></h3>

<ul class="a-blog-item-meta">
  <li class="day"><?php echo date('l', strtotime($aEvent->getPublishedAt())) ?></li>
  <li class="date"><?php echo date('F jS Y', strtotime($aEvent->getPublishedAt())) ?></li>
  <li class="author"><?php echo __('Posted By:', array(), 'apostrophe_blog') ?> <?php echo $aEvent->getAuthor() ?></li>   
</ul>

<div class="a-blog-item-excerpt">
<?php echo $aEvent->getTextForArea('blog-body', $options['excerptLength']) ?>
</div>

<?php if($options['maxImages'] > 0): ?>
<div class="a-blog-item-media">
<?php include_component('aSlideshowSlot', 'slideshow', array(
  'items' => $aEvent->getMediaForArea('blog-body', 'image', $options['maxImages']),
  'id' => 'test',
  'options' => $options['slideshowOptions']
  )) ?>
</div>
<?php endif ?>