<?php echo link_to($aEvent['title'], 'a_event_post', $aEvent) ?> by <?php echo $aEvent->Author ?>
<br/>
<?php echo $aEvent['published_at'] ?>
<br/><br/>
<?php foreach($aEvent->Page->getArea('blog-post-body') as $slot): ?>
<?php if(method_exists($slot, 'getSearchText')): ?>
<?php echo $slot->getSearchText() ?>
<?php endif ?>
<?php endforeach ?>
