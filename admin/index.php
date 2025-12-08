<?php
/**
 * Dashboard del panel administrativo
 */
$pageTitle = 'Dashboard';
require_once '../config.php';
require_once '_inc/header.php';

// Obtener estadísticas
$totalProducts = fetchOne("SELECT COUNT(*) as count FROM products");
$visibleProducts = fetchOne("SELECT COUNT(*) as count FROM products WHERE visible = 1");
$destacadosProducts = fetchOne("SELECT COUNT(*) as count FROM products WHERE destacado = 1 AND visible = 1");
$outOfStock = fetchOne("SELECT COUNT(*) as count FROM products WHERE stock = 0");

$stats = [
    'total' => $totalProducts['count'] ?? 0,
    'visible' => $visibleProducts['count'] ?? 0,
    'destacados' => $destacadosProducts['count'] ?? 0,
    'sin_stock' => $outOfStock['count'] ?? 0
];

// Obtener productos recientes
$recentProducts = fetchAll(
    "SELECT id, name, categoria, visible, stock, created_at 
     FROM products 
     ORDER BY created_at DESC 
     LIMIT 5"
);
?>

<div class="admin-content">
    <h2>Dashboard</h2>
    <p style="color: #666; margin-bottom: 2rem;">Bienvenido al panel de administración de LUME</p>
    
    <!-- Estadísticas principales -->
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-number"><?= $stats['total'] ?></div>
            <div class="stat-label">Total</div>
        </div>
        
        <div class="stat-card stat-visible">
            <div class="stat-number"><?= $stats['visible'] ?></div>
            <div class="stat-label">Visibles</div>
        </div>
        
        <div class="stat-card stat-destacados">
            <div class="stat-number"><?= $stats['destacados'] ?></div>
            <div class="stat-label">Destacados</div>
        </div>
        
        <div class="stat-card stat-stock">
            <div class="stat-number"><?= $stats['sin_stock'] ?></div>
            <div class="stat-label">Sin Stock</div>
        </div>
    </div>
    
    <!-- Accesos rápidos -->
    <div class="quick-actions">
        <h3 class="section-title">Accesos Rápidos</h3>
        <div class="actions-grid">
            <a href="add.php" class="action-card">
                <div class="action-icon">➕</div>
                <div class="action-title">Agregar Producto</div>
                <div class="action-desc">Crear un nuevo producto</div>
            </a>
            
            <a href="list.php" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-title">Lista de Productos</div>
                <div class="action-desc">Ver y gestionar productos</div>
            </a>
            
            <a href="galeria/list.php" class="action-card">
                <div class="action-icon">🖼️</div>
                <div class="action-title">Galería de Ideas</div>
                <div class="action-desc">Gestionar imágenes</div>
            </a>
        </div>
    </div>
    
    <!-- Resumen por categoría -->
    <?php
    $categorias = fetchAll("SELECT categoria, COUNT(*) as count FROM products GROUP BY categoria");
    if (!empty($categorias)):
    ?>
    <div class="category-summary">
        <h3 class="section-title">Productos por Categoría</h3>
        <div class="category-grid">
            <?php foreach ($categorias as $cat): ?>
                <div class="category-item">
                    <div class="category-name"><?= ucfirst(htmlspecialchars($cat['categoria'])) ?></div>
                    <div class="category-count"><?= $cat['count'] ?> productos</div>
                    <a href="list.php?categoria=<?= htmlspecialchars($cat['categoria']) ?>" class="category-link">Ver →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin: 2rem 0;
    }
    
    .stat-card {
        border-radius: 8px;
        padding: 1.25rem;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    
    .stat-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .stat-total {
        background: linear-gradient(135deg, #ffeef8, #ffe0f0);
        border-left: 4px solid #e0a4ce;
    }
    
    .stat-visible {
        background: linear-gradient(135deg, #eef0ff, #e0e5ff);
        border-left: 4px solid #667eea;
    }
    
    .stat-destacados {
        background: linear-gradient(135deg, #fff0f8, #ffe0f0);
        border-left: 4px solid #f093fb;
    }
    
    .stat-stock {
        background: linear-gradient(135deg, #fff5e6, #ffe6d9);
        border-left: 4px solid #fa709a;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.85rem;
        color: #666;
        font-weight: 500;
    }
    
    .section-title {
        margin-top: 3rem;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
        color: #333;
    }
    
    .quick-actions {
        margin-top: 3rem;
    }
    
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .action-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .action-card:hover {
        border-color: #e0a4ce;
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .action-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .action-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }
    
    .action-desc {
        font-size: 0.9rem;
        color: #666;
    }
    
    .category-summary {
        margin-top: 3rem;
    }
    
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .category-item {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .category-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .category-count {
        font-size: 1.5rem;
        font-weight: bold;
        color: #e0a4ce;
        margin-bottom: 0.75rem;
    }
    
    .category-link {
        display: inline-block;
        color: #e0a4ce;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s;
    }
    
    .category-link:hover {
        color: #d89bc0;
        text-decoration: underline;
    }
    
    /* Responsive para mobile */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin: 1.5rem 0;
        }
        
        .stat-card {
            padding: 1rem 0.75rem;
        }
        
        .stat-number {
            font-size: 1.75rem;
        }
        
        .stat-label {
            font-size: 0.8rem;
        }
        
        .section-title {
            margin-top: 2rem;
            font-size: 1.25rem;
        }
        
        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .action-card {
            padding: 1.5rem 1rem;
        }
        
        .action-icon {
            font-size: 2.5rem;
        }
        
        .action-title {
            font-size: 1.1rem;
        }
        
        .category-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .stat-card {
            padding: 0.875rem 0.5rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
        
        .stat-label {
            font-size: 0.75rem;
        }
        
        .actions-grid {
            grid-template-columns: 1fr;
        }
        
        .category-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once '_inc/footer.php'; ?>

