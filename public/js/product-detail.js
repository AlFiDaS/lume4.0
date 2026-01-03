/**
 * ============================================
 * Cargador de Detalle de Producto
 * ============================================
 * Carga un producto específico por slug
 * Compatible: Navegadores modernos
 * ============================================
 */

(function() {
    'use strict';
    
    // Detectar si estamos en desarrollo local y ajustar la URL de la API
    const isLocalDev = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const currentPort = window.location.port;
    
    // Si estamos en el servidor de Astro (puerto 4321), usar el servidor PHP directamente (puerto 8080)
    let API_BASE = '/api/products.php';
    if (isLocalDev && (currentPort === '4321' || currentPort === '')) {
        // Desde Astro, usar el servidor PHP directamente
        API_BASE = 'http://localhost:8080/api/products.php';
    }
    
    /**
     * Cargar producto por slug
     * @param {string} slug
     * @returns {Promise<Object>}
     */
    async function loadProductBySlug(slug) {
        try {
            const url = `${API_BASE}?slug=${encodeURIComponent(slug)}`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                if (response.status === 404) {
                    throw new Error('Producto no encontrado');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Error al cargar el producto');
            }
            
            return data.product || null;
            
        } catch (error) {
            console.error('Error al cargar producto:', error);
            throw error;
        }
    }
    
    /**
     * Extraer valor numérico del precio
     * @param {string} priceString - Precio como string (ej: "$15900")
     * @returns {number} Valor numérico
     */
    function extractPriceValue(priceString) {
        if (!priceString) return 0;
        // Remover símbolos y espacios, mantener solo números
        const cleaned = priceString.replace(/[^0-9]/g, '');
        return parseInt(cleaned, 10) || 0;
    }
    
    /**
     * Calcular precio con tarjeta (25% más, redondeado al 100 más cercano)
     * @param {string} priceString - Precio como string (ej: "$15900")
     * @returns {string} Precio con tarjeta formateado (ej: "$19900")
     */
    function calculateCardPrice(priceString) {
        const basePrice = extractPriceValue(priceString);
        if (basePrice === 0) return '';
        const cardPrice = Math.round((basePrice * 1.25) / 100) * 100;
        return '$' + cardPrice.toLocaleString('es-AR');
    }
    
    /**
     * Calcular precio de transferencia (25% menos = 80% del precio de tarjeta)
     * @param {string} priceString - Precio de tarjeta como string (ej: "$19875")
     * @returns {string} Precio de transferencia formateado (ej: "$15900")
     */
    function calculateTransferPrice(priceString) {
        const cardPrice = extractPriceValue(priceString);
        if (cardPrice === 0) return '';
        const transferPrice = Math.round(cardPrice * 0.8);
        return '$' + transferPrice.toLocaleString('es-AR');
    }
    
    /**
     * Renderizar detalle de producto
     * @param {Object} product
     * @param {HTMLElement} container
     */
    function renderProductDetail(product, container) {
        if (!container) {
            console.error('Contenedor no encontrado');
            return;
        }
        
        // Validar y sanitizar URLs de imágenes
        const placeholderPath = '/images/placeholder.svg';
        const hasValidImage = product.image && product.image.trim() !== '';
        const imageSrc = hasValidImage ? product.image : placeholderPath;
        const hasValidHover = product.hoverImage && product.hoverImage.trim() !== '';
        const hoverImage = hasValidHover ? product.hoverImage : imageSrc;
        
        // Guardar el precio de transferencia original en el carrito (no el precio de tarjeta)
        // Esto permite calcular correctamente los descuentos sin errores de redondeo
        // Stock disponible si es NULL (ilimitado) o > 0 (limitado)
        const hasStock = product.stock === null || product.stock > 0;
        const stockButton = hasStock ? 
            `<button 
                class="btn-agregar" 
                onclick="agregarAlCarrito('${escapeHtml(product.name)}', '${escapeHtml(product.price)}', '${escapeHtml(imageSrc)}', '${escapeHtml(product.slug)}', '${escapeHtml(product.categoria)}')"
            >
                Agregar al carrito
            </button>` :
            `<button class="btn-agregar" disabled>Sin stock</button>`;
        
        // Determinar nombre de categoría para mostrar
        const categoriaNames = {
            'productos': 'Producto',
            'souvenirs': 'Souvenir',
            'navidad': 'Navidad'
        };
        const categoriaName = categoriaNames[product.categoria] || product.categoria;
        
        container.innerHTML = `
            <div class="product-images">
                <div class="main-image">
                    <img 
                        id="mainProductImage"
                        src="${escapeHtml(imageSrc)}" 
                        alt="${escapeHtml(product.name)}"
                        class="imagen-principal"
                        loading="eager"
                        onerror="if(this.src!=='/images/placeholder.svg'){this.onerror=null;this.src='/images/placeholder.svg';}else{this.style.display='none';}"
                    />
                </div>
                ${hasValidHover ? `
                    <div class="thumbnails">
                        <div class="thumbnail active" data-image="${escapeHtml(imageSrc)}">
                            <img 
                                src="${escapeHtml(imageSrc)}" 
                                alt="Vista principal"
                                onerror="if(this.src!=='/images/placeholder.svg'){this.onerror=null;this.src='/images/placeholder.svg';}else{this.style.display='none';}"
                            />
                        </div>
                        <div class="thumbnail" data-image="${escapeHtml(hoverImage)}">
                            <img 
                                src="${escapeHtml(hoverImage)}" 
                                alt="Vista hover"
                                onerror="if(this.src!=='/images/placeholder.svg'){this.onerror=null;this.src='/images/placeholder.svg';}else{this.style.display='none';}"
                            />
                        </div>
                    </div>
                ` : ''}
            </div>
            
            <div class="producto-info">
                <div class="producto-header">
                    <div class="header-main">
                        <h1>${escapeHtml(product.name)}</h1>
                    </div>
                    <div class="header-badges">
                        <span class="categoria">${categoriaName}</span>
                        ${!hasStock ? '<span class="badge-sin-stock">Sin stock</span>' : ''}
                    </div>
                </div>
                
                ${product.descripcion ? `
                    <div class="producto-description">
                        <p>${escapeHtml(product.descripcion)}</p>
                    </div>
                ` : ''}
                
                <div class="producto-price">
                    <div class="price-main-row">
                        <span class="price">${escapeHtml(calculateCardPrice(product.price) || product.price || 'N/A')}</span>
                        <div class="price-badge">hasta en 3 cuotas</div>
                    </div>
                    ${(() => {
                        if (!product.price) return '';
                        // Formatear precio de transferencia (extraer número y formatear con separadores de miles)
                        const transferPriceValue = extractPriceValue(product.price);
                        const transferPriceFormatted = transferPriceValue > 0 ? '$' + transferPriceValue.toLocaleString('es-AR') : product.price;
                        return `
                        <div class="price-card-row">
                            <span class="price-card-label">Transferencia (-25%):</span>
                            <span class="price-card-value">${escapeHtml(transferPriceFormatted)}</span>
                        </div>
                    `;
                    })()}
                </div>
                
                <div class="producto-details">
                    ${product.categoria === 'souvenirs' ? `
                        <div class="detail-item">
                            <span class="detail-icon">📦</span>
                            <span class="detail-text">Cantidad mínima: 10 unidades</span>
                        </div>
                    ` : ''}
                    <div class="detail-item">
                        <span class="detail-icon">💳</span>
                        <span class="detail-text">Tarjeta de crédito: Hasta 3 cuotas sin interés</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon">💰</span>
                        <span class="detail-text">Transferencia: 25% de descuento</span>
                    </div>
                </div>
                
                <div class="producto-actions">
                    <button 
                        class="wishlist-btn-detail" 
                        data-wishlist-id="${escapeHtml(product.id)}"
                        onclick="toggleWishlist('${escapeHtml(product.id)}')"
                        title="Agregar a favoritos"
                    >
                        🤍 Agregar a favoritos
                    </button>
                    ${stockButton}
                    <a href="/${product.categoria}" class="btn-volver">
                        ← Volver al catálogo
                    </a>
                </div>
            </div>
        `;
        
        // Inicializar thumbnails después de renderizar
        if (hasValidHover) {
            setTimeout(() => {
                const thumbnails = container.querySelectorAll('.thumbnail');
                thumbnails.forEach(thumb => {
                    thumb.addEventListener('click', () => {
                        const thumbImageSrc = thumb.getAttribute('data-image');
                        if (thumbImageSrc) {
                            changeMainImage(thumbImageSrc);
                        }
                    });
                });
            }, 100);
        }
    }
    
    /**
     * Mostrar estado de carga
     * @param {HTMLElement} container
     */
    function showLoading(container) {
        if (!container) return;
        
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #e0a4ce; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #666;">Cargando producto...</p>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `;
    }
    
    /**
     * Mostrar error
     * @param {HTMLElement} container
     * @param {string} message
     */
    function showError(container, message) {
        if (!container) return;
        
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem; color: #dc3545;">
                <p><strong>Error al cargar el producto</strong></p>
                <p>${escapeHtml(message)}</p>
                <a href="/${getCategoriaFromPath()}" style="display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #e0a4ce; color: white; text-decoration: none; border-radius: 4px;">
                    Volver al catálogo
                </a>
            </div>
        `;
    }
    
    /**
     * Cambiar imagen principal
     * @param {string} imageSrc
     */
    window.changeMainImage = function(imageSrc) {
        const mainImage = document.getElementById('mainProductImage');
        if (mainImage) {
            mainImage.src = imageSrc;
        }
        
        // Actualizar thumbnails activos
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
            if (thumb.src === imageSrc) {
                thumb.classList.add('active');
            }
        });
    };
    
    /**
     * Obtener categoría desde la ruta actual
     * @returns {string}
     */
    function getCategoriaFromPath() {
        const path = window.location.pathname;
        if (path.includes('/productos/')) return 'productos';
        if (path.includes('/souvenirs/')) return 'souvenirs';
        if (path.includes('/navidad/')) return 'navidad';
        return 'productos';
    }
    
    /**
     * Escape HTML para prevenir XSS
     * @param {string} text
     * @returns {string}
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Inicializar cargador de detalle
     * @param {string} slug - Slug del producto
     * @param {Object} options
     */
    window.initProductDetail = async function(slug, options = {}) {
        const {
            containerSelector = '.product-detail-container',
            autoLoad = true
        } = options;
        
        const container = document.querySelector(containerSelector);
        
        if (!container) {
            console.warn(`Contenedor "${containerSelector}" no encontrado`);
            return;
        }
        
        if (!slug) {
            showError(container, 'Slug del producto no proporcionado');
            return;
        }
        
        if (autoLoad) {
            try {
                showLoading(container);
                const product = await loadProductBySlug(slug);
                
                if (!product) {
                    showError(container, 'Producto no encontrado');
                    return;
                }
                
                renderProductDetail(product, container);
                
                // Actualizar estado del botón de wishlist después de renderizar
                if (window.isInWishlist && product.id) {
                    setTimeout(async () => {
                        const isIn = await window.isInWishlist(product.id);
                        if (isIn && window.updateWishlistButtons) {
                            window.updateWishlistButtons(product.id, true);
                        }
                    }, 500);
                }
                
            } catch (error) {
                showError(container, error.message || 'Error desconocido');
            }
        }
        
        // Retornar función para recargar manualmente
        return {
            reload: async () => {
                try {
                    showLoading(container);
                    const product = await loadProductBySlug(slug);
                    if (product) {
                        renderProductDetail(product, container);
                    }
                } catch (error) {
                    showError(container, error.message || 'Error desconocido');
                }
            }
        };
    };
    
    // Auto-inicializar si hay un contenedor con clase 'product-detail-container'
    document.addEventListener('DOMContentLoaded', function() {
        const autoContainer = document.querySelector('.product-detail-container');
        if (autoContainer) {
            // Intentar obtener slug desde la URL
            const path = window.location.pathname;
            const slugMatch = path.match(/\/(productos|souvenirs|navidad)\/([^/]+)/);
            
            if (slugMatch && slugMatch[2]) {
                const slug = slugMatch[2];
                window.initProductDetail(slug, {
                    containerSelector: '.product-detail-container',
                    autoLoad: true
                });
            }
        }
    });
    
})();

