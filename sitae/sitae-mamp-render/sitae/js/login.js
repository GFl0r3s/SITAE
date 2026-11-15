const formulario = document.querySelector('#loginForm');
const boton = formulario.querySelector('button[type=submit]');
boton.disabled = true;
cargarSesion().then(() => {
  if (sesion) location.replace('lista.html');
  else boton.disabled = false;
}).catch(error => {
  document.querySelector('#lerr').textContent = error.message;
  document.querySelector('#lerr').hidden = false;
});
formulario.addEventListener('submit',async e => {
  e.preventDefault(); boton.disabled = true;
  try {
    await iniciarSesion(document.querySelector('#lu').value.trim(),document.querySelector('#lp').value);
    location.replace('lista.html');
  } catch (error) {
    document.querySelector('#lerr').textContent = error.message;
    document.querySelector('#lerr').hidden = false;
    boton.disabled = false;
  }
});
