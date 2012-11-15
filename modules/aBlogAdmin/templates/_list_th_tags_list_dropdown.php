<?php
  // Compatible with sf_escaping_strategy: true
  $filters = isset($filters) ? $sf_data->getRaw('filters') : null;
?>
<?php //Popular tags will go here eventually ?>
<?php $letter = ''; ?>
<?php $choices = $filters['tags_list']->getWidget()->getChoices() ?>
<?php $n = 0; ?>
<?php foreach($choices as $id => $choice): ?>
<?php   if(strtoupper($choice[0]) == $letter): ?>,<?php   else: ?>
<?php     if(strtoupper($choice[0]) != 'A'): ?></span><?php endif ?>
<?php   $letter = strtoupper($choice[0]) ?>
<span<?php echo ($n == 0)? ' class="first"':'' ?><?php echo ($n == count($choices))? ' class="last"':'' ?>>
  <b><?php echo $letter ?></b>
<?php endif ?>
<?php echo link_to($choice, 'a_blog_admin_addFilter', array('name' => 'tags_list', 'value' => $id), array('post' => true)) ?>
<?php $n++; endforeach ?>
