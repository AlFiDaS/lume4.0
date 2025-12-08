<?php
/**
 * Agregar nuevo producto
 */
$pageTitle = 'Agregar Producto';
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
$formData = [];

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
            'visible' => isset($_POST['visible']) ? 1 : 1  // Por defecto visible = 1
        ];
        
        // Validaciones
        if (empty($formData['name'])) {
            $error = 'El nombre del producto es requerido';
        } elseif (!in_array($formData['categoria'], ['productos', 'souvenirs', 'navidad'])) {
            $error = 'Categoría inválida';
        } else {
            // Generar slug si está vacío
            if (empty($formData['slug'])) {
                $formData['slug'] = generateUniqueSlug($formData['name']);
            } else {
                $formData['slug'] = slugify($formData['slug']);
                // Verificar si el slug ya existe
                if (slugExists($formData['slug'])) {
                    $error = 'El slug ya existe. Por favor, elige otro.';
                }
            }
            
            // Validar que el slug no contenga 'ñ' o 'Ñ'
            if (strpos($formData['slug'], 'ñ') !== false || strpos($formData['slug'], 'Ñ') !== false) {
                $error = 'El slug no puede contener la letra "ñ". Se reemplazará automáticamente por "n".';
                $formData['slug'] = str_replace(['ñ', 'Ñ'], 'n', $formData['slug']);
                // Verificar si el slug modificado ya existe
                if (slugExists($formData['slug'])) {
                    $error = 'El slug generado ya existe. Por favor, modifica el nombre o el slug manualmente.';
                }
            }
            
            if (empty($error)) {
                // Generar ID único
                $productId = generateProductId();
                
                // Procesar imágenes
                $imagePath = '';
                $hoverImagePath = '';
                
                // Imagen principal
                if (!empty($_FILES['image']['name'])) {
                    $uploadResult = uploadProductImage($_FILES['image'], $formData['slug'], $formData['categoria'], 'main');
                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                    } else {
                        $error = $uploadResult['error'];
                    }
                }
                
                // Imagen hover (opcional)
                if (empty($error) && !empty($_FILES['hoverImage']['name'])) {
                    $uploadResult = uploadProductImage($_FILES['hoverImage'], $formData['slug'], $formData['categoria'], 'hover');
                    if ($uploadResult['success']) {
                        $hoverImagePath = $uploadResult['path'];
                    }
                    // No fallar si la imagen hover falla
                }
                
                if (empty($error)) {
                    // Insertar en BD
                    $sql = "INSERT INTO products 
                            (id, slug, name, descripcion, price, image, hoverImage, stock, destacado, categoria, visible) 
                            VALUES 
                            (:id, :slug, :name, :descripcion, :price, :image, :hoverImage, :stock, :destacado, :categoria, :visible)";
                    
                    $params = [
                        'id' => $productId,
                        'slug' => $formData['slug'],
                        'name' => $formData['name'],
                        'descripcion' => $formData['descripcion'],
                        'price' => $formData['price'],
                        'image' => $imagePath,
                        'hoverImage' => $hoverImagePath,
                        'stock' => $formData['stock'],
                        'destacado' => $formData['destacado'],
                        'categoria' => $formData['categoria'],
                        'visible' => $formData['visible']
                    ];
                    
                    if (executeQuery($sql, $params)) {
                        $_SESSION['success_message'] = 'Producto agregado exitosamente';
                        header('Location: list.php');
                        exit;
                    } else {
                        $error = 'Error al guardar el producto en la base de datos';
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
    <h2>Agregar Nuevo Producto</h2>
    
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
                   value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                   oninput="updateSlug(this.value)">
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
            <small>Formato: $15900</small>
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
            <label for="image">Imagen Principal</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
            <small>Formatos: JPG, PNG, WEBP. Máximo 5MB</small>
            <div id="imagePreview" style="margin-top: 1rem;"></div>
        </div>
        
        <div class="form-group">
            <label for="hoverImage">Imagen Hover (opcional)</label>
            <input type="file" id="hoverImage" name="hoverImage" accept="image/jpeg,image/png,image/webp">
            <small>Imagen que se muestra al pasar el mouse</small>
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
                       <?= ($formData['visible'] ?? 1) ? 'checked' : '' ?>>
                <label for="visible">Visible en la Web</label>
            </div>
            <small>Si no está marcado, el producto no aparecerá en el sitio web</small>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Guardar Producto</button>
            <a href="list.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
function updateSlug(name) {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value || slugInput.value === '') {
        // Generar slug básico (sin hacer llamada al servidor)
        let slug = name.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/ñ/g, 'n')  // Reemplazar ñ por n
            .replace(/Ñ/g, 'n')  // Reemplazar Ñ por n
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        slugInput.value = slug;
    }
}

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

// Preview de imágenes
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = 
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
                '<img src="' + e.target.result + '" style="max-width: 200px; border-radius: 4px; margin-top: 0.5rem;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once '_inc/footer.php'; ?>
