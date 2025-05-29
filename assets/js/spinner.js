function showSpinner() {
  document.getElementById('loading-spinner').classList.remove('d-none');
}

function hideSpinner() {
  document.getElementById('loading-spinner').classList.add('d-none');
}

document.addEventListener('DOMContentLoaded', function() {
  const forms = document.querySelectorAll('form');
  
  forms.forEach(form => {
    form.addEventListener('submit', function() {
      if (this.checkValidity()) {
        showSpinner();
      }
    });
  });
  
  document.querySelectorAll('.spinner-trigger').forEach(el => {
    el.addEventListener('click', showSpinner);
  });
});