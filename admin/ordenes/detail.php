<?php
/**
 * Detalle de orden
 */
$pageTitle = 'Detalle de Orden';
require_once '../../config.php';
require_once '../../helpers/auth.php';

// Necesitamos autenticación
if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}
startSecureSession();
requireAuth();

$ordenId = sanitize($_GET['id'] ?? '');

if (empty($ordenId)) {
    $_SESSION['error_message'] = 'ID de orden no válido';
    header('Location: list.php');
    exit;
}

// Obtener orden
$orden = fetchOne("SELECT * FROM orders WHERE id = :id", ['id' => $ordenId]);

if (!$orden) {
    $_SESSION['error_message'] = 'Orden no encontrada';
    header('Location: list.php');
    exit;
}

// Decodificar datos JSON
$items = json_decode($orden['items'] ?? '[]', true);
$metadata = json_decode($orden['metadata'] ?? '{}', true);

$statusLabels = [
    'approved' => 'Pagada',
    'pending' => 'Pendiente',
    'rejected' => 'Rechazada',
    'cancelled' => 'Cancelada'
];
$statusLabel = $statusLabels[$orden['status'] ?? 'pending'] ?? 'Desconocido';

$statusClasses = [
    'approved' => 'status-approved',
    'pending' => 'status-pending',
    'rejected' => 'status-rejected',
    'cancelled' => 'status-cancelled'
];
$statusClass = $statusClasses[$orden['status'] ?? 'pending'] ?? 'status-pending';

require_once '../_inc/header.php';
?>

<style>
.orden-detail {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.detail-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.detail-section h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f0f0f0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #666;
}

.detail-value {
    text-align: right;
    color: #333;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 12px;
    font-weight: 500;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.status-cancelled {
    background: #e2e3e5;
    color: #383d41;
}

.items-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.items-list li {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    border-bottom: 1px solid #f0f0f0;
}

.items-list li:last-child {
    border-bottom: none;
}

.total-row {
    font-weight: bold;
    font-size: 1.1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #333;
}

.btn-back {
    display: inline-block;
    background: #6c757d;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    margin-bottom: 1rem;
}

.btn-back:hover {
    background: #5a6268;
}

@media (max-width: 968px) {
    .orden-detail {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="admin-content">
    <a href="list.php" class="btn-back">← Volver a Órdenes</a>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Orden #<?= htmlspecialchars($orden['id']) ?></h2>
        <span class="status-badge <?= $statusClass ?>">
            <?= $statusLabel ?>
        </span>
    </div>

    <div class="orden-detail">
        <!-- Información principal -->
        <div>
            <!-- Items del pedido -->
            <div class="detail-section">
                <h3>Productos Pedidos</h3>
                <ul class="items-list">
                    <?php if (is_array($items) && count($items) > 0): ?>
                        <?php 
                        $subtotal = 0;
                        foreach ($items as $item): 
                            $precio = floatval(str_replace(['$', ',', '.'], '', $item['price'] ?? '0'));
                            $cantidad = intval($item['cantidad'] ?? 1);
                            $totalItem = $precio * $cantidad;
                            $subtotal += $totalItem;
                        ?>
                            <li>
                                <div>
                                    <strong><?= htmlspecialchars($item['name'] ?? 'Producto') ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        <?= htmlspecialchars($item['price'] ?? '$0') ?> x <?= $cantidad ?>
                                    </small>
                                </div>
                                <div>
                                    <strong>$<?= number_format($totalItem, 2, ',', '.') ?></strong>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <li class="total-row">
                            <span>Total:</span>
                            <span>$<?= number_format($orden['total_amount'] ?? $subtotal, 2, ',', '.') ?></span>
                        </li>
                    <?php else: ?>
                        <li>No hay items en esta orden</li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Información de envío -->
            <?php if ($orden['shipping_type'] || $orden['shipping_address']): ?>
            <div class="detail-section">
                <h3>Información de Envío</h3>
                <?php if ($orden['shipping_type']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Tipo de envío:</span>
                        <span class="detail-value"><?= htmlspecialchars($orden['shipping_type']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($orden['shipping_address']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Dirección:</span>
                        <span class="detail-value" style="text-align: left; white-space: pre-line;"><?= htmlspecialchars($orden['shipping_address']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Notas -->
            <?php if ($orden['notes']): ?>
            <div class="detail-section">
                <h3>Notas del Cliente</h3>
                <p style="white-space: pre-line;"><?= htmlspecialchars($orden['notes']) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Información del cliente y pago -->
        <div>
            <!-- Datos del cliente -->
            <div class="detail-section">
                <h3>Datos del Cliente</h3>
                <div class="detail-row">
                    <span class="detail-label">Nombre:</span>
                    <span class="detail-value"><?= htmlspecialchars($orden['payer_name'] ?? 'N/A') ?></span>
                </div>
                <?php if ($orden['payer_email']): ?>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?= htmlspecialchars($orden['payer_email']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($orden['payer_phone']): ?>
                <div class="detail-row">
                    <span class="detail-label">Teléfono:</span>
                    <span class="detail-value"><?= htmlspecialchars($orden['payer_phone']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($orden['payer_document']): ?>
                <div class="detail-row">
                    <span class="detail-label">Documento:</span>
                    <span class="detail-value"><?= htmlspecialchars($orden['payer_document']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Información de pago -->
            <div class="detail-section">
                <h3>Información de Pago</h3>
                <div class="detail-row">
                    <span class="detail-label">Estado:</span>
                    <span class="detail-value">
                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </span>
                </div>
                <?php if ($orden['status_detail']): ?>
                <div class="detail-row">
                    <span class="detail-label">Detalle:</span>
                    <span class="detail-value"><?= htmlspecialchars($orden['status_detail']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($orden['payment_method']): ?>
                <div class="detail-row">
                    <span class="detail-label">Método:</span>
                    <span class="detail-value"><?= htmlspecialchars($orden['payment_method']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($orden['payment_type']): ?>
                <div class="detail-row">
                    <span class="detail-label">Tipo:</span>
                    <span class="detail-value"><?= htmlspecialchars($orden['payment_type']) ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label">Total:</span>
                    <span class="detail-value"><strong>$<?= number_format($orden['total_amount'] ?? 0, 2, ',', '.') ?></strong></span>
                </div>
            </div>

            <!-- IDs técnicos -->
            <div class="detail-section">
                <h3>IDs Técnicos</h3>
                <?php if ($orden['mercadopago_id']): ?>
                <div class="detail-row">
                    <span class="detail-label">MercadoPago ID:</span>
                    <span class="detail-value" style="font-family: monospace; font-size: 0.875rem;"><?= htmlspecialchars($orden['mercadopago_id']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($orden['preference_id']): ?>
                <div class="detail-row">
                    <span class="detail-label">Preference ID:</span>
                    <span class="detail-value" style="font-family: monospace; font-size: 0.875rem;"><?= htmlspecialchars($orden['preference_id']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($orden['external_reference']): ?>
                <div class="detail-row">
                    <span class="detail-label">Referencia Externa:</span>
                    <span class="detail-value" style="font-family: monospace; font-size: 0.875rem;"><?= htmlspecialchars($orden['external_reference']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Fechas -->
            <div class="detail-section">
                <h3>Fechas</h3>
                <div class="detail-row">
                    <span class="detail-label">Creada:</span>
                    <span class="detail-value"><?= date('d/m/Y H:i:s', strtotime($orden['created_at'])) ?></span>
                </div>
                <?php if ($orden['updated_at']): ?>
                <div class="detail-row">
                    <span class="detail-label">Actualizada:</span>
                    <span class="detail-value"><?= date('d/m/Y H:i:s', strtotime($orden['updated_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../_inc/footer.php'; ?>

