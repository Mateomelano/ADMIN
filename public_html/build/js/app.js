$(document).ready(function () {
  // BOTON MANTENIMIENTO
  const toggleMantenimiento = document.getElementById("mantenimiento-toggle");

  // 1. Obtener el estado actual del mantenimiento
  fetch("src/php/obtener_estado_mantenimiento.php")
    .then((res) => res.json())
    .then((data) => {
      if (data && toggleMantenimiento) {
        toggleMantenimiento.checked = data.activado == 1;
      }
    })
    .catch((err) => console.error("Error al obtener estado:", err));

  // 2. Escuchar cambios y actualizar
  toggleMantenimiento.addEventListener("change", () => {
    fetch("src/php/actualizar_estado_mantenimiento.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ activado: toggleMantenimiento.checked ? 1 : 0 }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          Swal.fire({
            icon: "success",
            title: "Modo mantenimiento actualizado",
            text: "El estado se cambió correctamente.",
            timer: 2000,
            showConfirmButton: false,
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo actualizar el estado.",
          });
        }
      })
      .catch((err) => {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Hubo un problema al conectar con el servidor.",
        });
      });
  });

  // Mostrar el modal al hacer clic en "Agregar Producto"
  $("#add-product-btn").click(function () {
    abrirModal("modalAgregar");
  });

  // Función para abrir el modal
  function abrirModal(id) {
    // Limpiar valores anteriores
    $("#formEditarPrecios")[0].reset();
    ["precio", "mayorista", "categoria", "stock"].forEach((campo) => {
      $(`#input-${campo}`).prop("disabled", true);
      $(`#check-${campo}`).prop("checked", false);
    });
    $("#" + id).css("display", "flex");
  }

  // Función para cerrar el modal
  function cerrarModal(id) {
    $("#" + id).css("display", "none");
  }

  // Cierra el modal si se hace clic fuera de él
  $(window).click(function (event) {
    $(".modal").each(function () {
      if (event.target == this) {
        $(this).css("display", "none");
      }
    });
  });

  // Función para cargar marcas desde el servidor y llenar los selects
  function cargarMarcas() {
    $.ajax({
      url: "src/php/get_marcas.php",
      type: "GET",
      dataType: "json",
      success: function (data) {
        let options = data
          .map((marca) => `<option value="${marca}">${marca}</option>`)
          .join("");
        $("#marcaAgregar").append(options);
        $("#marcaEditar").append(options);
      },
      error: function (xhr, status, error) {
        console.error("Error al cargar marcas: ", error);
      },
    });
  }

  cargarMarcas();

  // Función para cargar categorias desde el servidor y llenar los selects
  function cargarCategorias() {
    $.ajax({
      url: "src/php/get_categorias.php",
      type: "GET",
      dataType: "json",
      success: function (data) {
        let options = data
          .map((cat) => `<option value="${cat}">${cat}</option>`)
          .join("");
        $("#categoriaAgregar").append(options);
        $("#categoriaEditar").append(options); // sumamos edición
      },
      error: function (xhr, status, error) {
        console.error("Error al cargar categorías: ", error);
      },
    });
  }

  cargarCategorias();

  // Agregar Producto
  const cloudinaryUrl = "https://api.cloudinary.com/v1_1/dzfzqzdcu/upload";
  const cloudinaryPreset = "McaromasPics"; // Reemplaza con tu preset de Cloudinary

  $("#formAgregar").submit(async function (e) {
    debugger;
    e.preventDefault();
    let form = document.getElementById("formAgregar");
    let formData = new FormData(form);

    let precioMayorista = $("#precioMayoristaAgregar").val();
    formData.append("precioMayorista", precioMayorista);

    // Marcas
    let marcaSeleccionada = $("#marcaAgregar").val();
    let nuevaMarca = $("#nuevaMarcaAgregar").val().trim();

    if (nuevaMarca) {
      formData.append("marca", nuevaMarca);
    } else if (marcaSeleccionada) {
      formData.append("marca", marcaSeleccionada);
    } else {
      Swal.fire({
        icon: "warning",
        title: "¡Atención!",
        text: "Por favor, selecciona o ingresa una marca.",
      });
      return;
    }

    // Categorías
    let categoriaSeleccionada = $("#categoriaAgregar").val();
    let nuevaCategoria = $("#nuevaCategoriaAgregar").val().trim();

    if (nuevaCategoria) {
      formData.append("categoria", nuevaCategoria);
    } else if (categoriaSeleccionada) {
      formData.append("categoria", categoriaSeleccionada);
    } else {
      Swal.fire({
        icon: "warning",
        title: "¡Atención!",
        text: "Por favor, selecciona o ingresa una categoria.",
      });
      return;
    }

    //let imagen = document.getElementById("imagenAgregar").files[0];
    //if (imagen) {
    //  let imageUrl = await subirImagenACloudinary(imagen);
    //  if (imageUrl) {
    //    formData.append("imagenUrl", imageUrl);
    //  }
    //}

    $.ajax({
      url: "src/php/agregar_producto.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
    })
      .done(function (data) {
        Swal.fire({
          icon: "success",
          title: "Producto Agregado correctamente",
          showConfirmButton: false,
          timer: 1200,
          timerProgressBar: true,
        }).then(() => {
          location.reload();
        });
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        console.error(
          "❌ Error en la solicitud AJAX:",
          textStatus,
          errorThrown
        );
        alert("Error al agregar producto");
      });
  });

  //Codigo de subida de imagen
  async function subirImagenACloudinary(imagen) {
    let data = new FormData();
    data.append("file", imagen);
    data.append("upload_preset", cloudinaryPreset);

    try {
      let response = await fetch(cloudinaryUrl, {
        method: "POST",
        body: data,
      });
      let result = await response.json();
      return result.secure_url; // ✅ Devuelve la URL de la imagen subida
    } catch (error) {
      console.error("❌ Error al subir imagen a Cloudinary:", error);
      return null;
    }
  }

  // Abrir modal de edición con datos del producto
  $(document).on("click", ".edit-btn", function () {
    let row = $(this).closest("tr");
    $("#idEditar").val(row.find("td:eq(0)").text().trim()); // ID
    $("#nombreEditar").val(row.find("td:eq(1)").text().trim()); // Nombre

    let descripcion = row.find("td:eq(2)").text().trim();
    $("#descripcionEditar").val(descripcion);

    let categoria = row.find("td:eq(3)").text().trim();
    $("#categoriaEditar").val(categoria);

    let marca = row.find("td:eq(4)").text().trim();
    $("#marcaEditar").val(marca);

    let precio = row.find("td:eq(5)").text().replace("$", "").trim();
    $("#precioEditar").val(precio);

    let preciomayorista = row.find("td:eq(6)").text().replace("$", "").trim();
    $("#preciomayoristaEditar").val(preciomayorista);

    let stock = row.find("td:eq(7)").text().trim();
    $("#stockEditar").val(stock);

    let habilitado = row.find("td:eq(8) input[type='checkbox']").is(":checked")
      ? "1"
      : "0";
    $("#habilitadoEditar").val(habilitado);

    // 📌 NUEVO: Obtener la URL de la imagen actual desde un atributo data o columna oculta
    let imagenUrl = row.find("td:eq(9) img").attr("src"); // CORRECTO

    // Mostrar la imagen actual en el modal si existe
    if (imagenUrl) {
      $("#imagenActual").attr("src", imagenUrl).show();
      $("#imagenUrlActual").val(imagenUrl); // Asignar la URL al input oculto
    } else {
      $("#imagenActual").hide();
      $("#imagenUrlActual").val("");
    }

    abrirModal("modalEditar");
  });

  // Editar producto
  $("#formEditar").submit(async function (e) {
    e.preventDefault();
    let formData = new FormData();
    formData.append("id", $("#idEditar").val());
    formData.append("nombre", $("#nombreEditar").val());
    formData.append("descripcion", $("#descripcionEditar").val());
    formData.append("precio", $("#precioEditar").val());
    formData.append("preciomayorista", $("#preciomayoristaEditar").val());
    formData.append("stock", $("#stockEditar").val());
    formData.append("habilitado", $("#habilitadoEditar").val());
    console.log("stock editado", formData.get("stock"));

    // --- Marca (nueva o seleccionada)
    let marcaSeleccionada = $("#marcaEditar").val();
    let nuevaMarca = $("#nuevaMarcaEditar").val().trim();

    if (nuevaMarca) {
      formData.set("marca", nuevaMarca);
    } else if (marcaSeleccionada) {
      formData.set("marca", marcaSeleccionada);
    } else {
      Swal.fire({
        icon: "warning",
        title: "¡Atención!",
        text: "Por favor, selecciona o ingresa una marca.",
      });
      return;
    }

    // --- Categoría (nueva o seleccionada)
    let categoriaSeleccionada = $("#categoriaEditar").val();
    let nuevaCategoria = $("#nuevaCategoriaEditar").val().trim();

    if (nuevaCategoria) {
      formData.set("categoria", nuevaCategoria);
    } else if (categoriaSeleccionada) {
      formData.set("categoria", categoriaSeleccionada);
    } else {
      Swal.fire({
        icon: "warning",
        title: "¡Atención!",
        text: "Por favor, selecciona o ingresa una categoria.",
      });
      return;
    }

    // --- Imagen
    let imagenActual = $("#imagenUrlActual").val(); // La que ya tenía
    let imagen = document.getElementById("imagenEditar").files[0];

    if (imagen) {
      // Si se sube una nueva imagen, la agregamos al FormData
      formData.append("imagen", imagen);
    } else {
      // Mantener la imagen anterior
      formData.append("imagenUrlActual", imagenActual);
    }

    // --- Enviar solicitud AJAX
    $.ajax({
      url: "src/php/editar_producto.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
    })
      .done(function (data) {
        Swal.fire({
          icon: "success",
          title: "Producto editado correctamente",
          showConfirmButton: false,
          timer: 1200,
          timerProgressBar: true,
        }).then(() => {
          location.reload();
        });
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        console.error(
          "❌ Error en la solicitud AJAX:",
          textStatus,
          errorThrown
        );
        alert("Error al editar producto");
      });
  });

  // Abrir modal de confirmación de eliminación
  $(document).on("click", ".delete-btn", function () {
    let id = $(this).data("id");
    $("#confirmarEliminar").data("id", id);
    abrirModal("modalEliminar");
  });

  // Confirmar eliminación
  $("#confirmarEliminar").click(function () {
    let id = $(this).data("id");
    $.post("src/php/eliminar_producto.php", { id: id }, function (data) {
      location.reload();
    });
  });

  // Botones para cerrar los modales
  $(".close").click(function () {
    $(this).closest(".modal").css("display", "none");
  });

  // filtro precio

  $(".orden-icon").click(function () {
    let columna =
      $(this).attr("id") === "ordenar-precio"
        ? "precio"
        : $(this).attr("id") === "ordenar-mayorista"
        ? "preciomayorista"
        : "stock";
    // Identifica qué columna ordenar
    let ordenActual = $(this).data("order");

    if (!ordenActual || ordenActual === "null") {
      ordenActual = "asc"; // 🔼 Ascendente
      $(this).data("order", "asc").text("🔽");
    } else if (ordenActual === "asc") {
      ordenActual = "desc"; // 🔽 Descendente
      $(this).data("order", "desc").text("🔼");
    } else {
      ordenActual = null; // Sin orden
      $(this).data("order", "null").text("🔼🔽");
    }

    let query = $("#search-input").val();
    let habilitado = $("#filter-habilitado").data("state");

    // ✅ Pasamos también la columna a ordenar
    cargarProductos(query, habilitado, ordenActual, columna);
  });

  function cargarProductos(
    query = "",
    habilitadoFiltro = null,
    orden = null,
    columna = "precio"
  ) {
    let data = { q: query };
    if (habilitadoFiltro !== null) {
      data.habilitado = habilitadoFiltro;
    }
    if (orden) {
      data.orden = orden;
    }
    if (columna) {
      data.columna = columna;
    }

    $.ajax({
      url: "src/php/get_productos.php",
      type: "GET",
      data: data,
      dataType: "json",
      success: function (data) {
        let tableBody = $("#product-table-body");
        tableBody.empty();

        if (data.length > 0) {
          data.forEach(function (producto) {
            let checked = producto.habilitado == 1 ? "checked" : "";
            let row = `<tr>
    <td>${producto.id}</td>
    <td>${producto.nombre}</td>
    <td>${producto.descripcion}</td>
    <td>${producto.categoria}</td>
    <td>${producto.marca}</td>
    <td>$${parseFloat(producto.precio).toFixed(2)}</td>
    <td>$${parseFloat(producto.preciomayorista).toFixed(2)}</td>
    <td>${producto.stock}</td>
    <td>
        <input type="checkbox" class="toggle-habilitado" data-id="${
          producto.id
        }" ${checked}>
    </td>
    <td>
        <img src="${
          producto.imagen
        }" alt="Imagen del producto" width="50" height="50" onerror="this.onerror=null;this.src='default.jpg';">
    </td>
    <td>
        <button class='edit-btn' data-id='${producto.id}'>✏️</button>
        <button class='delete-btn' data-id='${producto.id}'>🗑️</button>
    </td>
    <td>
        <input type="checkbox" class="checkProducto" value="${
          producto.id
        }" name="producto_${producto.id}">
        <button class="edit-precio-btn" data-id="${producto.id}">✏️</button>
    </td>
</tr>`;

            tableBody.append(row);
          });
        } else {
          tableBody.append(
            "<tr><td colspan='9'>No hay productos disponibles</td></tr>"
          );
        }
      },
      error: function () {
        $("#product-table-body").append(
          "<tr><td colspan='9'>Error al cargar los productos</td></tr>"
        );
      },
    });
  }

  // 🟢 Estado inicial: intermedio (todos los productos)
  let filtroHabilitado = null;
  let filtroCheckbox = $("#filter-habilitado");
  filtroCheckbox.data("state", filtroHabilitado);
  filtroCheckbox.prop("indeterminate", true);

  // Cargar todos los productos al inicio
  cargarProductos();

  // 🔍 Filtrar productos en tiempo real (input de búsqueda)
  $("#search-input").on("input", function () {
    let query = $(this).val();
    cargarProductos(query, filtroCheckbox.data("state"));
  });

  // 🟢 Ciclo de estados para el filtro de habilitados
  $("#filter-habilitado").on("click", function () {
    let currentState = $(this).data("state");

    if (currentState === null || currentState === undefined) {
      $(this)
        .data("state", 1)
        .prop("checked", true)
        .prop("indeterminate", false);
    } else if (currentState === 1) {
      $(this)
        .data("state", 0)
        .prop("checked", false)
        .prop("indeterminate", false);
    } else {
      $(this)
        .data("state", null)
        .prop("checked", false)
        .prop("indeterminate", true);
    }

    let query = $("#search-input").val();
    let filtroEstado = $(this).data("state");
    cargarProductos(query, filtroEstado);
  });

  // 🔄 Controlar los checkboxes individuales para cada producto
  $(document).on("change", ".toggle-habilitado", function () {
    let productId = $(this).data("id");
    let nuevoEstado = $(this).is(":checked") ? 1 : 0;

    $.ajax({
      url: "src/php/actualizar_estado.php",
      type: "POST",
      data: { id: productId, habilitado: nuevoEstado },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          let query = $("#search-input").val();
          let filtroEstado = $("#filter-habilitado").data("state");
          cargarProductos(query, filtroEstado); // Refrescar productos después del cambio
        } else {
          console.error("Error al actualizar producto:", response.error);
        }
      },
      error: function () {
        console.error("Error al intentar actualizar el producto.");
      },
    });
  });

  //Funcion Exportar Excel

  // Evento al hacer clic en el botón Excel
  $("#export-excel-btn").on("click", function () {
    exportToExcel();
  });

  function exportToExcel() {
    let table = $("#product-table-body");
    let rows = table.find("tr");
    let data = [];

    // Agregar encabezados (sin imagen)
    let headers = [
      "ID",
      "Nombre",
      "Descripción",
      "Categoría",
      "Marca",
      "Precio",
      "Precio Mayorista",
      "Stock",
      "Habilitado",
    ];
    data.push(headers);

    // Recorrer las filas de la tabla
    rows.each(function () {
      let cells = $(this).find("td");
      if (cells.length > 0) {
        let habilitadoIcon = cells
          .eq(7)
          .find("input[type='checkbox']")
          .is(":checked")
          ? "✅ Sí"
          : "❌ No";

        let rowData = [
          cells.eq(0).text().trim(), // ID
          cells.eq(1).text().trim(), // Nombre
          cells.eq(2).text().trim(), // Descripción
          cells.eq(3).text().trim(), // Categoría
          cells.eq(4).text().trim(), // Marca
          cells.eq(5).text().trim(), // Precio
          cells.eq(6).text().trim(), // Precio Mayorista
          cells.eq(7).text().trim(), // Stock
          habilitadoIcon, // Habilitado con ícono de texto
        ];
        data.push(rowData);
      }
    });

    // Crear la hoja de Excel
    let ws = XLSX.utils.aoa_to_sheet(data);
    let wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Productos");

    // Descargar el archivo
    XLSX.writeFile(wb, "productos.xlsx");
  }

  //FUNCION EDITAR PRECIOS VARIOS

  //Submit a PHP
  $("#formEditarPrecios").on("submit", function (e) {
    e.preventDefault();

    const ids = $("#idsSeleccionados").val().split(",");
    const data = { ids };

    if ($("#check-precio").is(":checked")) {
      const val = $("#input-precio").val();
      if (!val || isNaN(val)) return mostrarError("Precio inválido");
      data.nuevoPrecio = val;
    }

    if ($("#check-mayorista").is(":checked")) {
      const val = $("#input-mayorista").val();
      if (!val || isNaN(val)) return mostrarError("Precio mayorista inválido");
      data.nuevoPrecioMayorista = val;
    }

    if ($("#check-categoria").is(":checked")) {
      const val = $("#input-categoria").val().trim();
      if (!val) return mostrarError("Categoría vacía");
      data.nuevaCategoria = val;
    }

    if ($("#check-stock").is(":checked")) {
      const val = $("#input-stock").val();
      if (val === "" || isNaN(val) || parseInt(val) < 0)
        return mostrarError("Stock inválido");
      data.nuevoStock = parseInt(val);
    }

    if (Object.keys(data).length === 1) {
      return mostrarError("No seleccionaste ningún campo para editar.");
    }

    $.ajax({
      url: "src/php/editar_precios.php",
      method: "POST",
      data: data,
      success: function () {
        Swal.fire({
          icon: "success",
          title: "¡Actualizado!",
          text: "Los productos fueron modificados.",
          timer: 1000,
          showConfirmButton: false,
        }).then(() => {
          cerrarModal("modalEditarPrecios");
          cargarProductos();
        });
      },
      error: function () {
        mostrarError("Ocurrió un error al actualizar los productos.");
      },
    });
  });

  function mostrarError(msg) {
    Swal.fire({
      icon: "warning",
      title: "Error",
      text: msg,
      confirmButtonColor: "#d33",
    });
  }

  // Habilitar/Deshabilitar campos en el modal según checkboxes
  ["precio", "mayorista", "categoria", "stock"].forEach((campo) => {
    $(`#check-${campo}`).on("change", function () {
      $(`#input-${campo}`).prop("disabled", !this.checked);
    });
  });
  //MODAL
  function abrirModal(id) {
    $("#" + id).show();
  }
  function cerrarModal(id) {
    $("#" + id).hide();
  }

  $(document).on("click", ".edit-precio-btn", function () {
    // Obtener todos los checkboxes seleccionados
    const seleccionados = $(".checkProducto:checked")
      .map(function () {
        return $(this).val(); // devuelve el ID del producto
      })
      .get(); // convierte en array

    if (seleccionados.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Sin productos seleccionados",
        text: "Por favor, seleccioná al menos un producto.",
        confirmButtonColor: "#f39c12",
        confirmButtonText: "Entendido",
      });
      return;
    }

    // Guardás los IDs en un input oculto o variable global
    $("#idsSeleccionados").val(seleccionados.join(",")); // si usás un input hidden

    // Abrís tu modal de edición de precio
    abrirModal("modalEditarPrecios");
  });
});
