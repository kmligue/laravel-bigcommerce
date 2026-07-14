@extends('limonlabs/bigcommerce::layouts.app')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Limon Admin / {{ $appName }} / API Tester</title>
@endsection

@section('content')
    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => 'Limon Admin / ' . $appName . ' / API Tester'])

    <p class="text-sm text-gray-500 mt-1">
        Poor man's Postman — hit any API path with your session credentials. Relative paths (e.g.
        <code class="bg-gray-100 px-1 rounded">/api/stores/abc123/settings</code>) go to this app; full URLs work too.
    </p>

    <div class="bg-white shadow-md p-5 mt-8">
        <h2 class="font-semibold text-gray-800">Request</h2>
        <p class="text-sm text-gray-500 mt-1">
            Choose a method, set the URL, and optionally include a JSON body for write requests.
        </p>

        <div class="flex flex-col sm:flex-row gap-2 mt-4">
            <select id="method" class="border border-gray-300 rounded px-3 py-2 text-sm font-bold bg-white min-w-[110px] text-blue-600">
                <option value="GET">GET</option>
                <option value="POST">POST</option>
                <option value="PUT">PUT</option>
                <option value="DELETE">DELETE</option>
            </select>

            <input
                id="url"
                type="text"
                placeholder="e.g. /api/stores/abc123/settings"
                class="border border-gray-300 rounded px-3 py-2 text-sm flex-1 font-mono"
            />

            <button
                id="send"
                type="button"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:opacity-60"
            >
                Send
            </button>
        </div>

        <div id="body-wrap" class="mt-4 hidden">
            <label for="body" class="block text-sm text-gray-500 mb-1">Request body (JSON)</label>
            <textarea
                id="body"
                rows="10"
                spellcheck="false"
                placeholder='{"key": "value"}'
                class="border border-gray-300 rounded px-3 py-2 text-sm w-full font-mono"
            >{
  "theme": "dark",
  "currency": "USD"
}</textarea>
        </div>
    </div>

    <div class="bg-white shadow-md p-5 mt-8">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
            <h2 class="font-semibold text-gray-800">Response</h2>
            <span id="status" class="text-sm font-bold"></span>
            <span id="duration" class="text-sm text-gray-500"></span>
        </div>

        <pre id="response" class="mt-4 p-4 bg-slate-900 text-slate-100 rounded text-xs font-mono overflow-auto whitespace-pre-wrap max-h-[500px]">Run a request to see the response...</pre>
    </div>
@endsection

@section('footer')
<script>
(function () {
    const methodEl = document.getElementById('method');
    const urlEl = document.getElementById('url');
    const bodyEl = document.getElementById('body');
    const bodyWrap = document.getElementById('body-wrap');
    const sendBtn = document.getElementById('send');
    const statusEl = document.getElementById('status');
    const durationEl = document.getElementById('duration');
    const responseEl = document.getElementById('response');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const methodSelectColors = {
        GET: 'text-blue-600',
        POST: 'text-green-600',
        PUT: 'text-amber-600',
        DELETE: 'text-red-600',
    };

    const methodButtonColors = {
        GET: 'bg-blue-500 hover:bg-blue-700',
        POST: 'bg-green-600 hover:bg-green-700',
        PUT: 'bg-amber-500 hover:bg-amber-600',
        DELETE: 'bg-red-500 hover:bg-red-700',
    };

    function syncMethodUi() {
        const method = methodEl.value;
        bodyWrap.classList.toggle('hidden', method === 'GET');

        methodEl.className = 'border border-gray-300 rounded px-3 py-2 text-sm font-bold bg-white min-w-[110px] ' + methodSelectColors[method];
        sendBtn.className = methodButtonColors[method] + ' text-white font-bold py-2 px-6 rounded disabled:opacity-60';
    }

    function formatBody(text) {
        if (!text) {
            return '(empty response body)';
        }

        try {
            return JSON.stringify(JSON.parse(text), null, 2);
        } catch (e) {
            return text;
        }
    }

    async function sendRequest() {
        const method = methodEl.value;
        const target = urlEl.value.trim();

        if (!target) {
            alert('Enter a URL to send the request to.');
            return;
        }

        let payload = undefined;
        if (method !== 'GET' && bodyEl.value.trim() !== '') {
            try {
                payload = JSON.stringify(JSON.parse(bodyEl.value));
            } catch (e) {
                alert('Request body is not valid JSON.');
                return;
            }
        }

        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';
        const start = performance.now();

        try {
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
            };

            if (payload !== undefined) {
                headers['Content-Type'] = 'application/json';
            }

            const res = await fetch(target, {
                method: method,
                headers: headers,
                credentials: 'same-origin',
                body: payload,
            });

            const text = await res.text();
            const durationMs = Math.round(performance.now() - start);

            statusEl.textContent = res.status + ' ' + res.statusText;
            statusEl.className = 'text-sm font-bold ' + (
                res.status >= 200 && res.status < 300 ? 'text-green-600'
                    : res.status >= 400 ? 'text-red-600'
                        : 'text-amber-600'
            );
            durationEl.textContent = durationMs + ' ms';
            responseEl.textContent = formatBody(text);
        } catch (err) {
            const durationMs = Math.round(performance.now() - start);
            statusEl.textContent = 'Network Error';
            statusEl.className = 'text-sm font-bold text-red-600';
            durationEl.textContent = durationMs + ' ms';
            responseEl.textContent = err && err.message ? err.message : 'Request failed before receiving a response.';
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';
        }
    }

    methodEl.addEventListener('change', syncMethodUi);
    sendBtn.addEventListener('click', sendRequest);
    urlEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            sendRequest();
        }
    });

    syncMethodUi();
})();
</script>
@endsection
