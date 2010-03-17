<?php use_helper('I18N', 'Date', 'jQuery', 'a') ?>
<?php include_partial('aBlogAdmin/assets') ?>

<div id="a-admin-container" class="<?php echo $sf_params->get('module') ?>">
  <?php include_partial('aBlogAdmin/form_bar', array('title' => __('Edit ABlogAdmin', array(), 'messages'))) ?>

<?php slot('a-subnav') ?>
  <div id="a-subnav" class="blog">
    <div id="a-subnav-top" class="a-subnav-top"></div>
      <div class="a-subnav-wrapper">
        <ul class="a-admin-action-controls">
          <li><?php echo link_to('Edit Categories', '@a_blog_category_admin') ?></li>
          <li><?php echo link_to('Edit Posts', '@a_blog_admin') ?></li>
          <li><?php echo link_to('New  Posts', '@a_blog_admin_new') ?></li>
          <li><?php echo link_to('Edit Categories', '@a_blog_category_admin') ?></li>
          <li><?php echo link_to('Edit Comments', '@a_comment_admin') ?></li>
        </ul>
      </div> 
    <div id="a-subnav-bottom" class="a-subnav-bottom"></div>
  </div>
<?php end_slot() ?>
  
  <?php include_partial('aBlogAdmin/flashes') ?>
	
<?php echo form_tag_for($form, '@a_blog_admin', array('id'=>'a-admin-form')) ?>  
	<div id="a-admin-content" style="float:left" class="main">
	
	<h2>Title</h2>
  <p><?php echo $form['title']->renderRow() ?></p>
	<br>
  <?php a_area('body', array(
  'editable' => false, 'toolbar' => 'basic', 'slug' => 'aBlogPost-'.$a_blog_post['id'],
  'allowed_types' => array('aRichText', 'aImage', 'aButton', 'aSlideshow', 'aVideo', 'aPDF'),
  'type_options' => array(
    'aRichText' => array('tool' => 'Main'),   
    'aImage' => array('width' => 400, 'flexHeight' => true, 'resizeType' => 's'),
    'aButton' => array('width' => 400, 'flexHeight' => true, 'resizeType' => 's'),
    'aSlideshow' => array("width" => 400, "flexHeight" => true, 'resizeType' => 's', ),
    'aPDF' => array('width' => 400, 'flexHeight' => true, 'resizeType' => 's'),   
  ))
  ) ?>
  </div>
  
  
  <div id="a-admin-right-subnav" style="float:left" class="right-subnav">
    <?php include_partial('aBlogAdmin/optionsForm', array('a_blog_post' => $a_blog_post, 'form' => $form)) ?>
  </div>
  
  <div id="a-admin-footer">
    <?php include_partial('aBlogAdmin/form_footer', array('a_blog_post' => $a_blog_post, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>
  
<?php include_partial('aBlogAdmin/form_actions', array('a_blog_post' => $a_blog_post, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
</div>
