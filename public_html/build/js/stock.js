document.addEventListener("DOMContentLoaded", () => {
  Promise.all([
    fetch("src/php/get_productos.php").then(res => res.json()),
    fetch("src/php/get_ventas.php").then(res => res.json()),
  ]).then(([productos, ventas]) => {
    const contadorVentas = {};
    
    ventas.forEach(v => {
      const productosVendidos = JSON.parse(v.productos);
      productosVendidos.forEach(p => {
        contadorVentas[p.id] = (contadorVentas[p.id] || 0) + p.cantidad;
      });
    });

    // ✅ Filtros de marca y categoría
    const categoriasSaphirus = ["Textil", "Aerosol", "Difusor", "Home Spray", "Aceite Esencial"];
    const categoriasAromanza = ["Tibetanos x8", "Tibetanos Premium", "Tibetanos slim"];

    const productosFiltrados = productos.filter(p => {
      if (p.marca.toLowerCase() === "saphirus") {
        return categoriasSaphirus.includes(p.categoria);
      } else if (p.marca.toLowerCase() === "aromanza") {
        return categoriasAromanza.includes(p.categoria);
      }
      return false;
    });

    const combinados = productosFiltrados.map(p => ({
      id: p.id,
      nombre: p.nombre,
      stock: parseInt(p.stock),
      vendidos: contadorVentas[p.id] || 0,
    }));

    // 🟥 Productos sin stock
    const sinStock = combinados.filter(p => p.stock === 0);

    // 🟨 Reposición sugerida
    const sugerenciasReposicion = combinados
      .filter(p => p.stock <= 20)
      .sort((a, b) => b.vendidos);

    // KPI
    document.getElementById("sin-stock").textContent = sinStock.length;
    document.getElementById("total-productos").textContent = productosFiltrados.length;
    document.getElementById("reposicion-sugerida").textContent = sugerenciasReposicion.length;

    // Promedio
    const promedioStock =
      productosFiltrados.reduce((sum, p) => sum + parseInt(p.stock), 0) /
      productosFiltrados.length;
    document.getElementById("prom-stock").textContent = promedioStock.toFixed(0);

    // Tabla: Sin stock
    const tablaSinStock = document.querySelector("#tabla-sin-stock tbody");
    tablaSinStock.innerHTML = "";
    sinStock.forEach(p => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${p.id}</td>
        <td>${p.nombre}</td>
        <td>${p.stock}</td>
      `;
      tablaSinStock.appendChild(row);
    });

    // Tabla: Reposición sugerida
    const tablaReposicion = document.querySelector("#tabla-reposicion tbody");
    tablaReposicion.innerHTML = "";
    sugerenciasReposicion.forEach(p => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${p.id}</td>
        <td>${p.nombre}</td>
        <td>${p.stock}</td>
        <td>${p.vendidos}</td>
      `;
      tablaReposicion.appendChild(row);
    });
  });
});

