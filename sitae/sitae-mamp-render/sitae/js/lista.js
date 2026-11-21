let DB = [];
let consultaActual = 0;
(async () => {
  if (!await exigirSesion()) return;
  $('#btnNew').hidden = !puedeEditar();
  $('#btnPapelera').hidden = !puedeEditar();
  $('#q').value = sessionStorage.getItem('sitae_busqueda') || '';
  $('#mensaje').textContent = sessionStorage.getItem('sitae_mensaje') || '';
  sessionStorage.removeItem('sitae_mensaje');
  $('#searchForm').onsubmit = e => { e.preventDefault(); mostrarLista().catch(errorPagina); };
  $('#btnClear').onclick = () => { $('#q').value=''; mostrarLista().catch(errorPagina); };
  $('#rows').onclick = async e => {
    const b = e.target.closest('[data-del]');
    if (!b || !puedeEditar()) return;
    const l = DB.find(x=>Number(x.num)===Number(b.dataset.del));
    if (!l || !confirm(`¿Eliminar la licencia ${l.licencia}? Se enviará a la papelera y podrá restaurarse.`)) return;
    b.disabled = true;
    try {
      await api('licencias.php?id='+encodeURIComponent(l.num),'DELETE',{version:Number(b.dataset.version)});
      await mostrarLista(); $('#mensaje').textContent = 'Licencia enviada a la papelera';
    } catch (error) { alert(error.message); b.disabled=false; }
  };
  await mostrarLista();
})().catch(errorPagina);
async function mostrarLista() {
  const consulta = ++consultaActual;
  const q = $('#q').value.trim();
  sessionStorage.setItem('sitae_busqueda',$('#q').value);
  const datos = await api('licencias.php?q='+encodeURIComponent(q));
  if (consulta!==consultaActual) return;
  DB = datos.licencias;
  const lista = DB;
  $('#rows').innerHTML = lista.length ? lista.map(l => `<tr>
    <td data-label="Núm.">${esc(l.num)}</td><td data-label="Cód. operador">${esc(l.codOperador)}</td><td data-label="Operador">${esc(l.operador)}</td>
    <td class="fw-semibold" data-label="Licencia">${esc(l.licencia)}</td><td data-label="Indicativo">${esc(l.indicativo)}</td>
    <td data-label="Estado"><span class="badge ${estado(l)==='VIGENTE'?'badge-v':'badge-x'}">${estado(l)}</span></td>
    <td data-label="Emisión">${esc(fmt(l.emision))}</td><td data-label="Vencimiento">${esc(fmt(l.vencimiento))}</td>
    <td class="text-end text-nowrap acciones"><a class="btn btn-sm btn-outline-primary" href="licencia.html?modo=ver&id=${encodeURIComponent(l.num)}"><i class="bi bi-eye"></i> Ver</a>
    ${puedeEditar()?`<a class="btn btn-sm btn-outline-secondary" href="licencia.html?modo=edit&id=${encodeURIComponent(l.num)}"><i class="bi bi-pencil"></i> Editar</a>
    <button class="btn btn-sm btn-outline-danger" data-del="${esc(l.num)}" data-version="${esc(l.version)}" aria-label="Eliminar ${esc(l.licencia)}"><i class="bi bi-trash"></i></button>`:''}</td>
  </tr>`).join('') : '<tr><td colspan="9" class="text-center text-muted py-4 sin-datos">No se encontró ninguna licencia con ese código.</td></tr>';
}
