<div class="row">
	<h1><?= __d('backend', 'Notifications'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __d('backend', 'Dashboard'); ?></a></li>
		<li><?= __d('backend', 'Notifications'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<?php if (! $notifications->isEmpty()) { ?>

	<table class='table table-striped table-hover responsive'>
		<tr class="bg-navy disabled">
			<th style='text-align: center; vertical-align: middle;'><?= __d('backend', 'Sent At'); ?></th>
			<th style='text-align: center; vertical-align: middle;'><?= __d('backend', 'Subject'); ?></th>
			<th style='text-align: center; vertical-align: middle;'><?= __d('backend', 'Message'); ?></th>
		</tr>
		<?php foreach ($notifications->all() as $item) { ?>
		<tr>
			<td style="text-align: center; vertical-align: middle;" width="15%"><?= $item->created_at->formatLocalized('%d %b %Y, %H:%M'); ?></td>
			<td style="text-align: center; vertical-align: middle;" width='30%'><?= $item->subject; ?></td>
			<td style="text-align: left; vertical-align: middle;" width="55%"><?= $item->body; ?></td>
		</tr>
		<?php } ?>
	</table>

	<?php } else { ?>

	<div class="alert alert-info">
		<h4><?= __d('backend', 'No unread notifications'); ?></h4>
		<p><?= __d('backend', 'You have no unread notifications.'); ?></p>
	</div>

	<?php } ?>
</div>
