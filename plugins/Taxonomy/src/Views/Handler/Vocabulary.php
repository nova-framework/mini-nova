<?php Section::start('page-top'); ?>

<div class="row">
    <h1><?= $title; ?></h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('/'); ?>"><?= __d('taxonomy', 'Homepage'); ?></a></li>
        <li><a href="<?= site_url($vocabulary->slug); ?>"><?= $vocabulary->name; ?></a></li>
    </ol>
</div>

<?php Section::stop(); ?>

<?php Section::start('content'); ?>

<div class="row">
    <div class="col-sm-12">
        <?php if (! $terms->isEmpty()) { ?>
        <?php foreach ($terms->all() as $term) { ?>
        <h4><strong><a href="<?= site_url($vocabulary->slug .'/' .$term->slug); ?>"><?= $term->name; ?></strong></a></h4>
        <p><?= $term->description; ?></p>
        <p class="text-muted"><?= __d('taxonomy', '{0} children, {1} relationships', $term->children->count(), $term->relations->count()); ?></p>
        <br>
        <?php } ?>
        <?php } else { ?>
        <div class="alert alert-info">
            <h4><?= __d('taxonomy', 'No terms'); ?></h4>
            <p><?= __d('taxonomy', 'You have no terms.'); ?></p>
        </div>
        <?php } ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="pull-right">
            <?= $terms->links(); ?>
        </div>
        <div class="clearfix"></div>
        <br>
    </div>
</div>

<?php Section::stop(); ?>
