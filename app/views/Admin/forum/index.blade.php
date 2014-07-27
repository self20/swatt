@extends('layout.admin')


@section('content')
<div class="container">
	<div class="col-md-10">
		<h2>Forums</h2>
		<a href="{{ route('admin_forum_add') }}" class="btn btn-primary">Add new forum</a>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Name</th>
				</tr>
			</thead>
			<tbody>
				@foreach($categories as $c)
					<tr>
						<td><a href="{{ route('admin_forum_edit', array('slug' => $c->slug, 'id' => $c->id)) }}">{{ $c->name }}</a></td>
					</tr>
					@foreach($c->getForumsInCategory() as $f)
						<tr>
							<td><a href="{{ route('admin_forum_edit', array('slug' => $f->slug, 'id' => $f->id)) }}">---- {{ $f->name }}</a></td>
						</tr>
					@endforeach
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@stop
