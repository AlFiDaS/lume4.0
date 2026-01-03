<?php
/**
 * Eliminar review
 */
require_once '../../config.php';

if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}
require_once '../../helpers/auth.php';
require_once '../../helpers/db.php';
startSecureSession();
requireAuth();

$reviewId = (int)($_GET['id'] ?? 0);

if ($reviewId > 0) {
    $sql = "DELETE FROM reviews WHERE id = :id";
    if (executeQuery($sql, ['id' => $reviewId])) {
        $_SESSION['success_message'] = 'Review eliminada exitosamente';
    } else {
        $_SESSION['error_message'] = 'Error al eliminar la review';
    }
} else {
    $_SESSION['error_message'] = 'ID de review inválido';
}

header('Location: list.php');
exit;

