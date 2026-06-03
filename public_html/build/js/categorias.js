const $list    = document.getElementById('marcas-list');
const $guardar = document.getElementById('guardar');
const $status  = document.getElementById('status');

const $btnAddMarca = document.getElementById('btn-agregar-marca');
const $newNombre   = document.getElementById('nueva-marca-nombre');
const $newOrden    = document.getElementById('nueva-marca-orden');
const $newVisible  = document.getElementById('nueva-marca-visible');

const marcasAEliminar = new Set();

function escapeHtml(s){ const d=document.createElement('div'); d.textContent=s??''; return d.innerHTML; }
function nextOrden(tbody){
  let max = 0;
  tbody.querySelectorAll('tr.cat-row .cat-orden').forEach(inp=>{
    const v = parseInt(inp.value||'0',10); if (v>max) max=v;
  });
  return String(max+1);
}

async function cargar() {
  try {
    $status.textContent = 'Cargando...';
    const res  = await fetch('src/php/sidebar_get.php', { credentials:'same-origin' });
    const text = await res.text();
    let data; try { data = JSON.parse(text); } catch { console.error('Respuesta no JSON:', text); throw new Error('Respuesta no válida'); }
    if (!data.ok) throw new Error(data.message || 'Error del servidor');

    $list.innerHTML = '';
    (data.marcas || []).forEach(m => renderCard(m));
    $status.textContent = '';
  } catch (err) {
    console.error(err);
    $status.textContent = 'Error al cargar (ver consola)';
  }
}

function renderCard(m){
  const card = document.createElement('div');
  card.className = 'card';
  card.dataset.origMarca = m.marca;   // para renombrar
  card.dataset.marca = m.marca;

  card.innerHTML = `
    <div class="row" style="gap:8px; align-items:center; flex-wrap:wrap">
      <input class="marca-nombre" value="${escapeHtml(m.marca)}" placeholder="Nombre de marca" />
      <label class="switch">
        <input type="checkbox" class="marca-visible" ${m.visible ? 'checked' : ''}><span> Visible</span>
      </label>
      <label>Orden <input type="number" class="marca-orden" value="${m.orden|0}" style="width:80px"></label>
      <button class="btn" type="button" data-action="add-cat">+ Categoría</button>
      <button class="btn danger" type="button" data-action="delete-marca">Eliminar marca</button>
    </div>
    <table class="tabla-cats">
      <thead><tr><th>Categoría</th><th>Visible</th><th>Orden</th><th>Acciones</th></tr></thead>
      <tbody></tbody>
    </table>
  `;

  const tbody = card.querySelector('tbody');
  (m.categorias || []).forEach(c => {
    tbody.appendChild(catRow(c.categoria, c.visible, c.orden));
  });

  // eventos
  card.querySelector('[data-action="add-cat"]').addEventListener('click', ()=>{
    tbody.appendChild(catRow('', 1, nextOrden(tbody)));
  });
card.querySelector('[data-action="delete-marca"]').addEventListener('click', async ()=>{
  const nombre = card.querySelector('.marca-nombre').value.trim() || card.dataset.origMarca;

  const res = await Swal.fire({
    title: '¿Eliminar marca?',
    html: `<b>${nombre}</b> y todas sus categorías serán eliminadas del sidebar.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#e23d3b'
  });

  if (!res.isConfirmed) return;

  marcasAEliminar.add(card.dataset.origMarca);
  card.remove();

  // Toast feedback
  Swal.fire({
    toast: true, position: 'top-end', timer: 1600, showConfirmButton: false,
    icon: 'success', title: 'Marca eliminada (pendiente de guardar)'
  });
});


  $list.appendChild(card);
}

function catRow(nombre, visible, orden){
  const tr = document.createElement('tr');
  tr.className = 'cat-row';
  tr.innerHTML = `
    <td><input class="cat-nombre" value="${escapeHtml(nombre)}" placeholder="Nombre de categoría" /></td>
    <td style="text-align:center"><input type="checkbox" class="cat-visible" ${visible ? 'checked' : ''}></td>
    <td><input type="number" class="cat-orden" value="${orden|0}" style="width:80px"></td>
    <td><button class="btn danger btn-del-cat" type="button">Eliminar</button></td>
  `;
  tr.querySelector('.btn-del-cat').addEventListener('click', ()=> {
    tr.remove();
  });
  return tr;
}

// Agregar marca
$btnAddMarca?.addEventListener('click', ()=>{
  const nombre = ($newNombre?.value||'').trim();
  if (!nombre) { alert('Ingresá un nombre de marca'); return; }
  const marca = { marca:nombre, visible: $newVisible.checked ? 1 : 0, orden: parseInt($newOrden.value||'0',10), categorias: [] };
  renderCard(marca);
  if ($newNombre) $newNombre.value = '';
});

// Guardar

$guardar?.addEventListener('click', async ()=>{
  const payload = { marcas: [], eliminar_marcas: Array.from(marcasAEliminar) };

  document.querySelectorAll('#marcas-list .card').forEach(card=>{
    const marcaNombre = card.querySelector('.marca-nombre').value.trim();
    const categorias = [];
    card.querySelectorAll('tbody tr.cat-row').forEach(tr=>{
      const nombre = tr.querySelector('.cat-nombre').value.trim();
      if (!nombre) return;
      categorias.push({
        categoria: nombre,
        visible: tr.querySelector('.cat-visible').checked ? 1 : 0,
        orden: parseInt(tr.querySelector('.cat-orden').value || '0', 10)
      });
    });

    payload.marcas.push({
      marca: marcaNombre,
      original_marca: card.dataset.origMarca || marcaNombre,
      visible: card.querySelector('.marca-visible').checked ? 1 : 0,
      orden: parseInt(card.querySelector('.marca-orden').value || '0', 10),
      categorias
    });
  });

  // Mostrar loading (¡sin await!)
  Swal.fire({
    title: 'Guardando cambios',
    didOpen: () => { Swal.showLoading(); },
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false
  });

  try {
    const res  = await fetch('src/php/sidebar_save.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    });

    const text = await res.text();
    let out;
    try { out = JSON.parse(text); }
    catch { throw new Error('Respuesta no válida del servidor'); }

    if (!out.ok) throw new Error(out.message || 'Error al guardar');

    Swal.close(); // cierro el loading

    await Swal.fire({
      icon: 'success',
      title: '¡Listo!',
      text: 'La configuración del sidebar se guardó correctamente.',
      confirmButtonColor: '#e23d3b'
    });

    marcasAEliminar.clear();
    await cargar();
  } catch (e) {
    console.error(e);
    Swal.close(); // cierro el loading si falló
    await Swal.fire({
      icon: 'error',
      title: 'Ups…',
      text: e.message || 'No se pudieron guardar los cambios.',
      confirmButtonColor: '#e23d3b'
    });
  }
});


cargar();
