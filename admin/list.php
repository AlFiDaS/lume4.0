<?php
/**
 * Lista de productos con filtros
 */
$pageTitle = 'Lista de Productos';
require_once '../config.php';
require_once '_inc/header.php';

// Filtros
$categoria = $_GET['categoria'] ?? '';
$visible = $_GET['visible'] ?? '';
$stock = $_GET['stock'] ?? '';

// Construir consulta
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($categoria)) {
    $sql .= " AND categoria = :categoria";
    $params['categoria'] = $categoria;
}

if ($visible !== '') {
    $sql .= " AND visible = :visible";
    $params['visible'] = (int)$visible;
}

if ($stock !== '') {
    $sql .= " AND stock = :stock";
    $params['stock'] = (int)$stock;
}

$sql .= " ORDER BY created_at DESC";

$products = fetchAll($sql, $params);
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h2>Lista de Productos</h2>
        <a href="add.php" class="btn btn-primary">➕ Agregar Producto</a>
    </div>
    
    <!-- Filtros -->
    <div class="filters-container">
        <form method="GET" class="filters-form">
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria">
                    <option value="">Todas</option>
                    <option value="productos" <?= $categoria === 'productos' ? 'selected' : '' ?>>Productos</option>
                    <option value="souvenirs" <?= $categoria === 'souvenirs' ? 'selected' : '' ?>>Souvenirs</option>
                    <option value="navidad" <?= $categoria === 'navidad' ? 'selected' : '' ?>>Navidad</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Visible</label>
                <select name="visible">
                    <option value="">Todas</option>
                    <option value="1" <?= $visible === '1' ? 'selected' : '' ?>>Visible</option>
                    <option value="0" <?= $visible === '0' ? 'selected' : '' ?>>Oculta</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Stock</label>
                <select name="stock">
                    <option value="">Todos</option>
                    <option value="1" <?= $stock === '1' ? 'selected' : '' ?>>En Stock</option>
                    <option value="0" <?= $stock === '0' ? 'selected' : '' ?>>Sin Stock</option>
                </select>
            </div>
            
            <div class="filters-actions">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="list.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>
    
    <!-- Tabla de productos -->
    <table>
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Stock</th>
                <th>Destacado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">
                        No se encontraron productos. <a href="add.php">Agregar producto</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td data-label="Imagen">
                            <?php if (!empty($product['image'])): ?>
                                <?php 
                                // Remover parámetros de cache busting para el admin
                                $imageUrl = preg_replace('/\?.*$/', '', $product['image']);
                                // Construir URL completa
                                $fullImageUrl = BASE_URL . $imageUrl;
                                ?>
                                <img src="<?= $fullImageUrl ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\'%3E%3Crect fill=\'%23f0f0f0\' width=\'60\' height=\'60\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-size=\'10\'%3ESin img%3C/text%3E%3C/svg%3E';">
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999;">
                                    Sin img
                                </div>
                            <?php endif; ?>
                        </td>
                        <td data-label="Nombre">
                            <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                            <small style="color: #666;"><?= htmlspecialchars($product['slug']) ?></small>
                        </td>
                        <td data-label="Categoría">
                            <span class="badge badge-info"><?= htmlspecialchars($product['categoria']) ?></span>
                        </td>
                        <td data-label="Precio"><?= htmlspecialchars($product['price'] ?? 'N/A') ?></td>
                        <td data-label="Estado-Stock-Destacado" class="mobile-info">
                            <?php if ($product['visible']): ?>
                                <span class="badge badge-success">✓ Visible</span>
                            <?php else: ?>
                                <span class="badge badge-warning">○ Oculta</span>
                            <?php endif; ?>
                            <?php if ($product['stock']): ?>
                                <span class="badge badge-success">En Stock</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Sin Stock</span>
                            <?php endif; ?>
                            <?php if ($product['destacado']): ?>
                                <span class="badge badge-warning">⭐ Destacado</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Estado" class="desktop-only">
                            <?php if ($product['visible']): ?>
                                <span class="badge badge-success">✓ Visible</span>
                            <?php else: ?>
                                <span class="badge badge-warning">○ Oculta</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Stock" class="desktop-only">
                            <?php if ($product['stock']): ?>
                                <span class="badge badge-success">En Stock</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Sin Stock</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Destacado" class="desktop-only">
                            <?php if ($product['destacado']): ?>
                                <span class="badge badge-warning">⭐ Destacado</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Acciones">
                            <div class="actions">
                                <a href="edit.php?id=<?= $product['id'] ?>" class="btn-edit">Editar</a>
                                <a href="delete.php?id=<?= $product['id'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 2rem; color: #666;">
        Total: <?= count($products) ?> producto(s)
    </div>
</div>

<?php require_once '_inc/footer.php'; ?>

