<?php
  // Compatible with sf_escaping_strategy: true
  $categories = isset($categories) ? $sf_data->getRaw('categories') : null;
  $n = isset($n) ? $sf_data->getRaw('n') : null;
  $noFeed = isset($noFeed) ? $sf_data->getRaw('noFeed') : null;
  $params = isset($params) ? $sf_data->getRaw('params') : null;
  $popular = isset($popular) ? $sf_data->getRaw('popular') : null;
  $tag = isset($tag) ? $sf_data->getRaw('tag') : null;
  $tags = isset($tags) ? $sf_data->getRaw('tags') : null;
	$selected = array('icon','a-selected');
?>

<?php if (aBlogItemTable::userCanPost()): ?>
	<div class="a-ui clearfix a-subnav-section">
	  <?php echo a_js_button(a_('New Post'), array('big', 'a-add', 'a-blog-new-post-button', 'a-sidebar-button'), 'a-blog-new-post-button') ?>
    <div class="a-options a-blog-admin-new-ajax dropshadow">
      <?php include_component('aBlogAdmin', 'newPost') ?>
    </div>
	</div>
<?php endif ?>

<div class="a-subnav-section search">
  <div class="a-search a-search-sidebar blog">
    <form action="<?php echo url_for('aBlog/search') ?>" method="get">
  		<div class="a-form-row"> <?php // div is for page validation ?>
  			<label for="a-search-blog-field" style="display:none;">Search</label><?php // label for accessibility ?>
  			<input type="text" name="q" value="<?php echo htmlspecialchars($sf_params->get('q', ESC_RAW)) ?>" class="a-search-field" id="a-search-blog-field"/>
  			<input type="image" src="<?php echo image_path('/apostrophePlugin/images/a-special-blank.gif') ?>" class="submit a-search-submit" value="Search Pages" alt="Search" title="Search"/>
  		</div>
    </form>
  </div>
</div>

<?php if(count($categories)): ?>
<div class="a-subnav-section categories">
  <h4>Categories</h4>
  <div class="a-filter-options blog clearfix">
	  <?php foreach ($categories as $category): ?>
	    <div class="a-filter-option">
				<?php $selected_category = ($category->getName() == $sf_params->get('cat')) ? $selected : array() ?>
				<?php echo a_button($category, url_for(($sf_params->get('cat') == $category->getName()) ? 'aBlog/index' : 'aBlog/index?cat='.$category->getName()), array_merge(array('a-link'),$selected_category)) ?>
			</div>
	  <?php endforeach ?>
  </div>	
</div>

<hr class="a-hr" />
<?php endif ?>

<div class='a-subnav-section range'>
  <h4>Browse by</h4>
  <div class="a-filter-options blog clearfix">
    <div class="a-filter-option">
			<?php $selected_day = ($dateRange == 'day') ? $selected : array() ?>
			<?php echo a_button('Day', url_for('aBlog/index?'.http_build_query(($dateRange == 'day') ? $params['nodate'] : $params['day'])), array_merge(array('a-link'),$selected_day)) ?>
		</div>
    <div class="a-filter-option">
			<?php $selected_month = ($dateRange == 'month') ? $selected : array() ?>
			<?php echo a_button('Month', url_for('aBlog/index?'.http_build_query(($dateRange == 'month') ? $params['nodate'] : $params['month'])), array_merge(array('a-link'),$selected_month)) ?>
		</div>
    <div class="a-filter-option">
			<?php $selected_year = ($dateRange == 'year') ? $selected : array() ?>
			<?php echo a_button('Year', url_for('aBlog/index?'.http_build_query(($dateRange == 'year') ? $params['nodate'] : $params['year'])), array_merge(array('a-link'),$selected_year)) ?>
		</div>
  </div>
</div>

<hr class="a-hr" />

<?php if(count($tags)): ?>
<div class="a-subnav-section tags">  

	<?php if (isset($tag)): ?>
	<h4 class="a-tag-sidebar-title selected-tag">Selected Tag</h4>  
	<div class="a-blog-selected-tag">
		<?php echo a_button($tag, url_for('aBlog/index', $params['tag']), array('a-link','icon','a-selected')) ?>
  </div>
	<?php endif ?>
  
  
	<h4 class="a-tag-sidebar-title popular">Popular Tags</h4>  			
	<ul class="a-ui a-tag-sidebar-list popular">
		<?php $n=1; foreach ($popular as $tag => $count): ?>
		  <li <?php echo ($n == count($popular) ? 'class="last"':'') ?>>
				<?php echo a_button('<span class="a-tag-count">'.$count.'</span>'.$tag, url_for('aBlog/index?tag='.$tag, $params['tag']), array('a-link','a-tag')) ?>
			</li>
		<?php $n++; endforeach ?>
	</ul>

	<br class="c"/>
	<h4 class="a-tag-sidebar-title all-tags">All Tags <span class="a-tag-sidebar-tag-count"><?php echo count($tags) ?></span></h4>
	<ul class="a-ui a-tag-sidebar-list all-tags">
		<?php $n=1; foreach ($tags as $tag => $count): ?>
		  <li <?php echo ($n == count($tags) ? 'class="last"':'') ?>>
				<?php echo a_button('<span class="a-tag-count">'.$count.'</span>'.$tag, url_for('aBlog/index?tag='.$tag), array('a-link','a-tag')) ?>
			</li>
		<?php $n++; endforeach ?>
	</ul>
	
</div>
<?php endif ?>

<?php if(!isset($noFeed)): ?>
	<hr class="a-hr" />
	<ul class="a-ui a-controls stacked">
  <?php $full = url_for('aBlog/index?feed=rss') ?>
  <?php $filtered = url_for(aUrl::addParams('aBlog/index?feed=rss', $params['tag'], $params['cat'])) ?>
  <?php if ($full === $filtered): ?>
    <li><?php echo a_button(a_('RSS Feed'), $full, array('icon','a-rss-feed', 'no-bg', 'alt')) ?></li>
  <?php else: ?>
    <li><?php echo a_button(a_('Full Feed'), $full, array('icon','a-rss-feed','no-bg', 'alt')) ?></li>
    <li><?php echo a_button(a_('Filtered Feed'), $filtered, array('icon','a-rss-feed','no-bg', 'alt')) ?></li>
  <?php endif ?>
	</ul>
<?php endif ?>

<?php a_js_call('aBlog.sidebarEnhancements(?)', array()) ?>
<?php a_js_call('apostrophe.selfLabel(?)', array('selector' => '#a-search-blog-field', 'title' => a_('Search'), 'focus' => false )) ?>