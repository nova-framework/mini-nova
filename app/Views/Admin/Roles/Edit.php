<?php

//
$opt_name = Input::old('name');
$opt_slug = Input::old('slug');
$opt_desc = Input::old('description');

//
$opt_name = ! empty($opt_name) ? $opt_name : $role->name;
$opt_slug = ! empty($opt_slug) ? $opt_slug : $role->slug;
$opt_desc = ! empty($opt_desc) ? $opt_desc : $role->description;

?>

<div class="row">
	<h1><?= __('Edit Role'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __('Dashboard'); ?></a></li>
		<li><a href='<?= site_url('admin/roles'); ?>'><?= __('Roles'); ?></a></li>
		<li><?= __('Edit Role'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h4 class="box-title"><?= __('Edit the Role : <b>{0}</b>', $role->name); ?></h4>
	<hr>

	<div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
		<form action="<?= site_url('admin/roles/' .$role->id); ?>" class="form-horizontal" method='POST' role="form">

		<div class="form-group">
			<label class="col-sm-4 control-label" for="name"><?= __('Name'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="name" id="name" type="text" class="form-control" value="<?= $opt_name; ?>" placeholder="<?= __('Name'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="slug"><?= __('Slug'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="slug" id="slug" type="text" class="form-control" value="<?= $opt_slug; ?>" placeholder="<?= __('Slug'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="description"><?= __('Description'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="description" id="description" type="text" class="form-control" value="<?= $opt_desc; ?>" placeholder="<?= __('Description'); ?>">
			</div>
		</div>
		<div class="clearfix"></div>
		<br>
		<font color='#CC0000'>*</font><?= __('Required field'); ?>
		<hr>
		<div class="form-group">
			<div class="col-sm-12">
				<input type="submit" name="submit" class="btn btn-success col-sm-3 pull-right" value="<?= __('Save'); ?>">
			</div>
		</div>

		<input type="hidden" name="_token" value="<?= csrf_token(); ?>" />
		<input type="hidden" name="userId" value="<?= $role->id; ?>" />

		</form>
	</div>
</div>

<div class="row">
	<hr>
	<a class='btn btn-primary' href='<?= site_url('admin/roles'); ?>'><?= __('<< Previous Page'); ?></a>
	<br>
</div>
