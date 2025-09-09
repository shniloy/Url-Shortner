<?php
// Supabase credentials
const SUPABASE_URL = '#';
const SUPABASE_ANON_KEY = '#';
const HOST_DOMAIN = 'site.com'; // Change to your domain
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom spinner animation */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #3b82f6;
            animation: spin 1s ease infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <!-- Main Container -->
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg space-y-6">
        <h1 class="text-3xl font-bold text-center text-gray-800">Simple URL Shortener</h1>
        <p class="text-center text-gray-500">Paste a long URL to shorten it.</p>
        
        <!-- Form and Input Section -->
        <div id="shortenForm">
            <div>
                <input name="longUrl" id="longUrlInput" type="url" placeholder="Enter your long URL here" class="w-full p-4 rounded-lg border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300">
            </div>
            <!-- Action Button -->
            <div class="flex flex-col sm:flex-row gap-4 mt-4">
                <button type="button" id="shortenBtn" class="w-full bg-blue-600 text-white font-semibold py-4 rounded-lg shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out transform hover:scale-105">
                    Shorten URL
                </button>
            </div>
        </div>

        <!-- Progress Spinner -->
        <div id="progressSpinner" class="hidden flex justify-center items-center py-8">
            <div class="spinner"></div>
        </div>

        <!-- Result Section -->
        <div id="resultContainer" class="hidden">
            <div class="bg-gray-50 p-4 rounded-lg border-2 border-gray-200 mt-4 flex items-center justify-between gap-4">
                <span id="shortUrlDisplay" class="truncate font-medium text-blue-600"></span>
                <button id="copyBtn" class="bg-gray-200 text-gray-700 p-2 rounded-lg hover:bg-gray-300 transition duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </button>
            </div>
            <p id="message" class="mt-2 text-center"></p>
            <div class="flex flex-col sm:flex-row gap-4 mt-4">
                 <button id="shortenAnotherBtn" class="w-full bg-blue-600 text-white font-semibold py-4 rounded-lg shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out transform hover:scale-105">
                    Shorten Another URL
                </button>
            </div>
        </div>
    </div>
    
    <script>
        const longUrlInput = document.getElementById('longUrlInput');
        const shortenBtn = document.getElementById('shortenBtn');
        const shortenForm = document.getElementById('shortenForm');
        const progressSpinner = document.getElementById('progressSpinner');
        const resultContainer = document.getElementById('resultContainer');
        const shortUrlDisplay = document.getElementById('shortUrlDisplay');
        const copyBtn = document.getElementById('copyBtn');
        const messageEl = document.getElementById('message');
        const shortenAnotherBtn = document.getElementById('shortenAnotherBtn');
        
        // PHP-injected constants
        const HOST_DOMAIN = '<?= HOST_DOMAIN ?>';

        const showMessage = (text, color) => {
            messageEl.textContent = text;
            messageEl.className = `mt-2 text-center ${color === 'red' ? 'text-red-500' : 'text-green-500'}`;
        };

        const toggleVisibility = (element, isVisible) => {
            if (isVisible) {
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        };

        const handleShorten = async () => {
            const longUrl = longUrlInput.value.trim();
            if (!longUrl) {
                showMessage('Please enter a valid URL.', 'red');
                return;
            }

            try {
                new URL(longUrl);
            } catch (e) {
                showMessage('The URL format is invalid. Make sure it includes http:// or https://', 'red');
                return;
            }

            // Show progress spinner, hide form
            toggleVisibility(shortenForm, false);
            toggleVisibility(progressSpinner, true);
            toggleVisibility(resultContainer, false);
            showMessage('', '');

            const formData = new FormData();
            formData.append('longUrl', longUrl);

            try {
                // Post to the PHP processor file
                const response = await fetch('process_url.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to save URL.');
                }

                const responseData = await response.json();
                const shortenedUrl = `${HOST_DOMAIN}/${responseData.shortCode}`;
                shortUrlDisplay.textContent = shortenedUrl;
                toggleVisibility(resultContainer, true);
                showMessage('URL shortened successfully!', 'green');

            } catch (error) {
                console.error('Error:', error);
                toggleVisibility(resultContainer, true);
                showMessage(`Error: ${error.message}`, 'red');
            } finally {
                toggleVisibility(progressSpinner, false);
            }
        };

        const copyUrl = () => {
            const tempInput = document.createElement('input');
            tempInput.value = shortUrlDisplay.textContent;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            showMessage('Copied to clipboard!', 'green');
            setTimeout(() => showMessage('', ''), 2000);
        };
        
        const resetForm = () => {
            longUrlInput.value = '';
            toggleVisibility(resultContainer, false);
            toggleVisibility(shortenForm, true);
            showMessage('', '');
        };

        shortenBtn.addEventListener('click', handleShorten);
        copyBtn.addEventListener('click', copyUrl);
        shortenAnotherBtn.addEventListener('click', resetForm);
    </script>
</body>
</html>
