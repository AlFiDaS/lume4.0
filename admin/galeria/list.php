<?php
/**
 * Lista de imágenes de la galería
 */
$pageTitle = 'Galería de Ideas';
require_once '../../config.php';
require_once '../_inc/header.php';

// Obtener todas las imágenes
$sql = "SELECT * FROM galeria ORDER BY orden ASC, id ASC";
$items = fetchAll($sql, []);
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h2>Galería de Ideas</h2>
        <a href="add.php" class="btn btn-primary">➕ Agregar Imagen</a>
    </div>
    
    <!-- Grid de imágenes -->
    <?php if (empty($items)): ?>
        <div style="text-align: center; padding: 3rem; color: #666;">
            <p>No hay imágenes en la galería.</p>
            <a href="add.php" class="btn btn-primary">Agregar primera imagen</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;" class="galeria-grid">
            <?php foreach ($items as $item): ?>
                <div style="background: #f8f9fa; border-radius: 8px; padding: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="position: relative; margin-bottom: 1rem;">
                        <?php 
                        // Remover parámetros de cache busting para el admin
                        $imageUrl = preg_replace('/\?.*$/', '', $item['imagen']);
                        $fullImageUrl = BASE_URL . $imageUrl;
                        ?>
                        <img src="<?= $fullImageUrl ?>" 
                             alt="<?= htmlspecialchars($item['alt'] ?? $item['nombre']) ?>" 
                             style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px;"
                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23f0f0f0\' width=\'200\' height=\'200\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3ESin img%3C/text%3E%3C/svg%3E';">
                        <?php if (!$item['visible']): ?>
                            <div style="position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                                Oculta
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="margin-bottom: 0.5rem;">
                        <strong><?= htmlspecialchars($item['nombre']) ?></strong>
                    </div>
                    <?php if (!empty($item['alt'])): ?>
                        <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem;">
                            <?= htmlspecialchars($item['alt']) ?>
                        </div>
                    <?php endif; ?>
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <a href="edit.php?id=<?= $item['id'] ?>" class="btn-edit" style="flex: 1; text-align: center;">Editar</a>
                        <a href="delete.php?id=<?= $item['id'] ?>" 
                           class="btn-delete" 
                           onclick="return confirm('¿Estás seguro de eliminar esta imagen?')"
                           style="flex: 1; text-align: center;">Eliminar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 2rem; color: #666;">
            Total: <?= count($items) ?> imagen(es)
        </div>
    <?php endif; ?>
</div>

<?php require_once '../_inc/footer.php'; ?>

