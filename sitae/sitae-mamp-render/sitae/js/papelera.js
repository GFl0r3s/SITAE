(async () => {
  if (!await exigirSesion()) return;
  if (!puedeEditar()) { location.replace('lista.html'); return; }
  $('#rows').onclick = async e => {
    const b = e.target.closest('[data-restaurar]');
    if (!b || !confirm(`¿Restaurar la licencia ${b.dataset.licencia}?`)) return;
    b.disabled = true;
    try {
      await api('licencias.php?accion=restaurar&id='+encodeURIComponent(b.dataset.restaurar),'POST',{});
      await cargar(); $('#mensaje').textContent = 'Licencia restaurada';
    } catch (error) { alert(error.message); b.disabled = false; }
  };
  await cargar();
})().catch(errorPagina);
async function cargar() {
  const lista = (await api('licencias.php?papelera=1')).licencias;
  $('#rows').innerHTML = lista.length ? lista.map(l => `<tr>
    <td class="fw-semibold" data-label="Licencia">${esc(l.licencia)}</td><td data-label="Cód. operador">${esc(l.codOperador)}</td><td data-label="Operador">${esc(l.operador)}</td>
    <td data-label="Eliminada por">${esc(l.eliminado_por_usuario || '—')}</td><td data-label="Fecha y hora">${esc(fmtHora(l.eliminado_en))}</td>
    <td class="text-end acciones"><button class="btn btn-sm btn-outline-success" data-restaurar="${esc(l.num)}" data-licencia="${esc(l.licencia)}"><i class="bi bi-arrow-counterclockwise"></i> Restaurar</button></td>
  </tr>`).join('') : '<tr><td colspan="6" class="text-center text-muted py-4 sin-datos">La papelera está vacía.</td></tr>';
}
