<?php
/**
 * Aprobar review
 */
require_once '../../config.php';

if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}
require_once '../../helpers/auth.php';
require_once '../../helpers/reviews.php';
startSecureSession();
requireAuth();

$reviewId = (int)($_GET['id'] ?? 0);

if ($reviewId > 0) {
    if (updateReviewStatus($reviewId, 'approved')) {
        $_SESSION['success_message'] = 'Review aprobada exitosamente';
    } else {
        $_SESSION['error_message'] = 'Error al aprobar la review';
    }
} else {
    $_SESSION['error_message'] = 'ID de review inválido';
}

header('Location: list.php');
exit;

