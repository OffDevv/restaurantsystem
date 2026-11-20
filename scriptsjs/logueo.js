const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');
togglePassword.addEventListener('click', () => {
  const mostrar = password.type === 'password';
  password.type = mostrar ? 'text' : 'password';
  togglePassword.setAttribute(
    'aria-label',
    mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña'
  );
});