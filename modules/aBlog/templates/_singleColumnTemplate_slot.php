<?php if($options['maxImages'] > 0): ?>
<div class="a-blog-item-media">
<?php include_component('aSlideshowSlot', 'slideshow', array(
  'items' => $aBlogPost->getMediaForArea('blog-body', 'image', $options['maxImages']),
  'id' => 'test',
  'options' => $options['slideshowOptions']
  )) ?>
</div>
<?php endif ?>
<?php //echo $aBlogPost->hasMedia() ?>
<div class="a-blog-item-excerpt">
<?php echo $aBlogPost->getTextForArea('blog-body', $options['excerptLength']) ?>
</div>

<?php echo sfConfig::get('app_add_this') ?>