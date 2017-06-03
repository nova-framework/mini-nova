<div class="row">
	<h1><?= __d('taxonomy', 'View the Terms'); ?></h1>
	<ol class="breadcrumb">
		<li><a href="<?= site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> <?= __d('taxonomy', 'Dashboard'); ?></a></li>
		<li><a href="<?= site_url('admin/taxonomy'); ?>"><?= __d('taxonomy', 'Taxonomy'); ?></a></li>
		<li><?= __d('taxonomy', 'View the Terms of Vocabulary : <b>{0}</b>', $vocabulary->name); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h3><?= __d('taxonomy', 'Manage the Terms'); ?></h3>
	<br>
	<a class='btn btn-success' href='<?= site_url('admin/taxonomy/' .$vocabulary->id .'/terms/create'); ?>'><i class='fa fa-send'></i> <?= __d('taxonomy', 'Create a new Term'); ?></a>
	<hr>
</div>

<style type="text/css">
*/
/**
 * Nestable
 */

.dd { position: relative; display: block; margin: 0; padding: 0; max-width: 900px; list-style: none; /*font-size: 13px; line-height: 20px;*/ }

.dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
.dd-list .dd-list { padding-left: 30px; }
.dd-collapsed .dd-list { display: none; }

.dd-item,
.dd-empty,
.dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: /*13px; line-height: 25px;*/ }

.dd-handle { display: block; height: 36px; margin: 5px 0; padding: 7px 5px 0 10px; color: #333; text-decoration: none; border: 1px solid #ccc;
	background: #fafafa;
	background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
	background:	-moz-linear-gradient(top, #fafafa 0%, #eee 100%);
	background:		 linear-gradient(top, #fafafa 0%, #eee 100%);
	-webkit-border-radius: 3px;
			border-radius: 3px;
	box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd-handle:hover { color: #2ea8e5; background: #fff; }

.dd-handle .btn-group { margin-top: -4px; }


.dd-item > button { display: block; position: relative; cursor: pointer; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
.dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
.dd-item > button[data-action="collapse"]:before { content: '-'; }

.dd-placeholder,
.dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; }
.dd-empty { border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;
	background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
					  -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
	background-image:	-moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
						 -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
	background-image:		 linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
							  linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
	background-size: 60px 60px;
	background-position: 0 0, 30px 30px;
}

.dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
.dd-dragel > .dd-item .dd-handle { margin-top: 0; }
.dd-dragel .dd-handle {
	-webkit-box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
			box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
}

.dd-item > button { display: block; position: relative; cursor: pointer; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
.dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
.dd-item > button[data-action="collapse"]:before { content: '-'; }

</style>

<div class="row">
	<h3><?= __d('taxonomy', 'The Terms of Vocabulary : <b>{0}</b>', $vocabulary->name); ?></h3>
	<br>

	<div class="col-md-10">
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="dd">
					<?= View::fetch('Taxonomy::Partials/TermsNestable', array('vocabulary' => $vocabulary, 'terms' => $terms)); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="clearfix"></div>

<div class="row">
	<hr>
	<a class='btn btn-primary' href='<?= site_url('admin/taxonomy'); ?>'><?= __d('taxonomy', '<< Previous Page'); ?></a>
	<br>
</div>

<div id="confirm-delete-dialog" class="modal modal-default fade" tabindex="-1" role="dialog" aria-labelledby="...">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-label="Close" data-dismiss="modal" class="close" type="button">
				<span aria-hidden="true">×</span></button>
				<h4 class="modal-title"><?= __d('taxonomy', 'Delete this Term?'); ?></h4>
			</div>
			<div class="modal-body">
				<p><?= __d('taxonomy', 'Are you sure you want to remove this Term, the operation being irreversible?'); ?></p>
				<p><?= __d('taxonomy', 'Please click the button <b>Delete</b> to proceed, or <b>Cancel</b> to abandon the operation.'); ?></p>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="formId" id="delete-term-form-id" value=""/>
				<button type="button" data-dismiss="modal" aria-hidden="true" class="btn btn-primary col-md-3"><?= __d('taxonomy', 'Cancel'); ?></button>
				<button type="button" class="delete-term-button btn btn-danger col-md-3 pull-right"><?= __d('taxonomy', 'Delete'); ?></button>
			</div>
		</div>
	</div>
</div>

<script>

$(function() {
	$('.dd').nestable({
		listNodeName: 'ul',
		expandBtnHTML: '',
		collapseBtnHTML: '',
		maxDepth: 7,
	});

	$('.dd').on('change', function() {
	   var json = JSON.stringify($(this).nestable('serialize'));

	   $.ajax({
			url: '<?= site_url("admin/taxonomy/" .$vocabulary->id ."/terms/order") ?>',
			type: 'post',
			data: {
				json
			},
			headers: {
				'X-CSRF-Token': '<?= csrf_token(); ?>',
			},
			dataType: 'json'
		});
	});

	$('.dd-handle input').on('mousedown', function(e) {
		e.stopPropagation();
	});

	$('.dd-handle input.delete-confirm-dialog').on('click', function(e) {
		e.preventDefault();

		var id = $(this).data('id');

		$('#delete-term-form-id').val(id);

		$('#confirm-delete-dialog').modal('show');
	});

	$('.delete-term-button').on('click', function(e) {
		e.preventDefault();

		$('#confirm-delete-dialog').modal('hide');

		var id = $('#delete-term-form-id').val();

		$('#delete-term-form-' + id).submit();
	});
});

</script>

<script type="text/javascript" src="<?= resource_url('js/jquery.nestable.js', 'Taxonomy'); ?>"></script>
