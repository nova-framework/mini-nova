<style>

body {
  background: #eee !important;
}

.wrapper {
  margin-top: 80px;
  margin-bottom: 80px;
}

.form-signin {
  max-width: 380px;
  padding: 15px 35px 45px;
  margin: 0 auto;
  background-color: #fff;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.form-signin .form-signin-heading,
.form-signin .checkbox {
  margin-bottom: 30px;
}

.form-signin .checkbox {
  font-weight: normal;
  padding-left: 20px;
}

.form-signin .checkbox input[type="checkbox"] {
  margin-top: -2px;
}

.form-signin .form-control {
  position: relative;
  font-size: 16px;
  height: auto;
  padding: 10px;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

.form-signin .form-control:focus {
  z-index: 2;
}

.form-signin input[type="text"] {
  margin-bottom: -1px;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.form-signin input[type="password"] {
  margin-bottom: 20px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

</style>

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

