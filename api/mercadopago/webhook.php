<?php
/**
 * ============================================
 * API: Webhook de MercadoPago
 * ============================================
 * Recibe notificaciones de MercadoPago sobre el estado de los pagos
 * ============================================
 */

// Asegurar que LUME_ADMIN está definido
if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}

// Cargar configuración
require_once '../../config.php';

// Cargar helpers
require_once '../../helpers/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/orders.php';

// Log para debugging (opcional)
function logWebhook($message) {
    $logFile = BASE_PATH . '/logs/mercadopago-webhook.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // MercadoPago envía datos via POST con parámetro 'data'
    $type = $_GET['type'] ?? '';
    $dataId = $_GET['data.id'] ?? '';

    logWebhook("Webhook recibido - Type: $type, Data ID: $dataId");

    if (empty($type) || empty($dataId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetros inválidos']);
        exit;
    }

    // Obtener credenciales
    $accessToken = defined('MERCADOPAGO_ACCESS_TOKEN') ? MERCADOPAGO_ACCESS_TOKEN : '';
    
    if (empty($accessToken)) {
        throw new Exception('MercadoPago no está configurado');
    }

    // Si es un pago, obtener información del pago
    if ($type === 'payment') {
        $ch = curl_init("https://api.mercadopago.com/v1/payments/$dataId");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $payment = json_decode($response, true);
            
            logWebhook("Pago procesado - ID: " . $payment['id'] . ", Status: " . $payment['status']);
            
            // Buscar orden por external_reference o preference_id
            $externalRef = $payment['external_reference'] ?? null;
            $preferenceId = $payment['payment_method_id'] ?? null; // Esto puede variar según la respuesta
            
            // Si tenemos external_reference, buscar la orden
            if ($externalRef) {
                $order = fetchOne("SELECT * FROM orders WHERE external_reference = :ref", ['ref' => $externalRef]);
                
                if ($order) {
                    // Actualizar orden con información del pago
                    $updateData = [
                        'mercadopago_id' => $payment['id'] ?? null,
                        'status' => $payment['status'] ?? 'pending',
                        'status_detail' => $payment['status_detail'] ?? null,
                        'payment_method' => $payment['payment_method_id'] ?? null,
                        'payment_type' => $payment['payment_type_id'] ?? null,
                        'total_amount' => $payment['transaction_amount'] ?? $order['total_amount']
                    ];
                    
                    // Datos del pagador si están disponibles
                    if (isset($payment['payer'])) {
                        $updateData['payer_email'] = $payment['payer']['email'] ?? $order['payer_email'];
                        $updateData['payer_phone'] = $payment['payer']['phone']['number'] ?? $order['payer_phone'];
                        if (isset($payment['payer']['identification'])) {
                            $updateData['payer_document'] = $payment['payer']['identification']['number'] ?? $order['payer_document'];
                        }
                    }
                    
                    updateOrder($order['id'], $updateData);
                    logWebhook("Orden #" . $order['id'] . " actualizada con status: " . $updateData['status']);
                } else {
                    logWebhook("Orden no encontrada para external_reference: " . $externalRef);
                }
            }
        }
    }

    // Responder a MercadoPago
    http_response_code(200);
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    logWebhook("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

