<?php
/**
 * ============================================
 * HELPER: Funciones de Órdenes
 * ============================================
 * Funciones auxiliares para trabajar con órdenes
 * ============================================
 */

if (!defined('LUME_ADMIN')) {
    die('Acceso directo no permitido');
}

// Asegurar que db.php esté cargado (necesario para executeQuery, lastInsertId, etc.)
if (!function_exists('executeQuery')) {
    require_once __DIR__ . '/db.php';
}

/**
 * Guardar orden en la base de datos
 * @param array $orderData Datos de la orden
 * @return int|false ID de la orden insertada o false en caso de error
 */
function saveOrder($orderData) {
    $sql = "INSERT INTO orders (
        mercadopago_id,
        preference_id,
        external_reference,
        status,
        status_detail,
        payer_name,
        payer_email,
        payer_phone,
        payer_document,
        proof_image,
        items,
        total_amount,
        payment_method,
        payment_type,
        shipping_type,
        shipping_address,
        notes,
        metadata
    ) VALUES (
        :mercadopago_id,
        :preference_id,
        :external_reference,
        :status,
        :status_detail,
        :payer_name,
        :payer_email,
        :payer_phone,
        :payer_document,
        :proof_image,
        :items,
        :total_amount,
        :payment_method,
        :payment_type,
        :shipping_type,
        :shipping_address,
        :notes,
        :metadata
    )";
    
    $params = [
        'mercadopago_id' => $orderData['mercadopago_id'] ?? null,
        'preference_id' => $orderData['preference_id'] ?? null,
        'external_reference' => $orderData['external_reference'] ?? null,
        'status' => $orderData['status'] ?? 'pending',
        'status_detail' => $orderData['status_detail'] ?? null,
        'payer_name' => $orderData['payer_name'] ?? null,
        'payer_email' => $orderData['payer_email'] ?? null,
        'payer_phone' => $orderData['payer_phone'] ?? null,
        'payer_document' => $orderData['payer_document'] ?? null,
        'proof_image' => $orderData['proof_image'] ?? null,
        'items' => is_array($orderData['items']) ? json_encode($orderData['items']) : $orderData['items'],
        'total_amount' => $orderData['total_amount'] ?? null,
        'payment_method' => $orderData['payment_method'] ?? null,
        'payment_type' => $orderData['payment_type'] ?? null,
        'shipping_type' => $orderData['shipping_type'] ?? null,
        'shipping_address' => $orderData['shipping_address'] ?? null,
        'notes' => $orderData['notes'] ?? null,
        'metadata' => is_array($orderData['metadata']) ? json_encode($orderData['metadata']) : ($orderData['metadata'] ?? null)
    ];
    
    if (executeQuery($sql, $params)) {
        $orderId = lastInsertId();
        
        // Enviar notificación a Telegram
        if ($orderId) {
            try {
                require_once __DIR__ . '/telegram.php';
                if (function_exists('sendTelegramNotification') && function_exists('formatOrderNotification')) {
                    $orderDataWithId = array_merge($orderData, ['id' => $orderId]);
                    $message = formatOrderNotification($orderDataWithId, $orderId);
                    sendTelegramNotification($message);
                }
            } catch (Exception $e) {
                // No fallar si Telegram no funciona, solo loguear el error
                error_log('Error al enviar notificación de Telegram: ' . $e->getMessage());
            }
        }
        
        return $orderId;
    }
    
    return false;
}

/**
 * Actualizar orden existente
 * @param int $orderId ID de la orden
 * @param array $orderData Datos a actualizar
 * @return bool
 */
function updateOrder($orderId, $orderData) {
    $fields = [];
    $params = ['id' => $orderId];
    
    $allowedFields = [
        'mercadopago_id', 'preference_id', 'external_reference', 'status', 'status_detail',
        'payer_name', 'payer_email', 'payer_phone', 'payer_document', 'proof_image',
        'items', 'total_amount', 'payment_method', 'payment_type',
        'shipping_type', 'shipping_address', 'notes', 'metadata'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($orderData[$field])) {
            $fields[] = "$field = :$field";
            if ($field === 'items' || $field === 'metadata') {
                $params[$field] = is_array($orderData[$field]) ? json_encode($orderData[$field]) : $orderData[$field];
            } else {
                $params[$field] = $orderData[$field];
            }
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = :id";
    
    return executeQuery($sql, $params);
}

