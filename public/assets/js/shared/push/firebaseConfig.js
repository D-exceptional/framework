// ----- Firebase Core ----- //
import {
  initializeApp,
  getApps,
  getApp,
} from "https://www.gstatic.com/firebasejs/12.7.0/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-analytics.js";

// ----- Messaging ----- //
import { getMessaging } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-messaging.js";

// ----- Import Configs ----- //
import { firebaseConfig } from "../../../assets/js/core/index.js";

// ----- Initialize Firebase Only Once ----- //
export function initFirebase() {
  const app = getApps().length ? getApp() : initializeApp(firebaseConfig);
  const messaging = getMessaging(app);
  const analytics = getAnalytics(app);

  return { app, messaging, analytics };
}
