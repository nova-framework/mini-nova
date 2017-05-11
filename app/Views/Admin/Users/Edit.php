<?php

//
$opt_role = Input::old('role', $user->role_id);

?>

<div class="row">
	<h1><?= __('Edit User'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __('Dashboard'); ?></a></li>
		<li><a href='<?= site_url('admin/users'); ?>'><?= __('Users'); ?></a></li>
		<li><?= __('Edit User'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h4><?= __('Edit the User Account : <b>{0}</b>', $user->username); ?></h4>
	<hr>

	<div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
		<form action="<?= site_url('admin/users/' .$user->id); ?>" class="form-horizontal" method='POST' enctype="multipart/form-data" role="form">

		<div class="form-group">
			<label class="col-sm-4 control-label" for="username"><?= __('Username'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="username" id="username" type="text" class="form-control" value="<?= Input::old('username',   $user->username); ?>" placeholder="<?= __('Username'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="role"><?= __('Role'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<select name="role" id="role" class="form-control select2">
					<?php foreach ($roles as $role) { ?>
					<option value="<?= $role->id ?>" <?php if ($opt_role == $role->id) echo 'selected'; ?>><?= $role->name; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="first_name"><?= __('First Name'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="first_name" id="first_name" type="text" class="form-control" value="<?= Input::old('first_name', $user->first_name); ?>" placeholder="<?= __('First Name'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="last_name"><?= __('Last Name'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="last_name" id="last_name" type="text" class="form-control" value="<?= Input::old('last_name', $user->last_name); ?>" placeholder="<?= __('Last Name'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="password"><?= __('Password'); ?></label>
			<div class="col-sm-8">
				<input name="password" id="password" type="password" class="form-control" value="" placeholder="<?= __('Password'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="password_confirmation"><?= __('Confirm Password'); ?></label>
			<div class="col-sm-8">
				<input name="password_confirmation" id="password_confirmation" type="password" class="form-control" value="" placeholder="<?= __('Password confirmation'); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="email"><?= __('E-mail'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-8">
				<input name="email" id="email" type="text" class="form-control" value="<?= Input::old('email', $user->email); ?>" placeholder="<?= __('E-mail'); ?>">
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
		<input type="hidden" name="userId" value="<?= $user->id; ?>" />

		</form>
	</div>
</div>

<div class="row">
	<hr>
	<a class='btn btn-primary' href='<?= site_url('admin/users'); ?>'><?= __('<< Previous Page'); ?></a>
	<br>
</div>
