class HttpResponse {
  constructor(response = {}) {
    Object.assign(this, response);
  }

  /**
   * Determine if the request succeeded.
   */
  isSuccessful() {
    return this.ok === true;
  }

  /**
   * Determine if the request failed.
   */
  hasFailed() {
    return !this.ok;
  }

  /**
   * Determine if this is a client error.
   */
  hasClientError() {
    return this.status >= 400 && this.status < 500;
  }

  /**
   * Determine if this is a server error.
   */
  hasServerError() {
    return this.status >= 500;
  }

  /**
   * Is JSON?
   */
  isJson() {
    return this.type === "json";
  }

  /**
   * Is HTML?
   */
  isHtml() {
    return this.type === "html";
  }

  /**
   * Is plain text?
   */
  isText() {
    return this.type === "text";
  }

  /**
   * Is a download?
   */
  isDownload() {
    return this.type === "download";
  }

  /**
   * Is binary data?
   */
  isBlob() {
    return this.type === "blob";
  }

  /**
   * Is redirect?
   */
  isRedirect() {
    return this.redirectUrl() !== null;
  }

  /**
   * Is an empty response?
   */
  isEmpty() {
    return this.type === "empty";
  }

  /**
   * Is a network error?
   */
  isNetworkError() {
    return this.type === "network";
  }

  /**
   * Is a validation error?
   */
  isValidationError() {
    return this.status === 422;
  }

  /**
   * Is unauthorized?
   */
  isUnauthorized() {
    return this.status === 401;
  }

  /**
   * Is forbidden?
   */
  isForbidden() {
    return this.status === 403;
  }

  /**
   * Is not found?
   */
  isNotFound() {
    return this.status === 404;
  }

  /**
   * Is timeout?
   */
  isTimeout() {
    return this.error === "Timeout";
  }

  /**
   * Is bad request?
   */
  isBadRequest() {
    return this.status === 400;
  }

  /**
   * Is too many requests?
   */
  isTooManyRequests() {
    return this.status === 429;
  }

  /**
   * Is conflict?
   */
  isConflict() {
    return this.status === 409;
  }

  /**
   * Is internal server error?
   */
  isInternalServerError() {
    return this.status === 500;
  }

  /**
   * Is service unavailable?
   */
  isServiceUnavailable() {
    return this.status === 503;
  }

  /**
   * Is gateway timeout?
   */
  isGatewayTimeout() {
    return this.status === 504;
  }
  /**
   * Is bad gateway?
   */
  isBadGateway() {
    return this.status === 502;
  }

  /**
   * Is maintenance?
   */
  isMaintenance() {
    return this.status === 503;
  }

  /**
   * Get response data.
   */
  body() {
    return this.data;
  }

  /**
   * Get JSON data.
   */
  json() {
    return this.data;
  }

  /**
   * Get HTML.
   */
  html() {
    return this.data;
  }

  /**
   * Get plain text.
   */
  text() {
    return this.data;
  }

  /**
   * Get blob.
   */
  blob() {
    return this.data;
  }

  /**
   * Get all headers.
   */
  allHeaders() {
    return this.headers ?? {};
  }

  /**
   * Get a single header.
   */
  header(name) {
    return this.headers?.[name.toLowerCase()] ?? null;
  }

  /**
   * Determine if a header exists.
   */
  hasHeader(name) {
    return this.header(name) !== null;
  }

  /**
   * Get HTTP status.
   */
  statusCode() {
    return this.status;
  }

  /**
   * Get status text.
   */
  statusTextValue() {
    return this.statusText;
  }

  /**
   * Get filename.
   */
  fileName() {
    return this.filename;
  }

  /**
   * Get redirect URL.
   */
  redirectUrl() {
    return this.data?.redirect ?? this.header("location");
  }

  /**
   * Redirect to new page.
   */
  redirectToPage(delay = 100) {
    const url = this.redirectUrl();

    if (!url) {
      return false;
    }

    setTimeout(() => {
      window.location = url; // Use this line to handle redirects manually
      // window.location.assign(url);  // For temporary redirects
      //window.location.replace(url); // For permanent redirects
    }, delay);
  }
}

export default HttpResponse;
