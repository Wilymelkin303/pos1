<?php
/**
 * Interfaz principal del punto de venta.
 *
 * Contiene el buscador de productos, el carrito activo, datos de pago y el
 * token CSRF utilizado por JavaScript para procesar ventas de forma segura.
 */
require 'auth/verificar.php';
require 'config/conexion.php';
include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<div class="pos-layout">

    <div class="main-content">

        <div class="page-heading compact">
            <div>
                <span class="eyebrow">Punto de venta</span>
                <h3>Ventas</h3>
                <p>Busca, escanea y arma el pedido desde una sola pantalla.</p>
            </div>
        </div>

        <div class="mb-3 position-relative">

            <input type="text" 
                   id="buscador" 
                   class="form-control form-control-lg"
                   placeholder="Escanear código o buscar producto..."
                   autocomplete="off">

            <div id="resultadosBusqueda"
                 class="list-group position-absolute"
                 style="z-index:1000;width:100%;">
            </div>

        </div>

        <div id="productosVisual">
            <div class="empty-state">
                <div>
                    <strong>Listo para vender</strong>
                    Busca un producto o escanea un código para empezar el pedido.
                </div>
            </div>
        </div>

    </div>


    <div class="carrito">

        <div class="cart-panel-header">
            <div>
                <span class="cart-kicker">Venta actual</span>
                <h5>Carrito</h5>
            </div>
            <div class="cart-header-icon">$</div>
        </div>

        <hr>

        <div id="listaCarrito"></div>

        <hr>

        <div class="cart-payment-box">

            <div class="mb-2">
                <label>Cliente</label>
                <input type="text" id="cliente" class="form-control form-control-sm">
            </div>

            <div class="mb-2">

                <label>Método de Pago</label>

                <select id="metodo_pago"
                        class="form-control form-control-sm"
                        onchange="verificarMetodo()">

                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>

                </select>

            </div>

            <div class="mb-2" id="campoEfectivo">

                <label>Efectivo Recibido</label>

                <input type="number"
                       step="0.01"
                       id="efectivo"
                       class="form-control form-control-sm"
                       oninput="calcularCambio()">

            </div>

            <div class="cart-change-row">
                <span>Cambio</span>
                <strong>L <span id="cambio">0.00</span></strong>
            </div>

        </div>

        <div class="cart-total-box">
            <span>Total</span>
            <strong>L <span id="total">0.00</span></strong>
        </div>

        <button class="btn btn-success w-100 mt-2"
                onclick="finalizarVenta()">

            Finalizar Venta

        </button>

    </div>

</div>

<script>
window.POS_CSRF_TOKEN = "<?= e(csrf_token()) ?>";
</script>

<?php include 'layouts/footer.php'; ?>
