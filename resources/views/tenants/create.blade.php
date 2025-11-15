<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Tenant</title>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
    <style>
        label { display:block; margin-top: 10px; }
        input, button { padding: 6px; }
        .error { color: #b91c1c; margin-top: 4px; }
        .success { color: #065f46; margin-top: 10px; }
        .row { margin-bottom: 8px; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('tenant-form');
            const out = document.getElementById('output');
            const errorsEl = document.getElementById('errors');

            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                out.textContent = '';
                errorsEl.innerHTML = '';

                const data = {
                    tenant: {
                        id: form.tenant_id.value || null,
                        domain: form.domain.value,
                        use_default_domain: !!form.use_default_domain.checked,
                    },
                    organization: {
                        short_name: form.short_name.value,
                        name: form.org_name.value,
                        category_name: form.category_name.value,
                        should_copy_from_other_org: form.copy_from.checked,
                        allow_cross_membership: form.cross_membership.checked,
                        theme: {},
                        order: parseInt(form.org_order.value || '0') || 0,
                    },
                    contacts: {
                        email: form.email.value,
                        website: form.website.value || null,
                        facebook: form.facebook.value || null,
                        twitter: form.twitter.value || null,
                        instagram: form.instagram.value || null,
                        threads: form.threads.value || null,
                        discord: form.discord.value || null,
                    }
                };

                try {
                    const res = await axios.post('{{ route('api.v1.tenants.store') }}', data);
                    out.textContent = JSON.stringify(res.data, null, 2);
                    out.className = 'success';
                } catch (err) {
                    if (err.response && err.response.status === 422) {
                        const v = err.response.data.errors || {};
                        for (const [key, msgs] of Object.entries(v)) {
                            const div = document.createElement('div');
                            div.className = 'error';
                            div.textContent = key + ': ' + msgs.join(', ');
                            errorsEl.appendChild(div);
                        }
                    } else {
                        out.textContent = (err.response && err.response.data && err.response.data.message) || err.message;
                        out.className = 'error';
                    }
                }
            });
        });
    </script>
</head>
<body>
    <h1>Create Tenant</h1>
    <form id="tenant-form">
        <fieldset>
            <legend>Tenant</legend>
            <label>Tenant ID (optional)
            </label>
            <input type="text" name="tenant_id" />
            <label>Domain
            </label>
            <input type="text" name="domain" required />
            <label>
                Use default Domain?
            </label>
            <input type="checkbox" name="use_default_domain" />
        </fieldset>

        <fieldset>
            <legend>Organization</legend>
            <label>Short Name
            </label>
            <input type="text" name="short_name" required />
            <label>Name
            </label>
            <input type="text" name="org_name" required />
            <label>Category Name
            </label>
            <input type="text" name="category_name" required />
            <label>Order
            </label>
            <input type="number" name="org_order" value="0" />
            <label>
                 Should copy from other org
            </label>
            <input type="checkbox" name="copy_from" />
            <label>
                 Allow cross membership
            </label>
            <input type="checkbox" name="cross_membership" />
        </fieldset>

        <fieldset>
            <legend>Contacts</legend>
            <label>Email
                <input type="email" name="email" required />
            </label>
            <label>Website
                <input type="url" name="website" />
            </label>
            <label>Facebook
                <input type="text" name="facebook" />
            </label>
            <label>Twitter
                <input type="text" name="twitter" />
            </label>
            <label>Instagram
                <input type="text" name="instagram" />
            </label>
            <label>Threads
                <input type="text" name="threads" />
            </label>
            <label>Discord
                <input type="text" name="discord" />
            </label>
        </fieldset>

        <div class="row">
            <button type="submit">Create Tenant</button>
        </div>
    </form>
    <div id="errors"></div>
    <pre id="output"></pre>
</body>
</html>
