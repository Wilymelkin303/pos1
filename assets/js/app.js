/*
 * Comportamiento principal del punto de venta.
 *
 * Administra búsqueda de productos, carrito, edición de precio especial,
 * cálculo de cambio, envío seguro de ventas y disparo de impresión de ticket.
 */

let carrito = [];
let total = 0;
let resultadosGlobales = [];
let buscador = document.getElementById("buscador");

function escapeHtml(valor){
    return String(valor)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

// ============================
// BUSCADOR
// ============================
if(buscador){
buscador.addEventListener("keyup", function(){

    let valor = this.value;

    if(valor.length < 1){
        document.getElementById("resultadosBusqueda").innerHTML = "";
        return;
    }

    fetch("productos/buscar.php?q=" + encodeURIComponent(valor))
    .then(res => res.json())
    .then(data => {

        resultadosGlobales = data;

        let contenedor = document.getElementById("resultadosBusqueda");
        contenedor.innerHTML = "";

        data.forEach((p, index) => {

            contenedor.innerHTML += `
                <a href="#" class="list-group-item list-group-item-action"
                onclick="agregarDesdeBusqueda(${index})">
                    ${escapeHtml(p.nombre)} - L ${parseFloat(p.precio_unidad).toFixed(2)}
                </a>
            `;
        });

        if(data.length === 1 && valor.length > 5){
            agregarProducto(data[0]);
            document.getElementById("buscador").value = "";
            contenedor.innerHTML = "";
        }
    });
});
}

// ============================
// AGREGAR DESDE RESULTADOS
// ============================
function agregarDesdeBusqueda(index){

    let producto = resultadosGlobales[index];

    agregarProducto(producto);

    document.getElementById("buscador").value = "";
    document.getElementById("resultadosBusqueda").innerHTML = "";
}

// ============================
// AGREGAR PRODUCTO
// ============================
function agregarProducto(producto){

    let existente = carrito.find(p => p.id == producto.id);

    if(existente){

        if((existente.cantidad + 1) > producto.stock){
            alert("Stock insuficiente");
            return;
        }

        existente.cantidad++;

    } else {

        if(producto.stock <= 0){
            alert("Producto sin stock");
            return;
        }

        carrito.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: parseFloat(producto.precio_unidad),
            precio_original: parseFloat(producto.precio_unidad),
            cantidad: 1,
            stock: producto.stock,
            imagen: producto.imagen_thumb
        });
    }

    renderCarrito();
    renderVisual();
}

// ============================
// RENDER VISUAL PRODUCTOS
// ============================
function renderVisual(){

    let contenedor = document.getElementById("productosVisual");

    if(!contenedor){
        return;
    }

    contenedor.innerHTML = "";

    if(carrito.length === 0){
        contenedor.innerHTML = `
            <div class="empty-state">
                <div>
                    <strong>Listo para vender</strong>
                    Busca un producto o escanea un código para empezar el pedido.
                </div>
            </div>
        `;
        return;
    }

    carrito.forEach(p => {

        contenedor.innerHTML += `
        
        <div class="card product-card shadow-sm">

            ${p.imagen ? 
            `<img src="data:image/jpeg;base64,${p.imagen}" 
            alt="${escapeHtml(p.nombre)}">`
            :
            `<div class="product-placeholder">$</div>`
            }

            <div class="card-body text-center p-2">

                <small>${escapeHtml(p.nombre)}</small>

                <div class="fw-bold">
                    L ${p.precio.toFixed(2)}
                </div>

                <div style="font-size:13px;color:#666;">
                    Cant: ${p.cantidad}
                </div>

            </div>

        </div>
        
        `;

    });

}

// ============================
// RENDER CARRITO
// ============================
function renderCarrito(){

    let lista = document.getElementById("listaCarrito");

    if(!lista){
        return;
    }

    lista.innerHTML = "";

    total = 0;

    carrito.forEach((p, index) => {

        let subtotal = p.precio * p.cantidad;

        total += subtotal;

        let precioEspecial = Math.abs(p.precio - p.precio_original) >= 0.01;

        lista.innerHTML += `
            <div class="cart-item ${precioEspecial ? "cart-item-special" : ""}">

                <div class="cart-item-head">
                    <div>
                        <strong>${escapeHtml(p.nombre)}</strong>
                        ${precioEspecial ? `<span class="cart-badge">Precio especial</span>` : ""}
                    </div>

                    <button class="btn btn-sm btn-danger"
                            onclick="eliminarProducto(${index})">
                        ×
                    </button>
                </div>

                <div class="cart-price-row">
                    <label>Precio:</label>
                    <input type="number"
                           step="0.01"
                           min="0.01"
                           value="${p.precio.toFixed(2)}"
                           class="form-control form-control-sm"
                           onchange="editarPrecio(${index}, this.value)">
                    ${precioEspecial ? `<small>Normal: L ${p.precio_original.toFixed(2)}</small>` : ""}
                </div>

                <div class="cart-qty-row">

                    <div class="qty-controls">

                        <button class="btn btn-sm btn-secondary"
                                onclick="cambiarCantidad(${index}, -1)">
                            -
                        </button>

                        <span>${p.cantidad}</span>

                        <button class="btn btn-sm btn-secondary"
                                onclick="cambiarCantidad(${index}, 1)">
                            +
                        </button>

                    </div>

                    <div class="cart-subtotal">
                        L ${subtotal.toFixed(2)}
                    </div>

                </div>

            </div>
        `;
    });

    document.getElementById("total").innerText = total.toFixed(2);

    calcularCambio();
}

// ============================
// CAMBIAR CANTIDAD
// ============================
function cambiarCantidad(index, cambio){

    let item = carrito[index];

    if((item.cantidad + cambio) > item.stock){
        alert("Stock insuficiente");
        return;
    }

    item.cantidad += cambio;

    if(item.cantidad <= 0){
        carrito.splice(index, 1);
    }

    renderCarrito();
    renderVisual();
}

// ============================
// EDITAR PRECIO
// ============================
function editarPrecio(index, nuevoPrecio){

    let precio = parseFloat(nuevoPrecio);

    if(!Number.isFinite(precio) || precio <= 0){
        alert("Precio inválido");
        renderCarrito();
        return;
    }

    carrito[index].precio = Math.round(precio * 100) / 100;

    renderCarrito();
    renderVisual();
}

// ============================
// ELIMINAR PRODUCTO
// ============================
function eliminarProducto(index){

    carrito.splice(index, 1);

    renderCarrito();
    renderVisual();
}

// ============================
// CALCULAR CAMBIO
// ============================
function calcularCambio(){

    let efectivo = parseFloat(document.getElementById("efectivo").value || 0);

    let cambio = efectivo - total;

    if(cambio < 0){
        cambio = 0;
    }

    document.getElementById("cambio").innerText = cambio.toFixed(2);

}

// ============================
// FINALIZAR VENTA
// ============================
function finalizarVenta(){

    if(carrito.length === 0){
        alert("No hay productos en el carrito");
        return;
    }

    let cliente = document.getElementById("cliente").value;
    let metodo = document.getElementById("metodo_pago").value;
    let efectivo = parseFloat(document.getElementById("efectivo").value || 0);
    let cambio = parseFloat(document.getElementById("cambio").innerText);

    if(metodo === "Efectivo" && efectivo < total){
        alert("Efectivo insuficiente");
        return;
    }

    fetch("ventas/procesar.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": window.POS_CSRF_TOKEN || ""
        },

        body: JSON.stringify({
            carrito: carrito,
            total: total,
            cliente: cliente,
            metodo_pago: metodo,
            efectivo: efectivo,
            cambio: cambio
        })

    })
    .then(res => res.json())
    .then(data => {

        if(data.ok){

            let idVenta = data.idVenta;

            carrito = [];

            renderCarrito();
            renderVisual();

            document.getElementById("cliente").value = "";
            document.getElementById("efectivo").value = "";
            document.getElementById("cambio").innerText = "0.00";

            imprimirTicket(idVenta);

        } else {

            alert(data.error || "Error en la venta");

        }

    })
    .catch(error => {

        alert("Error en la venta");

        console.error(error);

    });
}

// ============================
// IMPRIMIR TICKET
// ============================
function imprimirTicket(idVenta){

    let iframe = document.createElement("iframe");

    iframe.style.position = "fixed";
    iframe.style.right = "0";
    iframe.style.bottom = "0";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "0";

    iframe.src = "ventas/ticket.php?id=" + encodeURIComponent(idVenta);

    document.body.appendChild(iframe);

    iframe.onload = function(){

        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        setTimeout(function(){
            document.body.removeChild(iframe);
        }, 1000);

    };

}

// ============================
// METODO DE PAGO
// ============================
function verificarMetodo(){

    let metodo = document.getElementById("metodo_pago").value;
    let campoEfectivo = document.getElementById("campoEfectivo");
    let efectivo = document.getElementById("efectivo");

    if(metodo === "Efectivo"){
        campoEfectivo.style.display = "";
    } else {
        campoEfectivo.style.display = "none";
        efectivo.value = "";
        document.getElementById("cambio").innerText = "0.00";
    }
}

if(document.getElementById("metodo_pago")){
    verificarMetodo();
}
