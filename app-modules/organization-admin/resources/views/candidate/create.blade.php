
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-6">Create Candidate</h1>

    <form action="{{ route('org.api.v1.candidates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">First name</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-indigo-200" />
                @error('first_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle name</label>
                <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name') }}"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-indigo-200" />
                @error('middle_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-indigo-200" />
                @error('last_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="suffix" class="block text-sm font-medium text-gray-700">Suffix (optional)</label>
                <input id="suffix" name="suffix" type="text" value="{{ old('suffix') }}"
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-indigo-200" />
                @error('suffix')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="position_id" class="block text-sm font-medium text-gray-700">Position</label>
                <select id="position_id" name="position_id" required
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-indigo-200">
                    <option value="">Loading positions…</option>
                </select>
                @error('position_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="photo" class="block text-sm font-medium text-gray-700">Photo (optional)</label>
            <input id="photo" name="photo" type="file" accept="image/*"
                class="mt-1 block w-full text-sm text-gray-700" />
            @error('photo')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description / manifesto</label>
            <textarea id="description" name="description" rows="4"
                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-indigo-200">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="inline-block px-4 py-2 rounded border border-gray-300">Back</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create candidate</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('position_id');
    const oldPosition = @json(old('position_id'));

    // Fetch positions from the module API and populate the select
    fetch('/org/api/v1/position', { credentials: 'same-origin' })
        .then(function (res) {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(function (data) {
            select.innerHTML = '<option value="">-- Select position --</option>';
            if (!Array.isArray(data) || data.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '(No positions available)';
                select.appendChild(opt);
                return;
            }

            data.forEach(function (pos) {
                const opt = document.createElement('option');
                // support different shapes (id / uuid)
                const id = pos.id ?? pos.uuid ?? '';
                opt.value = id;
                opt.textContent = pos.name ?? pos.title ?? id;
                if (oldPosition && id == oldPosition) opt.selected = true;
                select.appendChild(opt);
            });
        })
        .catch(function (err) {
            select.innerHTML = '<option value="">(Failed to load positions)</option>';
            // eslint-disable-next-line no-console
            console.error('Failed to load positions:', err);
        });
});
</script>
