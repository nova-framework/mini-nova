<div class="row">
	<h1><?= __d('backend', 'Dashboard'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __d('backend', 'Dashboard'); ?></a></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->

<div class="row">
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-users fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?= $usersCount; ?></div>
						<div><?= __d('backend', 'Registered Users'); ?></div>
					</div>
				</div>
			</div>
			<a href="<?= site_url('admin/users'); ?>">
				<div class="panel-footer">
					<span class="pull-left"><?= __d('backend', 'View Details'); ?></span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-green">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-tasks fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge">12</div>
						<div>New Tasks!</div>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">View Details</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-yellow">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-shopping-cart fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge">124</div>
						<div>New Orders!</div>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">View Details</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-3 col-md-6">
		<div class="panel panel-red">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-support fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge">13</div>
						<div>Support Tickets!</div>
					</div>
				</div>
			</div>
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">View Details</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>

	<div class="clearfix"></div>

	<hr style="margin-top: 0;">
</div>

<style>

#rolesTable td.compact {
	padding: 5px;
}

#rolesTable_paginate .pagination {
	margin: 5px 0 -3px;
}

</style>

<div class="row">
	<h3><?= __d('backend', 'Users online'); ?></h3>
	<br>
	<table id='usersTable' class='table table-bordered table-striped table-hover responsive' style="width: 100%;">
		<thead>
			<tr>
				<th width='15%' class="text-center"><?= __d('backend', 'Username'); ?></th>
				<th width='13%' class="text-center"><?= __d('backend', 'Role'); ?></th>
				<th width='15%' class="text-center"><?= __d('backend', 'First Name'); ?></th>
				<th width='15%' class="text-center"><?= __d('backend', 'Last Name'); ?></th>
				<th width='18%' class="text-center"><?= __d('backend', 'Email'); ?></th>
				<th width='14%' class="text-center"><?= __d('backend', 'Last activity'); ?></th>
				<th width='10%' class="text-right"><?= __d('backend', 'Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>

<p>
<?= $debug; ?>
</p>

</div>

<script>

$(function () {
	$('#usersTable').DataTable({
		language: {
			url: '//cdn.datatables.net/plug-ins/1.10.15/i18n/<?= $langInfo; ?>.json'
		},
		responsive: true,
		stateSave: true,
		processing: true,
		serverSide: true,
		ajax: {
			type: 'POST',
			url: '<?= site_url('admin/dashboard/data'); ?>',
			data: function (data) {
				data._token = '<?= csrf_token(); ?>';
			}
		},
		pageLength: 15,
		lengthMenu: [ 5, 10, 15, 20, 25, 50, 75, 100 ],

		columns: [
			{ data: 'username',		orderable: true,  searchable: true,  className: "text-center" },
			{ data: 'role',			orderable: true,  searchable: false, className: "text-center" },
			{ data: 'first_name',	orderable: true,  searchable: true,  className: "text-center" },
			{ data: 'last_name',	orderable: true,  searchable: true,  className: "text-center" },
			{ data: 'email',		orderable: true,  searchable: true,  className: "text-center" },
			{ data: 'date',			orderable: false, searchable: false, className: "text-center" },
			{ data: 'actions', 		orderable: false, searchable: false, className: "text-right compact" },
		],

		drawCallback: function(settings) {
			var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');

			pagination.toggle(this.api().page.info().pages > 1);
		},
	});
});

</script>

