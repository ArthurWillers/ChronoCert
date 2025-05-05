function toggle_password_visibility(input_id, button) {
  const input = document.getElementById(input_id);
  if (input.type === "password") {
      input.type = "text";
      button.innerHTML = '<i class="bi bi-eye"></i>';
  } else {
      input.type = "password";
      button.innerHTML = '<i class="bi bi-eye-slash"></i>';
  }
}