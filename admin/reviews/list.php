<?php
/**
 * Lista de reviews/reseñas
 */
$pageTitle = 'Reviews y Reseñas';
require_once '../../config.php';

if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}
require_once '../../helpers/auth.php';
require_once '../../helpers/reviews.php';
startSecureSession();
requireAuth();

// Filtros
$status = $_GET['status'] ?? '';
$productId = $_GET['product_id'] ?? '';

// Obtener reviews
$reviews = getAllReviews($status ?: null);

// Si hay filtro por producto, filtrar
if (!empty($productId)) {
    $reviews = array_filter($reviews, function($review) use ($productId) {
        return $review['product_id'] === $productId;
    });
}

require_once '../_inc/header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h2>⭐ Reviews y Reseñas</h2>
        <div class="header-actions">
            <a href="?status=pending" class="btn btn-warning <?= $status === 'pending' ? 'active' : '' ?>">
                ⏳ Pendientes (<?= count(array_filter($reviews, fn($r) => $r['status'] === 'pending')) ?>)
            </a>
            <a href="?status=approved" class="btn btn-success <?= $status === 'approved' ? 'active' : '' ?>">
                ✅ Aprobadas (<?= count(array_filter($reviews, fn($r) => $r['status'] === 'approved')) ?>)
            </a>
            <a href="list.php" class="btn btn-secondary">Ver Todas</a>
        </div>
    </div>
    
    <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <p>No hay reviews aún.</p>
        </div>
    <?php else: ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card status-<?= $review['status'] ?>">
                    <div class="review-header">
                        <div class="review-info">
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">⭐</span>
                                <?php endfor; ?>
                                <span class="rating-number"><?= $review['rating'] ?>/5</span>
                            </div>
                            <div class="review-meta">
                                <strong><?= htmlspecialchars($review['customer_name']) ?></strong>
                                <?php if ($review['verified_purchase']): ?>
                                    <span class="badge badge-verified">✓ Compra Verificada</span>
                                <?php endif; ?>
                                <span class="review-date"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="review-status-badge">
                            <?php if ($review['status'] === 'pending'): ?>
                                <span class="badge badge-warning">⏳ Pendiente</span>
                            <?php elseif ($review['status'] === 'approved'): ?>
                                <span class="badge badge-success">✅ Aprobada</span>
                            <?php else: ?>
                                <span class="badge badge-danger">❌ Rechazada</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="review-product">
                        <strong>Producto:</strong> 
                        <a href="../edit.php?id=<?= htmlspecialchars($review['product_id']) ?>">
                            <?= htmlspecialchars($review['product_name']) ?>
                        </a>
                    </div>
                    
                    <?php if (!empty($review['comment'])): ?>
                        <div class="review-comment">
                            <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="review-actions">
                        <?php if ($review['status'] === 'pending'): ?>
                            <a href="approve.php?id=<?= $review['id'] ?>" class="btn btn-sm btn-success">
                                ✅ Aprobar
                            </a>
                            <a href="reject.php?id=<?= $review['id'] ?>" class="btn btn-sm btn-danger">
                                ❌ Rechazar
                            </a>
                        <?php elseif ($review['status'] === 'approved'): ?>
                            <a href="reject.php?id=<?= $review['id'] ?>" class="btn btn-sm btn-danger">
                                ❌ Rechazar
                            </a>
                        <?php else: ?>
                            <a href="approve.php?id=<?= $review['id'] ?>" class="btn btn-sm btn-success">
                                ✅ Aprobar
                            </a>
                        <?php endif; ?>
                        <a href="delete.php?id=<?= $review['id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Estás seguro de eliminar esta review?')">
                            🗑️ Eliminar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #e0e0e0;
    transition: all 0.3s;
}

.review-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.review-card.status-pending {
    border-left-color: #ffc107;
    background: #fffbf0;
}

.review-card.status-approved {
    border-left-color: #28a745;
    background: #f0fff4;
}

.review-card.status-rejected {
    border-left-color: #dc3545;
    background: #fff5f5;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
}

.star {
    font-size: 1.2rem;
    opacity: 0.3;
}

.star.filled {
    opacity: 1;
}

.rating-number {
    margin-left: 0.5rem;
    font-weight: 600;
    color: #333;
}

.review-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.review-date {
    color: #666;
    font-size: 0.9rem;
}

.badge-verified {
    background: #28a745;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}

.review-product {
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.review-product a {
    color: #e0a4ce;
    text-decoration: none;
    font-weight: 600;
}

.review-product a:hover {
    text-decoration: underline;
}

.review-comment {
    margin-bottom: 1rem;
    padding: 1rem;
    background: #fafafa;
    border-radius: 6px;
    line-height: 1.6;
}

.review-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding-top: 1rem;
    border-top: 1px solid #e0e0e0;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
    }
    
    .header-actions .btn {
        flex: 1;
        text-align: center;
    }
    
    .review-header {
        flex-direction: column;
    }
    
    .review-actions {
        flex-direction: column;
    }
    
    .review-actions .btn {
        width: 100%;
    }
}
</style>

<?php require_once '../_inc/footer.php'; ?>

