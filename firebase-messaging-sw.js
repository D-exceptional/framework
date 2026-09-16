// ----------------------------------------------------
// Firebase Messaging Service Worker (Hardened)
// ----------------------------------------------------

// Import Firebase (compat for SW stability)
importScripts(
  "https://www.gstatic.com/firebasejs/12.7.0/firebase-app-compat.js",
  "https://www.gstatic.com/firebasejs/12.7.0/firebase-messaging-compat.js"
);

// ----------------------------------------------------
// Initialize Firebase App
// ----------------------------------------------------
firebase.initializeApp({
  apiKey: "",
  authDomain: "",
  projectId: "",
  messagingSenderId: "",
  appId: "",
});

// ----------------------------------------------------
// Initialize Messaging
// ----------------------------------------------------
const messaging = firebase.messaging();

// ----------------------------------------------------
// Normalize incoming payload (critical for cross-device)
// ----------------------------------------------------
function normalizePayload(payload = {}) {
  const notification = payload.notification || {};
  const data = payload.data || {};

  return {
    title: notification.title || data.title || "Notification",
    body: notification.body || data.body || "",
    icon: data.icon || "/assets/img/logo-192.png",
    badge: data.badge || "/assets/img/badge.png",
    click_action: data.click_action || "/login",
    data,
  };
}

// ----------------------------------------------------
// Show Notification Helper
// ----------------------------------------------------
function showNotification(payload) {
  const n = normalizePayload(payload);

  return self.registration.showNotification(n.title, {
    body: n.body,
    icon: n.icon,
    badge: n.badge,
    data: n.data,
    tag: n.data.tag || "general",
    renotify: false,
  });
}

// ----------------------------------------------------
// Background message handler (FCM)
// ----------------------------------------------------
messaging.onBackgroundMessage((payload) => {
  showNotification(payload);
});

// ----------------------------------------------------
// Fallback: raw push event (important for some browsers)
// ----------------------------------------------------
self.addEventListener("push", (event) => {
  if (!event.data) return;

  let payload = {};
  try {
    payload = event.data.json();
  } catch (e) {
    return;
  }

  event.waitUntil(showNotification(payload));
});

// ----------------------------------------------------
// Handle notification click
// ----------------------------------------------------
self.addEventListener("notificationclick", (event) => {
  event.notification.close();

  const clickAction = event.notification?.data?.click_action || "/login";

  event.waitUntil(
    clients
      .matchAll({ type: "window", includeUncontrolled: true })
      .then((clientList) => {
        for (const client of clientList) {
          if (client.url.includes(clickAction) && "focus" in client) {
            return client.focus();
          }
        }
        if (clients.openWindow) {
          return clients.openWindow(clickAction);
        }
      })
  );
});
// ----------------------------------------------------
// End of file
// ----------------------------------------------------