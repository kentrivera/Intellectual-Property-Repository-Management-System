/**
 * Header Search
 * Provides working global search from the header (desktop + mobile).
 *
 * Behavior:
 * - Staff: redirects to /staff/search?q=...
 * - Admin: redirects to /admin/ip-records?search=...
 */

(function initHeaderSearch() {
    const config = window.IPRepoHeaderSearch || {};
    const baseUrl = String(config.baseUrl || (typeof BASE_URL !== 'undefined' ? BASE_URL : '') || '').replace(/\/+$/, '');
    const role = String(config.role || '').toLowerCase();
    const suggestionsUrl = String(config.suggestionsUrl || (baseUrl ? (baseUrl + '/search/suggestions') : '') || '');

    const desktopInput = document.getElementById('globalSearch');
    const mobileInput = document.getElementById('mobileSearch');
    const desktopBtn = document.getElementById('globalSearchBtn');
    const mobileBtn = document.getElementById('mobileSearchBtn');

    const desktopDropdown = document.getElementById('globalSearchDropdown');
    const mobileDropdown = document.getElementById('mobileSearchDropdown');

    let aborter = null;
    let lastQuery = '';

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function recordLink(id) {
        if (!id) return '#';
        return role === 'admin'
            ? `${baseUrl}/admin/ip-records/${id}`
            : `${baseUrl}/staff/ip-records/${id}`;
    }

    function showDropdown(dropdownEl, html) {
        if (!dropdownEl) return;
        dropdownEl.innerHTML = html;
        dropdownEl.classList.remove('hidden');
    }

    function hideDropdown(dropdownEl) {
        dropdownEl?.classList.add('hidden');
    }

    function setActiveTab(dropdownEl, tab) {
        if (!dropdownEl) return;
        const active = tab === 'files' ? 'files' : 'records';
        dropdownEl.dataset.activeTab = active;

        const btnRecords = dropdownEl.querySelector('[data-tab="records"]');
        const btnFiles = dropdownEl.querySelector('[data-tab="files"]');
        const panelRecords = dropdownEl.querySelector('[data-panel="records"]');
        const panelFiles = dropdownEl.querySelector('[data-panel="files"]');

        const activeBtn = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold';
        const inactiveBtn = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white text-gray-700 text-xs font-semibold border border-gray-200 hover:bg-gray-50';

        if (btnRecords) btnRecords.className = active === 'records' ? activeBtn : inactiveBtn;
        if (btnFiles) btnFiles.className = active === 'files' ? activeBtn : inactiveBtn;
        if (panelRecords) panelRecords.classList.toggle('hidden', active !== 'records');
        if (panelFiles) panelFiles.classList.toggle('hidden', active !== 'files');
    }

    function renderBanner(query, payload) {
        const ip = Array.isArray(payload?.ip_records) ? payload.ip_records : [];
        const docs = Array.isArray(payload?.documents) ? payload.documents : [];

        const q = escapeHtml(query);

        const head = `
            <div class="px-3 py-2 border-b border-gray-100">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-xs font-semibold text-gray-800">Matches for “${q}”</div>
                    <div class="text-[11px] text-gray-500">Press Enter to search</div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <button type="button" data-tab="records" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white text-gray-700 text-xs font-semibold border border-gray-200 hover:bg-gray-50">
                        <i class="fas fa-folder-open text-[11px]"></i>
                        Records
                        <span class="px-1.5 py-0.5 rounded-md bg-gray-100 text-gray-700 text-[10px]">${ip.length}</span>
                    </button>
                    <button type="button" data-tab="files" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white text-gray-700 text-xs font-semibold border border-gray-200 hover:bg-gray-50">
                        <i class="fas fa-file-lines text-[11px]"></i>
                        Files
                        <span class="px-1.5 py-0.5 rounded-md bg-gray-100 text-gray-700 text-[10px]">${docs.length}</span>
                    </button>
                </div>
            </div>
        `;

        const emptyPanel = `
            <div class="px-3 py-3 text-xs text-gray-500">No matches yet. Press Enter to search.</div>
        `;

        const recordItems = [];
        const fileItems = [];

        for (const r of ip.slice(0, 7)) {
            const title = escapeHtml(r.title);
            const typeName = escapeHtml(r.type_name);
            const link = escapeHtml(recordLink(r.id));
            recordItems.push(`
                <a href="${link}" class="block px-3 py-2 hover:bg-emerald-50 transition">
                    <div class="flex items-start gap-2">
                        <div class="mt-0.5 w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700">
                            <i class="fas fa-folder-open text-[11px]"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-gray-900 truncate">${title}</div>
                            <div class="text-[11px] text-gray-600 truncate">IP record${typeName ? ` • ${typeName}` : ''}</div>
                        </div>
                    </div>
                </a>
            `);
        }

        for (const d of docs.slice(0, 10)) {
            const filename = escapeHtml(d.original_name || d.file_name || 'Document');
            const recordTitle = escapeHtml(d.ip_title || '');
            const link = escapeHtml(recordLink(d.ip_record_id));
            fileItems.push(`
                <a href="${link}" class="block px-3 py-2 hover:bg-emerald-50 transition">
                    <div class="flex items-start gap-2">
                        <div class="mt-0.5 w-6 h-6 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700">
                            <i class="fas fa-file-lines text-[11px]"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-gray-900 truncate">${filename}</div>
                            ${recordTitle ? `<div class="text-[11px] text-gray-600 truncate">in ${recordTitle}</div>` : ''}
                        </div>
                    </div>
                </a>
            `);
        }

        const recordsPanel = recordItems.length ? recordItems.join('') : emptyPanel;
        const filesPanel = fileItems.length ? fileItems.join('') : emptyPanel;

        return head + `
            <div data-panel="records">${recordsPanel}</div>
            <div data-panel="files">${filesPanel}</div>
        `;
    }

    function debounce(fn, wait) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    }

    function buildUrl(query) {
        const q = String(query || '').trim();
        if (!q) return '';

        if (role === 'admin') {
            const url = new URL(baseUrl + '/admin/ip-records', window.location.origin);
            url.searchParams.set('search', q);
            return url.pathname + url.search;
        }

        const url = new URL(baseUrl + '/staff/search', window.location.origin);
        url.searchParams.set('q', q);
        return url.pathname + url.search;
    }

    function submit(query) {
        const target = buildUrl(query);
        if (!target) return;
        window.location.href = target;
    }

    const fetchSuggestions = debounce(async (query, dropdownEl) => {
        const q = String(query || '').trim();
        if (!dropdownEl) return;

        if (q.length < 2) {
            hideDropdown(dropdownEl);
            return;
        }

        if (!suggestionsUrl) {
            showDropdown(dropdownEl, `<div class="p-3 text-xs text-gray-500">Press Enter to search “${escapeHtml(q)}”.</div>`);
            return;
        }

        if (q === lastQuery) return;
        lastQuery = q;

        try {
            aborter?.abort();
        } catch (e) {
            // ignore
        }
        aborter = new AbortController();

        showDropdown(dropdownEl, `<div class="p-3 text-xs text-gray-500">Searching…</div>`);

        try {
            const url = new URL(suggestionsUrl, window.location.origin);
            url.searchParams.set('q', q);
            url.searchParams.set('limit', '6');

            const res = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                signal: aborter.signal
            });

            const data = await res.json();
            const html = renderBanner(q, data);
            showDropdown(dropdownEl, html);

            const hasFiles = Array.isArray(data?.documents) && data.documents.length > 0;
            const hasRecords = Array.isArray(data?.ip_records) && data.ip_records.length > 0;
            const preferred = hasFiles ? 'files' : (hasRecords ? 'records' : 'files');
            setActiveTab(dropdownEl, preferred);
        } catch (e) {
            if (e?.name === 'AbortError') return;
            showDropdown(dropdownEl, `<div class="p-3 text-xs text-gray-500">Press Enter to search “${escapeHtml(q)}”.</div>`);
        }
    }, 220);

    function wireInput(input) {
        if (!input) return;
        const dropdownEl = input.id === 'mobileSearch' ? mobileDropdown : desktopDropdown;

        input.addEventListener('input', () => {
            fetchSuggestions(input.value, dropdownEl);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                hideDropdown(dropdownEl);
                submit(input.value);
            }
            if (e.key === 'Escape') {
                hideDropdown(dropdownEl);
            }
        });

        input.addEventListener('focus', () => {
            // Re-render if query already there
            if (String(input.value || '').trim().length >= 2) {
                fetchSuggestions(input.value, dropdownEl);
            }
        });

        input.addEventListener('blur', () => {
            // allow click on dropdown links
            setTimeout(() => hideDropdown(dropdownEl), 180);
        });
    }

    function wireButton(button, input) {
        if (!button || !input) return;
        const dropdownEl = input.id === 'mobileSearch' ? mobileDropdown : desktopDropdown;
        button.addEventListener('click', (e) => {
            e.preventDefault();
            hideDropdown(dropdownEl);
            submit(input.value);
        });
    }

    wireInput(desktopInput);
    wireInput(mobileInput);
    wireButton(desktopBtn, desktopInput);
    wireButton(mobileBtn, mobileInput);

    // Tab toggles (event delegation)
    function wireDropdownTabs(dropdownEl) {
        if (!dropdownEl) return;
        dropdownEl.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-tab]');
            if (!btn) return;
            e.preventDefault();
            const tab = btn.getAttribute('data-tab');
            setActiveTab(dropdownEl, tab);
        });
    }
    wireDropdownTabs(desktopDropdown);
    wireDropdownTabs(mobileDropdown);

    // Close on outside click
    document.addEventListener('click', (e) => {
        const inDesktop = e.target.closest('#globalSearch') || e.target.closest('#globalSearchDropdown');
        const inMobile = e.target.closest('#mobileSearch') || e.target.closest('#mobileSearchDropdown');
        if (!inDesktop) hideDropdown(desktopDropdown);
        if (!inMobile) hideDropdown(mobileDropdown);
    });
})();
