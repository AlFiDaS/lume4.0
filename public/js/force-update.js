// 🔄 SCRIPT DE ACTUALIZACIÓN FORZADA - Lume 2.1.0
(function() {
  'use strict';
  
  const CURRENT_VERSION = '2.1.0-2025-09-03T22-13-49';
  const VERSION_KEY = 'lume_version';
  const LAST_UPDATE_KEY = 'lume_last_update';
  
  // 🔍 VERIFICAR SI HAY UNA NUEVA VERSIÓN
  function checkForUpdates() {
    const storedVersion = localStorage.getItem(VERSION_KEY);
    const lastUpdate = localStorage.getItem(LAST_UPDATE_KEY);
    
    if (storedVersion !== CURRENT_VERSION) {
      console.log('🔄 Nueva versión detectada:', CURRENT_VERSION);
      forceUpdate();
      return;
    }
    
    // Verificar si han pasado más de 24 horas desde la última actualización
    if (lastUpdate) {
      const lastUpdateTime = new Date(lastUpdate).getTime();
      const now = new Date().getTime();
      const hoursSinceUpdate = (now - lastUpdateTime) / (1000 * 60 * 60);
      
      if (hoursSinceUpdate > 24) {
        console.log('🔄 Han pasado más de 24 horas, verificando actualizaciones...');
        checkServiceWorker();
      }
    }
  }
  
  // 🚀 FORZAR ACTUALIZACIÓN
  function forceUpdate() {
    // Limpiar caché local
    if ('caches' in window) {
      caches.keys().then(cacheNames => {
        cacheNames.forEach(cacheName => {
          if (cacheName.includes('lume')) {
            caches.delete(cacheName);
            console.log('🗑️ Cache eliminado:', cacheName);
          }
        });
      });
    }
    
    // Limpiar localStorage
    const keysToKeep = ['cart_items', 'cart_count'];
    const keysToRemove = [];
    
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && !keysToKeep.includes(key)) {
        keysToRemove.push(key);
      }
    }
    
    keysToRemove.forEach(key => {
      localStorage.removeItem(key);
    });
    
    // Actualizar versión
    localStorage.setItem(VERSION_KEY, CURRENT_VERSION);
    localStorage.setItem(LAST_UPDATE_KEY, new Date().toISOString());
    
    // Mostrar notificación
    showUpdateNotification();
    
    // Recargar página después de 2 segundos
    setTimeout(() => {
      window.location.reload(true);
    }, 2000);
  }
  
  // 🔍 VERIFICAR SERVICE WORKER
  function checkServiceWorker() {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.getRegistrations().then(registrations => {
        registrations.forEach(registration => {
          registration.update();
          console.log('🔄 Service Worker actualizado');
        });
      });
    }
    
    localStorage.setItem(LAST_UPDATE_KEY, new Date().toISOString());
  }
  
  // 📢 MOSTRAR NOTIFICACIÓN DE ACTUALIZACIÓN
  function showUpdateNotification() {
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #e0a4ce, #f7d4ed);
      color: white;
      padding: 1rem 1.5rem;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(224, 164, 206, 0.3);
      z-index: 10000;
      font-family: 'Inter', sans-serif;
      font-weight: 600;
      max-width: 300px;
      animation: slideIn 0.5s ease-out;
    `;
    
    notification.innerHTML = `
      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <span style="font-size: 1.2rem;">🔄</span>
        <div>
          <div style="font-weight: 700; margin-bottom: 0.25rem;">¡Nueva versión disponible!</div>
          <div style="font-size: 0.9rem; opacity: 0.9;">Actualizando automáticamente...</div>
        </div>
      </div>
    `;
    
    // Agregar estilos de animación
    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Remover notificación después de 5 segundos
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 5000);
  }
  
  // 🚀 INICIALIZAR VERIFICACIÓN
  function init() {
    // Verificar al cargar la página
    checkForUpdates();
    
    // Verificar cada hora
    setInterval(checkForUpdates, 60 * 60 * 1000);
    
    // Verificar cuando la página vuelve a estar visible
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) {
        checkForUpdates();
      }
    });
    
    // Verificar cuando se recupera conexión
    window.addEventListener('online', checkForUpdates);
  }
  
  // 🎯 EJECUTAR CUANDO EL DOM ESTÉ LISTO
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
})();
