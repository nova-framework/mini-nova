<div class="row">
	<h1><?= __('Show Role'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'> <?= __('Dashboard'); ?></a></li>
		<li><a href='<?= site_url('admin/roles'); ?>'><?= __('Roles'); ?></a></li>
		<li><?= __('Show Role'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h4><?= __('User Role : <b>{0}</b>', $role->name); ?></h4>

	<table class='table table-bordered table-hover responsive'>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('ID'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='70%'><?= $role->id; ?></td>
		<tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Name'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $role->name; ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Slug'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $role->slug; ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Description'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $role->description; ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Created At'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $role->created_at->formatLocalized('%d %b %Y, %H:%M'); ?></td>
		</tr>
		<tr>
			<th style='text-align: left; vertical-align: middle;'><?= __('Updated At'); ?></th>
			<td style='text-align: left; vertical-align: middle;' width='75%'><?= $role->updated_at->formatLocalized('%d %b %Y, %H:%M'); ?></td>
		<tr>
	</table>
</div>

<div class="row">
	<a class='btn btn-primary' href='<?= site_url('admin/roles'); ?>'><?= __('<< Previous Page'); ?></a>
	<br>
</div>
