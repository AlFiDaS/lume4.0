<?php
/**
 * Editar producto existente
 */
$pageTitle = 'Editar Producto';
require_once '../config.php';
require_once '../helpers/upload.php';
require_once '../helpers/slugify.php';

// Necesitamos autenticación pero sin incluir el header todavía
if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}
require_once '../helpers/auth.php';
startSecureSession();
requireAuth();

$error = '';
$productId = sanitize($_GET['id'] ?? '');

// Validar ID antes de procesar POST
if (empty($productId)) {
    $_SESSION['error_message'] = 'ID de producto no válido';
    header('Location: list.php');
    exit;
}

// Obtener producto
$product = fetchOne("SELECT * FROM products WHERE id = :id", ['id' => $productId]);

if (!$product) {
    $_SESSION['error_message'] = 'Producto no encontrado';
    header('Location: list.php');
    exit;
}

$formData = $product;

// Procesar formulario ANTES de incluir el header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido. Por favor, recarga la página.';
    } else {
        $formData = [
            'name' => sanitize($_POST['name'] ?? ''),
            'slug' => sanitize($_POST['slug'] ?? ''),
            'descripcion' => sanitize($_POST['descripcion'] ?? ''),
            'price' => sanitize($_POST['price'] ?? ''),
            'categoria' => sanitize($_POST['categoria'] ?? 'productos'),
            'stock' => isset($_POST['stock']) ? 1 : 0,
            'destacado' => isset($_POST['destacado']) ? 1 : 0,
            'visible' => isset($_POST['visible']) ? 1 : 0,
            'image' => $product['image'],
            'hoverImage' => $product['hoverImage']
        ];
        
        // Validaciones
        if (empty($formData['name'])) {
            $error = 'El nombre del producto es requerido';
        } elseif (!in_array($formData['categoria'], ['productos', 'souvenirs', 'navidad'])) {
            $error = 'Categoría inválida';
        } else {
            // Generar slug si está vacío
            if (empty($formData['slug'])) {
                $formData['slug'] = generateUniqueSlug($formData['name'], $productId);
            } else {
                $formData['slug'] = slugify($formData['slug']);
                // Verificar si el slug ya existe (excluyendo el producto actual)
                if (slugExists($formData['slug'], $productId)) {
                    $error = 'El slug ya existe. Por favor, elige otro.';
                }
            }
            
            // Validar que el slug no contenga 'ñ' o 'Ñ'
            if (strpos($formData['slug'], 'ñ') !== false || strpos($formData['slug'], 'Ñ') !== false) {
                $error = 'El slug no puede contener la letra "ñ". Se reemplazará automáticamente por "n".';
                $formData['slug'] = str_replace(['ñ', 'Ñ'], 'n', $formData['slug']);
                // Verificar si el slug modificado ya existe (excluyendo el producto actual)
                if (slugExists($formData['slug'], $productId)) {
                    $error = 'El slug generado ya existe. Por favor, modifica el nombre o el slug manualmente.';
                }
            }
            
            // Detectar si cambió la categoría o el slug
            $categoriaCambio = ($product['categoria'] !== $formData['categoria']);
            $slugCambio = ($product['slug'] !== $formData['slug']);
            
            // Si cambió la categoría o el slug, mover/renombrar las imágenes
            if (empty($error) && ($categoriaCambio || $slugCambio)) {
                $categoriaOrigen = $product['categoria'];
                $categoriaDestino = $formData['categoria'];
                $slugOrigen = $product['slug'];
                $slugDestino = $formData['slug'];
                
                // Caso 1: Cambió la categoría (y posiblemente el slug también)
                if ($categoriaCambio) {
                    // Mover imágenes de la categoría antigua a la nueva
                    $moveResult = moveProductImages($slugOrigen, $categoriaOrigen, $categoriaDestino);
                    if ($moveResult['success']) {
                        // Actualizar rutas si se movieron las imágenes
                        if ($moveResult['imagePath']) {
                            $formData['image'] = $moveResult['imagePath'];
                        }
                        if ($moveResult['hoverImagePath']) {
                            $formData['hoverImage'] = $moveResult['hoverImagePath'];
                        }
                        
                        // Si también cambió el slug, renombrar la carpeta en la nueva categoría
                        if ($slugCambio) {
                            $oldFolder = IMAGES_PATH . '/' . $categoriaDestino . '/' . $slugOrigen;
                            $newFolder = IMAGES_PATH . '/' . $categoriaDestino . '/' . $slugDestino;
                            
                            if (is_dir($oldFolder) && !is_dir($newFolder)) {
                                if (@rename($oldFolder, $newFolder)) {
                                    // Actualizar rutas de imágenes con el nuevo slug
                                    if (!empty($formData['image'])) {
                                        $formData['image'] = str_replace('/' . $slugOrigen . '/', '/' . $slugDestino . '/', $formData['image']);
                                    }
                                    if (!empty($formData['hoverImage'])) {
                                        $formData['hoverImage'] = str_replace('/' . $slugOrigen . '/', '/' . $slugDestino . '/', $formData['hoverImage']);
                                    }
                                }
                            }
                        }
                    } else {
                        // Si falla el movimiento, registrar el error pero continuar
                        error_log('Error al mover imágenes: ' . ($moveResult['error'] ?? 'Desconocido'));
                    }
                }
                // Caso 2: Solo cambió el slug (sin cambiar categoría)
                elseif ($slugCambio) {
                    $oldFolder = IMAGES_PATH . '/' . $categoriaDestino . '/' . $slugOrigen;
                    $newFolder = IMAGES_PATH . '/' . $categoriaDestino . '/' . $slugDestino;
                    
                    if (is_dir($oldFolder) && !is_dir($newFolder)) {
                        if (@rename($oldFolder, $newFolder)) {
                            // Actualizar rutas de imágenes
                            if (!empty($formData['image'])) {
                                $formData['image'] = str_replace('/' . $slugOrigen . '/', '/' . $slugDestino . '/', $formData['image']);
                            }
                            if (!empty($formData['hoverImage'])) {
                                $formData['hoverImage'] = str_replace('/' . $slugOrigen . '/', '/' . $slugDestino . '/', $formData['hoverImage']);
                            }
                        }
                    }
                }
            }
            
            if (empty($error)) {
                // Eliminar imagen principal si se solicita
                if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
                    if (!empty($product['image'])) {
                        deleteProductImage($product['image']);
                    }
                    $formData['image'] = '';
                }
                
                // Procesar nueva imagen principal
                if (!empty($_FILES['image']['name'])) {
                    // Eliminar imagen anterior si existe
                    if (!empty($product['image'])) {
                        deleteProductImage($product['image']);
                    }
                    
                    $uploadResult = uploadProductImage($_FILES['image'], $formData['slug'], $formData['categoria'], 'main');
                    if ($uploadResult['success']) {
                        $formData['image'] = $uploadResult['path'];
                    } else {
                        $error = $uploadResult['error'];
                    }
                }
                
                // Eliminar imagen hover si se solicita
                if (isset($_POST['delete_hoverImage']) && $_POST['delete_hoverImage'] == '1') {
                    if (!empty($product['hoverImage'])) {
                        deleteProductImage($product['hoverImage']);
                    }
                    $formData['hoverImage'] = '';
                }
                
                // Procesar nueva imagen hover
                if (empty($error) && !empty($_FILES['hoverImage']['name'])) {
                    // Eliminar imagen anterior si existe
                    if (!empty($product['hoverImage'])) {
                        deleteProductImage($product['hoverImage']);
                    }
                    
                    $uploadResult = uploadProductImage($_FILES['hoverImage'], $formData['slug'], $formData['categoria'], 'hover');
                    if ($uploadResult['success']) {
                        $formData['hoverImage'] = $uploadResult['path'];
                    }
                }
                
                // Si se cambió el slug, mover imágenes a nueva carpeta
                if (empty($error) && $formData['slug'] !== $product['slug']) {
                    // Esto requeriría mover archivos, por simplicidad lo dejamos así
                    // En producción podrías implementar una función para mover imágenes
                }
                
                if (empty($error)) {
                    // Actualizar en BD
                    $sql = "UPDATE products SET 
                            slug = :slug,
                            name = :name,
                            descripcion = :descripcion,
                            price = :price,
                            image = :image,
                            hoverImage = :hoverImage,
                            stock = :stock,
                            destacado = :destacado,
                            categoria = :categoria,
                            visible = :visible,
                            updated_at = NOW()
                            WHERE id = :id";
                    
                    $params = [
                        'id' => $productId,
                        'slug' => $formData['slug'],
                        'name' => $formData['name'],
                        'descripcion' => $formData['descripcion'],
                        'price' => $formData['price'],
                        'image' => $formData['image'],
                        'hoverImage' => $formData['hoverImage'],
                        'stock' => $formData['stock'],
                        'destacado' => $formData['destacado'],
                        'categoria' => $formData['categoria'],
                        'visible' => $formData['visible']
                    ];
                    
                    if (executeQuery($sql, $params)) {
                        $_SESSION['success_message'] = 'Producto actualizado exitosamente';
                        header('Location: list.php');
                        exit;
                    } else {
                        $error = 'Error al actualizar el producto en la base de datos';
                    }
                }
            }
        }
    }
}

// Solo ahora incluimos el header (después de todas las redirecciones posibles)
require_once '_inc/header.php';

$csrfToken = generateCSRFToken();
?>

<div class="admin-content">
    <h2>Editar Producto</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" style="max-width: 800px;">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        
        <div class="form-group">
            <label for="name">Nombre del Producto *</label>
            <input type="text" id="name" name="name" required 
                   value="<?= htmlspecialchars($formData['name'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="slug">Slug (URL amigable) *</label>
            <input type="text" id="slug" name="slug" required 
                   value="<?= htmlspecialchars($formData['slug'] ?? '') ?>"
                   pattern="[a-z0-9-]+" 
                   title="Solo letras minúsculas, números y guiones. No se permiten la letra 'ñ'">
            <small>Se genera automáticamente desde el nombre si está vacío</small>
            <div style="margin-top: 0.5rem; padding: 0.75rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong>⚠️ Importante:</strong> No se permiten la letra <strong>"ñ"</strong> en el slug. Si el nombre contiene "ñ", se reemplazará automáticamente por "n".
            </div>
        </div>
        
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($formData['descripcion'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Precio</label>
            <input type="text" id="price" name="price" 
                   value="<?= htmlspecialchars($formData['price'] ?? '') ?>"
                   placeholder="$15900">
        </div>
        
        <div class="form-group">
            <label for="categoria">Categoría *</label>
            <select id="categoria" name="categoria" required>
                <option value="productos" <?= ($formData['categoria'] ?? 'productos') === 'productos' ? 'selected' : '' ?>>Productos</option>
                <option value="souvenirs" <?= ($formData['categoria'] ?? '') === 'souvenirs' ? 'selected' : '' ?>>Souvenirs</option>
                <option value="navidad" <?= ($formData['categoria'] ?? '') === 'navidad' ? 'selected' : '' ?>>Navidad</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Imagen Principal Actual</label>
            <?php if (!empty($formData['image'])): ?>
                <?php 
                // Remover parámetros de cache busting para el admin
                $imageUrl = preg_replace('/\?.*$/', '', $formData['image']);
                $fullImageUrl = BASE_URL . $imageUrl;
                ?>
                <div style="margin-bottom: 1rem;">
                    <img src="<?= $fullImageUrl ?>" 
                         alt="Imagen actual" 
                         style="max-width: 200px; border-radius: 4px;"
                         onerror="this.onerror=null; this.style.display='none';">
                </div>
                <div class="checkbox-group" style="margin-bottom: 1rem;">
                    <input type="checkbox" id="delete_image" name="delete_image" value="1">
                    <label for="delete_image" style="color: #dc3545;">Eliminar imagen principal</label>
                </div>
            <?php endif; ?>
            <label for="image">Nueva Imagen Principal (dejar vacío para mantener la actual)</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
            <small>Formatos: JPG, PNG, WEBP. Máximo 5MB</small>
            <div id="imagePreview" style="margin-top: 1rem;"></div>
        </div>
        
        <div class="form-group">
            <label>Imagen Hover Actual</label>
            <?php if (!empty($formData['hoverImage'])): ?>
                <?php 
                // Remover parámetros de cache busting para el admin
                $hoverImageUrl = preg_replace('/\?.*$/', '', $formData['hoverImage']);
                $fullHoverImageUrl = BASE_URL . $hoverImageUrl;
                ?>
                <div style="margin-bottom: 1rem;">
                    <img src="<?= $fullHoverImageUrl ?>" 
                         alt="Imagen hover actual" 
                         style="max-width: 200px; border-radius: 4px;"
                         onerror="this.onerror=null; this.style.display='none';">
                </div>
                <div class="checkbox-group" style="margin-bottom: 1rem;">
                    <input type="checkbox" id="delete_hoverImage" name="delete_hoverImage" value="1">
                    <label for="delete_hoverImage" style="color: #dc3545;">Eliminar imagen hover</label>
                </div>
            <?php endif; ?>
            <label for="hoverImage">Nueva Imagen Hover (dejar vacío para mantener la actual)</label>
            <input type="file" id="hoverImage" name="hoverImage" accept="image/jpeg,image/png,image/webp">
            <div id="hoverImagePreview" style="margin-top: 1rem;"></div>
        </div>
        
        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="stock" name="stock" 
                       <?= ($formData['stock'] ?? 1) ? 'checked' : '' ?>>
                <label for="stock">En Stock</label>
            </div>
        </div>
        
        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="destacado" name="destacado"
                       <?= ($formData['destacado'] ?? 0) ? 'checked' : '' ?>>
                <label for="destacado">Producto Destacado</label>
            </div>
        </div>
        
        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="visible" name="visible"
                       <?= ($formData['visible'] ?? 0) ? 'checked' : '' ?>>
                <label for="visible">Visible en la Web</label>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="list.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
// Validar slug en tiempo real para evitar ñ
document.getElementById('slug').addEventListener('input', function(e) {
    let value = e.target.value;
    if (value.includes('ñ') || value.includes('Ñ')) {
        value = value.replace(/ñ/g, 'n').replace(/Ñ/g, 'n');
        e.target.value = value;
        // Mostrar advertencia visual
        if (!document.getElementById('slug-warning')) {
            const warning = document.createElement('div');
            warning.id = 'slug-warning';
            warning.style.cssText = 'margin-top: 0.5rem; padding: 0.5rem; background: #f8d7da; color: #721c24; border-radius: 4px; font-size: 0.875rem;';
            warning.textContent = '⚠️ La letra "ñ" ha sido reemplazada por "n"';
            e.target.parentNode.appendChild(warning);
            setTimeout(() => {
                if (warning.parentNode) warning.remove();
            }, 3000);
        }
    }
});

// Preview de nuevas imágenes
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = 
                '<strong>Vista previa nueva imagen:</strong><br>' +
                '<img src="' + e.target.result + '" style="max-width: 200px; border-radius: 4px; margin-top: 0.5rem;">';
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('hoverImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('hoverImagePreview').innerHTML = 
                '<strong>Vista previa nueva imagen:</strong><br>' +
                '<img src="' + e.target.result + '" style="max-width: 200px; border-radius: 4px; margin-top: 0.5rem;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once '_inc/footer.php'; ?>
