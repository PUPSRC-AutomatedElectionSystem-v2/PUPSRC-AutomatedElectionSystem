<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Create Voting Schedule</title>
	<style>
		/* intentionally minimal — the user requested bare HTML */
		body { font-family: system-ui, sans-serif; padding: 1rem; }
		label { display: block; margin: 0.5rem 0; }
		textarea { width: 100%; height: 10rem; }
	</style>
</head>
<body>
	<h1>Create Voting Schedule</h1>

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

		<label>Matching rules
			<div id="matching-rules-builder" style="border:1px solid #ddd;padding:0.5rem">
				<div style="margin-bottom:0.5rem">
					<small>Build matching rules for users. Each row adds a new leaf condition - choose a connector (AND/OR) before the rule, select the field, and enter comma-separated values.</small>
				</div>

				<div style="margin-bottom:0.5rem">
					<button type="button" id="add-rule">Add rule</button>
				</div>

				<div id="rules-container" aria-live="polite"></div>

				<div style="margin-top:0.5rem">
					<small>Tip: separate multiple values with commas (e.g., <code>A,B,C</code> or <code>11,12</code>)</small>
				</div>
			</div>
			<input type="hidden" name="matching_rules" id="matching_rules_input">
		</label>

		<button type="submit">Create Schedule</button>
	</form>

	<div id="status" aria-live="polite" style="margin-top: 1rem"></div>

	<script>
		// Load server-provided type options so the UI mirrors allowed server rules.
		const RULE_OPTIONS = {!! json_encode(App\Services\ScheduleRuleOptionsDto::toClientOptions()) !!};

		function optionHtml(options) {
			return options.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('');
		}

		// Create a DOM row for a leaf condition. The first row skips the connector selector.
		function createRuleRow(isFirst = false) {
			const row = document.createElement('div');
			row.className = 'rule-row';
			const connectorHtml = `
				<select class="connector" style="margin-right:0.5rem;" aria-label="connector">
					<option value="and">AND</option>
					<option value="or">OR</option>
				</select>
			`;

			row.innerHTML = `
				<div style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem">
					${isFirst ? '<div style="width:75px"></div>' : connectorHtml}
					<select name="type" aria-label="type">${optionHtml(RULE_OPTIONS)}</select>
					<input name="values" placeholder="comma separated values" aria-label="values" style="flex:1" />
					<label style="margin-left:0.25rem"><input type="checkbox" name="negate" aria-label="negate">NOT</label>
					<button type="button" class="remove-rule">Remove</button>
				</div>
			`;

			// wire remove
			row.querySelector('.remove-rule').addEventListener('click', () => {
				row.remove();
				// ensure first row has no connector
				normalizeFirstConnector();
			});

			return row;
		}

		function normalizeFirstConnector() {
			const rows = document.querySelectorAll('#rules-container .rule-row');
			rows.forEach((r, idx) => {
				const sel = r.querySelector('.connector');
				if (!sel) return;
				sel.style.visibility = idx === 0 ? 'hidden' : 'visible';
			});
		}

		function buildLeafFromRow(row) {
			const type = row.querySelector('select[name="type"]').value;
			let values = (row.querySelector('input[name="values"]').value || '').split(',').map(v => v.trim()).filter(Boolean);
			// normalize ints where relevant
			if (type === 'year_level') {
				values = values.map(v => parseInt(v, 10)).filter(v => !isNaN(v));
			}
			const leaf = { type: type, values };
			if (row.querySelector('input[name="negate"]').checked) {
				return { not: leaf };
			}
			return leaf;
		}

		function buildExpressionFromRows() {
			const rows = Array.from(document.querySelectorAll('#rules-container .rule-row'));
			if (rows.length === 0) return null;
			let expr = buildLeafFromRow(rows[0]);
			for (let i = 1; i < rows.length; i++) {
				const op = rows[i].querySelector('.connector').value;
				const leaf = buildLeafFromRow(rows[i]);
				expr = (op === 'and') ? { all: [expr, leaf] } : { any: [expr, leaf] };
			}
			return expr;
		}

		// Add initial rule
		document.addEventListener('DOMContentLoaded', function () {
			const rulesContainer = document.getElementById('rules-container');
			document.getElementById('add-rule').addEventListener('click', () => {
				const isFirst = rulesContainer.querySelectorAll('.rule-row').length === 0;
				rulesContainer.appendChild(createRuleRow(isFirst));
				normalizeFirstConnector();
			});

			// start with one empty rule
			document.getElementById('add-rule').click();
		});
		const form = document.getElementById('schedule-form');
		const status = document.getElementById('status');

		form.addEventListener('submit', async (e) => {
			e.preventDefault();

			try {
						// If a rule-builder exists, construct matching_rules first
						// (FormData snapshots the form at creation time — build/set the hidden
						// input before creating FormData so the value is included.)
						const built = buildExpressionFromRows();
						if (built) {
							document.getElementById('matching_rules_input').value = JSON.stringify(built);
						}

						const fd = new FormData(form);
						const start = fd.get('start_time');
						const end = fd.get('end_time');

						// convert to ISO strings
						const isoStart = start ? new Date(start).toISOString() : null;
						const isoEnd = end ? new Date(end).toISOString() : null;

						let matchingRules = fd.get('matching_rules') || null;

				console.log('matchingRules raw:', matchingRules);
				if (matchingRules) {
					try {
						matchingRules = JSON.parse(matchingRules);
					} catch (err) {
						status.textContent = 'Invalid JSON for matching_rules: ' + err.message;
						return;
					}
				}

				const payload = {
					start_time: isoStart,
					end_time: isoEnd,
					is_active: fd.get('is_active') ? true : false,
					priority: fd.get('priority') ? parseInt(fd.get('priority')) : 0,
					is_default: fd.get('is_default') ? true : false,
					matching_rules: matchingRules,
				};

				status.textContent = 'Saving...';

				const token = document.querySelector('meta[name="csrf-token"]').content;

				const url = '{{ route("org.api.v1.voting.schedule.store") }}';

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

