<div class="row">
	<h1><?= __d('taxonomy', 'Create Vocabulary'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><i class="fa fa-dashboard"></i> <?= __d('taxonomy', 'Dashboard'); ?></a></li>
		<li><?= __d('taxonomy', 'Create Vocabulary'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h3><?= __d('taxonomy', 'Create a new Vocabulary'); ?></h3>
	<hr>

	<div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
		<form class="form-horizontal" action="<?= site_url('admin/taxonomy'); ?>" method='POST' enctype="multipart/form-data" role="form">

		<div class="form-group">
			<label class="col-sm-4 control-label" for="name"><?= __d('taxonomy', 'Name'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="name" id="name" type="text" class="form-control" value="<?= Input::old('name'); ?>" placeholder="<?= __d('taxonomy', 'Name'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="slug"><?= __d('taxonomy', 'Slug'); ?></label>
			<div class="col-sm-8">
				<input name="slug" id="slug" type="text" class="form-control" value="<?= Input::old('slug'); ?>" placeholder="<?= __d('taxonomy', 'Slug'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="description"><?= __d('taxonomy', 'Description'); ?></label>
			<div class="col-sm-8">
				<textarea name="description" id="description" class="form-control" placeholder="<?= __d('taxonomy', 'Description'); ?>"><?= Input::old('description'); ?></textarea>
			</div>
		</div>
		<div class="clearfix"></div>
		<br>

		<font color='#CC0000'>*</font><?= __d('taxonomy', 'Required field'); ?>
		<hr>

		<div class="form-group">
			<div class="col-sm-12">
				<input type="submit" name="submit" class="btn btn-success col-sm-3 pull-right" value="<?= __d('taxonomy', 'Save'); ?>">
			</div>
		</div>

		<input type="hidden" name="_token" value="<?= csrf_token(); ?>" />

		</form>
	</div>
</div>

<div class="row">
	<hr>
	<a class='btn btn-primary' href='<?= site_url('admin/taxonomy'); ?>'><?= __d('taxonomy', '<< Previous Page'); ?></a>
</div>


