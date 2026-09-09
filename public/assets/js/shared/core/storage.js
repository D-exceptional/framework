// ----------------------------------------------------
// Add To Cart (Logged-in AND Non Logged-in Users)
// ----------------------------------------------------
export const GUEST_CART_KEY = "cart";

export function getGuestCart() {
  const cart = JSON.parse(localStorage.getItem(GUEST_CART_KEY) || "[]");
  return Array.isArray(cart) ? cart : [];
}

export function saveGuestCart(cart) {
  localStorage.setItem(GUEST_CART_KEY, JSON.stringify(cart));
}

export function clearGuestCart() {
  localStorage.removeItem(GUEST_CART_KEY);
}

// ----------------------------------------------------
// Add To Wishlist (Logged-in AND Non Logged-in Users)
// ----------------------------------------------------
export const GUEST_WISHLIST_KEY = "wishlist";

export function getGuestWishlist() {
  const wishlist = JSON.parse(localStorage.getItem(GUEST_WISHLIST_KEY) || "[]");
  return Array.isArray(wishlist) ? wishlist : [];
}

export function saveGuestWishlist(wishlist) {
  localStorage.setItem(GUEST_WISHLIST_KEY, JSON.stringify(wishlist));
}

export function clearGuestWishlist() {
  localStorage.removeItem(GUEST_WISHLIST_KEY);
}

// ----------------------------------------------------
// Manage Push Storage
// ----------------------------------------------------

export function getPush(tag) {
  return localStorage.getItem(tag);
}

export function savePush(tag, value) {
  localStorage.setItem(tag, value);
}

export function clearPush(tag) {
  localStorage.removeItem(tag);
}

// ----------------------------------------------------
// Manage Log
// ----------------------------------------------------

export function getID(tag) {
  return localStorage.getItem(tag);
}

export function saveID(tag, value) {
  localStorage.setItem(tag, value);
}

export function clearID(tag) {
  localStorage.removeItem(tag);
}
