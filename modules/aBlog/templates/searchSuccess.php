<?php
  // Compatible with sf_escaping_strategy: true
  $pager = isset($pager) ? $sf_data->getRaw('pager') : null;
  $pagerUrl = isset($pagerUrl) ? $sf_data->getRaw('pagerUrl') : null;
  $results = isset($results) ? $sf_data->getRaw('results') : null;
  // Compatible with sf_escaping_strategy: true
  $blogCategories = isset($blogCategories) ? $sf_data->getRaw('blogCategories') : null;
?>
<?php use_helper('a') ?>
<?php slot('body_class') ?>a-search-results<?php end_slot() ?>

<?php slot('a-subnav') ?>
	<div class="a-subnav-wrapper blog">
		<div class="a-subnav-inner">
	    <?php include_component('aBlog', 'sidebar', array('params' => $params, 'dateRange' => $dateRange, 'categories' => $blogCategories)) ?>
	  </div> 
	</div>
<?php end_slot() ?>

<div class="a-search-results-container">

	<h2><?php echo __('Search: "%phrase%"', array('%phrase%' =>  htmlspecialchars($sf_request->getParameter('q', ESC_RAW))), 'apostrophe') ?></h2>
	
	<h4 class="a-search-results-count">
		<?php if (!$pager->getNbResults()): ?>
			No results were found.
		<?php endif ?>
		<?php if ($pager->getNbResults() == 1): ?>
			1 result was found.
		<?php endif ?>
		<?php if ($pager->getNbResults() > 1): ?>
			<?php echo $pager->getNbResults() ?> results were found.
		<?php endif ?>
	</h4>	
	
	<dl class="a-search-results">
	<?php foreach ($results as $result): ?>
	  <?php $url = $result->url ?>
	  <dt class="result-title <?php echo $result->class ?>">
			<?php echo link_to($result->title, $url) ?>
		</dt>
	  <dd class="result-summary"><?php echo $result->summary ?></dd>
		<dd class="result-url"><?php echo link_to($url,$url) ?></dd>
	<?php endforeach ?>
	</dl>

	<div class="a-search-footer">
	  <?php include_partial('aPager/pager', array('pager' => $pager, 'pagerUrl' => $pagerUrl)) ?>
	</div>

</div>