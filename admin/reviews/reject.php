<?php
/**
 * Rechazar review
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
    if (updateReviewStatus($reviewId, 'rejected')) {
        $_SESSION['success_message'] = 'Review rechazada';
    } else {
        $_SESSION['error_message'] = 'Error al rechazar la review';
    }
} else {
    $_SESSION['error_message'] = 'ID de review inválido';
}

header('Location: list.php');
exit;

