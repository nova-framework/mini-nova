<div class="row">
	<h1><?= __('Show Message'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><?= __('Dashboard'); ?></a></li>
		<li><a href='<?= site_url('admin/messages'); ?>'><?= __('Messages'); ?></a></li>
		<li><?= __('Show Message'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">
	<h3><strong><?= $message->subject; ?></strong></h3>
	<hr style="margin-top: 0;">

	<div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
		<!-- Status -->
		<div class="media">
			<div class="pull-left">
				<img  style="height: 50px; width: 50px" src="<?= asset('images/users/no-image.png'); ?>" alt="<?= $message->sender->name(); ?>" class="media-object">
			</div>
			<div class="media-body">
				<h4 class="media-heading"><?= $message->sender->name(); ?></h4>
				<p><?= e($message->body); ?></p>
				<ul class="list-inline text-muted">
					<li><?= $message->created_at->diffForHumans(); ?></li>
				</ul>
			</div>
		</div>
		<?php if (! $message->replies->isEmpty()) { ?>
		<hr style="margin-bottom: 0;">
		<?php } else { ?>
		<br>
		<?php } ?>
		<!-- Replies -->
		<?php foreach($message->replies as $reply) { ?>
		<div class="media comment-block">
			<a class="pull-left" href="<?= site_url('user/' .$reply->sender->username); ?>">
				<img  style="height: 50px; width: 50px" src="<?= asset('images/users/no-image.png'); ?>" alt="<?= $reply->sender->name(); ?>" class="media-object">
			</a>
			<div class="media-body">
				<h4 class="media-heading"><?= $reply->sender->name(); ?></h4>
				<p><?= e($reply->body); ?></p>
				<ul class="list-inline text-muted">
					<li><?= $reply->created_at->diffForHumans(); ?></li>
				</ul>
			</div>
		</div>
		<?php } ?>
		<!-- Reply Form -->
		<form action="<?= site_url('admin/messages/' .$message->id); ?>" role="form" method="POST">

		<div class="form-group <?= $errors->has('reply') ? 'has-error' : ''; ?>">
			<textarea style="resize: none" name="reply" class="form-control" placeholder="<?= __('Reply to this {0, select, 0 {message} other {thread}}...', $message->replies->count()); ?>" rows="3"></textarea>
			<?php if ($errors->has('reply')) { ?>
			<span class="help-block"><?= $errors->first(); ?></span>
			<?php } ?>
		</div>
		<button type="submit" class="btn btn-success col-sm-2 pull-right"><i class='fa fa-reply'></i> <?= __('Reply'); ?></button>
		<input type="hidden" name="_token" value="<?= csrf_token(); ?>">

		</form>
	</div>
</div>

<div class="row">
	<hr>
	<a class='btn btn-primary' href='<?= site_url('admin/messages'); ?>'><?= __('<< Previous Page'); ?></a>
	<br>
</div>