// -----------------------------------------------------
// PushClient.js (Hardened, Multi-device Safe)
// -----------------------------------------------------

// ----- Firebase Bootstrap ----- //
import { initFirebase } from "./firebaseConfig.js";

// ----- Firebase Messaging ----- //
import {
  getToken,
  deleteToken,
  onMessage,
} from "https://www.gstatic.com/firebasejs/12.7.0/firebase-messaging.js";

// ----- Core Config ----- //
import { firebaseConfig } from "../core/index.js";
import { makeRequest } from "../core/index.js";

// ----- UI Alerts ----- //
import { displayMessage } from "../ui/index.js";

export default class PushClient {
  static messaging = null;
  static swRegistration = null;
  static initialized = false;

  static enabled = false;
  static permissionWatcher = null;

  // ----- Persist Token Across Reloads ----- //
  static get lastToken() {
    return localStorage.getItem("__push_token");
  }

  static set lastToken(token) {
    if (token) {
      localStorage.setItem("__push_token", token);
    } else {
      localStorage.removeItem("__push_token");
    }
  }

  /* =====================================================
    PHASE 1: BOOTSTRAP (ONCE PER SESSION)
  ===================================================== */
  static async bootstrap(dashboard) {
    if (this.initialized) return;

    if (!("serviceWorker" in navigator) || !("Notification" in window)) {
      return;
    }

    if (!dashboard) return;

    // ----- Ensure Service Worker Is Ready (do NOT register again) ----- //
    this.swRegistration = await navigator.serviceWorker.ready;

    await navigator.serviceWorker.ready;

    const { messaging } = initFirebase();
    this.messaging = messaging;

    // ----- Foreground Message Handler (optional UX) ----- //
    onMessage(this.messaging, () => {
      console.log("[PushClient] Foreground message received");
    });

    // ----- Derive Real State From Permission + Token ----- //
    switch (dashboard) {
      case "customer":
        const isLoggedIn = $(".top-bar").hasClass("logged-in");
        if (isLoggedIn) {
          await this.sync(true);
        }
        break;

      case "admin":
      case "vendor":
        await this.sync(true);
        break;

      default:
        const userLoggedIn = $(".top-bar").hasClass("logged-in");
        if (userLoggedIn) {
          await this.sync(true);
        }
      break;
    }

    this.watchPermission();
    this.toggleButton($(".notification-bell"));

    this.initialized = true;
    console.log("[PushClient] Bootstrap complete");
  }

  /* =====================================================
    PHASE 2: DEVICE IDENTIFIER
  ===================================================== */
  static getDeviceId() {
    let id = localStorage.getItem("push_device_id");
    if (!id) {
      id = crypto.randomUUID();
      localStorage.setItem("push_device_id", id);
    }
    return id;
  }

  /* =====================================================
    PHASE 3A: USER-INITIATED SUBSCRIBE
  ===================================================== */
  static async subscribe() {
    if (!this.messaging) return;

    if (Notification.permission === "denied") {
      displayMessage(
        "Notifications are blocked in browser settings. Enable them manually to continue.",
        "warning"
      );
      return;
    }

    const permission = await Notification.requestPermission();
    if (permission !== "granted") return;

    await this.sync(true);
  }

  /* =====================================================
    PHASE 3B: AUTO SYNC (LOGIN / LOAD / TOKEN ROTATE)
  ===================================================== */
  static async sync(silent = true) {
    if (!this.messaging) return;
    if (Notification.permission !== "granted") return;

    let token;
    try {
      token = await getToken(this.messaging, {
        vapidKey: firebaseConfig.vapidKey,
        serviceWorkerRegistration: this.swRegistration,
      });
    } catch (e) {
      return;
    }

    if (!token) return;

    // ----- Do Not Aggressively Unsubscribe Old Token ----- //
    if (this.lastToken && this.lastToken === token) {
      this.enabled = true;
      this.toggleButton($(".notification-bell"));
      return;
    }

    // ----- Register Token With Backend ----- //
    const result = await this.request(
      "user/subscribe",
      "POST",
      { token, device_id: this.getDeviceId() },
      silent
    );

    if (result && result.status === 200) {
      this.lastToken = token;
      this.enabled = true;
      this.toggleButton($(".notification-bell"));
      console.log("[PushClient] Token synced");
    }
  }

  /* =====================================================
    PHASE 4: MANUAL DISABLE / LOGOUT
  ===================================================== */
  static async unsubscribe() {
    if (!this.messaging) return;

    const token = this.lastToken;
    if (!token) return;

    await this.request("user/unsubscribe", "DELETE", {
      token,
      device_id: this.getDeviceId(),
    });

    try {
      await deleteToken(this.messaging);
    } catch (_) {}

    this.lastToken = null;
    this.enabled = false;
    this.toggleButton($(".notification-bell"));

    console.log("[PushClient] Push disabled");
  }

  /* =====================================================
    PHASE 5: PERMISSION CHANGE WATCHER
    (Event-based, not aggressive polling)
  ===================================================== */
  static watchPermission(interval = 10000) {
    if (this.permissionWatcher) return;

    let lastPermission = Notification.permission;

    this.permissionWatcher = setInterval(async () => {
      if (Notification.permission === lastPermission) return;
      lastPermission = Notification.permission;

      if (lastPermission === "denied") {
        await this.handlePermissionRevoked();
      }

      if (lastPermission === "granted") {
        await this.sync(true);
      }

      this.toggleButton($(".notification-bell"));
    }, interval);
  }

  /* =====================================================
    PHASE 6: CLEANUP ON PERMISSION REVOKE
  ===================================================== */
  static async handlePermissionRevoked() {
    const token = this.lastToken;
    if (!token) return;

    await this.request(
      "user/unsubscribe",
      "DELETE",
      { token, device_id: this.getDeviceId() },
      true
    );

    try {
      await deleteToken(this.messaging);
    } catch (_) {}

    this.lastToken = null;
    this.enabled = false;

    console.log("[PushClient] Permission revoked — token cleaned");
  }

  /* =====================================================
    PHASE 7: NETWORK REQUEST HELPER
  ===================================================== */
  static async request(endpoint, method = "GET", payload = {}, silent = true) {
    try {
      const result = await makeRequest(
        endpoint,
        method,
        payload
      );

      if (!silent && result?.message) {
        displayMessage(
          result.message,
          result.status === 200 ? "success" : "info"
        );
      }

      return true;
    } catch (_) {
      if (!silent) {
        displayMessage("Network error occurred", "warning");
      }
      return false;
    }
  }

  /* =====================================================
  PHASE 8: SOURCE OF TRUTH — PUSH STATE
  ==================================================== */
  static async state() {
    if (!("serviceWorker" in navigator)) return "unsupported";

    let reg;
    try {
      reg = await navigator.serviceWorker.ready;
    } catch (_) {
      return "unsubscribed";
    }

    if (!reg || !reg.pushManager) {
      return "unsubscribed";
    }

    if (Notification.permission === "denied") return "blocked";

    const sub = await reg.pushManager.getSubscription();
    if (sub) return "subscribed";

    return "unsubscribed";
  }

  /* =====================================================
    UI HELPER
  ===================================================== */
  static toggleButton(buttonEl) {
    if (!buttonEl) return;

    $(buttonEl).html(`
      <i class="fa fa-bell ${
        this.enabled ? "bell-active" : "bell-inactive"
      }" aria-hidden="true"></i>
    `);
  }
}

/* =====================================================
  END OF PushClient.js
===================================================== */