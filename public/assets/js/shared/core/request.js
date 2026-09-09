// ----------------------------------------------------
// Import Config
// ----------------------------------------------------
import { apiUrl } from "../core/index.js";
import {
  buildRequestHeaders,
  buildUrl,
  buildRequestBody,
  parseResponse,
  handleNetworkError,
} from "../core/index.js";
import ResponseHandler from "../core/handler.js";

// ----------------------------------------------------
//  Make Network Requests
// ----------------------------------------------------
/**
 * Robust fetch wrapper with full response return
 * @param {string} url - The endpoint URL
 * @param {string} [method="GET"] - HTTP method (GET, POST, PUT, DELETE, PATCH)
 * @param {object} [payload={}] - Request body or query params
 * @param {object} [customHeaders={}] - Additional headers to include
 * @param {number} [timeout=15000] - Request timeout in milliseconds
 * @returns {Promise<{status: number, ok: boolean, headers: Headers, data: any, raw: Response, error: any}>}
 */

/**
 * Smart Request Handler (JSON + FormData + URLSearchParams)
 */
export async function makeRequest(
  url = "/",
  method = "GET",
  payload = null,
  customHeaders = {},
  timeout = 60000,
) {
  method = method.toUpperCase();

  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), timeout);

  // ------------------------------------------
  // ATTACH CSRF HEADER FOR STATE-CHANGING METHODS
  // ------------------------------------------
  const headers = buildRequestHeaders(method, customHeaders);

  // ------------------------------------------
  // HANDLE GET (query params)
  // ------------------------------------------
  url = buildUrl(url, method, payload);

  // ------------------------------------------
  // HANDLE BODY (POST/PUT/PATCH)
  // ------------------------------------------
  const body = buildRequestBody(method, payload, headers);

  // ------------------------------------------
  // NOW create options (AFTER header fixes)
  // ------------------------------------------
  const options = {
    method,
    headers,
    signal: controller.signal,
    body,
  };

  // Build full endpoint
  const endpoint = apiUrl + url;

  try {
    const res = await fetch(endpoint, {
      ...options,
      redirect: "manual", // Use this line to handle redirects manually
      // redirect: "follow",    // Use this line to allow automatic redirects
    });

    clearTimeout(timeoutId);

    const response = await parseResponse(res);

    ResponseHandler.handle(response);

    return response;
  } catch (err) {
    clearTimeout(timeoutId);

    const response = handleNetworkError(err, endpoint);

    ResponseHandler.handle(response);

    throw handleNetworkError(err, endpoint);
  }
}
