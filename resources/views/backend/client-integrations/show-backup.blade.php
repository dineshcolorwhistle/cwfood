@extends('backend.master', [
  'pageTitle' => 'Integrations',
  'activeMenu' => [
    'item' => 'Client',
    'subitem' => 'Integrations',
    'additional' => '',
  ],
  'features' => [],
  'breadcrumbItems' => [
    ['label' => 'Batchbase Admin', 'url' => '#'],
    ['label' => 'Integrations']
  ],
])

@push('styles')
<style>
  .integration-card { padding: 16px; } /* keep original spacing */
  .integration-actions .btn { margin-right: 8px; }
</style>
@endpush

@section('content')
<div class="container-fluid clients px-0">
  <div class="card-body">

    {{-- Tabs (unchanged) --}}
    <ul class="nav nav-tabs" id="integrationTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="xero-tab" data-bs-toggle="tab" data-bs-target="#xero-pane" type="button" role="tab" aria-controls="xero-pane" aria-selected="true">
          Xero
        </button>
      </li>
    </ul>

    <div class="tab-content p-3 border border-top-0" id="integrationTabContent">
      <div class="tab-pane fade show active" id="xero-pane" role="tabpanel" aria-labelledby="xero-tab" tabindex="0">

        {{-- Always show Authenticate button (same style family) --}}
        <div class="mb-3">
          <a class="btn btn-primary-orange"
             href="{{ route('xero.connect', ['return_to' => route('client.integrations.show')]) }}">
            Authenticate Xero
          </a>
        </div>

        @php $connections = $connections ?? collect(); @endphp

        {{-- If no connections, show the original single card --}}
        @if ($connections->isEmpty())
          <div class="card integration-card">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h4 class="mb-2">Xero</h4>
                <p class="mb-2">Status: <span class="text-danger">Not connected</span></p>
                <p class="text-muted mb-3">Authenticate with Xero to sync Contacts, Invoices and Credit Notes.</p>
              </div>
            </div>
            <div class="integration-actions"><!-- intentionally empty --></div>
          </div>
        @endif

        {{-- One card per connected tenant; layout identical to existing --}}
        @foreach ($connections as $conn)
          <div class="card integration-card">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                {{-- Title updated per your request --}}
                <h4 class="mb-2">Xero – {{ $conn->tenant_name ?? $conn->tenant_id }}</h4>
                {{-- ADD: last sync time --}}
                @if($conn->last_synced_contacts_at)
                <p class="text-muted mb-1">Contacts last synced: {{ $conn->last_synced_contacts_at->diffForHumans() }}</p>
                @else
                <p class="text-muted mb-1">Contacts last synced: never</p>
                @endif

                <p class="text-muted mb-1">
                Invoices last sync:
                {{ $conn->last_synced_invoices_at ? $conn->last_synced_invoices_at->diffForHumans() : 'never' }}
                </p>
                <p class="text-muted mb-3">
                Credit notes last sync:
                {{ $conn->last_synced_credit_notes_at ? $conn->last_synced_credit_notes_at->diffForHumans() : 'never' }}
                </p>

                <p class="mb-2">Status: <span class="text-success">Connected</span></p>
                <p class="text-muted mb-3">
                  Your account is linked to a Xero organisation. You can disconnect or trigger a manual sync.
                </p>
              </div>
            </div>

            <div class="integration-actions">
              {{-- Disconnect (opens confirmation modal) --}}
              <button
                type="button"
                class="btn btn-outline-danger js-disconnect-xero"
                data-tenant="{{ $conn->tenant_id }}"
                data-org="{{ $conn->tenant_name ?? $conn->tenant_id }}"
                data-bs-toggle="modal"
                data-bs-target="#confirmDisconnectModal">
                Disconnect Xero
              </button>

              {{-- Hidden form posted on confirm --}}
              <form id="disconnectXeroForm-{{ $conn->tenant_id }}" method="post" action="{{ route('xero.disconnect') }}" class="d-none">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $conn->tenant_id }}">
              </form>

              <div class="btn-group d-inline">
                <button type="button" class="btn btn-secondary-blue dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Sync Contacts
                </button>
                <ul class="dropdown-menu">
                    <li>
                    <a href="#" class="dropdown-item js-sync-contacts"
                        data-tenant="{{ $conn->tenant_id }}" data-full="0">
                        Delta (since last sync)
                    </a>
                    </li>
                    <li>
                    <a href="#" class="dropdown-item js-sync-contacts"
                        data-tenant="{{ $conn->tenant_id }}" data-full="1">
                        Full sync (all)
                    </a>
                    </li>
                </ul>
              </div>

                {{-- Hidden form that the dropdown submits --}}
              <form id="syncContactsForm-{{ $conn->tenant_id }}" method="post" action="{{ route('xero.sync.contacts') }}" class="d-none">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $conn->tenant_id }}">
                <input type="hidden" name="full" value="0">
              </form>

              {{-- Invoices dropdown --}}
              <div class="btn-group d-inline">
                <button type="button" class="btn btn-secondary-blue dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Sync Invoices
                </button>
                <ul class="dropdown-menu">
                    <li>
                    <a href="#" class="dropdown-item js-sync-invoices"
                        data-tenant="{{ $conn->tenant_id }}" data-full="0">
                        Delta (since last sync)
                    </a>
                    </li>
                    <li>
                    <a href="#" class="dropdown-item js-sync-invoices"
                        data-tenant="{{ $conn->tenant_id }}" data-full="1">
                        Full sync (last 3 months)
                    </a>
                    </li>
                </ul>
              </div>

              <form id="syncInvoicesForm-{{ $conn->tenant_id }}" method="post" action="{{ route('xero.sync.invoices') }}" class="d-none">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $conn->tenant_id }}">
                <input type="hidden" name="full" value="0">
              </form>



                {{-- Credit notes dropdown --}}
              <div class="btn-group d-inline">
                <button type="button" class="btn btn-secondary-blue dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Sync Credit Notes
                </button>
                <ul class="dropdown-menu">
                    <li>
                    <a href="#" class="dropdown-item js-sync-credit"
                        data-tenant="{{ $conn->tenant_id }}" data-full="0">
                        Delta (since last sync)
                    </a>
                    </li>
                    <li>
                    <a href="#" class="dropdown-item js-sync-credit"
                        data-tenant="{{ $conn->tenant_id }}" data-full="1">
                        Full sync (last 3 months)
                    </a>
                    </li>
                </ul>
              </div>

              <form id="syncCreditForm-{{ $conn->tenant_id }}" method="post" action="{{ route('xero.sync.credit_notes') }}" class="d-none">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $conn->tenant_id }}">
                <input type="hidden" name="full" value="0">
              </form>

            </div>
          </div>
        @endforeach

      </div>
    </div>

  </div>
</div>
@endsection

{{-- Mini Sync Modal --}}
<div class="modal fade" id="miniSyncModal" tabindex="-1" aria-labelledby="miniSyncLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="miniSyncLabel">Xero Syncing…</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="progress" style="height: 6px;">
          <div id="miniSyncBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <div id="miniSyncMsg" class="small text-muted mt-2">Please wait…</div>
      </div>
    </div>
  </div>
</div>

{{-- Confirm Disconnect Modal --}}
<div class="modal fade" id="confirmDisconnectModal" tabindex="-1" aria-labelledby="confirmDisconnectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDisconnectModalLabel">Disconnect Xero?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">
          Data related to this tenant will be <strong>permanently deleted</strong>, including Contacts, Invoices, Credit Notes, and their line items.
          This cannot be undone.
        </p>
        <p class="text-muted small mt-2" id="disconnectOrgHint"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-danger" id="confirmDisconnectBtn">Yes, delete data</button>
      </div>
    </div>
  </div>
</div>


@push('scripts')
<script>
(function () {
  const modalEl = document.getElementById('miniSyncModal');
  const barEl   = document.getElementById('miniSyncBar');
  const msgEl   = document.getElementById('miniSyncMsg');
  const modal   = modalEl ? new bootstrap.Modal(modalEl) : null;
  const csrf    = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  let progressTimer = null;
  function resetBar() {
    if (!barEl) return;
    barEl.style.width = '0%';
    barEl.classList.remove('bg-danger');
    msgEl && (msgEl.textContent = 'Please wait…');
  }
  function fakeProgress() {
    // simple easing fake progress (stops at ~90% until we finish)
    let p = 0;
    progressTimer = setInterval(() => {
      p = Math.min(90, p + Math.max(1, 8 - Math.floor(p/15))); // fast start, slow end
      if (barEl) barEl.style.width = p + '%';
    }, 120);
  }
  function finishProgress(ok) {
    if (progressTimer) { clearInterval(progressTimer); progressTimer = null; }
    if (barEl) {
      if (ok) {
        barEl.style.width = '100%';
      } else {
        barEl.classList.add('bg-danger');
        barEl.style.width = '100%';
      }
    }
    msgEl && (msgEl.textContent = ok ? 'Sync complete' : 'Sync failed');
    // Auto-close after a brief moment
    setTimeout(() => { modal?.hide(); resetBar(); }, ok ? 600 : 1200);
  }

  async function postSync(url, payload) {
    if (!modal) return;
    resetBar();
    modal.show();
    fakeProgress();
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      // Your controller currently returns a redirect/html for non-AJAX.
      // This handles both JSON and HTML gracefully.
      if (!res.ok) throw new Error('HTTP ' + res.status);
      finishProgress(true);
      // Optional: refresh page timestamps after close
      setTimeout(() => { window.location.reload(); }, 650);
    } catch (e) {
      console.error(e);
      finishProgress(false);
    }
  }

  document.addEventListener('click', function (e) {
    const c  = e.target.closest('.js-sync-contacts');
    const i  = e.target.closest('.js-sync-invoices');
    const cn = e.target.closest('.js-sync-credit');

    if (c || i || cn) {
      e.preventDefault();
      const el   = c || i || cn;
      const full = el.getAttribute('data-full') === '1';
      const tid  = el.getAttribute('data-tenant');

      if (c)  return postSync("{{ route('xero.sync.contacts') }}",      { tenant_id: tid, full });
      if (i)  return postSync("{{ route('xero.sync.invoices') }}",      { tenant_id: tid, full });
      if (cn) return postSync("{{ route('xero.sync.credit_notes') }}",  { tenant_id: tid, full });
    }
  });
})();

// Disconnect modal wiring
(function () {
  let currentTenantId = null;

  // When user clicks the "Disconnect Xero" button, stash tenant + set hint
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-disconnect-xero');
    if (!btn) return;

    currentTenantId = btn.getAttribute('data-tenant');
    const org = btn.getAttribute('data-org') || currentTenantId;
    const hint = document.getElementById('disconnectOrgHint');
    if (hint) hint.textContent = `Tenant: ${org}`;
  });

  // On confirm, submit the matching hidden form
  document.getElementById('confirmDisconnectBtn')?.addEventListener('click', function () {
    if (!currentTenantId) return;
    const form = document.getElementById('disconnectXeroForm-' + currentTenantId);
    form?.submit();
  });
})();

</script>
@endpush
