<!-- Main content -->
<div class="row">
	<div class="wrapper">
		<form action="<?= site_url('auth/login'); ?>" method='POST' class="form-signin">

		<h2 class="form-signin-heading">Please login</h2>

		<?= View::fetch('Partials/Messages'); ?>

		<input type="text" class="form-control" name="username" placeholder="Username" required="" autofocus="" />
		<input type="password" class="form-control" name="password" placeholder="Password" required=""/>
		<label class="checkbox">
			<input type="checkbox" value="remember" id="remember" name="remember">&nbsp;<?= __('Remember me'); ?>
		</label>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>

		<input type="hidden" name="_token" value="<?= csrf_token(); ?>" />

		</form>
	</div>
</div>

