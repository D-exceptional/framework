import { displayMessage } from "../ui/index.js"; 

class ResponseHandler {
  /**
   * Handle a response globally.
   *
   * Returns:
   * true  -> Continue processing.
   * false -> Stop processing.
   */
  static handle(response) {

    // ----------------------------------------------------
    // Redirects (optional)
    // ----------------------------------------------------

    if (response.isRedirect()) {

      response.redirectToPage();

      return false;
    }

    // ----------------------------------------------------
    // Network Errors
    // ----------------------------------------------------

    if (response.hasClientError()) {
      if (response.isTimeout()) {
        displayMessage("The request timed out. Please try again.", "error");
      } else {
        displayMessage(
          response.message || "Unable to connect to the server.",
          "error",
        );
      }

      return false;
    }

    // ----------------------------------------------------
    // Server Errors
    // ----------------------------------------------------

    if (response.hasServerError()) {
      displayMessage(`An unexpected server error occurred and the error is: ${response.message}`, "error");

      return false;
    }

    if (response.isMaintenance()) {
      displayMessage(
        "The system is currently under maintenance. Please try again later.",
        "warning",
      );

      return false;
    }

    if (response.isServiceUnavailable()) {
      displayMessage(
        "The system is currently unavailable. Please try again later.",
        "warning",
      );

      return false;
    }

    if (response.isBadGateway()) {
      displayMessage("The server received an invalid response from an upstream server.", "warning");

      return false;
    }

    if (response.isGatewayTimeout()) {
      displayMessage("The server took too long to respond.", "warning");

      return false;
    }

    // ----------------------------------------------------
    // Rate Limiting
    // ----------------------------------------------------

    if (response.isTooManyRequests()) {
      displayMessage(
        response.message ||
          "Too many requests. Please wait a moment and try again.",
        "warning",
      );

      return false;
    }

    // ----------------------------------------------------
    // Empty Response
    // ----------------------------------------------------

    if (response.isEmpty()) {
      return true;
    }

    // ----------------------------------------------------
    // Everything Else
    // ----------------------------------------------------

    return true;
  }
}

export default ResponseHandler;
