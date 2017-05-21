<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= isset($title) ? $title : 'Page'; ?> - <?= Config::get('app.name'); ?></title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

	<!-- DataTables -->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">

	<!-- Local customizations -->
	<link rel="stylesheet" type="text/css" href="<?= plugin_url('css/backend.css', 'Backend'); ?>">
	<link rel="stylesheet" type="text/css" href="<?= plugin_url('css/style.css', 'Backend'); ?>">

	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
</head>
<body>

<div id="wrapper">
	<div class="container-fluid">

	<!-- Navigation -->
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?= site_url('admin/dashboard'); ?>"> <strong><?= __d('bootstrap', 'Control Panel'); ?></strong></a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<ul class="nav navbar-nav navbar-right" style="margin-right: 10px;">
				<li <?php if($baseUri == 'admin/messages') echo 'class="active"'; ?>>
					<a href="<?= site_url('admin/messages'); ?>" title="<?= __d('bootstrap', 'Your Messages'); ?>">
						<i class='fa fa-envelope'></i>
						<?= __d('bootstrap', 'Messages'); ?> <?php if (isset($privateMessageCount) && ($privateMessageCount > 0)) echo '<span class="label label-success">' .$privateMessageCount .'</span>'; ?>
					</a>
				</li>
				<li <?php if($baseUri == 'admin/notifications') echo 'class="active"'; ?>>
					<a href="<?= site_url('admin/notifications'); ?>" title="<?= __d('bootstrap', 'Your Notifications'); ?>">
						<i class='fa fa-bell'></i>
						<?= __d('bootstrap', 'Notifications'); ?> <?php if (isset($notificationCount) && ($notificationCount > 0)) echo '<span class="label label-success">' .$notificationCount .'</span>'; ?>
					</a>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="<?= Language::name() .' (' .Language::code() .')'; ?>">
						<i class='fa fa-language'></i> <?= Language::name(); ?> <span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
					<?php foreach (Config::get('languages') as $code => $info) { ?>
						<li <?= ($code == Language::code()) ? 'class="active"' : ''; ?>>
							<a href='<?= site_url('language/' .$code) ?>' title='<?= $info['info'] ?>'><i class='fa fa-circle-o'></i> <?= $info['name'] .' (' .$code .')' ?></a>
						</li>
					<?php } ?>
					</ul>
				</li>
				<!-- Authentication Links -->
				<li class="dropdown <?= ($baseUri == 'admin/profile') ? 'active' : ''; ?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" title="<?= $currentUser->name() ?>">
						<i class='fa fa-user'></i> <?= $currentUser->username ?> <span class="caret"></span>
					</a>
					<ul class="dropdown-menu" role="menu">
						<li <?= ($baseUri == 'admin/profile') ? 'class="active"' : ''; ?>>
							<a href="<?= site_url('admin/profile'); ?>"><i class='fa fa-circle-o'></i> <?= __d('bootstrap', 'Profile'); ?></a>
						</li>
						<li role="separator" class="divider"></li>
						<li>
							<a href="<?= site_url('auth/logout'); ?>"
								onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
								<i class='fa fa-sign-out'></i> <?= __d('bootstrap', 'Logout'); ?>
							</a>
							<form id="logout-form" action="<?= site_url('auth/logout'); ?>" method="POST" style="display: none;"></form>
						</li>
					</ul>
				</li>
			</ul>

			<!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
			<ul class="nav navbar-nav navbar-inverse side-nav" id="side-menu">
				<li <?= ($baseUri == 'admin/dashboard') ? 'class="active"' : ''; ?> title="<?= __d('bootstrap', 'Your Dashboard'); ?>">
					<a href="<?= site_url('admin/dashboard'); ?>"><i class='fa fa-dashboard'></i> <?= __d('bootstrap', 'Dashboard'); ?></a>
				</li>
				<?php if ($currentUser->hasRole('administrator')) { ?>

				<?php $active = ($baseUri == 'admin/settings'); ?>
				<li class="<?= $active ? 'active' : ''; ?>">
					<a href="javascript:;" data-toggle="collapse" data-target="#settings-children" title="<?= __d('bootstrap', 'Manage the Platform'); ?>">
						<i class='fa fa-server'></i> <?= __d('bootstrap', 'Platform'); ?> <span class="caret"></span>
					</a>
					<ul id="settings-children" class="nav nav-second-level <?= ! $active ? 'collapse' : ''; ?>">
						<li <?= ($baseUri == 'admin/settings') ? 'class="active"' : ''; ?>>
							<a href="<?= site_url('admin/settings'); ?>"><i class='fa fa-circle-o'></i> <?= __d('bootstrap', 'Settings'); ?></a>
						</li>
					</ul>
				</li>
				<?php $active = ($baseUri == 'admin/users') || ($baseUri == 'admin/roles'); ?>
				<li <?= $active ? 'class="active"' : ''; ?>">
					<a href="javascript:;" data-toggle="collapse" data-target="#users-children" title="<?= __d('bootstrap', 'Manage the Users'); ?>">
						<i class='fa fa-users'></i> <?= __d('bootstrap', 'Users'); ?> <span class="caret"></span>
					</a>
					<ul id="users-children" class="nav nav-second-level <?= ! $active ? 'collapse' : ''; ?>">
						<li <?= ($baseUri == 'admin/users') ? 'class="active"' : ''; ?>>
							<a href="<?= site_url('admin/users'); ?>"><i class='fa fa-circle-o'></i> <?= __d('bootstrap', 'Users List'); ?></a>
						</li>
						<li <?= ($baseUri == 'admin/users/create') ? 'class="active"' : ''; ?>>
							<a href="<?= site_url('admin/users/create'); ?>"><i class='fa fa-circle-o'></i> <?= __d('bootstrap', 'Create a new User'); ?></a>
						</li>
						<li <?= ($baseUri == 'admin/roles') ? 'class="active"' : ''; ?>>
							<a href="<?= site_url('admin/roles'); ?>"><i class='fa fa-circle-o'></i> <?= __d('bootstrap', 'Roles List'); ?></a>
						</li>
						<li <?= ($baseUri == 'admin/roles/create') ? 'class="active"' : ''; ?>>
							<a href="<?= site_url('admin/roles/create'); ?>"><i class='fa fa-circle-o'></i> <?= __d('bootstrap', 'Create a new Role'); ?></a>
						</li>
					</ul>
				<li>

				<?php } ?>
			</ul>
		</div>
	<!-- /.container -->
	</nav>

	</div>

	<div id="page-wrapper">
		<div class="container-fluid">
			<?= $content; ?>
		<div>

			<footer class="footer">
				<div class="container-fluid">
					<div class="row" style="margin: 15px 0 0;">
						<div class="col-lg-4">
							Mini MVC Framework <strong><?= VERSION; ?></strong> / Kernel <strong><?= App::version(); ?></strong>
						</div>
						<div class="col-lg-8">
							<p class="text-muted pull-right">
								<small><!-- DO NOT DELETE! - Profiler --></small>
							</p>
						</div>
					</div>
				</div>
			</footer>
	</div>
</div>

<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<!-- DataTables -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript" src="<?= plugin_url('js/backend.js', 'Backend'); ?>"></script>

</body>
</html>
