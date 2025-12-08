// cart.js actualizado para manejar productos con cantidad y talle
// Envuelto en IIFE para evitar conflictos y compatibilidad con navegadores

(function() {
  'use strict';
  
  // Funciones del carrito
  function getCarrito() {
    return JSON.parse(localStorage.getItem("carrito")) || [];
  }

  function saveCarrito(carrito) {
    localStorage.setItem("carrito", JSON.stringify(carrito));
    updateCartCount();
  }

  // Función global para agregar al carrito
  function agregarAlCarrito(name, price, image, slug, categoria) {
    let carrito = getCarrito();

    const index = carrito.findIndex(
      (p) => p.slug === slug && p.name === name
    );

    const esSouvenir = categoria === "souvenirs";

    if (index !== -1) {
      carrito[index].cantidad++;
    } else {
      carrito.push({ 
        name, 
        price, 
        image, 
        slug, 
        categoria, 
        cantidad: esSouvenir ? 10 : 1 
      });
    }

    saveCarrito(carrito);
    mostrarToast(`${name} agregado al carrito 🛒`);
  }

  function updateCartCount() {
    const carrito = getCarrito();
    const total = carrito.reduce((acc, p) => acc + p.cantidad, 0);

    const badgeDesktop = document.getElementById("cart-count");
    const badgeMobile = document.getElementById("cart-count-mobile");

    if (badgeDesktop) badgeDesktop.textContent = total;
    if (badgeMobile) badgeMobile.textContent = total;
  }

  function mostrarToast(mensaje) {
    const toast = document.createElement("div");
    toast.textContent = mensaje;
    toast.style.cssText = `
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: black;
      color: white;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      font-weight: bold;
      z-index: 9999;
      opacity: 0;
      transition: opacity 0.4s ease-in-out;
    `;
    document.body.appendChild(toast);
    requestAnimationFrame(() => {
      toast.style.opacity = 1;
    });
    setTimeout(() => {
      toast.style.opacity = 0;
      setTimeout(() => toast.remove(), 400);
    }, 2000);
  }

  // Hacer funciones disponibles globalmente
  window.getCarrito = getCarrito;
  window.saveCarrito = saveCarrito;
  window.agregarAlCarrito = agregarAlCarrito;
  window.updateCartCount = updateCartCount;
  
  // Actualizar contador al cargar
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateCartCount);
  } else {
    updateCartCount();
  }
})();
