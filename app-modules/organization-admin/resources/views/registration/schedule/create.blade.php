<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Create Registration Schedule</title>
	<style>
		/* intentionally minimal — the user requested bare HTML */
		body { font-family: system-ui, sans-serif; padding: 1rem; }
		label { display: block; margin: 0.5rem 0; }
		textarea { width: 100%; height: 10rem; }
	</style>
</head>
<body>
	<h1>Create Registration Schedule</h1>

	<form id="schedule-form">
		<label>Start time
			<input type="datetime-local" name="start_time" required>
		</label>

		<label>End time
			<input type="datetime-local" name="end_time" required>
		</label>

		<label>Active
			<input type="checkbox" name="is_active" checked>
		</label>

		<label>Priority
			<input type="number" name="priority" value="0">
		</label>

		<label>Is Default
			<input type="checkbox" name="is_default">
		</label>

		<button type="submit">Create Schedule</button>
	</form>

	<div id="status" aria-live="polite" style="margin-top: 1rem"></div>

	<script>

		const form = document.getElementById('schedule-form');
		const status = document.getElementById('status');

		form.addEventListener('submit', async (e) => {
			e.preventDefault();

			try {
				const fd = new FormData(form);
				const start = fd.get('start_time');
				const end = fd.get('end_time');

				// convert to ISO strings
				const isoStart = start ? new Date(start).toISOString() : null;
				const isoEnd = end ? new Date(end).toISOString() : null;


				const payload = {
					start_time: isoStart,
					end_time: isoEnd,
					is_active: fd.get('is_active') ? true : false,
					priority: fd.get('priority') ? parseInt(fd.get('priority')) : 0,
					is_default: fd.get('is_default') ? true : false,
				};

				status.textContent = 'Saving...';

				const token = document.querySelector('meta[name="csrf-token"]').content;

				const url = '{{ route("org.api.v1.registration.schedule.store") }}';

				const res = await fetch(url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': token
					},
					body: JSON.stringify(payload),
				});

				if (!res.ok) {
					const text = await res.text();
					status.textContent = 'Save failed: ' + text;
					return;
				}

				const json = await res.json();
				status.textContent = 'Created schedule ' + (json.id ?? 'ok');
				// Optionally clear the form
				// form.reset();
			} catch (err) {
				status.textContent = 'Unexpected error: ' + (err.message || err);
			}
		});
	</script>
</body>
</html>

