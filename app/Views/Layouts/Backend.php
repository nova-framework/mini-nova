<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?= isset($title) ? $title : 'Page'; ?> - <?= Config::get('app.name'); ?></title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

	<!-- DataTables -->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">

	<!-- Local customizations -->
	<link rel="stylesheet" type="text/css" href="<?= asset('css/style.css'); ?>">

	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?= site_url('admin/dashboard'); ?>"> Control Panel</a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<ul class="nav navbar-nav">
				<li>
					<a href="<?= site_url('admin/dashboard'); ?>"><?= __('Dashboard'); ?></a>
				</li>
				<li>
					<a href="<?= site_url('admin/roles'); ?>"><?= __('Roles'); ?></a>
				</li>
				<li>
					<a href="<?= site_url('admin/users'); ?>"><?= __('Users'); ?></a>
				</li>
			</ul>
			<ul class="nav navbar-nav navbar-right" style="margin-right: 0;">
				<!-- Authentication Links -->
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
						<?= Auth::user()->username ?> <span class="caret"></span>
					</a>
					<ul class="dropdown-menu" role="menu">
						<li>
							<a href="<?= site_url('auth/logout'); ?>"
								onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
								<?= __('Logout'); ?>
							</a>
							<form id="logout-form" action="<?= site_url('auth/logout'); ?>" method="POST" style="display: none;">
								<input type="hidden" name="_token" value="<?= csrf_token(); ?>" />
							</form>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<!-- /.navbar-collapse -->
	</div>
	<!-- /.container -->
</nav>

<div class="container">
	<?= $content; ?>
</div>

<footer class="footer">
	<div class="container-fluid">
		<div class="row" style="margin: 15px 0 0;">
			<div class="col-lg-4">
				Mini-Nova <strong><?= App::version(); ?></strong>
			</div>
			<div class="col-lg-8">
				<p class="text-muted pull-right">
					<small><!-- DO NOT DELETE! - Profiler --></small>
				</p>
			</div>
		</div>
	</div>
</footer>

<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<!-- DataTables -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>

</body>
</html>
