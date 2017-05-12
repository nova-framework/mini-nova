<div class="row">
	<h1><?= __('Show User'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __('Dashboard'); ?></a></li>
		<li><a href='<?= site_url('admin/users'); ?>'><?= __('Users'); ?></a></li>
		<li><?= __('Show User'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h4><?= __('User Account : <b>{0}</b>', $user->username); ?></h4>

	<table class='table table-bordered table-hover table-hover responsive'>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('ID'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='70%'><?= $user->id; ?></td>
		<tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Username'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $user->username; ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Role'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $user->role->name; ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Name and Surname'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $user->first_name; ?> <?= $user->last_name; ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('E-mail'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $user->email; ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Created At'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $user->created_at->formatLocalized('%d %b %Y, %H:%M'); ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Updated At'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $user->updated_at->formatLocalized('%d %b %Y, %H:%M'); ?></td>
		<tr>
	</table>
</div>

<div class="row">
	<a class='btn btn-primary' href='<?= site_url('admin/users'); ?>'><?= __('<< Previous Page'); ?></a>
	<br>
</div>
