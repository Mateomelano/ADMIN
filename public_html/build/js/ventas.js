document.addEventListener("DOMContentLoaded", () => {
  fetch("src/php/get_ventas.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#tabla-ventas tbody");
      tbody.innerHTML = "";

      const contadorProductos = {};
      const ventasPorFecha = {};
      let totalVendido = 0;
      let totalProductosVendidos = 0;
      let totalGanancia = 0;
      let entregadas = 0;

      // Fechas base
      const hoy = new Date();
      const inicioSemana = new Date(hoy);
      inicioSemana.setDate(hoy.getDate() - hoy.getDay());
      const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
      const inicioAnio = new Date(hoy.getFullYear(), 0, 1);

      // Totales por período
      let totalHoy = 0, totalSemana = 0, totalMes = 0, totalAnio = 0;

data.forEach(venta => {
  const fila = document.createElement("tr");

  const productosArray = JSON.parse(venta.productos);

  productosArray.forEach(p => {
    const key = `${p.nombre}`;
    contadorProductos[key] = (contadorProductos[key] || 0) + p.cantidad;
    totalProductosVendidos += p.cantidad;
  });

  const productosOrdenados = [...productosArray].sort((a, b) => {
    const categoriaA = (a.categoria || "Sin categoría").replace(/\s+/g, " ").trim();
    const categoriaB = (b.categoria || "Sin categoría").replace(/\s+/g, " ").trim();

    const comparacionCategoria = categoriaA.localeCompare(categoriaB, "es", {
      sensitivity: "base"
    });

    if (comparacionCategoria !== 0) return comparacionCategoria;

    const nombreA = (a.nombre || "").replace(/\s+/g, " ").trim();
    const nombreB = (b.nombre || "").replace(/\s+/g, " ").trim();

    return nombreA.localeCompare(nombreB, "es", {
      sensitivity: "base"
    });
  });

  let productos = "";
  let categoriaActual = "";

  productosOrdenados.forEach(p => {
    const categoria = (p.categoria || "Sin categoría").replace(/\s+/g, " ").trim();

    if (categoria !== categoriaActual) {
      categoriaActual = categoria;
      productos += `
        <div style="
          margin-top: 10px;
          margin-bottom: 6px;
          padding: 6px 10px;
          background: #f2f2f2;
          border-left: 4px solid #e53935;
          font-weight: bold;
          color: #333;
          border-radius: 4px;
        ">
          📂 ${categoriaActual}
        </div>
      `;
    }

    productos += `
      <div style="margin-left: 6px; margin-bottom: 4px;">
        ID ${p.id} - ${p.nombre} x${p.cantidad} - $${(p.precio * p.cantidad).toFixed(2)}
      </div>
    `;
  });

  let fechaVenta = new Date(venta.fecha);
  fechaVenta.setHours(fechaVenta.getHours() - 3);

  const fechaFormateada =
    String(fechaVenta.getDate()).padStart(2, "0") + "/" +
    String(fechaVenta.getMonth() + 1).padStart(2, "0") + "/" +
    fechaVenta.getFullYear() + " " +
    String(fechaVenta.getHours()).padStart(2, "0") + ":" +
    String(fechaVenta.getMinutes()).padStart(2, "0") + ":" +
    String(fechaVenta.getSeconds()).padStart(2, "0");

  const fechaSimple = venta.fecha.split(" ")[0];
  ventasPorFecha[fechaSimple] = (ventasPorFecha[fechaSimple] || 0) + parseFloat(venta.total);

  const total = parseFloat(venta.total);
  const mayorista = parseFloat(venta.total_mayorista);
  const ganancia = total;

  totalVendido += total;
  totalGanancia += ganancia;

  if (mismaFecha(fechaVenta, hoy)) totalHoy += total;
  if (fechaVenta >= inicioSemana) totalSemana += total;
  if (fechaVenta >= inicioMes) totalMes += total;
  if (fechaVenta >= inicioAnio) totalAnio += total;

  if (venta.entregado == 1) entregadas++;

  // Es mayorista si la venta tiene un usuario registrado asociado (id_usuario).
  const esMayorista = venta.id_usuario !== null && venta.id_usuario !== undefined && venta.id_usuario !== "";
  const tipoVenta = esMayorista
    ? `<span style="background:#2e7d32;color:#fff;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:bold;white-space:nowrap;">Mayorista</span>${venta.nombre_usuario ? `<div style="font-size:12px;color:#555;margin-top:4px;">${escVenta(venta.nombre_usuario)}</div>` : ""}`
    : `<span style="background:#9e9e9e;color:#fff;padding:4px 10px;border-radius:6px;font-size:12px;white-space:nowrap;">Minorista</span>`;

  fila.innerHTML = `
    <td>${fechaFormateada}</td>
    <td style="text-align:center;">${tipoVenta}</td>
    <td style="text-align:left; vertical-align:top;">${productos}</td>
    <td>$${venta.total}</td>
    <td>$${venta.total_mayorista}</td>
    <td style="text-align:center;">
      <input type="checkbox" class="checkbox-entregado" data-id="${venta.id}" ${venta.entregado == 1 ? "checked" : ""}>
    </td>
    <td style="text-align:center;">
      <button class="btn-eliminar" data-id="${venta.id}" style="background-color: #e74c3c; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 5px;">Eliminar</button>
    </td>
  `;

  tbody.appendChild(fila);
});

      // Mostrar KPIs actualizados
      actualizarKPIsExtendido({
        totalVendido,
        totalGanancia,
        totalProductosVendidos,
        entregadas,
        totalHoy,
        totalSemana,
        totalMes,
        totalAnio,
        contadorProductos
      });

      generarGraficoProductos(contadorProductos);
      generarGraficoEvolucion(ventasPorFecha);
      agregarEventosEliminar();
    });
});

// Comparar fechas sin hora
function mismaFecha(fecha1, fecha2) {
  return fecha1.getFullYear() === fecha2.getFullYear() &&
         fecha1.getMonth() === fecha2.getMonth() &&
         fecha1.getDate() === fecha2.getDate();
}

function actualizarKPIsExtendido({
  totalVendido,
  totalGanancia,
  totalProductosVendidos,
  entregadas,
  totalHoy,
  totalSemana,
  totalMes,
  totalAnio,
  contadorProductos
}) {
  document.querySelector("#kpi-total-vendido p").textContent = `$${totalVendido.toFixed(2)}`;
  document.querySelector("#kpi-total-productos p").textContent = totalProductosVendidos;

  let productoTop = "-";
  let maxCantidad = 0;
  for (const [producto, cantidad] of Object.entries(contadorProductos)) {
    if (cantidad > maxCantidad) {
      productoTop = producto;
      maxCantidad = cantidad;
    }
  }
  document.querySelector("#kpi-producto-top p").textContent = productoTop;

  // Agrega nuevas KPIs si no existen
  const kpisExtra = document.querySelector("#kpis-extra");
  if (!kpisExtra) {
    const section = document.querySelector(".kpis-ventas");
    const div = document.createElement("div");
    div.id = "kpis-extra";
    div.style.display = "flex";
    div.style.flexWrap = "wrap";
    div.style.justifyContent = "center";
    div.style.gap = "20px";
    div.style.marginTop = "20px";

    const crearKPI = (titulo, valor) => `
      <div class="kpi-card" style="flex: 1; background: #eef; padding: 20px; border-radius: 10px; text-align: center;">
        <h4>${titulo}</h4>
        <p style="font-size: 18px; font-weight: bold;">${valor}</p>
      </div>
    `;

    div.innerHTML = `
      ${crearKPI("💰 Ganancia total", `$${totalGanancia.toFixed(2)}`)}
      ${crearKPI("📅 Vendido hoy", `$${totalHoy.toFixed(2)}`)}
      ${crearKPI("🗓️ Vendido esta semana", `$${totalSemana.toFixed(2)}`)}
      ${crearKPI("📆 Vendido este mes", `$${totalMes.toFixed(2)}`)}
      ${crearKPI("🗓️ Vendido este año", `$${totalAnio.toFixed(2)}`)}
      ${crearKPI("✅ Ventas entregadas", entregadas)}
    `;
    section.appendChild(div);
  }
}


function agregarEventosEliminar() {
  // Checkbox entregado
  document.querySelectorAll(".checkbox-entregado").forEach(checkbox => {
    checkbox.addEventListener("change", function () {
      const id = this.getAttribute("data-id");
      const entregado = this.checked ? 1 : 0;

      fetch("src/php/actualizar_estado_entregado.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&entregado=${entregado}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            console.log("Estado actualizado");
          } else {
            alert("Error al actualizar estado");
          }
        });
    });
  });

  // Botón eliminar
  document.querySelectorAll(".btn-eliminar").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-id");
      if (confirm("¿Estás seguro de eliminar esta venta?")) {
        fetch("src/php/eliminar_ventas.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${id}`
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) location.reload();
            else alert("Error al eliminar");
          });
      }
    });
  });
}


// (las funciones de KPIs y Gráficos ya las tienes cargadas aquí también)


// Función para actualizar tarjetas KPI
function actualizarKPIs(totalVendido, totalProductosVendidos, contadorProductos) {
  document.querySelector("#kpi-total-vendido p").textContent = `$${totalVendido.toFixed(2)}`;
  document.querySelector("#kpi-total-productos p").textContent = totalProductosVendidos;

  let productoTop = "-";
  let maxCantidad = 0;
  for (const [producto, cantidad] of Object.entries(contadorProductos)) {
    if (cantidad > maxCantidad) {
      productoTop = producto;
      maxCantidad = cantidad;
    }
  }
  document.querySelector("#kpi-producto-top p").textContent = productoTop;
}

// Función para generar gráfico de productos vendidos
function generarGraficoProductos(contadorProductos) {
  const ctx = document.getElementById('grafico-productos').getContext('2d');

  const labels = Object.keys(contadorProductos);
  const data = Object.values(contadorProductos);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Cantidad vendida',
        data: data,
        backgroundColor: '#ff6384',
        borderColor: '#e60039',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

// Función para generar gráfico de evolución de ventas
function generarGraficoEvolucion(ventasPorFecha) {
  const ctx = document.getElementById('grafico-evolucion').getContext('2d');

  const labels = Object.keys(ventasPorFecha).sort();
  const data = labels.map(fecha => ventasPorFecha[fecha]);

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total Vendido',
        data: data,
        borderColor: '#36a2eb',
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top' }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

// Escapa el nombre del usuario para que no rompa el HTML de la tabla.
function escVenta(valor) {
  if (valor === null || valor === undefined) return "";
  return String(valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}
