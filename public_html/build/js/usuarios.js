document.addEventListener("DOMContentLoaded", () => {
  fetch("src/php/get_usuariosweb.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#tabla-usuarios tbody");
      tbody.innerHTML = "";

      if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No hay usuarios registrados.</td></tr>`;
        return;
      }

      data.forEach(u => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
          <td>${u.id}</td>
          <td>${esc(u.nombre)}</td>
          <td>${esc(u.apellido)}</td>
          <td>${esc(u.dni)}</td>
          <td>${esc(u.telefono)}</td>
          <td>${esc(u.mail)}</td>
        `;
        tbody.appendChild(fila);
      });

      document.querySelector("#total-usuarios").textContent = data.length;
    })
    .catch(err => console.error("Error al obtener usuarios:", err));
});

// Escapa el contenido para evitar que datos cargados por el usuario rompan el HTML.
function esc(valor) {
  if (valor === null || valor === undefined) return "";
  return String(valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}
