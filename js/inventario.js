class InventarioManager {
    constructor() {
        this.apiUrl = 'database/inventario_crud.php';
        this.init();
    }

    init() {
        this.cargarProductos();
        this.cargarEstadisticas();
        this.configurarEventos();
    }

    // Configurar eventos
    configurarEventos() {
        // Búsqueda
        const searchInput = document.querySelector('.input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                if (e.target.value.length > 2) {
                    this.buscarProductos(e.target.value);
                } else if (e.target.value.length === 0) {
                    this.cargarProductos();
                }
            });
        }

        // Botón nuevo producto
        const btnNuevo = document.querySelector('.button7');
        if (btnNuevo) {
            btnNuevo.addEventListener('click', () => this.mostrarModalCrear());
        }

        // Botones de editar y eliminar
        document.addEventListener('click', (e) => {
            if (e.target.closest('.button10')) {
                const productId = e.target.closest('.card5').dataset.productId;
                this.editarProducto(productId);
            }
            
            if (e.target.closest('[data-action="eliminar"]')) {
                const productId = e.target.closest('.card5').dataset.productId;
                this.eliminarProducto(productId);
            }
        });
    }

    // Realizar petición AJAX
    async realizarPeticion(data, method = 'POST') {
        try {
            const formData = new FormData();
            
            if (method === 'POST') {
                Object.keys(data).forEach(key => {
                    formData.append(key, data[key]);
                });
            }

            const response = await fetch(this.apiUrl + (method === 'GET' && data.id ? `?id=${data.id}` : ''), {
                method: method,
                body: method === 'POST' ? formData : null
            });

            return await response.json();
        } catch (error) {
            console.error('Error en la petición:', error);
            return null;
        }
    }

    // Cargar todos los productos
    async cargarProductos() {
        const productos = await this.realizarPeticion({ action: 'obtener_todos' });
        if (productos) {
            this.renderizarProductos(productos);
        }
    }

    // Buscar productos
    async buscarProductos(termino) {
        const productos = await this.realizarPeticion({ 
            action: 'buscar', 
            termino: termino 
        });
        if (productos) {
            this.renderizarProductos(productos);
        }
    }

    // Cargar estadísticas
    async cargarEstadisticas() {
        const stats = await this.realizarPeticion({ action: 'obtener_estadisticas' });
        if (stats) {
            this.actualizarEstadisticas(stats);
        }
    }

    // Crear producto
    async crearProducto(datosProducto) {
        const resultado = await this.realizarPeticion({
            action: 'crear',
            ...datosProducto
        });

        if (resultado && resultado.success) {
            this.mostrarMensaje('Producto creado exitosamente', 'success');
            this.cargarProductos();
            this.cargarEstadisticas();
            this.cerrarModal();
        } else {
            this.mostrarMensaje('Error al crear el producto', 'error');
        }
    }

    // Actualizar producto
    async actualizarProducto(id, datosProducto) {
        const resultado = await this.realizarPeticion({
            action: 'actualizar',
            id: id,
            ...datosProducto
        });

        if (resultado && resultado.success) {
            this.mostrarMensaje('Producto actualizado exitosamente', 'success');
            this.cargarProductos();
            this.cargarEstadisticas();
            this.cerrarModal();
        } else {
            this.mostrarMensaje('Error al actualizar el producto', 'error');
        }
    }

    // Eliminar producto
    async eliminarProducto(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
            const resultado = await this.realizarPeticion({
                action: 'eliminar',
                id: id
            });

            if (resultado && resultado.success) {
                this.mostrarMensaje('Producto eliminado exitosamente', 'success');
                this.cargarProductos();
                this.cargarEstadisticas();
            } else {
                this.mostrarMensaje('Error al eliminar el producto', 'error');
            }
        }
    }

    // Obtener producto por ID
    async obtenerProducto(id) {
        return await this.realizarPeticion({ id: id }, 'GET');
    }

    // Renderizar productos en el HTML
    renderizarProductos(productos) {
        const container = document.querySelector('.container21');
        if (!container) return;

        container.innerHTML = '';

        productos.forEach(producto => {
            const estadoStock = this.obtenerEstadoStock(producto.stock);
            const productCard = this.crearTarjetaProducto(producto, estadoStock);
            container.appendChild(productCard);
        });
    }

    // Crear tarjeta de producto
    crearTarjetaProducto(producto, estadoStock) {
        const card = document.createElement('div');
        card.className = 'card5';
        card.dataset.productId = producto.id_producto;

        card.innerHTML = `
            <div class="inventorymanagement7">
                <div class="container22">
                    <img src="/public/images/boxMo.svg" alt="" class="icon-xl" style="width: 40px; height: 40px; border-radius: 6px;">
                    <div class="container23">
                        <div class="container24">
                            <div class="heading-3">${producto.nombre}</div>
                            <div class="dhanshree-stationery-e-commerc-badge">${producto.codigo_barras}</div>
                        </div>
                        <div class="container25">
                            <div class="text2">${producto.categoria || 'Sin categoría'}</div>
                            <div class="text3">Stock: ${producto.stock}</div>
                            <div class="text4">Precio: $${parseFloat(producto.precio).toFixed(2)}</div>
                        </div>
                    </div>
                </div>
                <div class="${producto.stock <= 5 ? 'container62' : 'container26'}">
                    <div class="${producto.stock <= 5 ? 'container63' : 'container27'}">
                        <div class="container5">
                            <div class="${producto.stock <= 5 ? 'text9' : 'text5'}">$${parseFloat(producto.precio).toFixed(2)}</div>
                        </div>
                        <div class="${estadoStock.clase}">${estadoStock.texto}</div>
                    </div>
                    <div class="container29">
                        <div class="container30">
                            <div class="button8">-</div>
                            <input type="text" value="${producto.stock}" class="dhanshree-stationery-e-commerc-input">
                            <div class="button9">+</div>
                        </div>
                        <div class="button10">
                            <img src="/public/images/edit.svg" alt="" class="icon-sm">
                            <div>Editar</div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return card;
    }

    // Obtener estado del stock
    obtenerEstadoStock(stock) {
        if (stock === 0) {
            return { clase: 'badge10', texto: 'Agotado' };
        } else if (stock <= 5) {
            return { clase: 'badge10', texto: 'Stock bajo' };
        } else {
            return { clase: 'badge2', texto: 'En stock' };
        }
    }

    // Actualizar estadísticas en el HTML
    actualizarEstadisticas(stats) {
        // Total productos
        const totalProductos = document.querySelector('.container13 .b');
        if (totalProductos) totalProductos.textContent = stats.total_productos || 0;

        // Valor total
        const valorTotal = document.querySelector('.container14 .dhanshree-stationery-e-commerc-b');
        if (valorTotal) valorTotal.textContent = `$${parseFloat(stats.valor_total || 0).toLocaleString()}`;

        // Total items
        const totalItems = document.querySelector('.container15 .b');
        if (totalItems) totalItems.textContent = stats.total_items || 0;

        // Stock bajo
        const stockBajo = document.querySelector('.container16 .b');
        if (stockBajo) stockBajo.textContent = stats.stock_bajo || 0;

        // Agotados
        const agotados = document.querySelector('.container17 .b');
        if (agotados) agotados.textContent = stats.agotados || 0;

        // Actualizar alerta de stock bajo
        const alertaNumero = document.querySelector('.inventorymanagement6 .sistema-de-punto');
        if (alertaNumero) alertaNumero.textContent = stats.stock_bajo || 0;
    }

    // Mostrar modal para crear producto
    mostrarModalCrear() {
        this.mostrarModal('Nuevo Producto', {});
    }

    // Editar producto
    async editarProducto(id) {
        const producto = await this.obtenerProducto(id);
        if (producto) {
            this.mostrarModal('Editar Producto', producto);
        }
    }

    // Mostrar modal
    async mostrarModal(titulo, producto = {}) {
        try {
            const response = await fetch('modal-producto.html');
            const html = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const modalElement = doc.querySelector('.modal-overlay');
            
            document.body.appendChild(modalElement);
            
            // Llenar datos del producto
            document.getElementById('modal-titulo').textContent = titulo;
            document.getElementById('producto-id').value = producto.id_producto || '';
            document.getElementById('producto-nombre').value = producto.nombre || '';
            document.getElementById('producto-sku').value = producto.codigo_barras || '';
            document.getElementById('producto-categoria').value = producto.id_categoria || '';
            document.getElementById('producto-stock').value = producto.stock || '';
            document.getElementById('producto-precio').value = producto.precio || '';
            document.getElementById('producto-descripcion').value = producto.descripcion || '';
            document.getElementById('btn-guardar').textContent = producto.id_producto ? 'Actualizar' : 'Crear';
            
            // Eventos del modal
            modalElement.querySelector('.modal-close').addEventListener('click', () => this.cerrarModal());
            modalElement.querySelector('.btn-cancel').addEventListener('click', () => this.cerrarModal());
            modalElement.querySelector('.modal-form').addEventListener('submit', (e) => this.manejarSubmitModal(e));
            
            // Cerrar al hacer clic fuera del modal
            modalElement.addEventListener('click', (e) => {
                if (e.target === modalElement) this.cerrarModal();
            });
        } catch (error) {
            console.error('Error al cargar modal:', error);
        }
    }

    // Manejar submit del modal
    async manejarSubmitModal(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const datos = Object.fromEntries(formData.entries());
        
        if (datos.id) {
            await this.actualizarProducto(datos.id, datos);
        } else {
            delete datos.id;
            await this.crearProducto(datos);
        }
    }

    // Cerrar modal
    cerrarModal() {
        const modal = document.querySelector('.modal-overlay');
        if (modal) {
            modal.remove();
        }
    }

    // Mostrar mensaje
    mostrarMensaje(mensaje, tipo = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${tipo}`;
        toast.textContent = mensaje;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new InventarioManager();
});

