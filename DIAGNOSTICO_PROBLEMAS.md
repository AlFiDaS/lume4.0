# 🔍 Diagnóstico: Productos No Aparecen e Imágenes Rotas

## Problemas Identificados

1. **Los productos no se ven en la página web**
2. **Las imágenes aparecen rotas después de subirlas**

## Pasos para Diagnosticar

### 1. Verificar Productos en la Base de Datos

Ejecuta el script de diagnóstico:
```
http://localhost:8080/verificar-productos.php
```

Este script te mostrará:
- ✅ Todos los productos en la base de datos
- ✅ Si las imágenes existen físicamente
- ✅ Si los productos están marcados como visibles
- ✅ Las rutas de las imágenes

### 2. Verificar que los Productos Tengan `visible = 1`

Los productos solo se muestran si tienen `visible = 1` en la base de datos. Verifica en el panel admin que el checkbox "Visible en la Web" esté marcado.

### 3. Verificar la API

Prueba estas URLs directamente en el navegador:
- `http://localhost:8080/api/products.php` - Todos los productos visibles
- `http://localhost:8080/api/products.php?categoria=productos` - Solo productos
- `http://localhost:8080/api/products.php?categoria=souvenirs` - Solo souvenirs
- `http://localhost:8080/api/products.php?categoria=navidad` - Solo navidad

Deberías ver JSON válido, no HTML.

### 4. Verificar la Consola del Navegador

Abre las herramientas de desarrollo (F12) y verifica:
- **Console**: Busca errores de JavaScript
- **Network**: Verifica que la petición a `/api/products.php` devuelva 200 OK
- **Network**: Verifica que las imágenes se carguen correctamente

### 5. Verificar Rutas de Imágenes

Las imágenes se guardan en: `public/images/[slug]/main.[ext]`

Por ejemplo:
- Slug: `a` → Imagen: `public/images/a/main.jpg`
- URL: `http://localhost:8080/images/a/main.jpg`

## Soluciones Comunes

### Problema: Productos no aparecen

**Causas posibles:**
1. Los productos no están marcados como visibles (`visible = 0`)
2. El script `products-loader.js` no se está ejecutando
3. Hay un error en la API (ver consola del navegador)
4. La categoría no coincide

**Soluciones:**
1. Verifica que los productos tengan `visible = 1` en el panel admin
2. Abre la consola del navegador (F12) y busca errores
3. Prueba la API directamente (ver punto 3 arriba)
4. Recarga la página con Ctrl+Shift+R para limpiar caché

### Problema: Imágenes aparecen rotas

**Causas posibles:**
1. La ruta de la imagen es incorrecta
2. La imagen no existe físicamente en el servidor
3. Problema de permisos del archivo

**Soluciones:**
1. Verifica que la imagen existe: `public/images/[slug]/main.[ext]`
2. Verifica la ruta en la base de datos (ejecuta `verificar-productos.php`)
3. Verifica que la URL sea accesible: `http://localhost:8080/images/[slug]/main.[ext]`

## Verificación Rápida

1. ✅ Ejecuta `verificar-productos.php` para ver el estado
2. ✅ Abre la consola del navegador (F12) y busca errores
3. ✅ Prueba la API directamente en el navegador
4. ✅ Verifica que las imágenes existan físicamente
5. ✅ Asegúrate de que los productos tengan `visible = 1`

