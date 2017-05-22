<div class="row">
	<h1><?= __d('backend', 'Dashboard'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __d('backend', 'Dashboard'); ?></a></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h3><?= __d('backend', 'Users online'); ?></h3>
	<table id='usersTable' class='table table-striped table-hover responsive'>
	<tr>
		<th class="text-center"><?= __d('backend', 'ID'); ?></th>
		<th class="text-center"><?= __d('backend', 'Username'); ?></th>
		<th class="text-center"><?= __d('backend', 'Role'); ?></th>
		<th class="text-center"><?= __d('backend', 'Name'); ?></th>
		<th class="text-center"><?= __d('backend', 'Email'); ?></th>
		<th class="text-center"><?= __d('backend', 'Last activity'); ?></th>
	</tr>
<?php foreach ($onlineUsers as $online) { ?>
	<?php $user = $online->user; $user->load('role'); ?>
	<tr>
		<td width='5%' class="text-center"><?= $user->id; ?></td>
		<td width='20%' class="text-center"><?= $user->username; ?></td>
		<td width='15%' class="text-center"><?= $user->role->name; ?></td>
		<td width='20%' class="text-center"><?= trim($user->first_name .' ' .$user->last_name); ?></td>
		<td width='25%' class="text-center"><?= $user->email; ?></td>
		<td width='15%' class="text-center"><?=  $online->last_activity->formatLocalized(__d('backend', '%d %b %Y, %H:%M')); ?>
	</tr>

<?php } ?>

</table>

<?= $debug; ?>

</div>
