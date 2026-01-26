/**
 * Enhanced Header Search
 * Provides comprehensive global search from the header (desktop + mobile).
 *
 * Features:
 * - Real-time search suggestions with debouncing
 * - Keyboard navigation (Arrow keys, Enter, Escape)
 * - Recent searches (localStorage)
 * - Loading states and error handling
 * - Clear button functionality
 * - Accessibility support (ARIA labels)
 * - Staff: redirects to /staff/search?q=...
 * - Admin: redirects to /admin/ip-records?search=...
 */

(function initHeaderSearch() {
    'use strict';
    
    const config = window.IPRepoHeaderSearch || {};
    const baseUrl = String(config.baseUrl || (typeof BASE_URL !== 'undefined' ? BASE_URL : '') || '').replace(/\/+$/, '');
    const role = String(config.role || '').toLowerCase();
    const suggestionsUrl = String(config.suggestionsUrl || (baseUrl ? (baseUrl + '/search/suggestions') : '') || '');

    // DOM Elements
    const desktopInput = document.getElementById('globalSearch');
    const mobileInput = document.getElementById('mobileSearch');
    const desktopBtn = document.getElementById('globalSearchBtn');
    const mobileBtn = document.getElementById('mobileSearchBtn');
    const desktopClear = document.getElementById('globalSearchClear');
    const mobileClear = document.getElementById('mobileSearchClear');
    const desktopLoading = document.getElementById('globalSearchLoading');
    const mobileLoading = document.getElementById('mobileSearchLoading');
    const desktopDropdown = document.getElementById('globalSearchDropdown');
    const mobileDropdown = document.getElementById('mobileSearchDropdown');

    // State management
    let aborter = null;
    let lastQuery = '';
    let selectedIndex = -1;
    let searchResults = [];
    
    // Constants
    const RECENT_SEARCHES_KEY = `ip_repo_recent_searches_${role}`;
    const MAX_RECENT_SEARCHES = 5;
    const MIN_SEARCH_LENGTH = 2;
    const DEBOUNCE_DELAY = 250;

    // ===========================
    // Utility Functions
    // ===========================
    
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
    
    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        const regex = new RegExp(`(${escapeHtml(query).replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escapeHtml(text).replace(regex, '<mark class="bg-yellow-200 text-gray-900">$1</mark>');
    }

    // ===========================
    // Recent Searches Management
    // ===========================
    
    function getRecentSearches() {
        try {
            const stored = localStorage.getItem(RECENT_SEARCHES_KEY);
            return stored ? JSON.parse(stored) : [];
        } catch (e) {
            return [];
        }
    }
    
    function saveRecentSearch(query) {
        try {
            const q = String(query || '').trim();
            if (!q || q.length < MIN_SEARCH_LENGTH) return;
            
            let recent = getRecentSearches();
            recent = recent.filter(item => item.toLowerCase() !== q.toLowerCase());
            recent.unshift(q);
            recent = recent.slice(0, MAX_RECENT_SEARCHES);
            
            localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(recent));
        } catch (e) {
            // Ignore localStorage errors
        }
    }
    
    function clearRecentSearches() {
        try {
            localStorage.removeItem(RECENT_SEARCHES_KEY);
        } catch (e) {
            // Ignore
        }
    }

    // ===========================
    // Dropdown Management
    // ===========================
    
    function showDropdown(dropdownEl, html) {
        if (!dropdownEl) return;
        dropdownEl.innerHTML = html;
        dropdownEl.classList.remove('hidden');
        
        // Update ARIA
        const input = dropdownEl.id === 'mobileSearchDropdown' ? mobileInput : desktopInput;
        if (input) input.setAttribute('aria-expanded', 'true');
    }

    function hideDropdown(dropdownEl) {
        if (!dropdownEl) return;
        dropdownEl.classList.add('hidden');
        selectedIndex = -1;
        searchResults = [];
        
        // Update ARIA
        const input = dropdownEl.id === 'mobileSearchDropdown' ? mobileInput : desktopInput;
        if (input) input.setAttribute('aria-expanded', 'false');
    }
    
    function showLoading(isMobile) {
        const loading = isMobile ? mobileLoading : desktopLoading;
        const clear = isMobile ? mobileClear : desktopClear;
        if (loading) loading.classList.remove('hidden');
        if (clear) clear.classList.add('hidden');
    }
    
    function hideLoading(isMobile) {
        const loading = isMobile ? mobileLoading : desktopLoading;
        if (loading) loading.classList.add('hidden');
        updateClearButton(isMobile);
    }
    
    function updateClearButton(isMobile) {
        const input = isMobile ? mobileInput : desktopInput;
        const clear = isMobile ? mobileClear : desktopClear;
        if (!input || !clear) return;
        
        if (input.value.trim()) {
            clear.classList.remove('hidden');
        } else {
            clear.classList.add('hidden');
        }
    }

    function setActiveTab(dropdownEl, tab) {
        if (!dropdownEl) return;
        const active = tab === 'files' ? 'files' : 'records';
        dropdownEl.dataset.activeTab = active;

        const btnRecords = dropdownEl.querySelector('[data-tab="records"]');
        const btnFiles = dropdownEl.querySelector('[data-tab="files"]');
        const panelRecords = dropdownEl.querySelector('[data-panel="records"]');
        const panelFiles = dropdownEl.querySelector('[data-panel="files"]');

        const activeBtn = 'flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-white text-emerald-700 text-xs font-bold shadow-md transition-all transform scale-105';
        const inactiveBtn = 'flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-white bg-opacity-20 text-white text-xs font-semibold backdrop-blur-sm hover:bg-opacity-30 transition-all';

        if (btnRecords) btnRecords.className = active === 'records' ? activeBtn : inactiveBtn;
        if (btnFiles) btnFiles.className = active === 'files' ? activeBtn : inactiveBtn;
        if (panelRecords) panelRecords.classList.toggle('hidden', active !== 'records');
        if (panelFiles) panelFiles.classList.toggle('hidden', active !== 'files');
    }

    // ===========================
    // Keyboard Navigation
    // ===========================
    
    function updateSelection(dropdownEl) {
        if (!dropdownEl) return;
        
        const items = dropdownEl.querySelectorAll('a[data-result-index]');
        items.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('bg-emerald-50');
                item.setAttribute('aria-selected', 'true');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('bg-emerald-50');
                item.setAttribute('aria-selected', 'false');
            }
        });
    }
    
    function handleKeyboardNav(e, dropdownEl) {
        if (!dropdownEl || dropdownEl.classList.contains('hidden')) return;
        
        const items = dropdownEl.querySelectorAll('a[data-result-index]');
        if (items.length === 0) return;
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(dropdownEl);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection(dropdownEl);
                break;
                
            case 'Enter':
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    e.preventDefault();
                    const selectedItem = items[selectedIndex];
                    
                    // Check if it's a file result
                    if (selectedItem.classList.contains('file-result-item')) {
                        const fileData = {
                            id: selectedItem.getAttribute('data-file-id'),
                            name: selectedItem.getAttribute('data-file-name'),
                            recordTitle: selectedItem.getAttribute('data-record-title'),
                            recordId: selectedItem.getAttribute('data-record-id'),
                            recordLink: selectedItem.getAttribute('data-record-link'),
                            url: `${baseUrl}/document/preview/${selectedItem.getAttribute('data-file-id')}`,
                            downloadUrl: `${baseUrl}/document/preview/${selectedItem.getAttribute('data-file-id')}`
                        };
                        hideDropdown(dropdownEl);
                        if (window.openFilePreviewModal) {
                            window.openFilePreviewModal(fileData);
                        }
                    } else {
                        // Regular link click
                        selectedItem.click();
                    }
                }
                break;
                
            case 'Escape':
                e.preventDefault();
                hideDropdown(dropdownEl);
                break;
        }
    }

    // ===========================
    // Render Functions
    // ===========================
    
    function renderRecentSearches(dropdownEl) {
        const recent = getRecentSearches();
        if (recent.length === 0) {
            return `
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-3xl text-emerald-400"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700 mb-1">Start Your Search</p>
                    <p class="text-xs text-gray-500">Type at least 2 characters to find IP records and documents</p>
                </div>
            `;
        }
        
        return `
            <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-slate-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-history text-gray-400 text-xs"></i>
                        <span class="text-xs font-semibold text-gray-700">Recent Searches</span>
                    </div>
                    <button type="button" onclick="window.__clearRecentSearches && window.__clearRecentSearches()" 
                            class="text-[10px] text-red-500 hover:text-red-700 font-medium hover:underline transition">
                        Clear All
                    </button>
                </div>
            </div>
            <div class="py-2">
                ${recent.map((term, idx) => `
                    <a href="#" data-result-index="${idx}" data-recent-search="${escapeHtml(term)}" 
                       class="group block px-4 py-2.5 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 transition-all duration-200"
                       role="option" aria-selected="false">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-emerald-100 flex items-center justify-center transition-colors">
                                <i class="fas fa-clock-rotate-left text-gray-400 group-hover:text-emerald-600 text-xs transition-colors"></i>
                            </div>
                            <span class="text-sm text-gray-700 group-hover:text-emerald-700 font-medium transition-colors">${escapeHtml(term)}</span>
                        </div>
                    </a>
                `).join('')}
            </div>
        `;
    }
    
    function renderResults(query, payload) {
        const ip = Array.isArray(payload?.ip_records) ? payload.ip_records : [];
        const docs = Array.isArray(payload?.documents) ? payload.documents : [];

        const q = escapeHtml(query);

        const head = `
            <div class="px-4 py-3 bg-gradient-to-r from-emerald-500 to-green-600 sticky top-0 z-10">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-magnifying-glass text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white">Search Results</div>
                            <div class="text-[11px] text-emerald-100">for "${q}"</div>
                        </div>
                    </div>
                    <div class="text-[10px] text-white bg-white bg-opacity-20 px-2 py-1 rounded-md backdrop-blur-sm">
                        <kbd class="px-1 bg-white bg-opacity-30 rounded text-[9px]">↑</kbd>
                        <kbd class="px-1 bg-white bg-opacity-30 rounded text-[9px]">↓</kbd>
                        navigate
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-tab="records" 
                            class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-white bg-opacity-20 text-white text-xs font-semibold backdrop-blur-sm hover:bg-opacity-30 transition-all">
                        <i class="fas fa-folder-open text-[11px]"></i>
                        <span>Records</span>
                        <span class="px-1.5 py-0.5 rounded-md bg-white bg-opacity-30 text-white text-[10px] font-bold min-w-[20px] text-center">${ip.length}</span>
                    </button>
                    <button type="button" data-tab="files" 
                            class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-white bg-opacity-20 text-white text-xs font-semibold backdrop-blur-sm hover:bg-opacity-30 transition-all">
                        <i class="fas fa-file-lines text-[11px]"></i>
                        <span>Files</span>
                        <span class="px-1.5 py-0.5 rounded-md bg-white bg-opacity-30 text-white text-[10px] font-bold min-w-[20px] text-center">${docs.length}</span>
                    </button>
                </div>
            </div>
        `;

        const emptyPanel = `
            <div class="px-4 py-10 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-3xl text-gray-300"></i>
                </div>
                <p class="text-sm font-medium text-gray-700 mb-1">No matches found</p>
                <p class="text-xs text-gray-500">Try different keywords or press Enter for full search</p>
            </div>
        `;

        const recordItems = [];
        const fileItems = [];
        let resultIndex = 0;

        for (const r of ip.slice(0, 7)) {
            const title = highlightMatch(r.title, query);
            const typeName = escapeHtml(r.type_name);
            const status = escapeHtml(r.status || '');
            const link = escapeHtml(recordLink(r.id));
            
            const statusConfig = {
                'approved': { bg: 'bg-green-100', text: 'text-green-700', icon: 'fa-check-circle' },
                'pending': { bg: 'bg-yellow-100', text: 'text-yellow-700', icon: 'fa-clock' },
                'active': { bg: 'bg-emerald-100', text: 'text-emerald-700', icon: 'fa-circle-check' },
                'rejected': { bg: 'bg-red-100', text: 'text-red-700', icon: 'fa-times-circle' },
                'expired': { bg: 'bg-gray-100', text: 'text-gray-700', icon: 'fa-calendar-xmark' }
            };
            const config = statusConfig[status] || { bg: 'bg-gray-100', text: 'text-gray-700', icon: 'fa-circle' };
            
            recordItems.push(`
                <a href="${link}" data-result-index="${resultIndex++}" role="option" aria-selected="false"
                   class="group block px-4 py-3 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 border-b border-gray-100 last:border-b-0 transition-all duration-200">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-100 to-green-100 group-hover:from-emerald-200 group-hover:to-green-200 flex items-center justify-center text-emerald-700 shadow-sm transition-all">
                            <i class="fas fa-folder-open text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 group-hover:text-emerald-700 mb-1 transition-colors line-clamp-1">${title}</div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[11px] text-gray-600 flex items-center gap-1">
                                    <i class="fas fa-tag text-[9px]"></i>
                                    ${typeName}
                                </span>
                                ${status ? `
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold ${config.bg} ${config.text}">
                                        <i class="fas ${config.icon} text-[8px]"></i>
                                        ${status}
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all text-xs mt-3"></i>
                    </div>
                </a>
            `);
        }

        for (const d of docs.slice(0, 10)) {
            const filename = highlightMatch(d.original_name || d.file_name || 'Document', query);
            const recordTitle = escapeHtml(d.ip_title || '');
            const link = escapeHtml(recordLink(d.ip_record_id));
            const fileId = d.id || 0;
            const fileName = escapeHtml(d.original_name || d.file_name || 'Document');
            
            // Get file extension for icon
            const ext = (d.original_name || d.file_name || '').split('.').pop().toLowerCase();
            const fileIconConfig = {
                'pdf': { icon: 'fa-file-pdf', color: 'from-red-100 to-red-200', textColor: 'text-red-700' },
                'doc': { icon: 'fa-file-word', color: 'from-blue-100 to-blue-200', textColor: 'text-blue-700' },
                'docx': { icon: 'fa-file-word', color: 'from-blue-100 to-blue-200', textColor: 'text-blue-700' },
                'xls': { icon: 'fa-file-excel', color: 'from-green-100 to-green-200', textColor: 'text-green-700' },
                'xlsx': { icon: 'fa-file-excel', color: 'from-green-100 to-green-200', textColor: 'text-green-700' },
                'ppt': { icon: 'fa-file-powerpoint', color: 'from-orange-100 to-orange-200', textColor: 'text-orange-700' },
                'pptx': { icon: 'fa-file-powerpoint', color: 'from-orange-100 to-orange-200', textColor: 'text-orange-700' },
                'jpg': { icon: 'fa-file-image', color: 'from-purple-100 to-purple-200', textColor: 'text-purple-700' },
                'jpeg': { icon: 'fa-file-image', color: 'from-purple-100 to-purple-200', textColor: 'text-purple-700' },
                'png': { icon: 'fa-file-image', color: 'from-purple-100 to-purple-200', textColor: 'text-purple-700' },
                'zip': { icon: 'fa-file-zipper', color: 'from-gray-100 to-gray-200', textColor: 'text-gray-700' },
            };
            const fileConfig = fileIconConfig[ext] || { icon: 'fa-file-lines', color: 'from-blue-100 to-blue-200', textColor: 'text-blue-700' };
            
            fileItems.push(`
                <a href="#" data-result-index="${resultIndex++}" data-file-id="${fileId}" 
                   data-file-name="${fileName}" 
                   data-record-title="${recordTitle}"
                   data-record-id="${d.ip_record_id || 0}"
                   data-record-link="${link}"
                   class="file-result-item group block px-4 py-3 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 border-b border-gray-100 last:border-b-0 transition-all duration-200"
                   role="option" aria-selected="false">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 w-10 h-10 rounded-xl bg-gradient-to-br ${fileConfig.color} group-hover:shadow-md flex items-center justify-center ${fileConfig.textColor} transition-all">
                            <i class="fas ${fileConfig.icon} text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-700 mb-1 transition-colors line-clamp-1">${filename}</div>
                            ${recordTitle ? `
                                <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                    <i class="fas fa-folder text-[9px]"></i>
                                    <span class="truncate">${recordTitle}</span>
                                </div>
                            ` : ''}
                        </div>
                        <i class="fas fa-external-link text-gray-400 group-hover:text-blue-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all text-xs mt-3"></i>
                    </div>
                </a>
            `);
        }

        const recordsPanel = recordItems.length ? recordItems.join('') : emptyPanel;
        const filesPanel = fileItems.length ? fileItems.join('') : emptyPanel;

        // Store results for keyboard navigation
        searchResults = [...ip.slice(0, 7), ...docs.slice(0, 10)];

        return head + `
            <div data-panel="records" class="max-h-96 overflow-y-auto custom-scrollbar bg-white">${recordsPanel}</div>
            <div data-panel="files" class="max-h-96 overflow-y-auto custom-scrollbar bg-white hidden">${filesPanel}</div>
        `;
    }

    function debounce(fn, wait) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    }

    // ===========================
    // Search Functions
    // ===========================
    
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
        const q = String(query || '').trim();
        if (!q) return;
        
        saveRecentSearch(q);
        const target = buildUrl(q);
        if (target) {
            window.location.href = target;
        }
    }

    const fetchSuggestions = debounce(async (query, dropdownEl, isMobile) => {
        const q = String(query || '').trim();
        if (!dropdownEl) return;

        if (q.length < MIN_SEARCH_LENGTH) {
            hideDropdown(dropdownEl);
            hideLoading(isMobile);
            return;
        }

        if (!suggestionsUrl) {
            showDropdown(dropdownEl, `
                <div class="p-4 text-center text-gray-500">
                    <p class="text-sm">Press <kbd class="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono">Enter</kbd> to search "${escapeHtml(q)}"</p>
                </div>
            `);
            hideLoading(isMobile);
            return;
        }

        if (q === lastQuery) return;
        lastQuery = q;

        showLoading(isMobile);

        try {
            aborter?.abort();
        } catch (e) {
            // ignore
        }
        aborter = new AbortController();

        try {
            const url = new URL(suggestionsUrl, window.location.origin);
            url.searchParams.set('q', q);
            url.searchParams.set('limit', '7');

            const res = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                signal: aborter.signal
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const data = await res.json();
            const html = renderResults(q, data);
            showDropdown(dropdownEl, html);

            const hasFiles = Array.isArray(data?.documents) && data.documents.length > 0;
            const hasRecords = Array.isArray(data?.ip_records) && data.ip_records.length > 0;
            const preferred = hasRecords ? 'records' : (hasFiles ? 'files' : 'records');
            setActiveTab(dropdownEl, preferred);
            
            selectedIndex = -1; // Reset selection
            
        } catch (e) {
            if (e?.name === 'AbortError') return;
            
            showDropdown(dropdownEl, `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-xl mb-2"></i>
                    <p class="text-sm">Unable to fetch suggestions</p>
                    <p class="text-xs text-gray-400 mt-1">Press Enter to search "${escapeHtml(q)}"</p>
                </div>
            `);
        } finally {
            hideLoading(isMobile);
        }
    }, DEBOUNCE_DELAY);

    // ===========================
    // Event Handlers
    // ===========================
    
    function wireInput(input, isMobile) {
        if (!input) return;
        const dropdownEl = isMobile ? mobileDropdown : desktopDropdown;

        input.addEventListener('input', () => {
            updateClearButton(isMobile);
            const q = input.value.trim();
            
            if (q.length === 0) {
                hideDropdown(dropdownEl);
                hideLoading(isMobile);
            } else if (q.length >= MIN_SEARCH_LENGTH) {
                fetchSuggestions(input.value, dropdownEl, isMobile);
            }
        });

        input.addEventListener('keydown', (e) => {
            handleKeyboardNav(e, dropdownEl);
            
            if (e.key === 'Enter' && selectedIndex === -1) {
                e.preventDefault();
                hideDropdown(dropdownEl);
                submit(input.value);
            }
        });

        input.addEventListener('focus', () => {
            const q = input.value.trim();
            if (q.length >= MIN_SEARCH_LENGTH) {
                fetchSuggestions(input.value, dropdownEl, isMobile);
            } else if (q.length === 0) {
                // Show recent searches
                showDropdown(dropdownEl, renderRecentSearches(dropdownEl));
            }
        });

        input.addEventListener('blur', () => {
            // Allow click on dropdown links
            setTimeout(() => hideDropdown(dropdownEl), 200);
        });
    }

    function wireButton(button, input, isMobile) {
        if (!button || !input) return;
        const dropdownEl = isMobile ? mobileDropdown : desktopDropdown;
        
        button.addEventListener('click', (e) => {
            e.preventDefault();
            hideDropdown(dropdownEl);
            submit(input.value);
        });
    }
    
    function wireClearButton(button, input, isMobile) {
        if (!button || !input) return;
        const dropdownEl = isMobile ? mobileDropdown : desktopDropdown;
        
        button.addEventListener('click', (e) => {
            e.preventDefault();
            input.value = '';
            input.focus();
            hideDropdown(dropdownEl);
            updateClearButton(isMobile);
            lastQuery = '';
        });
    }

    // ===========================
    // Tab Switching
    // ===========================
    
    function wireDropdownTabs(dropdownEl) {
        if (!dropdownEl) return;
        dropdownEl.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-tab]');
            if (!btn) return;
            e.preventDefault();
            const tab = btn.getAttribute('data-tab');
            setActiveTab(dropdownEl, tab);
            selectedIndex = -1; // Reset selection when switching tabs
        });
    }

    // ===========================
    // Recent Search Handler
    // ===========================
    
    function handleRecentSearchClick(dropdownEl) {
        if (!dropdownEl) return;
        dropdownEl.addEventListener('click', (e) => {
            const link = e.target.closest('[data-recent-search]');
            if (!link) return;
            e.preventDefault();
            
            const term = link.getAttribute('data-recent-search');
            const isMobile = dropdownEl.id === 'mobileSearchDropdown';
            const input = isMobile ? mobileInput : desktopInput;
            
            if (input && term) {
                input.value = term;
                input.focus();
                fetchSuggestions(term, dropdownEl, isMobile);
            }
        });
    }
    
    // ===========================
    // File Preview Handler
    // ===========================
    
    function handleFilePreviewClick(dropdownEl) {
        if (!dropdownEl) return;
        dropdownEl.addEventListener('click', (e) => {
            const fileLink = e.target.closest('.file-result-item');
            if (!fileLink) return;
            e.preventDefault();
            
            const fileData = {
                id: fileLink.getAttribute('data-file-id'),
                name: fileLink.getAttribute('data-file-name'),
                recordTitle: fileLink.getAttribute('data-record-title'),
                recordId: fileLink.getAttribute('data-record-id'),
                recordLink: fileLink.getAttribute('data-record-link'),
                url: `${baseUrl}/document/preview/${fileLink.getAttribute('data-file-id')}`,
                downloadUrl: `${baseUrl}/document/preview/${fileLink.getAttribute('data-file-id')}`
            };
            
            // Close search dropdown
            hideDropdown(dropdownEl);
            
            // Open preview modal
            if (window.openFilePreviewModal) {
                window.openFilePreviewModal(fileData);
            }
        });
    }

    // ===========================
    // Initialize
    // ===========================
    
    wireInput(desktopInput, false);
    wireInput(mobileInput, true);
    wireButton(desktopBtn, desktopInput, false);
    wireButton(mobileBtn, mobileInput, true);
    wireClearButton(desktopClear, desktopInput, false);
    wireClearButton(mobileClear, mobileInput, true);
    wireDropdownTabs(desktopDropdown);
    wireDropdownTabs(mobileDropdown);
    handleRecentSearchClick(desktopDropdown);
    handleRecentSearchClick(mobileDropdown);
    handleFilePreviewClick(desktopDropdown);
    handleFilePreviewClick(mobileDropdown);

    // Close on outside click
    document.addEventListener('click', (e) => {
        const inDesktop = e.target.closest('#globalSearch') || e.target.closest('#globalSearchDropdown') || e.target.closest('#globalSearchClear');
        const inMobile = e.target.closest('#mobileSearch') || e.target.closest('#mobileSearchDropdown') || e.target.closest('#mobileSearchClear');
        if (!inDesktop) hideDropdown(desktopDropdown);
        if (!inMobile) hideDropdown(mobileDropdown);
    });
    
    // Expose clear recent searches function
    window.__clearRecentSearches = () => {
        clearRecentSearches();
        hideDropdown(desktopDropdown);
        hideDropdown(mobileDropdown);
        if (desktopInput) desktopInput.focus();
        if (mobileInput) mobileInput.focus();
    };

    // Initialize clear button visibility
    updateClearButton(false);
    updateClearButton(true);
})();
