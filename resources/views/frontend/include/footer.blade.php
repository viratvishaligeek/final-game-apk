 <footer class="px-6 py-12 bg-black text-white border-t border-black/10 relative z-10 mt-20" style="text-align:center;">
     <div class="max-w-7xl mx-auto text-center space-y-8">
         <div class="flex items-center justify-center gap-2 mb-4">
             <span class="w-12 h-[1px] bg-brand-400/30"></span>
             <span class="text-xl font-black text-white italic tracking-tighter uppercase">SATTA<span
                     class="text-brand-400">786</span></span>
             <span class="w-12 h-[1px] bg-brand-400/30"></span>
         </div>

         <div
             class="max-w-4xl mx-auto text-[10px] font-bold text-gray-300 uppercase tracking-widest leading-loose italic">
             View This WebSite On Your Own Risk. All Of The info Shown Here Is We Are You That Satta Matka Gambling
             Maybe Banned or Illegal On Your Nation. We're Not Responsible For Any Problems or Scam. We Respect All
             Nation Rules/Laws. Should You Not Agree With Our Website Disclaimer Please Quit Our Site Website at This
             Time.
         </div>

         <div
             class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-[9px] font-black uppercase tracking-[0.3em] text-brand-400/40 mb-10">
             <a href="terms-and-conditions.php" class="hover:text-brand-400 transition-colors">Terms &
                 Conditions</a>
             <a href="privacy-policy.php" class="hover:text-brand-400 transition-colors">Privacy Policy</a>
             <a href="disclaimer.php" class="hover:text-brand-400 transition-colors">Disclaimer</a>
         </div>

         <p class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-300">
             Gali Disawar | Satta King 786 | &copy; 2026 satta786.com All Rights Reserved
         </p>
     </div>
 </footer>

<script>
(() => {
    const button = document.getElementById('public-push-subscribe');
    if (!button) return;

    const storageKey = 'public_push_token';
    const setButtonState = (subscribed) => {
        button.textContent = subscribed ? '🔕 Unsubscribe' : '🔔 Subscribe';
        button.setAttribute('aria-pressed', subscribed ? 'true' : 'false');
        button.title = subscribed ? 'Unsubscribe from public notifications' : 'Subscribe to public notifications';
    };

    setButtonState(Boolean(localStorage.getItem(storageKey)));

    button.addEventListener('click', async () => {
        button.disabled = true;
        const originalText = button.textContent;
        try {
            const savedToken = localStorage.getItem(storageKey);
            if (savedToken) {
                const response = await fetch('/api/v1/push/subscribe', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ token: savedToken }),
                });
                if (!response.ok) throw new Error('The server could not remove this subscription.');
                localStorage.removeItem(storageKey);
                setButtonState(false);
                alert('Public notifications unsubscribed on this browser.');
                return;
            }

            if (!window.isSecureContext || !('Notification' in window) || !('serviceWorker' in navigator)) {
                throw new Error('Browser push requires a supported browser and HTTPS.');
            }
            const permission = Notification.permission === 'granted'
                ? 'granted'
                : await Notification.requestPermission();
            if (permission !== 'granted') {
                throw new Error('Notifications were not allowed. Enable them in your browser site settings and try again.');
            }

            const configResponse = await fetch('/firebase-config.json', { cache: 'no-store' });
            if (!configResponse.ok) throw new Error('Firebase web configuration is unavailable.');
            const config = await configResponse.json();
            const { vapidKey, ...firebaseConfig } = config;
            if (!firebaseConfig.apiKey || !firebaseConfig.projectId ||
                !firebaseConfig.messagingSenderId || !firebaseConfig.appId || !vapidKey) {
                throw new Error('Website push is not configured. The server administrator must set the public Firebase web values and VAPID key.');
            }

            const [appSdk, messagingSdk] = await Promise.all([
                import('https://www.gstatic.com/firebasejs/11.10.0/firebase-app.js'),
                import('https://www.gstatic.com/firebasejs/11.10.0/firebase-messaging.js'),
            ]);
            if (!(await messagingSdk.isSupported())) {
                throw new Error('Firebase web push is not supported by this browser.');
            }

            const app = appSdk.getApps().length ? appSdk.getApp() : appSdk.initializeApp(firebaseConfig);
            const messaging = messagingSdk.getMessaging(app);
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            const token = await messagingSdk.getToken(messaging, { vapidKey, serviceWorkerRegistration: registration });
            if (!token) throw new Error('Firebase did not return a browser push token.');

            const response = await fetch('/api/v1/push/subscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ token, platform: 'web' }),
            });
            if (!response.ok) throw new Error('The server could not register this browser for public notifications.');

            localStorage.setItem(storageKey, token);
            setButtonState(true);
            alert('Public notifications enabled for this browser. This does not automatically subscribe your separate mobile app.');
        } catch (error) {
            alert(error?.message || 'Unable to update notification subscription.');
            setButtonState(Boolean(localStorage.getItem(storageKey)));
        } finally {
            button.disabled = false;
            if (!localStorage.getItem(storageKey)) button.textContent = originalText.startsWith('🔕') ? '🔔 Subscribe' : button.textContent;
        }
    });
})();
</script>

