<ul class="dd-list">
	<?php foreach ($terms as $key => $term) { ?>
	<li class="dd-item" data-id="<?= $term->id; ?>">
		<!-- drag handle -->
		<div class="handle dd-handle">
			<?= $term->id; ?>
			<i class="fa fa-ellipsis-v"></i>
			<!-- checkbox -->
			<!-- todo text -->
			<span class="text"><?= $term->name; ?> <i class="fa fa-ellipsis-v"></i> <?= $term->slug; ?></span>
			<!-- Emphasis label -->
			<!-- General tools such as edit or delete-->
			<div class="pull-right">
				<div class="btn-group">
					<form method="GET" action="<?= site_url('admin/taxonomy/' .$vocabulary->id .'/terms/' .$term->id .'/edit') ?>">
						<input type="submit" class='btn btn-xs btn-primary' title="<?= __d('taxonomy', 'Edit this Term') ?>" role="button" value="<?= __d('taxonomy', 'Edit'); ?>" />
					</form>
				</div>
				<div class="btn-group">
					<form id="delete-term-form-<?= $term->id; ?>" method="POST" action="<?= site_url('admin/taxonomy/' .$vocabulary->id .'/terms/' .$term->id .'/destroy') ?>">
						<input type="hidden" name="_token" value="<?= csrf_token(); ?>" />
						<input type="submit" class='delete-confirm-dialog btn btn-xs btn-danger' data-id="<?= $term->id; ?>" title="<?= __d('taxonomy', 'Delete this Term') ?>" role="button" value="<?= __d('taxonomy', 'Delete'); ?>" />
					</form>
				</div>
			</div>
		</div>

		<?php $children = $term->children()->get(); ?>
		<?php if (! $children->isEmpty()) { ?>
		<?= View::fetch('Taxonomy::Partials/TermsNestable', array('vocabulary' => $vocabulary, 'terms' => $children)); ?>
		<?php } ?>
	</li>
	<?php } ?>
</ul>
