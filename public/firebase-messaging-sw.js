self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  const path = event.notification.data?.url || '/'
  const target = new URL(path, self.location.origin).href
  event.waitUntil((async () => {
    const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
    for (const client of windows) {
      if ('focus' in client) {
        await client.focus()
        if ('navigate' in client) await client.navigate(target)
        return
      }
    }
    await self.clients.openWindow(target)
  })())
})

importScripts('/firebase-config.js')
importScripts('https://www.gstatic.com/firebasejs/11.10.0/firebase-app-compat.js')
importScripts('https://www.gstatic.com/firebasejs/11.10.0/firebase-messaging-compat.js')

if (self.FIREBASE_CONFIG && self.FIREBASE_CONFIG.apiKey && self.FIREBASE_CONFIG.projectId) {
  firebase.initializeApp(self.FIREBASE_CONFIG)
  const messaging = firebase.messaging()

  messaging.onBackgroundMessage((payload) => {
    // FCM/OS displays notification-payload messages automatically. Only create
    // a notification here for data-only messages to avoid duplicate alerts.
    if (payload.notification) return

    const title = payload.data?.title || 'Play Online Khaiwal'
    const body = payload.data?.body || ''
    self.registration.showNotification(title, {
      body,
      icon: '/icons/icon-192.webp',
      badge: '/favicon.ico',
      data: { url: payload.data?.url || '/' },
      silent: false,
      vibrate: [180, 90, 180],
    })
  })
}
