// Mostrar u ocultar cada contraseña.
document.querySelectorAll('[data-password]').forEach(boton => {
  const campo = document.getElementById(
    boton.dataset.password
  );

  const descripcion = campo.id === 'password'
    ? 'contraseña'
    : 'confirmación de contraseña';

  boton.addEventListener('click', () => {
    const mostrar = campo.type === 'password';
    campo.type = mostrar ? 'text' : 'password';
    boton.setAttribute(
      'aria-label',
      `${mostrar ? 'Ocultar' : 'Mostrar'} ${descripcion}`
    );
  });
});

// Comprobar que ambas contraseñas coincidan.
const password = document.getElementById('password');
const confirmacion = document.getElementById(
  'confirmar_password'
);

function validarContrasenas() {
  const diferentes =
    confirmacion.value !== password.value;

  confirmacion.setCustomValidity(
    diferentes ? 'Las contraseñas no coinciden.' : ''
  );
}

password.addEventListener('input', validarContrasenas);
confirmacion.addEventListener('input', validarContrasenas);