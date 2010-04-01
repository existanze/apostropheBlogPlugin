<div class="a-subnav-wrapper blog">
	
	<div class="a-subnav-inner">
		<?php include_component('aBlogSlot', 'tagSidebar', array('params' => $params, 'dateRange' => $dateRange)) ?>
	</div>		
	
</div>

<div class="a-blog-main">
  <?php if ($a_blog_post): ?>
    <?php echo include_partial('aBlogSlot/post', array('a_blog_post' => $a_blog_post)); ?>
  <?php else: ?>
    <?php echo include_partial('aBlogSlot/list', array('a_blog_posts' => $a_blog_posts, 'params' => $params)); ?>
  <?php endif ?>
</div>