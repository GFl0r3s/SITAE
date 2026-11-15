// La sesión y los permisos se obtienen del servidor, nunca de localStorage.
let sesion = null;
let tokenCSRF = '';
async function api(ruta, metodo='GET', datos) {
  const opciones = {method:metodo, credentials:'same-origin', cache:'no-store', headers:{}};
  if (metodo !== 'GET') {
    opciones.headers['Content-Type'] = 'application/json';
    opciones.headers['X-CSRF-Token'] = tokenCSRF;
    opciones.body = JSON.stringify(datos ?? {});
  }
  const respuesta = await fetch('api/'+ruta, opciones);
  let contenido;
  try { contenido = await respuesta.json(); }
  catch { throw new Error('El servidor no respondió con JSON. Abre la aplicación desde Apache de MAMP (o desde el enlace de Render) y revisa PHP.'); }
  if (!respuesta.ok) {
    if (respuesta.status===401 && !ruta.startsWith('auth.php')) location.replace('index.html');
    throw new Error(contenido.error || 'No se pudo completar la operación.');
  }
  return contenido;
}
const puedeEditar = () => ['admin','editor'].includes(sesion?.rol);
async function cargarSesion() {
  const datos = await api('auth.php');
  sesion = datos.usuario; tokenCSRF = datos.csrf;
  return sesion;
}
async function iniciarSesion(usuario,password) {
  const datos = await api('auth.php','POST',{accion:'entrar',usuario,password});
  sesion = datos.usuario; tokenCSRF = datos.csrf;
}
async function cerrarSesion() {
  try { await api('auth.php','POST',{accion:'salir'}); location.replace('index.html'); }
  catch (error) { alert(error.message); }
}
async function exigirSesion() {
  await cargarSesion();
  if (!sesion) { location.replace('index.html'); return false; }
  document.querySelector('#who').textContent = sesion.usuario+(puedeEditar()?'':' (solo lectura)');
  document.querySelector('#btnOut').onclick = cerrarSesion;
  const enlace = document.querySelector('#btnUsuarios');
  if (enlace) enlace.hidden = sesion.rol !== 'admin';
  document.body.hidden = false;
  return true;
}
function errorPagina(error) {
  document.body.hidden = false;
  const aviso = document.createElement('p');
  aviso.className = 'alert alert-danger m-3'; aviso.setAttribute('role','alert');
  aviso.textContent = error.message; document.body.prepend(aviso);
}
window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
