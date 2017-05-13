<div class="row">
	<h1><?= __('Send Message'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __('Dashboard'); ?></a></li>
		<li><a href='<?= site_url('admin/messages'); ?>'><?= __('Messages'); ?></a></li>
		<li><?= __('Send Message'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h3 class="box-title"><?= __('Send a new Private Message'); ?></h3>
	<hr style="margin-top: 0;">

	<div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
		<form class="form-horizontal" action="<?= site_url('admin/messages'); ?>" method="POST" role="form">

		<div class="form-group <?= $errors->has('subject') ? 'has-error' : ''; ?>">
			<label class="col-sm-3 control-label" for="subject"><?= __('Subject'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-9">
				<input name="subject" id="subject" type="text" class="form-control" value="<?= Input::old('subject'); ?>" placeholder="<?= __('Subject'); ?>">
				<?php if ($errors->has('subject')) { ?>
				<span class="help-block"><?= $errors->first('subject'); ?></span>
				<?php } ?>
			</div>
		</div>
		<div class="form-group <?= $errors->has('message') ? 'has-error' : ''; ?>">
			<label class="col-sm-3 control-label" for="message"><?= __('Message'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-9">
				<textarea id="message" name="message" class="form-control" style="resize: none;" placeholder="<?= __('Message'); ?>" rows="5" ><?= Input::old('message'); ?></textarea>
				<?php if ($errors->has('message')) { ?>
				<span class="help-block"><?= $errors->first('message'); ?></span>
				<?php } ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label" for="user"><?= __('Receiver'); ?> <font color='#CC0000'>*</font></label>
			<div class="col-sm-9">
				<?php $opt_user = Input::old('user'); ?>
				<select name="user" id="user" class="form-control select2">
					<option value="" <?php if (empty($opt_user)) echo 'selected'; ?>>- <?= __('Choose a User'); ?> -</option>
					<?php foreach ($users as $user) { ?>
					<option value="<?= $user->id ?>" <?php if ($opt_user == $user->id) echo 'selected'; ?>><?= $user->username; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="clearfix"></div>
		<br>
		<font color='#CC0000'>*</font><?= __('Required field'); ?>
		<hr>
		<div class="form-group">
			<div class="col-sm-12">
				<button type="submit" class="btn btn-success col-sm-2 pull-right"><i class='fa fa-send'></i> <?= __('Send'); ?></button>
			</div>
		</div>

		<input type="hidden" name="_token" value="<?= csrf_token(); ?>">

		</form>
	</div>
</div>

<div class="row">
	<hr>
	<a class='btn btn-primary' href='<?= site_url('admin/messages'); ?>'><?= __('<< Previous Page'); ?></a>
	<br>
</div>
