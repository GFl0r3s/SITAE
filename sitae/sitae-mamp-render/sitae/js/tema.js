// Modo claro/oscuro. Se aplica antes de pintar la página para evitar parpadeos.
// La preferencia se guarda en este navegador (cada persona elige la suya).
(function () {
  const CLAVE = 'sitae_tema';
  let guardado = null;
  try { guardado = localStorage.getItem(CLAVE); } catch (e) {}
  const tema = guardado || (window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  document.documentElement.setAttribute('data-bs-theme', tema);
  document.addEventListener('DOMContentLoaded', function () {
    const interruptor = document.getElementById('temaSwitch');
    if (!interruptor) return;
    interruptor.checked = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    interruptor.addEventListener('change', function () {
      const nuevo = interruptor.checked ? 'dark' : 'light';
      document.documentElement.setAttribute('data-bs-theme', nuevo);
      try { localStorage.setItem(CLAVE, nuevo); } catch (e) {}
    });
  });
})();
