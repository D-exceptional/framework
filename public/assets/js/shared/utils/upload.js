// ----------------------------------------------------
// Import UI Alerts
// ----------------------------------------------------
import { displayMessage } from "../ui/index.js";
// ----------------------------------------------------
// Import Config & Cloudinary Name
// ----------------------------------------------------
import {
  CLOUDINARY_NAME,
  SUPPORTED_IMAGE_EXTENSTIONS,
  MAX_IMAGE_SIZE_MB,
  SUPPORTED_VIDEO_EXTENSTIONS,
  MAX_VIDEO_SIZE_MB,
  SUPPORTED_DOCUMENT_EXTENSTIONS,
  MAX_DOCUMENT_SIZE_MB,
  SUPPORTED_IMAGE_MIMES,
  SUPPORTED_VIDEO_MIMES,
  SUPPORTED_DOCUMENT_MIMES,
} from "../core/index.js";

// ---------------------------------------------------------------
// Preview Files Before Upload
// ---------------------------------------------------------------
const FILE_VALIDATION_RULES = {
  image: {
    label: "Image",
    required: true,
    preview: false, // Set to true to enable auto preview
    previewType: "image",
    extensions: SUPPORTED_IMAGE_EXTENSTIONS,
    mimes: SUPPORTED_IMAGE_MIMES,
    maxSizeMB: MAX_IMAGE_SIZE_MB,
    message: "Select a valid image file (JPG, JPEG, PNG)",
    cloudinary: {
      resource_type: "image",
      folder: "uploads/images",
    },
  },

  video: {
    label: "Video",
    required: false,
    preview: false, // Set to true to enable auto preview
    previewType: "video",
    extensions: SUPPORTED_VIDEO_EXTENSTIONS,
    mimes: SUPPORTED_VIDEO_MIMES,
    maxSizeMB: MAX_VIDEO_SIZE_MB,
    message: "Select a valid video file (MP4, MOV, AVI, MKV)",
    cloudinary: {
      resource_type: "video",
      folder: "uploads/videos",
    },
  },

  document: {
    label: "Document",
    required: true,
    preview: false,
    extensions: SUPPORTED_DOCUMENT_EXTENSTIONS,
    mimes: SUPPORTED_DOCUMENT_MIMES,
    maxSizeMB: MAX_DOCUMENT_SIZE_MB,
    message: "Select a valid document (PDF, DOCX)",
    cloudinary: {
      resource_type: "raw",
      folder: "uploads/documents",
    },
  },
};

function renderPreview(file, rule, container) {
  if (!rule.preview || !container) return;

  container.innerHTML = "";

  const url = URL.createObjectURL(file);

  if (rule.previewType === "image") {
    const img = document.createElement("img");
    img.src = url;
    img.style.maxWidth = "100%";
    img.style.borderRadius = "8px";
    container.appendChild(img);
  }

  if (rule.previewType === "video") {
    const video = document.createElement("video");
    video.src = url;
    video.controls = true;
    video.style.maxWidth = "100%";
    container.appendChild(video);
  }
}

export function previewFile(input, ruleKey = "image", previewContainer = null) {
  const rule = FILE_VALIDATION_RULES[ruleKey];

  if (!rule) {
    return { ok: false, message: "Invalid file rule", type: "warning" };
  }

  if (!input.files || !input.files[0]) {
    if (rule.required) {
      return {
        ok: false,
        message: `${rule.label} is required`,
        type: "warning",
      };
    }

    return { ok: true, message: "No file selected", type: "info" };
  }

  const file = input.files[0];
  const extension = file.name.split(".").pop().toLowerCase();
  const mimeType = file.type;
  const sizeInMB = file.size / (1024 * 1024);

  if (!rule.extensions.includes(extension) || !rule.mimes.includes(mimeType)) {
    input.value = "";
    return { ok: false, message: rule.message, type: "warning" };
  }

  if (sizeInMB > rule.maxSizeMB) {
    input.value = "";
    return {
      ok: false,
      message: `${rule.label} too large. Max ${rule.maxSizeMB} MB`,
      type: "warning",
    };
  }

  // 🔥 Auto preview
  renderPreview(file, rule, previewContainer);

  return {
    ok: true,
    message: `${rule.label} supported (${sizeInMB.toFixed(2)} MB)`,
    type: "success",
    file,
    cloudinary: rule.cloudinary, // 👈 pass-through
  };
}

// ---------------------------------------------------------------
// Upload File To Cloudinary
// ------------------------------------------------------------------
export function uploadToCloudinary(file, folderName) {

  const uploadPresets = {
    documents: "preset_ecommerce_documents",
    products: "preset_ecommerce_products",
    shops: "preset_ecommerce_shops",
    uploads: "preset_ecommerce_uploads",
  };

  const preset = uploadPresets[folderName];
  if (!preset) {
      displayMessage("No upload preset found for this folder", "warning");
    return Promise.reject("No upload preset found for this folder");
  }

  const url = `https://api.cloudinary.com/v1_1/${CLOUDINARY_NAME}/upload`;
  const formData = new FormData();
  formData.append("upload_preset", preset);
  formData.append("file", file);

  return fetch(url, { method: "POST", body: formData })
    .then((res) => {
      if (!res.ok) throw new Error("Upload failed");
      return res.json();
    })
    .then((data) => {
      if (data.error) throw new Error(data.error.message);
      return data.secure_url;
    })
    .catch((err) => {
        displayMessage(`Upload error occured: ${err}`, "warning");
      throw err;
    });
}
