// ----------------------------------------------------------------------------------------
// Global Service Worker File
// This file will control firebasebase push notification needed for push service
// It will also control the PWA features needed to make the app installable across devices
// -----------------------------------------------------------------------------------------


// ----------------------------------------------------
// Firebase Messaging Setup (Hardened)
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
    icon: data.icon || "/public/assets/img/icons-192.png",
    badge: data.badge || "/public/assets/img/badge.png",
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
// End of push service section
// ----------------------------------------------------


// ----------------------------------------------------
// PWA Setup
// ----------------------------------------------------
// Cache versioning
const CACHE_NAME = "v1";
const CACHE_EXPIRY_TIME = 3600000; // 1 hour in milliseconds
const CACHE_TIMESTAMP_KEY = "cacheTimestamp";

// Files to cache
const ASSETS_TO_CACHE = [
  "/public/assets/js/sweetalert-2.6.0.min.js",
  // Add other assets you want to cache here
];

// Install event: Cache files
self.addEventListener("install", (event) => {
  self.skipWaiting(); // Activate worker immediately
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log("[Service Worker] Caching assets");
      return cache.addAll(ASSETS_TO_CACHE).catch((error) => {
        console.error("[Service Worker] Failed to cache assets:", error);
      });
    })
  );
});

// Activate event: Clean up old caches and check cache expiry
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((ASSETS_TO_CACHE) => {
      // Get the current time and compare with the stored cache timestamp
      const now = Date.now();
      return caches.open(CACHE_NAME).then((cache) => {
        // Get the cache timestamp from the cache storage or set it to the current time if not set
        return cache.match(CACHE_TIMESTAMP_KEY).then((timestampResponse) => {
          if (timestampResponse) {
            return timestampResponse.json().then((timestampData) => {
              const cacheTimestamp = timestampData.timestamp;
              if (now - cacheTimestamp > CACHE_EXPIRY_TIME) {
                // If cache has expired (more than 1 hour), update the cache
                console.log("[Service Worker] Cache expired, updating...");
                return updateCache(cache);
              } else {
                console.log("[Service Worker] Cache is still valid.");
              }
            });
          } else {
            // If no timestamp exists, this is the first installation, so cache and set timestamp
            console.log("[Service Worker] First install, caching assets...");
            return updateCache(cache);
          }
        });
      });
    })
  );
});

// Update the cache by fetching the latest resources from the network
function updateCache(cache) {
  return cache
    .addAll(ASSETS_TO_CACHE)
    .then(() => {
      // Update the cache timestamp to the current time
      cache.put(
        CACHE_TIMESTAMP_KEY,
        new Response(JSON.stringify({ timestamp: Date.now() }))
      );
      console.log("[Service Worker] Cache updated successfully.");
    })
    .catch((error) => {
      console.error("[Service Worker] Failed to update cache:", error);
    });
}

// Fetch event: Network first for CSS and JS, cache first for others
self.addEventListener("fetch", (event) => {
  const requestUrl = new URL(event.request.url);

  // Determine cache strategy based on request URL
  if (
    requestUrl.pathname.startsWith("/public/assets/css/") ||
    requestUrl.pathname.startsWith("/public/assets/js/") ||
    requestUrl.pathname.startsWith("/js/")
  ) {
    // Network first strategy
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.ok) {
            const clonedResponse = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, clonedResponse).catch((error) => {
                console.error(
                  "[Service Worker] Failed to cache network response:",
                  error,
                );
              });
            });
            return networkResponse;
          }
          throw new Error("Network response was not ok");
        })
        .catch(() => {
          return caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            return new Response("Offline", {
              status: 503,
              statusText: "Service Unavailable",
            });
          });
        }),
    );
  } else {
    // Cache first strategy for other assets
    event.respondWith(
      caches.match(event.request).then((cachedResponse) => {
        if (cachedResponse) {
          console.log(
            `[Service Worker] Returning cached response for ${event.request.url}`,
          );
          return cachedResponse;
        }
        return fetch(event.request).catch((error) => {
          console.error("[Service Worker] Fetch failed:", error);
          return new Response("Offline", {
            status: 503,
            statusText: "Service Unavailable",
          });
        });
      }),
    );
  }
});
