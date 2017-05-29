@section ('page-top')

<div class="row">
    <img src="{{ resource_url('images/nova.png', 'Bootstrap') }}" alt="{{ Config::get('app.name') }}">
    <hr>
</div>

<div class="row">
	<h1>{{ $title }}</h1>
	<ol class="breadcrumb">
		<li><a href="{{ site_url('/') }}">{{ __d('content', 'Homepage') }}</a></li>
		<li>{{ __d('content', 'Content') }}</li>
	</ol>
</div>

@stop

@section ('content')

@include ('Partials/Messages')

<div class="row">
	<p>{{ $content }}</p>
</div>

@stop
