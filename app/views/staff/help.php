<?php ob_start(); ?>

<div class="max-w-7xl mx-auto">
    <!-- Hero -->
    <div class="bg-gradient-to-br from-emerald-600 to-green-700 rounded-2xl shadow-md p-4 sm:p-6 mb-4 sm:mb-6 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-base sm:text-lg lg:text-xl font-extrabold">Help & Guide</h1>
                <p class="text-[11px] sm:text-xs text-emerald-50/90 mt-1">Quick guidance for staff users.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="contactSupport()" class="px-4 py-2 rounded-lg bg-white text-emerald-700 hover:bg-emerald-50 font-semibold text-xs shadow-sm">
                    <i class="fas fa-envelope mr-2"></i>Contact Support
                </button>
                <button type="button" onclick="showSection('faq')" class="px-4 py-2 rounded-lg bg-white/15 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs">
                    <i class="fas fa-circle-question mr-2"></i>FAQs
                </button>
            </div>
        </div>
    </div>

    <!-- Quick cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 mb-4 sm:mb-6">
        <button type="button" onclick="showSection('getting-started')" class="text-left bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 hover:shadow-md transition">
            <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-rocket text-emerald-700"></i>
            </div>
            <div class="text-sm font-bold text-gray-900">Getting started</div>
            <div class="text-[11px] text-gray-600 mt-1">Basics of browsing and viewing records.</div>
        </button>

        <button type="button" onclick="showSection('faq')" class="text-left bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 hover:shadow-md transition">
            <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-circle-question text-emerald-700"></i>
            </div>
            <div class="text-sm font-bold text-gray-900">FAQs</div>
            <div class="text-[11px] text-gray-600 mt-1">Quick answers to common questions.</div>
        </button>

        <button type="button" onclick="showSection('guides')" class="text-left bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 hover:shadow-md transition">
            <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-book text-emerald-700"></i>
            </div>
            <div class="text-sm font-bold text-gray-900">Guides</div>
            <div class="text-[11px] text-gray-600 mt-1">Short tutorials and best practices.</div>
        </button>

        <button type="button" onclick="contactSupport()" class="text-left bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 hover:shadow-md transition">
            <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-headset text-emerald-700"></i>
            </div>
            <div class="text-sm font-bold text-gray-900">Contact support</div>
            <div class="text-[11px] text-gray-600 mt-1">Send a message to admin/support.</div>
        </button>
    </div>

    <!-- Getting started -->
    <section id="getting-started" class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-rocket text-emerald-700 text-sm"></i>
            </div>
            <div>
                <h2 class="text-sm sm:text-base font-extrabold text-gray-900">Getting started</h2>
                <p class="text-[11px] sm:text-xs text-gray-600">Common tasks for staff users.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-3 mt-4">
            <div class="bg-white rounded-xl border border-emerald-100 p-4">
                <div class="text-xs font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-folder-open text-emerald-700"></i>Browse IP records</div>
                <p class="text-[11px] text-gray-600 mt-1">Go to “IP Records”, open a record, and preview the documents inside.</p>
                <a href="<?= BASE_URL ?>/staff/ip-records" class="inline-flex items-center gap-2 mt-3 text-xs font-semibold text-emerald-800 hover:text-emerald-900 hover:underline">
                    Open records
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="bg-white rounded-xl border border-emerald-100 p-4">
                <div class="text-xs font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-eye text-emerald-700"></i>Preview a file</div>
                <p class="text-[11px] text-gray-600 mt-1">Open an IP record and use “Preview” to view a file. Downloads require admin approval.</p>
                <a href="<?= BASE_URL ?>/staff/ip-records" class="inline-flex items-center gap-2 mt-3 text-xs font-semibold text-emerald-800 hover:text-emerald-900 hover:underline">
                    Open records
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="bg-white rounded-xl border border-emerald-100 p-4">
                <div class="text-xs font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-download text-emerald-700"></i>Request a download</div>
                <p class="text-[11px] text-gray-600 mt-1">Click “Request Download”, provide a reason, and wait for approval.</p>
                <a href="<?= BASE_URL ?>/staff/my-requests" class="inline-flex items-center gap-2 mt-3 text-xs font-semibold text-emerald-800 hover:text-emerald-900 hover:underline">
                    Track requests
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="bg-white rounded-xl border border-emerald-100 p-4">
                <div class="text-xs font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-bell text-emerald-700"></i>Notifications</div>
                <p class="text-[11px] text-gray-600 mt-1">The bell icon shows approvals/rejections and other updates.</p>
                <div class="text-[11px] text-gray-600 mt-3">Tip: use “Mark all read” to clear the badge.</div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-circle-question text-emerald-700 text-sm"></i>
            </div>
            <div>
                <h2 class="text-sm sm:text-base font-extrabold text-gray-900">Frequently asked questions</h2>
                <p class="text-[11px] sm:text-xs text-gray-600">Click a question to expand.</p>
            </div>
        </div>

        <div class="space-y-2 sm:space-y-3 mt-4">
            <div class="bg-white rounded-xl border border-emerald-100">
                <button type="button" onclick="toggleFaq(1, event)" class="w-full p-4 text-left flex items-center justify-between hover:bg-emerald-50/50 transition">
                    <span class="text-xs sm:text-sm font-bold text-gray-900">How do I request a document download?</span>
                    <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                </button>
                <div id="faq-1" class="faq-content px-4 pb-4 text-[11px] sm:text-xs text-gray-700" style="display: none;">
                    <ol class="list-decimal list-inside mt-2 space-y-1">
                        <li>Open an IP record.</li>
                        <li>Click “Request Download”.</li>
                        <li>Write a reason and submit.</li>
                        <li>Wait for admin approval.</li>
                        <li>Go to “My Requests” to download if approved.</li>
                    </ol>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-emerald-100">
                <button type="button" onclick="toggleFaq(2, event)" class="w-full p-4 text-left flex items-center justify-between hover:bg-emerald-50/50 transition">
                    <span class="text-xs sm:text-sm font-bold text-gray-900">How long does approval take?</span>
                    <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                </button>
                <div id="faq-2" class="faq-content px-4 pb-4 text-[11px] sm:text-xs text-gray-700" style="display: none;">
                    Approvals depend on admin availability. Check “My Requests” and the notification bell for updates.
                </div>
            </div>

            <div class="bg-white rounded-xl border border-emerald-100">
                <button type="button" onclick="toggleFaq(3, event)" class="w-full p-4 text-left flex items-center justify-between hover:bg-emerald-50/50 transition">
                    <span class="text-xs sm:text-sm font-bold text-gray-900">Why can I preview but not download?</span>
                    <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                </button>
                <div id="faq-3" class="faq-content px-4 pb-4 text-[11px] sm:text-xs text-gray-700" style="display: none;">
                    Staff access is view-only by default. Downloads require an approved request to protect sensitive files.
                </div>
            </div>

            <div class="bg-white rounded-xl border border-emerald-100">
                <button type="button" onclick="toggleFaq(4, event)" class="w-full p-4 text-left flex items-center justify-between hover:bg-emerald-50/50 transition">
                    <span class="text-xs sm:text-sm font-bold text-gray-900">What file formats are supported?</span>
                    <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                </button>
                <div id="faq-4" class="faq-content px-4 pb-4 text-[11px] sm:text-xs text-gray-700" style="display: none;">
                    Common formats like PDF, Word, images, and archives are supported (depends on admin upload rules).
                </div>
            </div>
        </div>
    </section>

    <!-- Guides -->
    <section id="guides" class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-book text-emerald-700 text-sm"></i>
            </div>
            <div>
                <h2 class="text-sm sm:text-base font-extrabold text-gray-900">Guides</h2>
                <p class="text-[11px] sm:text-xs text-gray-600">Short, actionable how-tos.</p>
            </div>
        </div>

        <div class="mt-4 space-y-2 sm:space-y-3">
            <div class="bg-white rounded-xl border border-emerald-100 p-4">
                <div class="text-xs font-bold text-gray-900">Write a good download request</div>
                <p class="text-[11px] text-gray-600 mt-1">Include purpose, timeframe, and where the file will be used.</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-100 p-4">
                <div class="text-xs font-bold text-gray-900">Find what you need faster</div>
                <p class="text-[11px] text-gray-600 mt-1">Use record titles, keywords, tags, and filenames inside IP records.</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-100 p-4">
                <div class="text-xs font-bold text-gray-900">Understand approvals</div>
                <p class="text-[11px] text-gray-600 mt-1">Approved links can expire or have limited downloads.</p>
            </div>
        </div>
    </section>

    <!-- Support -->
    <section class="bg-gradient-to-br from-emerald-600 to-green-700 rounded-2xl shadow-md p-4 sm:p-6 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-sm sm:text-base font-extrabold">Still need help?</h2>
                <p class="text-[11px] sm:text-xs text-emerald-50/90 mt-1">Send a message and we’ll get back to you.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="contactSupport()" class="px-4 py-2 rounded-lg bg-white text-emerald-700 hover:bg-emerald-50 font-semibold text-xs shadow-sm">
                    <i class="fas fa-envelope mr-2"></i>Contact Support
                </button>
                <a href="tel:+15551234567" class="px-4 py-2 rounded-lg bg-white/15 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs">
                    <i class="fas fa-phone mr-2"></i>Call
                </a>
            </div>
        </div>
        <div class="mt-4 text-[11px] sm:text-xs text-emerald-50/90">
            <div>Email: support@iprepo.com</div>
            <div>Phone: +1 (555) 123-4567</div>
            <div>Hours: Mon–Fri, 9AM–5PM</div>
        </div>
    </section>
</div>

<script src="<?= BASE_URL ?>/js/common.js?v=<?= filemtime(PUBLIC_PATH . '/js/common.js') ?>"></script>
<script src="<?= BASE_URL ?>/js/utils.js?v=<?= filemtime(PUBLIC_PATH . '/js/utils.js') ?>"></script>
<script>
    function showSection(sectionId) {
        const el = document.getElementById(sectionId);
        if (!el) return;
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function toggleFaq(id, evt) {
        const content = document.getElementById(`faq-${id}`);
        const icon = evt?.currentTarget?.querySelector('.faq-icon');
        if (!content) return;

        const isOpen = content.style.display === 'block';
        const nextOpen = !isOpen;
        content.style.display = nextOpen ? 'block' : 'none';

        if (icon) {
            icon.classList.toggle('fa-chevron-down', !nextOpen);
            icon.classList.toggle('fa-chevron-up', nextOpen);
        }
    }

    function contactSupport() {
        const base = {
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-4 py-2 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700',
                cancelButton: 'px-4 py-2 rounded-lg text-xs font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50'
            }
        };

        Swal.fire({
            ...base,
            title: 'Contact support',
            html: `
                <div class="text-left space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subject *</label>
                        <input type="text" id="supportSubject" class="swal2-input" placeholder="Brief description">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Category *</label>
                        <select id="supportCategory" class="swal2-input">
                            <option value="">Select a category</option>
                            <option value="technical">Technical issue</option>
                            <option value="account">Account problem</option>
                            <option value="download">Download request</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Message *</label>
                        <textarea id="supportMessage" class="swal2-textarea" rows="4" placeholder="Describe your issue..."></textarea>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Send message',
            cancelButtonText: 'Cancel',
            width: 640,
            preConfirm: () => {
                const subject = document.getElementById('supportSubject').value.trim();
                const category = document.getElementById('supportCategory').value.trim();
                const message = document.getElementById('supportMessage').value.trim();

                if (!subject || !category || !message) {
                    Swal.showValidationMessage('Please fill in all fields');
                    return false;
                }
                return { subject, category, message };
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            try {
                if (window.IPRepoUtils?.Loading?.show) {
                    IPRepoUtils.Loading.show('Sending message...');
                }
            } catch (e) {
                // ignore
            }

            setTimeout(() => {
                try {
                    if (window.IPRepoUtils?.Loading?.hide) {
                        IPRepoUtils.Loading.hide();
                    }
                } catch (e) {
                    // ignore
                }
                Swal.fire({
                    ...base,
                    icon: 'success',
                    title: 'Message sent',
                    text: 'Support will get back to you soon.',
                    confirmButtonText: 'OK'
                });
            }, 900);
        });
    }

    // (No page-level search on this screen)
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
