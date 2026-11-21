// Definición de campos: nombre, etiqueta, tipo y columna Bootstrap.
const f = (n,l,t='text',c='col-md-4') => ({n,l,t,c});
const OPERADOR = [f('operador','Operador'),f('denominacion','Denominación'),f('rtn','RTN'),f('identidad','Identidad'),f('direccion','Dirección'),f('municipio','Municipio'),f('departamento','Departamento'),f('pais','País'),f('telefono','Teléfono'),f('fax','Fax'),f('correo','Correo','email'),f('otroTel','Otro teléfono')];
const REPRES = [f('rNombre','Nombre completo'),f('rIdentidad','Identidad'),f('rTelefono','Teléfono'),f('rCorreo','Correo','email'),f('rDireccion','Dirección')];
const AERONAVE = [f('tipo','1.1 Tipo de aeronave'),f('modelo','1.2 Modelo'),f('fuselaje','1.3 Número de fuselaje'),f('registro','1.4 Número de registro nacional'),f('fabricacion','1.5 Fecha de fabricación','date'),f('aeropuerto','1.6 Aeropuerto base'),f('actividad','1.7 Actividad principal'),f('maxPasajeros','1.8 Máx. cantidad de pasajeros','number')];
const ADMIN = [f('indicativo','3.1 Indicativo de llamada'),f('emision','3.2 Fecha de emisión','date'),f('vencimiento','3.3 Fecha de expiración','date'),f('observaciones','3.4 Observaciones','textarea','col-12')];
const EQ = {
  hf:{t:'2.1 Equipo de comunicación (voz) HF',c:[f('marca','Marca'),f('modelo','Modelo'),f('serie','No. serie'),f('banda','Banda de frecuencia'),f('clase','Clase de emisión'),f('cantidad','Cantidad','number')]},
  vhf:{t:'2.2 Equipos de comunicaciones VHF/UHF',c:[f('marca','Marca'),f('modelo','Modelo'),f('serie','No. serie'),f('banda','Banda de frecuencia'),f('clase','Clase de emisión'),f('cantidad','Cantidad','number')]},
  nav:{t:'2.3 Ayudas para la radionavegación',c:[f('tipo','Tipo de equipo'),f('marca','Marca'),f('modelo','Modelo'),f('serie','No. serie'),f('banda','Banda de frecuencia'),f('cantidad','Cantidad','number')]},
  radar:{t:'2.4 Sistema radar',c:[f('tipo','Tipo de equipo'),f('marca','Marca'),f('modelo','Modelo'),f('clase','Clase de emisión'),f('banda','Banda de frecuencia'),f('modulacion','Tipo de modulación'),f('cantidad','Cantidad','number')]},
  emerg:{t:'2.5 Equipos de emergencia',c:[f('tipo','Tipo de equipo'),f('marca','Marca'),f('modelo','Modelo'),f('banda','Banda de frecuencia'),f('cantidad','Cantidad','number')]}
};
const OPCIONALES = ['fax','otroTel','observaciones'];
let contador = 0;
function campo(x,v,equipo=false) {
  const id = 'campo-'+(++contador);
  const atributo = equipo ? `data-f="${x.n}"` : `name="${x.n}"`;
  const obligatorio = !equipo && !OPCIONALES.includes(x.n);
  const requerido = obligatorio ? 'required' : '';
  return `<div class="${x.c}"><label for="${id}" class="form-label small mb-1">${x.l}${obligatorio?' <span class="req text-danger" aria-hidden="true">*</span>':''}</label>${x.t==='textarea'
    ? `<textarea id="${id}" class="form-control" ${atributo} rows="3" ${requerido}>${esc(v)}</textarea>`
    : `<input id="${id}" class="form-control" type="${x.t}" ${atributo} value="${esc(v)}" ${requerido} ${x.t==='number'?'min="0" step="1"':''}>`}</div>`;
}
const grid = (arr,d) => `<div class="row g-3">${arr.map(x=>campo(x,d[x.n])).join('')}</div>`;
const eqRow = (k,d={}) => `<div class="eq" data-k="${k}"><div class="row g-3">${EQ[k].c.map(x=>campo(x,d[x.n],true)).join('')}</div><button type="button" class="btn btn-sm btn-link text-danger no-view mt-2 p-0" data-rm>Quitar equipo</button></div>`;
let cur = null, mode = 'ver';
let codigoSiguiente = '';
const nextCode = () => codigoSiguiente;
(async () => { if (await exigirSesion()) await inicializarFormulario(); })().catch(errorPagina);
async function inicializarFormulario() {
  const parametros = new URLSearchParams(location.search);
  mode = parametros.get('modo') || 'ver';
  if (!['new','edit','ver'].includes(mode)) { location.replace('lista.html'); return; }
  if (mode !== 'ver' && !puedeEditar()) { location.replace('lista.html'); return; }
  if (mode==='new') { codigoSiguiente = (await api('licencias.php?siguiente=1')).codigo; }
  else { cur = (await api('licencias.php?id='+encodeURIComponent(parametros.get('id') || ''))).licencia; }
  if (mode !== 'new' && !cur) { alert('La licencia no existe.'); location.replace('lista.html'); return; }
  const d = cur ? {...cur,...cur.admin} : {};
  $('#formTitle').textContent = mode==='new' ? 'Nueva licencia' : (mode==='ver'?'Licencia ':'Editar licencia ')+cur.licencia;
  const equipos = Object.keys(EQ).map(k=>`<h4 class="h6 mt-4">${EQ[k].t}</h4>
    ${k==='radar'?`<div class="mb-2 small"><label for="radarSi">¿Cuenta con equipo radar?</label><select id="radarSi" name="radarSi" class="form-select form-select-sm d-inline-block w-auto ms-2"><option>No</option><option ${d.radarSi==='Sí'?'selected':''}>Sí</option></select></div>`:''}
    <div id="eq-${k}">${(cur?.eq?.[k]||[]).map(r=>eqRow(k,r)).join('')}</div>
    <button type="button" class="btn btn-sm btn-outline-primary no-view" data-add="${k}"><i class="bi bi-plus"></i> Agregar equipo</button>`).join('');
  $('#formBody').innerHTML = `
    <h3 class="h5 sec">Datos de la licencia</h3><div class="row g-3">${campo(f('licencia','Código de licencia (ej. CNT-MA-0259)'),d.licencia||nextCode())}${campo(f('codOperador','Código aeronave / operador'),d.codOperador)}</div>
    <h3 class="h5 sec">Operador</h3>${grid(OPERADOR,d)}
    <h3 class="h5 sec">Datos del representante</h3>${grid(REPRES,d)}
    <h3 class="h5 sec">1. Datos de la aeronave</h3>${grid(AERONAVE,d)}
    <h3 class="h5 sec">2. Sistemas de radiocomunicaciones</h3>${equipos}
    <h3 class="h5 sec">3. Datos de administración</h3>${grid(ADMIN,d)}`;
  $('#fs').disabled = mode==='ver';
  $('#formBtns').innerHTML = mode==='ver'
    ? `${puedeEditar()?`<a href="licencia.html?modo=edit&id=${encodeURIComponent(cur.num)}" class="btn btn-primary"><i class="bi bi-pencil"></i> Editar licencia</a>`:''}<a class="btn btn-outline-secondary" href="lista.html">Volver</a>`
    : '<button type="submit" class="btn btn-primary">Guardar licencia</button><a class="btn btn-outline-secondary" href="lista.html">Cancelar</a>';
  $('#formBody').onclick = e => {
    if (mode==='ver' || !puedeEditar()) return;
    const agregar = e.target.closest('[data-add]');
    if (agregar) $('#eq-'+agregar.dataset.add).insertAdjacentHTML('beforeend',eqRow(agregar.dataset.add));
    if (e.target.closest('[data-rm]')) e.target.closest('.eq').remove();
  };
  $('#licForm').onsubmit = guardarLicencia;
}
async function guardarLicencia(e) {
  e.preventDefault();
  if (mode==='ver' || !puedeEditar()) return;
  const fd = Object.fromEntries(new FormData(e.target));
  const etiquetas = {licencia:'Código de licencia', codOperador:'Código aeronave / operador'};
  [...OPERADOR,...REPRES,...AERONAVE,...ADMIN].forEach(x => etiquetas[x.n] = x.l);
  const faltan = Object.keys(etiquetas).filter(k => !OPCIONALES.includes(k) && String(fd[k] ?? '').trim() === '').map(k => etiquetas[k]);
  if (faltan.length) { alert('Completa los campos obligatorios:\n- ' + faltan.join('\n- ')); return; }
  fd.licencia = fd.licencia.trim();
  fd.operador = fd.operador.trim();
  if (fd.emision && fd.vencimiento && fd.vencimiento < fd.emision) {
    alert('La fecha de expiración no puede ser anterior a la emisión.'); return;
  }
  const eq = {};
  Object.keys(EQ).forEach(k=>{
    eq[k] = [...document.querySelectorAll(`.eq[data-k="${k}"]`)].map(div=>
      Object.fromEntries(EQ[k].c.map(x=>[x.n,div.querySelector(`[data-f="${x.n}"]`).value])));
  });
  const boton = e.target.querySelector('button[type=submit]');
  boton.disabled = true;
  try {
    await api('licencias.php'+(cur?'?id='+encodeURIComponent(cur.num):''),cur?'PUT':'POST',{...fd,eq,version:cur?.version});
    sessionStorage.setItem('sitae_mensaje',mode==='new'?'Licencia creada':'Cambios guardados');
    location.href='lista.html';
  } catch (error) { alert(error.message); boton.disabled=false; }
}
