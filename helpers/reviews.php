<?php
/**
 * ============================================
 * HELPER: Sistema de Reviews/Reseñas
 * ============================================
 * Funciones para manejar reseñas de productos
 * Compatible: PHP 7.4+
 * ============================================
 */

if (!defined('LUME_ADMIN')) {
    die('Acceso directo no permitido');
}

// Asegurar que db.php esté cargado
if (!function_exists('executeQuery')) {
    require_once __DIR__ . '/db.php';
}

/**
 * Obtener reviews de un producto
 * @param string $productId ID del producto
 * @param bool $approvedOnly Solo reviews aprobadas
 * @return array
 */
function getProductReviews($productId, $approvedOnly = true) {
    $sql = "SELECT * FROM reviews WHERE product_id = :product_id";
    $params = ['product_id' => $productId];
    
    if ($approvedOnly) {
        $sql .= " AND status = 'approved'";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    return fetchAll($sql, $params) ?: [];
}

/**
 * Obtener estadísticas de reviews de un producto
 * @param string $productId ID del producto
 * @return array
 */
function getProductReviewStats($productId) {
    $sql = "SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM reviews 
            WHERE product_id = :product_id AND status = 'approved'";
    
    $stats = fetchOne($sql, ['product_id' => $productId]);
    
    return [
        'total_reviews' => (int)($stats['total_reviews'] ?? 0),
        'avg_rating' => round((float)($stats['avg_rating'] ?? 0), 1),
        'five_star' => (int)($stats['five_star'] ?? 0),
        'four_star' => (int)($stats['four_star'] ?? 0),
        'three_star' => (int)($stats['three_star'] ?? 0),
        'two_star' => (int)($stats['two_star'] ?? 0),
        'one_star' => (int)($stats['one_star'] ?? 0)
    ];
}

/**
 * Crear una nueva review
 * @param array $reviewData Datos de la review
 * @return int|false ID de la review creada o false en error
 */
function createReview($reviewData) {
    $sql = "INSERT INTO reviews (
        product_id, customer_name, customer_email, rating, comment,
        verified_purchase, order_id, status
    ) VALUES (
        :product_id, :customer_name, :customer_email, :rating, :comment,
        :verified_purchase, :order_id, :status
    )";
    
    $params = [
        'product_id' => $reviewData['product_id'] ?? '',
        'customer_name' => $reviewData['customer_name'] ?? '',
        'customer_email' => $reviewData['customer_email'] ?? null,
        'rating' => (int)($reviewData['rating'] ?? 5),
        'comment' => $reviewData['comment'] ?? null,
        'verified_purchase' => isset($reviewData['verified_purchase']) ? (int)$reviewData['verified_purchase'] : 0,
        'order_id' => $reviewData['order_id'] ?? null,
        'status' => $reviewData['status'] ?? 'pending'
    ];
    
    // Validar rating
    if ($params['rating'] < 1 || $params['rating'] > 5) {
        return false;
    }
    
    if (executeQuery($sql, $params)) {
        return lastInsertId();
    }
    
    return false;
}

/**
 * Actualizar estado de una review
 * @param int $reviewId ID de la review
 * @param string $status Nuevo estado: 'pending', 'approved', 'rejected'
 * @return bool
 */
function updateReviewStatus($reviewId, $status) {
    if (!in_array($status, ['pending', 'approved', 'rejected'])) {
        return false;
    }
    
    $sql = "UPDATE reviews SET status = :status WHERE id = :id";
    return executeQuery($sql, ['status' => $status, 'id' => $reviewId]);
}

/**
 * Obtener todas las reviews (para admin)
 * @param string $status Filtrar por estado (opcional)
 * @return array
 */
function getAllReviews($status = null) {
    $sql = "SELECT r.*, p.name as product_name, p.slug as product_slug 
            FROM reviews r
            INNER JOIN products p ON r.product_id = p.id";
    
    $params = [];
    if ($status) {
        $sql .= " WHERE r.status = :status";
        $params['status'] = $status;
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    return fetchAll($sql, $params) ?: [];
}

/**
 * Verificar si un cliente puede dejar review (basado en orden)
 * @param string $customerEmail Email del cliente
 * @param string $productId ID del producto
 * @return bool
 */
function canCustomerReview($customerEmail, $productId) {
    if (empty($customerEmail) || empty($productId)) {
        return false;
    }
    
    // Buscar si el cliente tiene una orden aprobada con este producto
    $sql = "SELECT COUNT(*) as count 
            FROM orders o
            WHERE o.payer_email = :email 
            AND o.status IN ('approved', 'a_confirmar')
            AND JSON_CONTAINS(o.items, JSON_OBJECT('id', :product_id))";
    
    $result = fetchOne($sql, ['email' => $customerEmail, 'product_id' => $productId]);
    
    return (int)($result['count'] ?? 0) > 0;
}

