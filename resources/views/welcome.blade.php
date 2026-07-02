<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Reverb Real-time Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <style>
        /* Animation for toast */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-enter {
            animation: slideIn 0.3s ease-out forwards;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">

    <nav
        class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex justify-between items-center sticky top-0 z-40">
        <div class="text-xl font-bold text-indigo-600 flex items-center gap-2">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white">R</div>
            Reverb Tester
        </div>

        <div class="relative">
            <button class="relative p-2 rounded-full hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                    </path>
                </svg>
                <span id="badge-count"
                    class="hidden absolute top-0 right-0 bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full animate-pulse border-2 border-white">
                    0
                </span>
            </button>
        </div>
    </nav>

    <main class="p-8 max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Live Notification Listener</h1>
            <p class="text-gray-600 mb-8">
                Keep this page open. Use Bruno to hit your backend API (http://10.10.10.55:60/api/...).
                When the backend broadcasts an event, it will automatically appear here.
            </p>

            <div class="bg-gray-900 rounded-xl p-5 overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-700 pb-3 mb-3">
                    <span class="text-xs font-mono text-gray-400">Connection Status Log</span>
                    <div class="flex gap-2">
                        <div id="status-indicator" class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    </div>
                </div>
                <pre
                    class="text-sm font-mono text-green-400 overflow-x-auto whitespace-pre-wrap"><code id="console-log">Initializing Echo...</code></pre>
            </div>
        </div>
    </main>

    <div id="toast-container" class="fixed top-20 right-6 z-50 flex flex-col gap-3 pointer-events-none">
    </div>

    <script>
        // Setup state variables
        let unreadCount = 0;
        const consoleLog = document.getElementById('console-log');
        const statusIndicator = document.getElementById('status-indicator');
        const badgeCount = document.getElementById('badge-count');
        const toastContainer = document.getElementById('toast-container');

        // Helper to log to on-screen console
        function logMsg(msg) {
            consoleLog.innerHTML += `\n> ${msg}`;
        }

        // Helper to create and show a toast popup
        function showToast(title, message) {
            const toast = document.createElement('div');
            toast.className = 'w-80 bg-white rounded-lg shadow-xl border border-gray-200 p-4 flex gap-3 pointer-events-auto toast-enter';

            toast.innerHTML = `
                <div class="text-indigo-500 mt-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-800">${title}</h4>
                    <p class="text-sm text-gray-600 mt-1">${message}</p>
                </div>
            `;

            toastContainer.appendChild(toast);

            // Update badge
            unreadCount++;
            badgeCount.innerText = unreadCount;
            badgeCount.classList.remove('hidden');

            // Remove toast after 5 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        window.Pusher = Pusher;

        try {
            const echo = new window.Echo({
                broadcaster: 'reverb',
                key: 'oskghldeokkdfjdslhnfd',
                wsHost: '10.10.28.53',
                wsPort: 8020,
                wssPort: 8020,
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],

                // Sanctum API Token Authentication Setup
                authEndpoint: '/api/broadcasting/auth',
                auth: {
                    headers: {
                        Authorization: 'Bearer 94|byDi12ddm6JS5JN6rLPy6VMQzHM1K56hT3SKBu9Z7d3eea50',
                        Accept: 'application/json'
                    }
                }
            });

            // Listen for successful connection
            echo.connector.pusher.connection.bind('connected', () => {
                statusIndicator.classList.replace('bg-yellow-500', 'bg-green-500');
                logMsg('Successfully connected to Reverb WebSocket server!');
            });

            // Listen for errors
            echo.connector.pusher.connection.bind('error', (err) => {
                statusIndicator.classList.replace('bg-yellow-500', 'bg-red-500');
                logMsg('Connection error: ' + JSON.stringify(err));
            });

            // ==========================================
            // LISTENER CONFIGURATION
            // =========================================
            const userId =1;

            echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    logMsg('Real-time Notification Received: ' + JSON.stringify(notification));
                    if(notification.type === 'new_order') {
                        showToast('🎉 New Order!', notification.message);
                    } else if (notification.type === 'refunded') {
                        showToast('💳 Refund Processed', notification.message);
                    } else if (notification.type === 'status_updated') {
                        showToast('📦 Status Updated', notification.message);
                    } else if (notification.type === 'cancelled') {
                        showToast('🚫 Order Cancelled', notification.message);
                    } else {
                        showToast('Notification', notification.message || 'New private alert received');
                    }
                });

        } catch (error) {
            logMsg('Error initializing Echo: ' + error.message);
            statusIndicator.classList.replace('bg-yellow-500', 'bg-red-500');
        }
    </script>
</body>

</html>