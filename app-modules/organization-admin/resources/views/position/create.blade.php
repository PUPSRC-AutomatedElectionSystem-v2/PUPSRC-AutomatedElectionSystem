<form method="POST" action="{{ route('org.api.v1.position.store') }}">
	@csrf

	<div>
		<label for="title">Title</label>
		<input id="title" name="title" type="text" value="{{ old('title') }}">
		@error('title')
			<div>{{ $message }}</div>
		@enderror
	</div>

	<div>
		<label for="description">Description</label>
		<textarea id="description" name="description">{{ old('description') }}</textarea>
		@error('description')
			<div>{{ $message }}</div>
		@enderror
	</div>

	<div>
		<label for="votable_count">Votable Count</label>
		<input id="votable_count" name="votable_count" type="number" min="1" value="{{ old('votable_count', 1) }}">
		@error('votable_count')
			<div>{{ $message }}</div>
		@enderror
	</div>

	<div>
		<label for="order">Order</label>
		<input id="order" name="order" type="number" value="{{ old('order') }}">
		@error('order')
			<div>{{ $message }}</div>
		@enderror
	</div>

	<div>
		<button type="submit">Create Position</button>
	</div>
</form>
