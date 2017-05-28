<div class="row">
	<h1><?= $title; ?></h1>
	<hr>
</div>

<div class="row">
	<?php if (! $terms->isEmpty()) { ?>
	<?php foreach ($terms->all() as $term) { ?>
	<h3><a href="<?= site_url($vocabulary->slug .'/' .$term->slug); ?>"><?= $term->name; ?></a></h3>
	<p><?= $term->description; ?></p>
	<p class="text-muted"><?= __d('taxonomy', '{0} children, {1} relationships', $term->children->count(), $term->relations->count()); ?></p>
	<br>
	<?php } ?>
	<?php } else { ?>
	<div class="alert alert-info">
		<h4><?= __d('taxonomy', 'No terms'); ?></h4>
		<p><?= __d('taxonomy', 'You have no terms.'); ?></p>
	</div>
	<?php } ?>
</div>

<div class="row">
	<div class="pull-right">
		<?= $terms->links(); ?>
	</div>
	<div class="clearfix"></div>
	<br>
</div>
