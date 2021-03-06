@extends('layout.admin')

@section('content')
<div class="col-md-10" id="dashboard">
	<h1>Dashboard Tracker :</h1>
	<div class="col-md-2" id="admin-panel">
		<div class="panel padder-v item">
			<div class="h1 text-info font-thin h1">{{ $num_user }}</div>
			<span class="text-muted text-xs">{{ trans('traduction.users') }}</span>
		</div>
	</div>

	<div class="col-md-2" id="admin-panel">
		<div class="panel padder-v item">
			<div class="h1 text-info font-thin h1">{{ $num_torrent }}</div>
				<span class="text-muted text-xs">Torrents</span>
		</div>
	</div>

	<div class="col-md-2" id="admin-panel">
		<div class="panel padder-v item">
			<div class="h1 text-info font-thin h1">{{ $num_article }}</div>
			<span class="text-muted text-xs">Articles</span>
		</div>
	</div>
	<div class="col-md-3">
		<h2>Dernier billets :</h2>
		<p>ici les derniers billets</p>
	</div>
</div>
@stop
