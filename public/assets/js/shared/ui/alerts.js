// -------------------------------------------------
// Display Messages
// -------------------------------------------------
export function displayMessage(message, type = "info", fullscreen = false) {
  if (fullscreen) {
    Swal.fire({
      title: message,
      icon: type,
      showConfirmButton: false,
      confirmButtonText: "Ok",
      allowOutsideClick: true,
      timer: 3000,
      timerProgressBar: true,
      customClass: {
        popup: "swal2-fullscreen",
      },
      showClass: {
        popup: "animate__animated animate__fadeInDown",
      },
      hideClass: {
        popup: "animate__animated animate__fadeOutUp",
      },
      width: "100vw",
      padding: 0,
    });
  } else {
    // toast mode
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      showClass: {
        popup: "animate__animated animate__fadeInRight",
      },
      hideClass: {
        popup: "animate__animated animate__fadeOutRight",
      },
    });

    Toast.fire({
      icon: type,
      title: message,
    });
  }
}
