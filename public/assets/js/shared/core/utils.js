// ----------------------------------------------------
// Import Config
// ----------------------------------------------------
import HttpResponse from "../core/response.js";

/**
 * Get CSRF token from meta tag
 */
export function getCsrfToken() {
  const meta = $('meta[name="csrf-token"]');
  return meta.length ? meta.attr("content") : null;
}

/**
 * Get API token from local storage
 */
export function getApiToken() {
  return localStorage.getItem("api_token");
}

/**
 * Build request headers.
 */
export function buildRequestHeaders(method, customHeaders = {}) {
  const headers = {
    ...customHeaders,
  };

  /*
  |--------------------------------------------------------------------------
  | Attach CSRF Token
  |--------------------------------------------------------------------------
  */

  if (["POST", "PUT", "PATCH", "DELETE"].includes(method)) {
    const token = getCsrfToken();

    if (token) {
      headers["X-CSRF-TOKEN"] = token;
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Attach API Token
  |--------------------------------------------------------------------------
  */

  const apiToken = getApiToken();

  if (apiToken) {
    headers["Authorization"] = `Bearer ${apiToken}`;
  }

  return headers;
}

/**
 * Append query parameters.
 */
export function buildUrl(url, method, payload) {
  if (method !== "GET") {
    return url;
  }

  if (!payload || typeof payload !== "object") {
    return url;
  }

  const query = new URLSearchParams(payload).toString();

  if (!query) {
    return url;
  }

  return url + (url.includes("?") ? "&" : "?") + query;
}

/**
 * Build request body.
 */
export function buildRequestBody(method, payload, headers) {
  if (method === "GET" || method === "HEAD") {
    return null;
  }

  /*
  |--------------------------------------------------------------------------
  | Multipart FormData
  |--------------------------------------------------------------------------
  */

  if (payload instanceof FormData) {
    delete headers["Content-Type"];
    delete headers["content-type"];

    return payload;
  }

  /*
  |--------------------------------------------------------------------------
  | URL Encoded
  |--------------------------------------------------------------------------
  */

  if (payload instanceof URLSearchParams) {
    headers["Content-Type"] = "application/x-www-form-urlencoded";

    return payload.toString();
  }

  /*
  |--------------------------------------------------------------------------
  | JSON
  |--------------------------------------------------------------------------
  */

  if (payload && typeof payload === "object") {
    headers["Content-Type"] = "application/json";

    return JSON.stringify(payload);
  }

  return payload;
}

// ----------------------------------------------------
//  Detect Response Type
// ----------------------------------------------------
export function detectResponseType(headers) {
  const contentType = headers["content-type"] ?? "";

  const disposition = headers["content-disposition"] ?? "";

  if (disposition.includes("attachment")) {
    return "download";
  }

  if (contentType.includes("application/json")) {
    return "json";
  }

  if (contentType.includes("text/html")) {
    return "html";
  }

  if (contentType.includes("text/plain")) {
    return "text";
  }

  if (contentType.startsWith("image/")) {
    return "blob";
  }

  if (contentType.startsWith("video/")) {
    return "blob";
  }

  if (contentType.startsWith("audio/")) {
    return "blob";
  }

  if (contentType.includes("application/pdf")) {
    return "blob";
  }

  if (contentType.includes("application/octet-stream")) {
    return "blob";
  }

  return "text";
}

// ----------------------------------------------------
//  Get Filename from Content-Disposition Header
// ----------------------------------------------------
export function getFilename(headers) {
  const disposition = headers["content-disposition"];

  if (!disposition) {
    return null;
  }

  const match = disposition.match(/filename="?([^"]+)"?/);

  return match ? match[1] : null;
}

/**
 * Parse HTTP response.
 */
export async function parseResponse(res) {
  const headers = Object.fromEntries(res.headers.entries());

  const type = detectResponseType(headers);

  /*
    |--------------------------------------------------------------------------
    | Empty Responses
    |--------------------------------------------------------------------------
    */

  if (res.status === 204 || res.status === 205) {
    return new HttpResponse({
      ok: true,

      status: res.status,

      statusText: res.statusText,

      headers: headers,

      type: "empty",

      filename: null,

      redirected: false,

      location: null,

      url: res.url,

      data: null,

      raw: res,
    });
  }

  /*
    |--------------------------------------------------------------------------
    | Redirect Responses
    |--------------------------------------------------------------------------
    */

  if (res.status >= 300 && res.status < 400) {
    return new HttpResponse({
      ok: true,

      status: res.status,

      statusText: res.statusText,

      headers: headers,

      type: "redirect",

      location: headers.location ?? null,

      filename: null,

      redirected: true,

      url: res.url,

      data: null,

      raw: res,
    });
  }

  /*
    |--------------------------------------------------------------------------
    | Read Body
    |--------------------------------------------------------------------------
    */

  let data = null;

  try {
    switch (type) {
      case "json":
        data = await res.json();

        break;

      case "html":
      case "text":
        data = await res.text();

        break;

      default:
        data = await res.blob();
    }
  } catch {
    data = null;
  }

  const body =
    type === "json" && data && typeof data === "object" ? data : null;

  return new HttpResponse({
    ok: body.ok ?? res.ok,

    status: body.status ?? res.status,

    statusText: res.statusText,

    message: body.message ?? null,

    error: body.error ?? null,

    data: body.data ?? data,

    headers,

    type,

    filename: getFilename(headers),

    redirected: res.redirected,

    location: headers.location ?? null,

    url: res.url,

    raw: res,
  });
}

/**
 * Build network error response.
 */
export function handleNetworkError(err, endpoint) {
  return new HttpResponse({
    ok: false,

    status: 0,

    statusText: "",

    headers: {},

    type: "network",

    filename: null,

    redirected: false,

    location: null,

    url: endpoint,

    data: null,

    raw: null,

    error: err.name === "AbortError" ? "Timeout" : "NetworkError",

    message: err.name === "AbortError" ? "Request timed out" : err.message,
  });
}

// ----------------------------------------------------
//  Download Blob Response Data
// ----------------------------------------------------
export function downloadResponse(response) {
  if (!response.isDownload()) {
    return;
  }

  const url = URL.createObjectURL(response.blob());

  const link = document.createElement("a");

  link.href = url;

  link.download = response.fileName() ?? "download";

  document.body.appendChild(link);

  link.click();

  link.remove();

  URL.revokeObjectURL(url);

  /*
  * ******* USAGE *******
  *
    const response = await makeRequest(...);

    if (response.isDownload()) {
      downloadResponse(response);
    }
    
  *
  */
}
