document.addEventListener('DOMContentLoaded', function () {
  const toast_element = document.getElementById('toastMessage');
  if (toast_element) {
    const toast = new bootstrap.Toast(toast_element);
    toast.show();
  }
});