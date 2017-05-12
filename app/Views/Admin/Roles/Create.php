<div class="row">
	<h1><?= __('Create Role'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __('Dashboard'); ?></a></li>
		<li><a href='<?= site_url('admin/roles'); ?>'><?= __('Roles'); ?></a></li>
		<li><?= __('Create Role'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h4 class="box-title"><?= __('Create a new User Role'); ?></h4>
	<hr>

	<div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
		<form class="form-horizontal" action="<?= site_url('admin/roles'); ?>" method='POST' role="form">

		<div class="form-group">
			<label class="col-sm-4 control-label" for="name"><?= __('Name'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="name" id="name" type="text" class="form-control" value="<?= Input::old('name'); ?>" placeholder="<?= __('Name'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="slug"><?= __('Slug'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="slug" id="slug" type="text" class="form-control" value="<?= Input::old('slug'); ?>" placeholder="<?= __('Slug'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="description"><?= __('Description'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="description" id="description" type="text" class="form-control" value="<?= Input::old('description'); ?>" placeholder="<?= __('Description'); ?>">
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

		</form>
	</div>
</div>

<div class="row">
	<hr>
	<a class='btn btn-primary' href='<?= site_url('admin/roles'); ?>'><?= __('<< Previous Page'); ?></a>
	<br>
</div>
