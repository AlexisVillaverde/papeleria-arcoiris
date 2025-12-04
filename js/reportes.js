// reportes.js - Manejo de reportes con AJAX

class ReportesManager {
    constructor() {
        this.baseUrl = 'database/reportes_crud.php';
        this.initEventListeners();
        this.cargarResumenHoy();
        this.cargarEmpleados();
    }

    initEventListeners() {
        // Crear elementos de filtros si no existen
        this.crearElementosFiltros();
        
        // Botones de reportes (usar clases del HTML original)
        document.querySelector('.card5')?.addEventListener('click', () => this.generarReporteVentas());
        document.querySelector('.card6')?.addEventListener('click', () => this.generarReporteProductos());
        
        // Botón exportar (usar botón existente)
        document.querySelector('.button5')?.addEventListener('click', () => this.exportarCSV());
        document.querySelector('.button6')?.addEventListener('click', () => this.generarReporteInventario());
        
        // Agregar más botones de reportes
        this.agregarBotonesReportes();
    }

    async cargarResumenHoy() {
        try {
            const response = await fetch(this.baseUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'accion=resumen_hoy'
            });
            
            const data = await response.json();
            
            if (data) {
                // Actualizar usando selectores del HTML original
                const totalVentas = document.querySelector('.container16 .b');
                const ingresosTotales = document.querySelector('.container17 .dhanshree-stationery-e-commerc-b');
                const promedioVenta = document.querySelector('.container18 .b2');
                
                if (totalVentas) totalVentas.textContent = data.total_ventas || 0;
                if (ingresosTotales) ingresosTotales.textContent = '$' + (parseFloat(data.ingresos_totales) || 0).toFixed(2);
                if (promedioVenta) promedioVenta.textContent = '$' + (parseFloat(data.promedio_venta) || 0).toFixed(2);
            }
        } catch (error) {
            console.error('Error al cargar resumen:', error);
        }
    }

    async cargarEmpleados() {
        try {
            const response = await fetch(this.baseUrl + '?accion=empleados');
            const empleados = await response.json();
            
            // Guardar empleados para uso posterior
            this.empleados = empleados;
        } catch (error) {
            console.error('Error al cargar empleados:', error);
        }
    }

    async generarReporteVentas() {
        const fechaInicio = document.getElementById('fecha-inicio').value;
        const fechaFin = document.getElementById('fecha-fin').value;
        const empleadoId = document.getElementById('filtro-empleado').value;

        if (!fechaInicio || !fechaFin) {
            alert('Por favor selecciona las fechas de inicio y fin');
            return;
        }

        this.mostrarCargando(true);

        try {
            const formData = new FormData();
            formData.append('accion', 'ventas');
            formData.append('fecha_inicio', fechaInicio);
            formData.append('fecha_fin', fechaFin);
            if (empleadoId) formData.append('empleado_id', empleadoId);

            const response = await fetch(this.baseUrl, {
                method: 'POST',
                body: formData
            });

            const ventas = await response.json();
            this.mostrarReporteVentas(ventas);
        } catch (error) {
            console.error('Error:', error);
            alert('Error al generar el reporte');
        } finally {
            this.mostrarCargando(false);
        }
    }

    async generarReporteProductos() {
        const fechaInicio = document.getElementById('fecha-inicio').value;
        const fechaFin = document.getElementById('fecha-fin').value;
        const limite = document.getElementById('limite-productos')?.value || 10;

        if (!fechaInicio || !fechaFin) {
            alert('Por favor selecciona las fechas');
            return;
        }

        this.mostrarCargando(true);

        try {
            const formData = new FormData();
            formData.append('accion', 'productos_vendidos');
            formData.append('fecha_inicio', fechaInicio);
            formData.append('fecha_fin', fechaFin);
            formData.append('limite', limite);

            const response = await fetch(this.baseUrl, {
                method: 'POST',
                body: formData
            });

            const productos = await response.json();
            this.mostrarReporteProductos(productos);
        } catch (error) {
            console.error('Error:', error);
            alert('Error al generar el reporte');
        } finally {
            this.mostrarCargando(false);
        }
    }

    async generarReporteInventario() {
        this.mostrarCargando(true);

        try {
            const response = await fetch(this.baseUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'accion=inventario_bajo'
            });

            const productos = await response.json();
            this.mostrarReporteInventario(productos);
        } catch (error) {
            console.error('Error:', error);
            alert('Error al generar el reporte');
        } finally {
            this.mostrarCargando(false);
        }
    }

    async generarReporteEmpleados() {
        const fechaInicio = document.getElementById('fecha-inicio').value;
        const fechaFin = document.getElementById('fecha-fin').value;

        if (!fechaInicio || !fechaFin) {
            alert('Por favor selecciona las fechas');
            return;
        }

        this.mostrarCargando(true);

        try {
            const formData = new FormData();
            formData.append('accion', 'ventas_empleado');
            formData.append('fecha_inicio', fechaInicio);
            formData.append('fecha_fin', fechaFin);

            const response = await fetch(this.baseUrl, {
                method: 'POST',
                body: formData
            });

            const empleados = await response.json();
            this.mostrarReporteEmpleados(empleados);
        } catch (error) {
            console.error('Error:', error);
            alert('Error al generar el reporte');
        } finally {
            this.mostrarCargando(false);
        }
    }

    mostrarReporteVentas(ventas) {
        const container = document.getElementById('resultado-reportes');
        
        if (!ventas || ventas.length === 0) {
            container.innerHTML = '<p>No se encontraron ventas en el período seleccionado.</p>';
            return;
        }

        let total = 0;
        let html = `
            <h3>Reporte de Ventas</h3>
            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Empleado</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Método Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
        `;

        ventas.forEach(venta => {
            total += parseFloat(venta.total);
            html += `
                <tr>
                    <td>${venta.folio}</td>
                    <td>${new Date(venta.fecha_venta).toLocaleDateString()}</td>
                    <td>${venta.empleado}</td>
                    <td>$${parseFloat(venta.subtotal).toFixed(2)}</td>
                    <td>$${parseFloat(venta.iva).toFixed(2)}</td>
                    <td>$${parseFloat(venta.total).toFixed(2)}</td>
                    <td>${venta.metodo_pago}</td>
                    <td>
                        <button onclick="reportes.verDetalle(${venta.folio})" class="btn-detalle">Ver</button>
                        <button onclick="reportes.eliminarVenta(${venta.folio})" class="btn-eliminar">Eliminar</button>
                    </td>
                </tr>
            `;
        });

        html += `
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5"><strong>Total General:</strong></td>
                        <td><strong>$${total.toFixed(2)}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        `;

        container.innerHTML = html;
        this.datosActuales = ventas;
        this.tipoReporteActual = 'reporte_ventas';
    }

    mostrarReporteProductos(productos) {
        const container = document.getElementById('resultado-reportes');
        
        if (!productos || productos.length === 0) {
            container.innerHTML = '<p>No se encontraron productos vendidos en el período.</p>';
            return;
        }

        let html = `
            <h3>Productos Más Vendidos</h3>
            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Cantidad Vendida</th>
                        <th>Ingresos Generados</th>
                    </tr>
                </thead>
                <tbody>
        `;

        productos.forEach(producto => {
            html += `
                <tr>
                    <td>${producto.nombre}</td>
                    <td>${producto.codigo_barras}</td>
                    <td>${producto.total_vendido}</td>
                    <td>$${parseFloat(producto.ingresos_generados).toFixed(2)}</td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
        this.datosActuales = productos;
        this.tipoReporteActual = 'productos_vendidos';
    }

    mostrarReporteInventario(productos) {
        const container = document.getElementById('resultado-reportes');
        
        if (!productos || productos.length === 0) {
            container.innerHTML = '<p>No hay productos con stock bajo.</p>';
            return;
        }

        let html = `
            <h3>Productos con Stock Bajo</h3>
            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                    </tr>
                </thead>
                <tbody>
        `;

        productos.forEach(producto => {
            html += `
                <tr class="${producto.stock === 0 ? 'sin-stock' : 'stock-bajo'}">
                    <td>${producto.codigo_barras}</td>
                    <td>${producto.nombre}</td>
                    <td>${producto.stock}</td>
                    <td>${producto.stock_minimo}</td>
                    <td>${producto.categoria || 'N/A'}</td>
                    <td>${producto.marca || 'N/A'}</td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
        this.datosActuales = productos;
        this.tipoReporteActual = 'inventario_bajo';
    }

    mostrarReporteEmpleados(empleados) {
        const container = document.getElementById('resultado-reportes');
        
        if (!empleados || empleados.length === 0) {
            container.innerHTML = '<p>No se encontraron datos de empleados.</p>';
            return;
        }

        let html = `
            <h3>Ventas por Empleado</h3>
            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Total Ventas</th>
                        <th>Ingresos Totales</th>
                        <th>Promedio por Venta</th>
                    </tr>
                </thead>
                <tbody>
        `;

        empleados.forEach(empleado => {
            html += `
                <tr>
                    <td>${empleado.empleado}</td>
                    <td>${empleado.total_ventas || 0}</td>
                    <td>$${parseFloat(empleado.ingresos_totales || 0).toFixed(2)}</td>
                    <td>$${parseFloat(empleado.promedio_venta || 0).toFixed(2)}</td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
        this.datosActuales = empleados;
        this.tipoReporteActual = 'ventas_empleados';
    }

    async verDetalle(folio) {
        try {
            const formData = new FormData();
            formData.append('accion', 'detalle_venta');
            formData.append('folio', folio);

            const response = await fetch(this.baseUrl, {
                method: 'POST',
                body: formData
            });

            const detalle = await response.json();
            this.mostrarModalDetalle(detalle);
        } catch (error) {
            console.error('Error:', error);
            alert('Error al obtener el detalle');
        }
    }

    async eliminarVenta(folio) {
        if (!confirm('¿Estás seguro de eliminar esta venta? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('accion', 'eliminar_venta');
            formData.append('folio', folio);

            const response = await fetch(this.baseUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                alert('Venta eliminada correctamente');
                this.generarReporteVentas(); // Recargar el reporte
            } else {
                alert('Error al eliminar la venta');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al eliminar la venta');
        }
    }

    mostrarModalDetalle(detalle) {
        if (!detalle || detalle.length === 0) return;

        const venta = detalle[0];
        let productosHtml = '';
        
        detalle.forEach(item => {
            productosHtml += `
                <tr>
                    <td>${item.producto}</td>
                    <td>${item.cantidad}</td>
                    <td>$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                    <td>$${parseFloat(item.subtotal_producto).toFixed(2)}</td>
                </tr>
            `;
        });

        const modalHtml = `
            <div class="modal-overlay" onclick="this.remove()">
                <div class="modal-content" onclick="event.stopPropagation()">
                    <h3>Detalle de Venta - Folio: ${venta.folio}</h3>
                    <p><strong>Fecha:</strong> ${new Date(venta.fecha_venta).toLocaleString()}</p>
                    <p><strong>Empleado:</strong> ${venta.empleado}</p>
                    <p><strong>Método de Pago:</strong> ${venta.metodo_pago}</p>
                    
                    <table class="tabla-detalle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${productosHtml}
                        </tbody>
                    </table>
                    
                    <div class="totales-venta">
                        <p><strong>Subtotal:</strong> $${parseFloat(venta.subtotal).toFixed(2)}</p>
                        <p><strong>IVA:</strong> $${parseFloat(venta.iva).toFixed(2)}</p>
                        <p><strong>Total:</strong> $${parseFloat(venta.total).toFixed(2)}</p>
                        ${venta.monto_recibido ? `<p><strong>Recibido:</strong> $${parseFloat(venta.monto_recibido).toFixed(2)}</p>` : ''}
                        ${venta.cambio ? `<p><strong>Cambio:</strong> $${parseFloat(venta.cambio).toFixed(2)}</p>` : ''}
                    </div>
                    
                    <button onclick="this.closest('.modal-overlay').remove()" class="btn-cerrar">Cerrar</button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    exportarCSV() {
        if (!this.datosActuales || this.datosActuales.length === 0) {
            alert('No hay datos para exportar');
            return;
        }

        const formData = new FormData();
        formData.append('accion', 'exportar_csv');
        formData.append('tipo_reporte', this.tipoReporteActual);
        formData.append('datos', JSON.stringify(this.datosActuales));

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = this.baseUrl;
        form.style.display = 'none';

        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    validarFechas() {
        const fechaInicio = document.getElementById('fecha-inicio').value;
        const fechaFin = document.getElementById('fecha-fin').value;

        if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
            alert('La fecha de inicio no puede ser mayor a la fecha fin');
            document.getElementById('fecha-inicio').value = '';
        }
    }

    mostrarCargando(mostrar) {
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.display = mostrar ? 'block' : 'none';
        }
    }
}

// Funciones auxiliares
ReportesManager.prototype.crearElementosFiltros = async function() {
    if (!document.getElementById('filtros-reportes')) {
        try {
            const response = await fetch('filtros-reportes.html');
            const html = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const filtrosElement = doc.getElementById('filtros-reportes');
            
            const container = document.querySelector('.container14');
            if (container) {
                container.insertBefore(filtrosElement, container.firstChild);
            }
        } catch (error) {
            console.error('Error al cargar filtros:', error);
        }
    }
};

ReportesManager.prototype.agregarBotonesReportes = async function() {
    const container = document.querySelector('.container14');
    if (container && !document.getElementById('botones-reportes')) {
        try {
            const response = await fetch('botones-reportes.html');
            const html = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const botonesElement = doc.getElementById('botones-reportes');
            const resultadosElement = doc.getElementById('resultado-reportes');
            
            container.appendChild(botonesElement);
            container.appendChild(resultadosElement);
        } catch (error) {
            console.error('Error al cargar botones:', error);
        }
    }
};

ReportesManager.prototype.obtenerFechaInicio = function() {
    const fecha = new Date();
    fecha.setDate(fecha.getDate() - 30);
    return fecha.toISOString().split('T')[0];
};

ReportesManager.prototype.obtenerFechaFin = function() {
    return new Date().toISOString().split('T')[0];
};

ReportesManager.prototype.mostrarMensaje = function(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 12px 20px;
        background: ${tipo === 'error' ? '#ef4444' : tipo === 'success' ? '#10b981' : '#3b82f6'};
        color: white; border-radius: 6px; z-index: 1000;
        transform: translateX(100%); transition: transform 0.3s ease;
    `;
    toast.textContent = mensaje;
    
    document.body.appendChild(toast);
    setTimeout(() => toast.style.transform = 'translateX(0)', 100);
    setTimeout(() => toast.remove(), 3000);
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.reportes = new ReportesManager();
});