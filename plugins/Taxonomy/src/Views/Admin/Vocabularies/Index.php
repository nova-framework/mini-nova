<div class="row">
    <h1><?= __d('taxonomy', 'Taxonomy'); ?></h1>
    <ol class="breadcrumb">
        <li><a href='<?= site_url('admin/dashboard'); ?>'><i class="fa fa-dashboard"></i> <?= __d('taxonomy', 'Dashboard'); ?></a></li>
        <li><?= __d('taxonomy', 'Taxonomy'); ?></li>
    </ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
    <h3><?= __d('taxonomy', 'Manage the Vocabularies'); ?></h3>
    <br>
    <a class='btn btn-success' href='<?= site_url('admin/taxonomy/create'); ?>'><i class='fa fa-send'></i> <?= __d('taxonomy', 'Create a new Vocabulary'); ?></a>
    <hr>
</div>

<div class="row">
    <h3><?= __d('taxonomy', 'Vocabularies'); ?></h3>
    <br>

    <?php if (! $vocabularies->isEmpty()) { ?>
    <table class='table table-bordered table-striped table-hover responsive'>
        <thead>
            <tr>
                <th style='text-align: center; vertical-align: middle;'><?= __d('taxonomy', 'ID'); ?></th>
                <th style='text-align: center; vertical-align: middle;'><?= __d('taxonomy', 'Name'); ?></th>
                <th style='text-align: center; vertical-align: middle;'><?= __d('taxonomy', 'Slug'); ?></th>
                <th style='text-align: center; vertical-align: middle;'><?= __d('taxonomy', 'Description'); ?></th>
                <th style='text-align: center; vertical-align: middle;'><?= __d('taxonomy', 'Terms'); ?></th>
                <th style='text-align: right; vertical-align: middle;'><?= __d('taxonomy', 'Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vocabularies->all() as $item) { ?>
            <tr>
                <td style="text-align: center; vertical-align: middle;" width="5%"><?= $item->id; ?></td>
                <td style="text-align: center; vertical-align: middle;" width="16%"><?= $item->name; ?></td>
                <td style="text-align: center; vertical-align: middle;" width='15%'><?= $item->slug; ?></td>
                <td style="text-align: left; vertical-align: middle;" width="40%"><?= $item->description; ?></td>
                <td style="text-align: center; vertical-align: middle;" width='12%'><?= $item->terms->count(); ?></td>
                <td style="text-align: right; vertical-align: middle; padding: 5px;" width="12%">
                    <div class='btn-group' role='group' aria-label='...'>
                        <a class='btn btn-sm btn-warning' href="<?= site_url('admin/taxonomy/' .$item->id .'/terms'); ?>" title="<?= __d('taxonomy', 'Show the Terms'); ?>" role="button"><i class="fa fa-search"></i></a>
                        <a class='btn btn-sm btn-success' href="<?= site_url('admin/taxonomy/' .$item->id .'/edit') ?>" title="<?= __d('taxonomy', 'Edit this Vocabulary') ?>" role="button"><i class="fa fa-pencil"></i></a>
                        <a class='btn btn-sm btn-danger' href='#' data-toggle="modal" data-id='<?= $item->id ?>' data-target="#modal_delete_vocabulary" title="<?= __d('taxonomy', 'Delete this Vocabulary'); ?>" role="button"><i class="fa fa-remove"></i></a>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php } else { ?>
    <div class="alert alert-info">
        <h4><?= __d('taxonomy', 'No vocabularies'); ?></h4>
        <p><?= __d('taxonomy', 'You have no vocabularies.'); ?></p>
    </div>
    <?php } ?>
</div>

<div class="row">
    <div class="pull-right">
        <?= $vocabularies->links(); ?>
    </div>
    <div class="clearfix"></div>
</div>

<?php if (! $vocabularies->isEmpty()) { ?>

<div class="modal modal-default fade" id="modal_delete_vocabulary">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= __d('taxonomy', 'Delete this Vocabulary?'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= __d('taxonomy', 'Are you sure you want to remove this Vocabulary, the operation being irreversible?'); ?></p>
                <p><?= __d('taxonomy', 'Please click the button <b>Delete</b> to proceed, or <b>Cancel</b> to abandon the operation.'); ?></p>
            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-primary pull-left col-md-3" type="button"><?= __d('taxonomy', 'Cancel'); ?></button>
                <form id="modal_delete_form" action="" method="POST">
                    <input type="hidden" name="userId" id="delete_vocabulary_id" value="0" />
                    <input type="hidden" name="_token" value="<?= csrf_token(); ?>" />
                    <input type="submit" name="button" class="btn btn btn-danger pull-right col-md-3" value="<?= __d('taxonomy', 'Delete'); ?>">
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<script>

$(function ()
{
    $('#modal_delete_vocabulary').on('show.bs.modal', function (event)
    {
        var button = $(event.relatedTarget); // Button that triggered the modal

        var id = button.data('id'); // Extract the Vocabulary ID from data-* attributes

        //
        $('#delete_vocabulary_id').val(id);

        $('#modal_delete_form').attr('action', "<?= site_url('admin/taxonomy'); ?>" + '/' + id + '/destroy');
    });
});

</script>

<?php } ?>


