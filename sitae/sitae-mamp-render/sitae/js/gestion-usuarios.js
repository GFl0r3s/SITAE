let usuarios = [], editando = null;
(async () => {
  if (!await exigirSesion()) return;
  if (sesion.rol!=='admin') { location.replace('lista.html'); return; }
  $('#cancelarUsuario').onclick = limpiarUsuario;
  $('#usuariosRows').onclick = e => {
    const b = e.target.closest('[data-user]'); if (!b) return;
    editando = usuarios.find(u=>Number(u.id)===Number(b.dataset.user));
    for (const k of ['nombre','usuario','rol','activo']) $('#'+k).value=editando[k];
    $('#password').value=''; $('#password').required=false;
    $('#tituloUsuario').textContent='Editar usuario: '+editando.usuario;
    $('#mensajeUsuario').textContent=''; window.scrollTo(0,0);
  };
  $('#usuarioForm').onsubmit = async e => {
    e.preventDefault(); const b=e.target.querySelector('button[type=submit]'); b.disabled=true;
    const datos={}; for (const k of ['nombre','usuario','password','rol','activo']) datos[k]=$('#'+k).value;
    try {
      await api('usuarios.php'+(editando?'?id='+encodeURIComponent(editando.id):''),editando?'PUT':'POST',datos);
      limpiarUsuario(); await listarUsuarios(); $('#mensajeUsuario').textContent='Usuario guardado.';
    } catch (error) { $('#mensajeUsuario').textContent=error.message; }
    finally { b.disabled=false; }
  };
  await listarUsuarios();
})().catch(errorPagina);
function limpiarUsuario() {
  editando=null; $('#usuarioForm').reset(); $('#password').required=true;
  $('#tituloUsuario').textContent='Registrar usuario'; $('#mensajeUsuario').textContent='';
}
async function listarUsuarios() {
  usuarios=(await api('usuarios.php')).usuarios;
  $('#usuariosRows').innerHTML=usuarios.map(u=>`<tr><td>${esc(u.usuario)}</td><td>${esc(u.nombre)}</td><td>${esc(u.rol)}</td><td>${Number(u.activo)?'Activo':'Inactivo'}</td><td><button class="btn btn-sm btn-outline-primary" data-user="${esc(u.id)}">Editar</button></td></tr>`).join('');
}
