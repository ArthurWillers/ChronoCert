function showSpinner() {
  document.getElementById('loading-spinner').classList.remove('d-none');
}

function hideSpinner() {
  document.getElementById('loading-spinner').classList.add('d-none');
}

document.addEventListener('DOMContentLoaded', function () {
  // Only add spinner to forms that have the spinner-trigger class
  const formsWithSpinner = document.querySelectorAll('form.spinner-trigger');

  formsWithSpinner.forEach(form => {
    form.addEventListener('submit', function () {
      if (this.checkValidity()) {
        showSpinner();
      }
    });
  });

  // Add spinner to links and buttons with spinner-trigger class
  document.querySelectorAll('.spinner-trigger:not(form)').forEach(el => {
    el.addEventListener('click', showSpinner);
  });
});