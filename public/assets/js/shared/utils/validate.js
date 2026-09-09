// ----------------------------------------------------
// Import UI Alerts
// ----------------------------------------------------
import { displayMessage } from "../ui/index.js";

// ---------------------------------------------------------------
// Form Input Validation
// ---------------------------------------------------------------
/**
 * Validate and sanitize different input types automatically.
 * Detects input type from data-type, name, or type attribute.
 *
 * Supported types: all, email, alpha, numeric, alpha-numeric, name, phone, location
 *
 * @param {HTMLElement|string} elem - Input element or selector
 * @returns {string|boolean} Cleaned string or false if invalid
 */
export function validateInput(elem) {
  const $el = $(elem);
  let content = $el.val() || "";
  let cleaned = content; // don't trim yet to preserve spaces while typing

  // Auto-detect validation tag
  let tag = $el.data("type") || $el.attr("name") || $el.attr("type") || "text";

  tag = tag.toLowerCase();

  switch (tag) {
    case "all": // Accept all (e.g., passwords, notes)
    case "password":
      cleaned = content;
      break;

    case "email":
      cleaned = content.replace(/[^a-zA-Z0-9@._\-]/g, "");
      if (cleaned && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cleaned)) {
        displayMessage("Enter a valid email address", "info");
      }
      break;

    case "alpha": // Only letters and spaces
      cleaned = content.replace(/[^a-zA-Z\s]/g, "");
      cleaned = cleaned.replace(/\s{2,}/g, " ");
      break;

    case "numeric":
    case "number":
      cleaned = content.replace(/[^0-9]/g, "");
      break;

    case "alpha-numeric":
    case "text":
    case "username":
      // Allow letters, numbers, spaces, commas, dots, dashes
      cleaned = content.replace(/[^a-zA-Z0-9 ,.\-]/g, "");
      cleaned = cleaned.replace(/\s{2,}/g, " ");
      break;

    case "name":
      // Allow letters, spaces, apostrophes, hyphens
      cleaned = content.replace(/[^a-zA-Z\s'\-]/g, "");
      cleaned = cleaned.replace(/\s{2,}/g, " ");
      if (cleaned.trim().length > 0 && cleaned.trim().length < 2) {
        displayMessage("Enter a valid name", "info");
      }
      break;

    case "phone":
    case "tel":
      // Allow +, digits, spaces, and dashes
      cleaned = content.replace(/[^0-9+\s\-]/g, "");
      cleaned = cleaned.replace(/\s{2,}/g, " ");
      const digitsOnly = cleaned.replace(/\D/g, "");
      if (digitsOnly.length > 0 && digitsOnly.length < 7) {
        displayMessage("Enter a valid phone number", "info");
      }
      break;

    case "location":
    case "address":
    case "city":
      // Allow letters, numbers, commas, periods, dashes, spaces
      cleaned = content.replace(/[^a-zA-Z0-9 ,.\-]/g, "");
      cleaned = cleaned.replace(/\s{2,}/g, " ");
      if (cleaned.trim().length > 0 && cleaned.trim().length < 3) {
        displayMessage("Enter a valid location", "info");
      }
      break;

    default:
      // Fallback: letters, numbers, and single spaces
      cleaned = content.replace(/[^a-zA-Z0-9\s]/g, "");
      cleaned = cleaned.replace(/\s{2,}/g, " ");
  }

  // Update visible field (preserve cursor position properly)
  $el.val(cleaned);

  // Final cleanup value (for returning or final validation)
  const finalValue = cleaned.trim();

  // Validation for empty values
  if (!finalValue) {
    displayMessage("Enter a valid input", "info");
    return "";
  }

  return finalValue;
}
