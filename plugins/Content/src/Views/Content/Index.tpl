<div class="row">
    <img src="{{ plugin_url('images/nova.png', 'Bootstrap') }}" alt="{{ Config::get('app.name') }}">
</div>

<div class="row">
	<h1>{{ $title }}</h1>
	<hr>
</div>

@include ('Partials/Messages')

<div class="row">
	<p>{{ $content }}</p>
</div>
