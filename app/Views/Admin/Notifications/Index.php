<div class="row">
	<h1><?= __('Notifications'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __('Dashboard'); ?></a></li>
		<li><?= __('Notifications'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<?php if (! $notifications->isEmpty()) { ?>

	<table class='table table-striped table-hover responsive'>
		<tr class="bg-navy disabled">
			<th style='text-align: center; vertical-align: middle;'><?= __('Sent At'); ?></th>
			<th style='text-align: center; vertical-align: middle;'><?= __('Subject'); ?></th>
			<th style='text-align: center; vertical-align: middle;'><?= __('Message'); ?></th>
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
		<h4><?= __('No unread notifications'); ?></h4>
		<p><?= __('You have no unread notifications.'); ?></p>
	</div>

	<?php } ?>
</div>
