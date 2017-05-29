<div class="row">
	<h1><?= $title; ?></h1>
	<ol class="breadcrumb">
		<li><a href="<?= site_url('/'); ?>"><?= __d('taxonomy', 'Homepage'); ?></a></li>
		<li><a href="<?= site_url($vocabulary->slug); ?>"><?= $vocabulary->name; ?></a></li>
		<li><a href="<?= site_url($term->slug); ?>"><?= $term->name; ?></a></li>
	</ol>
</div>

<div class="row">
	<?php if (! $term->children->isEmpty()) { ?>
	<h3><?= __d('taxonomy', 'Child Terms'); ?></h3>
	<hr>
	<?php foreach ($term->children as $child) { ?>
	<h4><strong><a href="<?= site_url($vocabulary->slug .'/' .$child->slug); ?>"><?= $child->name; ?></a></strong></h4>
	<p><?= $child->description; ?></p>
	<p class="text-muted"><?= __d('taxonomy', '{0} children, {1} relationships', $child->children->count(), $child->relations->count()); ?></p>
	<br>
	<?php } ?>
	<?php } ?>
</div>

<div class="row">
	<h3><?= __d('taxonomy', 'Relationships'); ?></h3>
	<hr>

	<?php if (! $term->relations->isEmpty()) { ?>

	<?php } else { ?>
	<p><?= __d('taxonomy', 'The term <b>{0}</b> have no relationships.', $term->name); ?></p>
	<?php } ?>
</div>
