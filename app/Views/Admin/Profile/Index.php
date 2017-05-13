<div class="row">
	<h1><?= __('User Profile : <b>{0}</b>', $user->username); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('users/dashboard'); ?>'><?= __('Dashboard'); ?></a></li>
		<li><a href='<?= site_url('admin/users'); ?>'><?= __('Users'); ?></a></li>
		<li><?= __('User Profile'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h4 class="box-title"><?= __('Change Password'); ?></h4>
	<hr>

	<div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
		<form method='post' role="form">

		<div class="form-group">
			<label class="col-sm-4 control-label" for="name"><?= __('Current Password'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="current_password" id="current_password" type="password" class="form-control" value="" placeholder="<?= __('Insert the current Password'); ?>">
			</div>
		</div>
		<div class="clearfix"></div>
		<br>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="name"><?= __('New Password'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="password" id="password" type="password" class="form-control" value="" placeholder="<?= __('Insert the new Password'); ?>">
			</div>
		</div>
		<div class="clearfix"></div>
		<br>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="name"><?= __('Confirm Password'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="password_confirmation" id="password_confirmation" type="password" class="form-control" value="" placeholder="<?= __('Verify the new Password'); ?>">
			</div>
		</div>
		<div class="clearfix"></div>
		<br>
		<font color='#CC0000'>*</font><?= __('Required field'); ?>
		<hr>
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12">
				<input type="submit" name="submit" class="btn btn-success col-sm-3 pull-right" value="<?= __('Save'); ?>">
			</div>
		</div>

		<input type="hidden" name="_token" value="<?= csrf_token(); ?>" />

		</form>
	</div>
</div>
