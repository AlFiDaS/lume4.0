<?php
/**
 * Página para reordenar productos con drag and drop
 */
$pageTitle = 'Ordenar Productos';
require_once '../config.php';
require_once '_inc/header.php';

// Obtener categoría del filtro
$categoriaFiltro = $_GET['categoria'] ?? 'productos';

// Validar categoría
if (!in_array($categoriaFiltro, ['productos', 'souvenirs', 'navidad'])) {
    $categoriaFiltro = 'productos';
}

// Verificar si la columna orden existe
$hasOrdenColumn = false;
try {
    $checkOrden = fetchOne("SHOW COLUMNS FROM products LIKE 'orden'");
    $hasOrdenColumn = !empty($checkOrden);
} catch (Exception $e) {
    // Si hay error, asumir que no existe
    $hasOrdenColumn = false;
}

// Obtener productos visibles de la categoría seleccionada, ordenados igual que en la página pública
$sql = "SELECT id, name, image, slug, categoria";
if ($hasOrdenColumn) {
    $sql .= ", orden";
}
$sql .= " FROM products 
        WHERE visible = 1 AND categoria = :categoria";

if ($hasOrdenColumn) {
    $sql .= " ORDER BY 
            CASE WHEN orden IS NULL THEN 1 ELSE 0 END,
            orden ASC,
            destacado DESC,
            name ASC";
} else {
    $sql .= " ORDER BY destacado DESC, name ASC";
}

$products = fetchAll($sql, ['categoria' => $categoriaFiltro]);

// Asegurar que siempre sea un array
if (!is_array($products)) {
    $products = [];
}

// Debug: mostrar cantidad de productos encontrados
$debugCount = count($products);

// Asignar orden si no existe
foreach ($products as $index => $product) {
    if (!isset($product['orden']) || $product['orden'] === null || $product['orden'] === '') {
        $products[$index]['orden'] = $index + 1;
    }
}
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h2>Ordenar Productos</h2>
        <div>
            <button type="button" id="btn-guardar-orden" class="btn btn-primary" style="display: none;">
                💾 Guardar Orden
            </button>
            <a href="list.php" class="btn btn-secondary">← Volver a Lista</a>
        </div>
    </div>
    
    <!-- Filtros por categoría -->
    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <span style="font-weight: 600; color: #333;">Categoría:</span>
            <a href="?categoria=productos" 
               class="categoria-filter <?= $categoriaFiltro === 'productos' ? 'active' : '' ?>"
               style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.3s;">
                PRODUCTOS
            </a>
            <span style="color: #ccc;">|</span>
            <a href="?categoria=souvenirs" 
               class="categoria-filter <?= $categoriaFiltro === 'souvenirs' ? 'active' : '' ?>"
               style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.3s;">
                SOUVENIRS
            </a>
            <span style="color: #ccc;">|</span>
            <a href="?categoria=navidad" 
               class="categoria-filter <?= $categoriaFiltro === 'navidad' ? 'active' : '' ?>"
               style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.3s;">
                NAVIDAD
            </a>
        </div>
        <p style="margin: 0.75rem 0 0 0; color: #666; font-size: 0.9rem;">
            <strong>Instrucciones:</strong> Arrastra y suelta las imágenes de los productos para cambiar su orden. 
            Los productos se mostrarán en filas de 4 columnas. Haz clic en "Guardar Orden" cuando termines.
        </p>
    </div>
    
    <?php if (empty($products)): ?>
        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
            <p style="margin: 0; color: #856404;">
                <strong>⚠️ No se encontraron productos</strong><br>
                <small>Categoría: <?= strtoupper($categoriaFiltro) ?> | Visible: 1</small>
            </p>
            <p style="margin: 0.5rem 0 0 0; color: #856404; font-size: 0.9rem;">
                Verifica que haya productos de esta categoría marcados como visibles en la base de datos.
            </p>
            <?php 
            // Debug: contar productos totales de esta categoría
            $totalCategoria = fetchOne("SELECT COUNT(*) as count FROM products WHERE categoria = :categoria", ['categoria' => $categoriaFiltro]);
            $totalVisible = fetchOne("SELECT COUNT(*) as count FROM products WHERE categoria = :categoria AND visible = 1", ['categoria' => $categoriaFiltro]);
            ?>
            <p style="margin: 0.5rem 0 0 0; color: #856404; font-size: 0.85rem;">
                <small>
                    Total en categoría: <?= $totalCategoria['count'] ?? 0 ?> | 
                    Visibles: <?= $totalVisible['count'] ?? 0 ?>
                </small>
            </p>
        </div>
    <?php else: ?>
        <div style="background: #d4edda; border: 1px solid #28a745; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
            <small style="color: #155724;">
                Mostrando <?= count($products) ?> producto(s) de <?= strtoupper($categoriaFiltro) ?>
            </small>
        </div>
    <?php endif; ?>
    
    <div id="products-grid" class="products-sortable-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-sortable-card" 
                 draggable="true" 
                 data-id="<?= htmlspecialchars($product['id']) ?>"
                 data-orden="<?= htmlspecialchars($product['orden'] ?? '') ?>">
                <?php if (!empty($product['image'])): ?>
                    <?php 
                    $imageUrl = preg_replace('/\?.*$/', '', $product['image']);
                    $fullImageUrl = BASE_URL . $imageUrl;
                    ?>
                    <img src="<?= $fullImageUrl ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23f0f0f0\' width=\'200\' height=\'200\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-size=\'14\'%3ESin img%3C/text%3E%3C/svg%3E';">
                <?php else: ?>
                    <div class="product-placeholder">
                        Sin imagen
                    </div>
                <?php endif; ?>
                <div class="product-sortable-name">
                    <?= htmlspecialchars($product['name']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div id="save-message" style="display: none; margin-top: 1rem; padding: 1rem; border-radius: 8px; text-align: center;"></div>
</div>

<style>
.products-sortable-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-top: 1rem;
}

.product-sortable-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    padding: 1rem;
    cursor: move;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.product-sortable-card:hover {
    border-color: #e0a4ce;
    box-shadow: 0 4px 12px rgba(224, 164, 206, 0.2);
    transform: translateY(-2px);
}

.product-sortable-card.dragging {
    opacity: 0.5;
    border-color: #e0a4ce;
    box-shadow: 0 8px 16px rgba(224, 164, 206, 0.4);
}

.product-sortable-card.drag-over {
    border-color: #e0a4ce;
    background: #f8f0f5;
}

.product-sortable-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    display: block;
    margin-bottom: 0.75rem;
}

.product-placeholder {
    width: 100%;
    height: 200px;
    background: #f0f0f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    margin-bottom: 0.75rem;
}

.product-sortable-name {
    font-weight: 600;
    color: #333;
    text-align: center;
    font-size: 0.9rem;
    line-height: 1.3;
    min-height: 2.6em;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Responsive */
@media (max-width: 1200px) {
    .products-sortable-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .products-sortable-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .product-sortable-card {
        padding: 0.75rem;
    }
    
    .product-sortable-card img,
    .product-placeholder {
        height: 150px;
    }
}

@media (max-width: 480px) {
    .products-sortable-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function() {
    const grid = document.getElementById('products-grid');
    const saveBtn = document.getElementById('btn-guardar-orden');
    const saveMessage = document.getElementById('save-message');
    let draggedElement = null;
    let hasChanges = false;
    
    // Hacer todas las cards arrastrables
    const cards = grid.querySelectorAll('.product-sortable-card');
    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
        card.addEventListener('dragover', handleDragOver);
        card.addEventListener('drop', handleDrop);
        card.addEventListener('dragenter', handleDragEnter);
        card.addEventListener('dragleave', handleDragLeave);
    });
    
    function handleDragStart(e) {
        draggedElement = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }
    
    function handleDragEnd(e) {
        this.classList.remove('dragging');
        // Limpiar todas las clases drag-over
        cards.forEach(card => card.classList.remove('drag-over'));
    }
    
    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }
    
    function handleDragEnter(e) {
        if (this !== draggedElement) {
            this.classList.add('drag-over');
        }
    }
    
    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }
    
    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        if (draggedElement !== this) {
            // Intercambiar posiciones
            const allCards = Array.from(grid.querySelectorAll('.product-sortable-card'));
            const draggedIndex = allCards.indexOf(draggedElement);
            const targetIndex = allCards.indexOf(this);
            
            if (draggedIndex < targetIndex) {
                grid.insertBefore(draggedElement, this.nextSibling);
            } else {
                grid.insertBefore(draggedElement, this);
            }
            
            hasChanges = true;
            saveBtn.style.display = 'inline-block';
        }
        
        this.classList.remove('drag-over');
        return false;
    }
    
    // Guardar orden
    saveBtn.addEventListener('click', function() {
        const cards = grid.querySelectorAll('.product-sortable-card');
        const order = [];
        const categoria = '<?= $categoriaFiltro ?>';
        
        cards.forEach((card, index) => {
            order.push({
                id: card.getAttribute('data-id'),
                orden: index + 1
            });
        });
        
        // Deshabilitar botón mientras se guarda
        saveBtn.disabled = true;
        saveBtn.textContent = '💾 Guardando...';
        
        fetch('api/save-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order: order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hasChanges = false;
                saveBtn.style.display = 'none';
                saveMessage.style.display = 'block';
                saveMessage.className = 'alert-success';
                saveMessage.textContent = '✅ Orden guardado correctamente';
                
                // Actualizar data-orden en las cards
                cards.forEach((card, index) => {
                    card.setAttribute('data-orden', index + 1);
                });
                
                setTimeout(() => {
                    saveMessage.style.display = 'none';
                }, 3000);
            } else {
                saveMessage.style.display = 'block';
                saveMessage.className = 'alert-error';
                saveMessage.textContent = '❌ Error al guardar: ' + (data.error || 'Error desconocido');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            saveMessage.style.display = 'block';
            saveMessage.className = 'alert-error';
            saveMessage.textContent = '❌ Error al guardar el orden';
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = '💾 Guardar Orden';
        });
    });
})();
</script>

<?php require_once '_inc/footer.php'; ?>

