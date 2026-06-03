<?php
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>MC Aromas - Productos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="icon" type="image/jpeg"
        href="https://res.cloudinary.com/dzfzqzdcu/image/upload/v1743554383/ari6vwivcy0ndoeqpmmw.jpg">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Importar jQuery -->
    <!-- Estilos -->
    <link rel="stylesheet" href="build/css/app.css?v=<?php echo time(); ?>">
    <!-- JS -->
    <script src="build/js/app.js?v=<?php echo time(); ?>"></script>
    <!-- Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Fuente Lexend -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet" />
</head>

<body>

    <aside class="sidebar">
        <nav>
            <img src="https://res.cloudinary.com/dzfzqzdcu/image/upload/v1743554383/ari6vwivcy0ndoeqpmmw.jpg"
                class="logo" alt="">
            <ul>
                <li><a href="index.php">Información</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="stock.php">Stock</a></li>
                <li><a href="banners.php">Banners</a></li>
                <li><a href="pedidos.php">Pedidos</a></li>
                <li><a href="ventas.php">Ventas</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li><a href="src/php/logout.php">
                        <button id="logout-button">Cerrar Sesión</button>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="content">
        <section id="productos" class="productos-section">
            <h2>Lista de Productos</h2>

            <div class="buscador-productos">
                <p class="titulo-buscador">Buscador de Productos</p>
                <input type="text" id="search-input" placeholder="Buscar...🔍">

                <div class="botones-acciones">
                    <button id="add-product-btn" class="btn">➕ Agregar Producto</button>

                    <button id="modo-mantenimiento-btn" class="btn">
                        <label>
                            <input type="checkbox" id="mantenimiento-toggle">
                            🔧 Modo Mantenimiento
                        </label>
                    </button>

                    <button id="export-excel-btn" class="btn-excel">📊 Exportar Excel</button>
                </div>

            </div>


            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripcion</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Precio
                            <span id="ordenar-precio" class="orden-icon" data-order="null">🔼🔽</span>
                        </th>
                        <th>Precio Mayorista
                            <span id="ordenar-preciomayorista" class="orden-icon" data-order="null">🔼🔽</span>
                        </th>
                        <th>Stock
                            <span id="ordenar-stock" class="orden-icon" data-order="null">🔼🔽</span>
                        </th>
                        <th>Habilitado
                            <label class="checkbox-container">
                                <input type="checkbox" id="filter-habilitado" data-state="null">
                                <!-- Asegúrate de agregar data-state -->
                            </label>
                        </th>
                        </th>
                        <th>Imagen</th>
                        <th>Acciones</th>
                        <th>Editar $</th> <!-- para seleccionar todos -->
                        <input type="hidden" id="idsSeleccionados" name="idsSeleccionados">


                    </tr>
                </thead>
                <tbody id="product-table-body">
                    <!-- Aquí se cargarán los productos con AJAX -->
                </tbody>
            </table>
        </section>
    </main>

    <!-- Modal Agregar Producto -->
    <div id="modalAgregar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('modalAgregar')">&times;</span>
            <h2>Agregar Producto</h2>
            <form id="formAgregar" enctype="multipart/form-data">
                <label>Nombre:</label>
                <input type="text" id="nombreAgregar" name="nombre" required>

                <label>Descripción:</label>
                <input type="text" id="descripcionAgregar" name="descripcion" required>

                <label>Categoría:</label>
                <select id="categoriaAgregar" name="categoriaSelect">
                    <option value="" disabled selected>Seleccionar categoría existente</option>
                    <!-- Aquí se van a cargar las categorías desde la base de datos -->
                </select>
                <input type="text" id="nuevaCategoriaAgregar" placeholder="o escribe una nueva categoría" />

                <label>Marca:</label>
                <select id="marcaAgregar" name="marcaSelect">
                    <option value="" disabled selected>Seleccionar marca existente</option>
                    <!-- Aquí se van a cargar las marcas desde la base de datos -->
                </select>
                <input type="text" id="nuevaMarcaAgregar" name="nuevaMarca" placeholder="O escribe una nueva marca">

                <label>Precio:</label>
                <input type="number" id="precioAgregar" name="precio" step="0.01" required>

                <label>Precio Mayorista:</label>
                <input type="number" id="precioMayoristaAgregar" step="0.01" name="precioMayorista" required>

                <label>Stock:</label>
                <input type="number" id="stockAgregar" step="1" name="stock" required>


                <label>Imagen:</label>
                <input type="file" id="imagenAgregar" name="imagen" accept="image/*">

                <label>Habilitado:</label>
                <select id="habilitadoAgregar" name="habilitado">
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>

                <button type="submit">Agregar</button>
            </form>
        </div>
    </div>

    <!-- Modal Editar Producto -->

    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('modalEditar')">&times;</span>
            <form id="formEditar" enctype="multipart/form-data">
                <input type="hidden" id="idEditar" name="id">

                <label>Nombre:</label>
                <input type="text" id="nombreEditar" name="nombre" required>

                <label>Descripción:</label>
                <input type="text" id="descripcionEditar" name="descripcion" required>

                <label>Categoría:</label>
                <select id="categoriaEditar" name="categoriaSelectEditar">
                    <option value="" disabled selected>Seleccionar categoría existente</option>
                    <!-- Se cargarán dinámicamente -->
                </select>
                <input type="text" id="nuevaCategoriaEditar" placeholder="o escribe una nueva categoría" />


                <label>Marca:</label>
                <select id="marcaEditar" name="marca" required>
                    <option value="" disabled selected>Seleccionar o agregar nueva marca</option>
                </select>
                <input type="text" id="nuevaMarcaEditar" placeholder="Nueva Marca">
                <br>

                <label>Precio:</label>
                <input type="number" id="precioEditar" name="precio" step="0.01" required>

                <label>Precio Mayorista:</label>
                <input type="number" id="preciomayoristaEditar" name="precioMayorista" step="0.01" required>

                <label>Stock:</label>
                <input type="number" id="stockEditar" step="1" name="stock" required>

                <label>Imagen Actual:</label>
                <div>
                    <img id="imagenActual" src="" alt="Imagen Actual" style="width: 150px;">
                </div>

                <!-- Input oculto para la URL actual -->
                <input type="hidden" id="imagenUrlActual" name="imagenUrlActual">

                <label>Subir Nueva Imagen (Opcional):</label>
                <input type="file" id="imagenEditar" name="imagen" accept="image/*">

                <label>Habilitado:</label>
                <select id="habilitadoEditar" name="habilitado">
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>

                <button type="submit">Guardar Cambios</button>
            </form>
        </div>
    </div>


    <!-- Modal Confirmar Eliminación -->
    <div id="modalEliminar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('modalEliminar')">&times;</span>
            <h2>¿Estás seguro?</h2>
            <p>¿Quieres eliminar este producto?</p>
            <button id="confirmarEliminar">Eliminar</button>
            <button onclick="cerrarModal('modalEliminar')">Cancelar</button>
        </div>
    </div>
    <!-- Modal Editar Productos Seleccionados -->
    <div id="modalEditarPrecios" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('modalEditarPrecios')">&times;</span>
            <form id="formEditarPrecios">
                <h3>Editar Productos Seleccionados</h3>

                <label><input type="checkbox" id="check-precio"> Editar Precio</label>
                <input type="number" step="0.01" name="precionuevo" id="input-precio" disabled>

                <label><input type="checkbox" id="check-mayorista"> Editar Precio Mayorista</label>
                <input type="number" step="0.01" name="preciomayoristanuevo" id="input-mayorista" disabled>

                <label><input type="checkbox" id="check-categoria"> Editar Categoría</label>
                <input type="text" name="categoriaNueva" id="input-categoria" disabled>

                <label><input type="checkbox" id="check-stock"> Editar Stock</label>
                <input type="number" step="1" name="stockNuevo" id="input-stock" disabled>

                <input type="hidden" name="ids" id="idsSeleccionados">
                <button type="submit" id="btnEditarPrecios">Actualizar</button>
            </form>
        </div>
    </div>





</body>

</html>