/**
 * ============================================
 * Cargador Dinámico de Productos
 * ============================================
 * Carga productos desde la API y los renderiza
 * Compatible: Navegadores modernos
 * ============================================
 */

(function() {
    'use strict';
    
    // Detectar si estamos en desarrollo local y ajustar la URL de la API
    const isLocalDev = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const currentPort = window.location.port;
    
    // Si estamos en el servidor de Astro (puerto 4321), usar el servidor PHP directamente (puerto 8080)
    // Si estamos en producción o el servidor PHP, usar ruta relativa
    let API_BASE = '/api/products.php';
    if (isLocalDev && (currentPort === '4321' || currentPort === '')) {
        // Desde Astro, usar el servidor PHP directamente
        API_BASE = 'http://localhost:8080/api/products.php';
    }
    
    /**
     * Cargar productos desde la API
     * @param {Object} filters - Filtros de búsqueda
     * @returns {Promise<Array>}
     */
    async function loadProducts(filters = {}) {
        try {
            // Construir URL con parámetros
            const params = new URLSearchParams();
            
            if (filters.categoria) params.append('categoria', filters.categoria);
            if (filters.destacado !== undefined) params.append('destacado', filters.destacado ? 1 : 0);
            if (filters.stock !== undefined) params.append('stock', filters.stock ? 1 : 0);
            if (filters.limit) params.append('limit', filters.limit);
            
            const url = API_BASE + (params.toString() ? '?' + params.toString() : '');
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            let data;
            
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                console.error('Respuesta recibida:', text.substring(0, 500));
                throw new Error('La respuesta del servidor no es válida JSON. Verifica la consola para más detalles.');
            }
            
            if (!data.success) {
                throw new Error(data.error || 'Error al cargar productos');
            }
            
            return data.products || [];
            
        } catch (error) {
            console.error('Error al cargar productos:', error);
            throw error;
        }
    }
    
    /**
     * Renderizar producto como card
     * @param {Object} product
     * @returns {string} HTML del producto
     */
    function renderProductCard(product) {
        const stockClass = !product.stock ? 'sin-stock' : '';
        const stockText = !product.stock ? 'Sin stock' : 'Agregar al carrito';
        const disabledAttr = !product.stock ? 'disabled' : '';
        
        const hoverImage = product.hoverImage || product.image;
        const hoverAttr = product.hoverImage ? 
            `onmouseover="this.src='${hoverImage}'" onmouseout="this.src='${product.image}'"` : 
            '';
        
        return `
            <div class="product-card">
                <div class="image-container">
                    <a href="/${product.categoria}/${product.slug}" class="card-link">
                        <img
                            src="${product.image}"
                            alt="${escapeHtml(product.name)} - Lume Velas Artesanales"
                            class="imagen-con-transicion"
                            width="400"
                            height="400"
                            ${hoverAttr}
                            loading="lazy"
                            onerror="this.onerror=null; this.src='/images/placeholder.svg';"
                        />
                    </a>
                    ${!product.stock ? '<div class="sin-stock">Sin stock</div>' : ''}
                </div>
                
                <div class="info">
                    <h3>${escapeHtml(product.name)}</h3>
                    <p class="price">${escapeHtml(product.price || 'N/A')}</p>
                    <button 
                        class="btn-agregar" 
                        onclick="agregarAlCarrito('${escapeHtml(product.name)}', '${escapeHtml(product.price)}', '${product.image}', '${product.slug}', '${product.categoria}')"
                        ${disabledAttr}
                    >
                        ${stockText}
                    </button>
                </div>
            </div>
        `;
    }
    
    /**
     * Renderizar grid de productos
     * @param {Array} products
     * @param {HTMLElement} container
     */
    function renderProductsGrid(products, container) {
        if (!container) {
            console.error('Contenedor no encontrado');
            return;
        }
        
        if (products.length === 0) {
            container.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #666;">
                    <p>No se encontraron productos en esta categoría.</p>
                </div>
            `;
            return;
        }
        
        // Verificar si es el contenedor de destacados y si hay número impar
        const isDestacados = container.hasAttribute('data-destacados') && container.getAttribute('data-destacados') === 'true';
        const isOdd = products.length % 2 !== 0;
        
        // Renderizar productos
        let html = '';
        products.forEach((product, index) => {
            const isLast = index === products.length - 1;
            // Aplicar clase especial si es el último producto impar en destacados
            // El CSS se encargará de aplicarlo solo en mobile
            const shouldCenter = isDestacados && isOdd && isLast;
            
            let cardHtml = renderProductCard(product);
            
            // Si es el último y es impar en destacados, agregar clase especial
            if (shouldCenter) {
                // Reemplazar la clase product-card para agregar la clase especial
                cardHtml = cardHtml.replace('class="product-card"', 'class="product-card product-card-last-odd"');
            }
            
            html += cardHtml;
        });
        
        container.innerHTML = html;
    }
    
    /**
     * Mostrar estado de carga
     * @param {HTMLElement} container
     */
    function showLoading(container) {
        if (!container) return;
        
        container.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #e0a4ce; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #666;">Cargando productos...</p>
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
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #dc3545;">
                <p><strong>Error al cargar productos</strong></p>
                <p>${escapeHtml(message)}</p>
                <button onclick="location.reload()" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #e0a4ce; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Reintentar
                </button>
            </div>
        `;
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
     * Inicializar cargador de productos
     * @param {Object} options
     */
    window.initProductsLoader = function(options = {}) {
        const {
            containerSelector = '.products-grid',
            categoria = null,
            destacado = null,
            stock = null,
            limit = null,
            autoLoad = true
        } = options;
        
        const container = document.querySelector(containerSelector);
        
        if (!container) {
            console.warn(`Contenedor "${containerSelector}" no encontrado`);
            return;
        }
        
        if (autoLoad) {
            loadAndRender();
        }
        
        async function loadAndRender() {
            try {
                showLoading(container);
                
                const filters = {};
                if (categoria) filters.categoria = categoria;
                if (destacado !== null) filters.destacado = destacado;
                if (stock !== null) filters.stock = stock;
                if (limit) filters.limit = limit;
                
                const products = await loadProducts(filters);
                renderProductsGrid(products, container);
                
            } catch (error) {
                showError(container, error.message || 'Error desconocido');
            }
        }
        
        // Retornar función para recargar manualmente
        return {
            reload: loadAndRender,
            loadProducts: loadProducts,
            renderProductsGrid: renderProductsGrid
        };
    };
    
    // Auto-inicializar si hay un contenedor con clase 'products-grid'
    document.addEventListener('DOMContentLoaded', function() {
        // Primero verificar si hay contenedores de destacados (en la página de inicio)
        const destacadosContainer = document.querySelector('.products-grid[data-destacados="true"]');
        if (destacadosContainer) {
            const limit = parseInt(destacadosContainer.dataset.limit || '5');
            window.initProductsLoader({
                containerSelector: '.products-grid[data-destacados="true"]',
                destacado: true,
                limit: limit,
                autoLoad: true
            });
            return; // No procesar otros contenedores si encontramos uno de destacados
        }
        
        // Luego verificar contenedores normales (sin destacados)
        const autoContainer = document.querySelector('.products-grid');
        if (autoContainer) {
            // Intentar detectar categoría desde la URL
            let categoria = null;
            const path = window.location.pathname;
            
            if (path.includes('/productos')) categoria = 'productos';
            else if (path.includes('/souvenirs')) categoria = 'souvenirs';
            else if (path.includes('/navidad')) categoria = 'navidad';
            
            window.initProductsLoader({
                containerSelector: '.products-grid',
                categoria: categoria,
                autoLoad: true
            });
        }
    });
    
})();

