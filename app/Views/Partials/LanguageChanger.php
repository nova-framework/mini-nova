<li class="dropdown">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
		<i class='fa fa-language'></i> <?= Language::name() ?>
	</a>
	<ul class="dropdown-menu">
		<?php foreach (Config::get('languages') as $code => $info) { ?>
		<li <?= ($code == Language::code()) ? 'class="active"' : ''; ?>>
			<a href='<?= site_url('language/' .$code) ?>' title='<?= $info['info'] ?>'><?= $info['name'] ?></a>
		</li>
		<?php } ?>
	</ul>
</li>

