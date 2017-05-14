<div class="row">
	<h1><?= __('Settings'); ?></h1>
	<ol class="breadcrumb">
		<li><a href='<?= site_url('admin/dashboard'); ?>'><i class="fa fa-dashboard"></i> <?= __('Dashboard'); ?></a></li>
		<li><?= __('Settings'); ?></li>
	</ol>
</div>

<?= View::fetch('Partials/Messages'); ?>

<!-- Main content -->
<div class="row">

<?php if (CONFIG_STORE == 'database') { ?>

<form class="form-horizontal" action="<?= site_url('admin/settings'); ?>" method="POST">

<div class="panel panel-default">
	<div class="panel-body" style="padding-top: 0;">
		<h3><?= __('Site Settings'); ?></h3>
		<hr style="margin-top: 0;">

		<div class="form-group">
			<label class="col-sm-4 control-label" for="sitename"><?= __('Site Name'); ?></label>
			<div class="col-sm-8">
				<input name="siteName" id="siteName" type="text" class="form-control" value="<?= $options['siteName']; ?>">
			</div>
		</div>
	</div>
</div>

<div class="col-lg-12" style="padding-right: 0;">
	<input class="btn btn-success col-sm-2 pull-right" type="submit" id="submit" name="submit" value="<?= __('Apply the changes') ?>" />&nbsp;
</div>

<div class="clearfix"></div>
<br>

<div class="panel panel-default">
	<div class="panel-body" style="padding-top: 0;">
		<h3><?= __('Mailer Settings'); ?></h3>
		<hr style="margin-top: 0;">

		<div class="form-group">
			<label class="col-sm-4 control-label" for="mailDriver"><?= __('Mail Driver'); ?></label>
			<div class="col-sm-8">
				<div class="col-sm-3" style="padding: 0;">
					<select name="mailDriver" id="mailDriver" class="form-control">
						<option value="smtp" <?php if ($options['mailDriver'] == 'smtp') { echo "selected='selected'"; }?>><?= __('SMTP'); ?></option>
						<option value="mail" <?php if ($options['mailDriver'] == 'mail') { echo "selected='selected'"; }?>><?= __('Mail'); ?></option>
						<option value="sendmail" <?php if ($options['mailDriver'] == 'sendmail') { echo "selected='selected'"; }?>><?= __('Sendmail'); ?></option>
					</select>
				</div>
				<div class='clearfix'></div>
				<small><?= __('Whether or not is used a external SMTP Server to send the E-mails.'); ?></small>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="mailFromAddress"><?= __('E-mail From Address'); ?></label>
			<div class="col-sm-8">
				<div class="col-sm-6" style="padding: 0;">
					<input name="mailFromAddress" id="mailFromAddress" type="text" class="form-control" value="<?= $options['mailFromAddress']; ?>">
				</div>
				<div class='clearfix'></div>
				<small><?= __('The outgoing E-mail address.'); ?></small>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="mailFromName"><?= __('E-mail From Name'); ?></label>
			<div class="col-sm-8">
				<div class="col-sm-6" style="padding: 0;">
					<input name="mailFromName" id="mailFromName" type="text" class="form-control" value="<?= $options['mailFromName']; ?>">
				</div>
				<div class='clearfix'></div>
				<small><?= __('The From Field of any outgoing e-mails.'); ?></small>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-4 control-label" for="mailHost"><?= __('Server Name'); ?></label>
			<div class="col-sm-8">
				<div class="col-sm-6" style="padding: 0;">
					<input name="mailHost" id="mailHost" type="text" class="form-control" value="<?= $options['mailHost']; ?>">
				</div>
				<div class='clearfix'></div>
				<small><?= __('The name of the external SMTP Server.'); ?></small>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="mailPort"><?= __('Server Port'); ?></label>
			<div class="col-sm-8">
				<div class="col-sm-2" style="padding: 0;">
					<input name="mailPort" id="mailPort" type="text" class="form-control" value="<?= $options['mailPort']; ?>">
				</div>
				<div class='clearfix'></div>
				<small><?= __('The Port used for connecting to the external SMTP Server.'); ?></small>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="mailEncryption"><?= __('Use the Cryptography'); ?></label>
			<div class="col-sm-8">
				<div class="col-sm-2" style="padding: 0;">
					<select name="mailEncryption" id="mailEncryption" class="form-control">
						<option value="ssl" <?php if ($options['mailEncryption'] == 'ssl') { echo "selected='selected'"; }?>>SSL</option>
						<option value="tls" <?php if ($options['mailEncryption'] == 'tls') { echo "selected='selected'"; }?>>TLS</option>
						<option value="" <?php if ($options['mailEncryption'] == '') { echo "selected='selected'"; }?>><?= __('NONE'); ?></option>
					</select>
				</div>
				<div class='clearfix'></div>
				<small><?= __('Whether or not is used the Cryptography for connecting to the external SMTP Server.'); ?></small>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="mailUsername"><?= __('Server Username'); ?></label>
			<div class="col-sm-8">
				<div class="col-sm-6" style="padding: 0;">
					<input name="mailUsername" id="mailUsername" type="text" class="form-control" value="<?= $options['mailUsername']; ?>">
				</div>
				<div class='clearfix'></div>
				<small><?= __('The Username used to connect to the external SMTP Server.'); ?></small>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label" for="mailPassword"><?= __('Server Password'); ?></label>
			<div class="col-sm-8">
				<div class="col-sm-6" style="padding: 0;">
					<input name="mailPassword" id="mailPassword" type="password" class="form-control" value="<?= $options['mailPassword']; ?>">
				</div>
				<div class='clearfix'></div>
				<small><?= __('The Password used to connect to the external SMTP Server.'); ?></small>
			</div>
		</div>
	</div>
</div>

<div class="col-lg-12" style="padding-right: 0;">
	<input class="btn btn-success col-sm-2 pull-right" type="submit" id="submit" name="submit" value="<?= __('Apply the changes') ?>" />&nbsp;
</div>

<input type="hidden" name="_token" value="<?= csrf_token(); ?>" />

</form>

<?php } else { ?>

<div class="callout callout-info">
	<?= __('The Settings are not available while the Config Store is on Files Mode.'); ?>
</div>

<?php } ?>

</div>
