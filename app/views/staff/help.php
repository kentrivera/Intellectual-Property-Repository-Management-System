<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <?php 
        $pageTitle = 'Help & Support';
        include APP_PATH . '/views/components/sidebar-staff.php'; 
        ?>

        <div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
            <?php include APP_PATH . '/views/components/header.php'; ?>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-4">How can we help you?</h1>
                    <p class="text-gray-600 text-lg">Search our knowledge base or contact support</p>
                </div>

                <!-- Help Search -->
                <div class="max-w-3xl mx-auto mb-12">
                    <div class="relative">
                        <input type="text" id="helpSearch" 
                               placeholder="Search for help articles, guides, and FAQs..." 
                               class="w-full px-6 py-4 pr-14 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none text-lg shadow-md">
                        <button onclick="searchHelp()" class="absolute right-3 top-3 bg-indigo-500 hover:bg-indigo-600 text-white w-10 h-10 rounded-lg transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Quick Help Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition cursor-pointer" onclick="showSection('getting-started')">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-rocket text-blue-500 text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Getting Started</h3>
                        <p class="text-sm text-gray-600">Learn the basics of using the IP Repository</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition cursor-pointer" onclick="showSection('faq')">
                        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-question-circle text-green-500 text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">FAQs</h3>
                        <p class="text-sm text-gray-600">Frequently asked questions and answers</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition cursor-pointer" onclick="showSection('guides')">
                        <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-book text-purple-500 text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">User Guides</h3>
                        <p class="text-sm text-gray-600">Detailed guides and tutorials</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition cursor-pointer" onclick="contactSupport()">
                        <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-headset text-orange-500 text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Contact Support</h3>
                        <p class="text-sm text-gray-600">Get in touch with our support team</p>
                    </div>
                </div>

                <!-- Getting Started Section -->
                <div id="getting-started" class="help-section bg-white rounded-xl shadow-md p-6 lg:p-8 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-rocket text-blue-500 mr-3"></i>
                        Getting Started
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 border border-gray-200 rounded-lg hover:border-indigo-300 transition">
                            <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-user-plus text-indigo-500 mr-2"></i>
                                Creating Your Account
                            </h3>
                            <p class="text-sm text-gray-600 mb-3">Learn how to set up your profile and get started with the system.</p>
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Read more →</a>
                        </div>

                        <div class="p-4 border border-gray-200 rounded-lg hover:border-indigo-300 transition">
                            <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-search text-indigo-500 mr-2"></i>
                                Searching for IP Records
                            </h3>
                            <p class="text-sm text-gray-600 mb-3">Master the search features to quickly find the documents you need.</p>
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Read more →</a>
                        </div>

                        <div class="p-4 border border-gray-200 rounded-lg hover:border-indigo-300 transition">
                            <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-download text-indigo-500 mr-2"></i>
                                Requesting Downloads
                            </h3>
                            <p class="text-sm text-gray-600 mb-3">Understand the process of requesting and downloading documents.</p>
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Read more →</a>
                        </div>

                        <div class="p-4 border border-gray-200 rounded-lg hover:border-indigo-300 transition">
                            <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-bell text-indigo-500 mr-2"></i>
                                Managing Notifications
                            </h3>
                            <p class="text-sm text-gray-600 mb-3">Configure your notification preferences and stay updated.</p>
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Read more →</a>
                        </div>
                    </div>
                </div>

                <!-- FAQs Section -->
                <div id="faq" class="help-section bg-white rounded-xl shadow-md p-6 lg:p-8 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-question-circle text-green-500 mr-3"></i>
                        Frequently Asked Questions
                    </h2>
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg">
                            <button onclick="toggleFaq(1)" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition">
                                <span class="font-semibold text-gray-800">How do I request a document download?</span>
                                <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                            </button>
                            <div id="faq-1" class="faq-content p-4 border-t border-gray-200 bg-gray-50" style="display: none;">
                                <p class="text-gray-600">To request a document download:</p>
                                <ol class="list-decimal list-inside mt-2 space-y-1 text-gray-600">
                                    <li>Navigate to the IP record you're interested in</li>
                                    <li>Click the "Request Download" button</li>
                                    <li>Provide a reason for your request</li>
                                    <li>Submit the request for admin approval</li>
                                    <li>Check "My Requests" page for status updates</li>
                                </ol>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg">
                            <button onclick="toggleFaq(2)" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition">
                                <span class="font-semibold text-gray-800">How long does it take to approve a download request?</span>
                                <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                            </button>
                            <div id="faq-2" class="faq-content p-4 border-t border-gray-200 bg-gray-50" style="display: none;">
                                <p class="text-gray-600">Download requests are typically reviewed within 24-48 hours. You'll receive an email notification once your request has been approved or if additional information is needed.</p>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg">
                            <button onclick="toggleFaq(3)" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition">
                                <span class="font-semibold text-gray-800">Can I download multiple documents at once?</span>
                                <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                            </button>
                            <div id="faq-3" class="faq-content p-4 border-t border-gray-200 bg-gray-50" style="display: none;">
                                <p class="text-gray-600">Yes, you can select multiple documents from an IP record and request them all in a single download request. Each document will require individual approval from the administrator.</p>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg">
                            <button onclick="toggleFaq(4)" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition">
                                <span class="font-semibold text-gray-800">What file formats are supported?</span>
                                <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                            </button>
                            <div id="faq-4" class="faq-content p-4 border-t border-gray-200 bg-gray-50" style="display: none;">
                                <p class="text-gray-600">The system supports various file formats including:</p>
                                <ul class="list-disc list-inside mt-2 space-y-1 text-gray-600">
                                    <li>PDF (.pdf)</li>
                                    <li>Microsoft Word (.doc, .docx)</li>
                                    <li>Images (.jpg, .png, .gif)</li>
                                    <li>Archives (.zip, .rar)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg">
                            <button onclick="toggleFaq(5)" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition">
                                <span class="font-semibold text-gray-800">How do I reset my password?</span>
                                <i class="fas fa-chevron-down faq-icon text-gray-400"></i>
                            </button>
                            <div id="faq-5" class="faq-content p-4 border-t border-gray-200 bg-gray-50" style="display: none;">
                                <p class="text-gray-600">To reset your password, click on your profile picture in the top right, select "Settings", and then choose "Change Password". You'll need to enter your current password and your new password.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Guides Section -->
                <div id="guides" class="help-section bg-white rounded-xl shadow-md p-6 lg:p-8 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-book text-purple-500 mr-3"></i>
                        User Guides & Tutorials
                    </h2>
                    <div class="space-y-4">
                        <a href="#" class="flex items-start p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mr-4">
                                <i class="fas fa-play text-indigo-500"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 mb-1">Complete Beginner's Tutorial</h3>
                                <p class="text-sm text-gray-600 mb-2">A comprehensive video walkthrough for new users covering all basic features.</p>
                                <span class="text-xs text-gray-500"><i class="fas fa-clock mr-1"></i>15 minutes</span>
                            </div>
                        </a>

                        <a href="#" class="flex items-start p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mr-4">
                                <i class="fas fa-file-pdf text-blue-500"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 mb-1">Advanced Search Techniques</h3>
                                <p class="text-sm text-gray-600 mb-2">Learn how to use advanced filters and boolean operators for precise searches.</p>
                                <span class="text-xs text-gray-500"><i class="fas fa-file-download mr-1"></i>PDF Guide</span>
                            </div>
                        </a>

                        <a href="#" class="flex items-start p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 mr-4">
                                <i class="fas fa-lightbulb text-green-500"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 mb-1">Understanding IP Types</h3>
                                <p class="text-sm text-gray-600 mb-2">Detailed explanation of patents, trademarks, copyrights, and industrial designs.</p>
                                <span class="text-xs text-gray-500"><i class="fas fa-clock mr-1"></i>10 minutes</span>
                            </div>
                        </a>

                        <a href="#" class="flex items-start p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 mr-4">
                                <i class="fas fa-download text-purple-500"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 mb-1">Download Request Best Practices</h3>
                                <p class="text-sm text-gray-600 mb-2">Tips for writing effective download requests that get approved quickly.</p>
                                <span class="text-xs text-gray-500"><i class="fas fa-file-download mr-1"></i>PDF Guide</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Contact Support Section -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-xl p-8 text-white text-center">
                    <i class="fas fa-headset text-6xl mb-4 opacity-90"></i>
                    <h2 class="text-2xl font-bold mb-2">Still need help?</h2>
                    <p class="text-indigo-100 mb-6">Our support team is here to assist you</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <button onclick="contactSupport()" class="bg-white hover:bg-gray-100 text-indigo-600 px-8 py-3 rounded-lg transition font-semibold shadow-md">
                            <i class="fas fa-envelope mr-2"></i>Contact Support
                        </button>
                        <a href="tel:+15551234567" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-8 py-3 rounded-lg transition font-semibold backdrop-blur">
                            <i class="fas fa-phone mr-2"></i>Call Us
                        </a>
                    </div>
                    <div class="mt-6 text-sm text-indigo-100">
                        <p>Email: support@iprepo.com</p>
                        <p>Phone: +1 (555) 123-4567</p>
                        <p>Hours: Monday - Friday, 9AM - 5PM EST</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/common.js"></script>
    <script src="<?= BASE_URL ?>/js/utils.js"></script>
    <script>
        function searchHelp() {
            const query = document.getElementById('helpSearch').value;
            if (query.trim()) {
                IPRepoUtils.Loading.show('Searching help articles...');
                setTimeout(() => {
                    IPRepoUtils.Loading.hide();
                    showToast('info', `Found 12 results for "${query}"`);
                }, 1000);
            }
        }

        function showSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }

        function toggleFaq(id) {
            const content = document.getElementById(`faq-${id}`);
            const icon = event.currentTarget.querySelector('.faq-icon');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        function contactSupport() {
            Swal.fire({
                title: 'Contact Support',
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Subject *</label>
                            <input type="text" id="supportSubject" class="swal2-input w-full" 
                                   placeholder="Brief description of your issue">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Category *</label>
                            <select id="supportCategory" class="swal2-input w-full">
                                <option value="">Select a category</option>
                                <option value="technical">Technical Issue</option>
                                <option value="account">Account Problem</option>
                                <option value="download">Download Request</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Message *</label>
                            <textarea id="supportMessage" class="swal2-textarea w-full" rows="4" 
                                      placeholder="Please describe your issue in detail..."></textarea>
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Send Message',
                confirmButtonColor: '#6366f1',
                width: 600,
                preConfirm: () => {
                    const subject = document.getElementById('supportSubject').value;
                    const category = document.getElementById('supportCategory').value;
                    const message = document.getElementById('supportMessage').value;
                    
                    if (!subject || !category || !message) {
                        Swal.showValidationMessage('Please fill in all fields');
                        return false;
                    }
                    return { subject, category, message };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    IPRepoUtils.Loading.show('Sending message...');
                    setTimeout(() => {
                        IPRepoUtils.Loading.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Message Sent!',
                            text: 'Our support team will get back to you within 24 hours.',
                            confirmButtonColor: '#6366f1'
                        });
                    }, 1500);
                }
            });
        }

        // Search on Enter key
        document.getElementById('helpSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchHelp();
            }
        });
    </script>
</body>
</html>
