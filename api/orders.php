<?php
/**
 * ============================================
 * API REST: Búsqueda de Pedidos
 * ============================================
 * Endpoint para buscar pedidos por número o email
 * Compatible: PHP 7.4+
 * ============================================
 */

// Desactivar display de errores ANTES de cargar config
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Headers CORS y JSON
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Asegurar que LUME_ADMIN está definido
if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}

// Cargar configuración
require_once '../config.php';

// Temporalmente desactivar display_errors
ini_set('display_errors', 0);

// Cargar helpers
require_once '../helpers/db.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $search = trim($_GET['search'] ?? '');
    
    if (empty($search)) {
        echo json_encode([
            'success' => true,
            'orders' => [],
            'message' => 'Ingresa un número de pedido o email para buscar'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Buscar por número de pedido o email
    $sql = "SELECT id, email, phone, items, total, status, created_at 
            FROM orders 
            WHERE id LIKE :search 
               OR email LIKE :search 
            ORDER BY created_at DESC 
            LIMIT 20";
    
    $params = ['search' => '%' . $search . '%'];
    
    $orders = fetchAll($sql, $params);
    
    if ($orders) {
        echo json_encode([
            'success' => true,
            'orders' => $orders,
            'count' => count($orders)
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'orders' => [],
            'message' => 'No se encontraron pedidos'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

