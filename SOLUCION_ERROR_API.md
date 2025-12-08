# ✅ Solución: Error API Devuelve Código PHP

## Problema

Cuando accedes desde el servidor de Astro (puerto 4321), la API devuelve código PHP en lugar de JSON:
```
Error al parsear JSON: SyntaxError: Unexpected token '<', "<?php /**"... is not valid JSON
```

## Causa

El servidor de Astro (puerto 4321) no puede ejecutar PHP. Cuando haces una petición a `/api/products.php`, va al servidor de Astro, que no sabe cómo procesar PHP y devuelve el código fuente.

## Solución

He configurado un **proxy en Astro** que redirige todas las peticiones a `/api/` al servidor PHP (puerto 8080).

### Cambios Realizados

1. ✅ **Proxy configurado en `astro.config.mjs`**: Todas las peticiones a `/api/` se redirigen automáticamente a `http://localhost:8080`

## Cómo Funciona Ahora

```
Navegador (Astro puerto 4321)
    ↓ fetch('/api/products.php')
Astro (detecta /api/)
    ↓ proxy automático
Servidor PHP (puerto 8080)
    ↓ procesa PHP
Devuelve JSON ✅
```

## Importante

**Debes tener AMBOS servidores corriendo:**

1. **Servidor PHP** (puerto 8080): Para la API y panel admin
   - Ejecuta: `start-server.bat`
   - URL: `http://localhost:8080`

2. **Servidor Astro** (puerto 4321): Para el frontend
   - Ejecuta: `npm run dev`
   - URL: `http://localhost:4321`

## Prueba

1. Asegúrate de que ambos servidores estén corriendo
2. Accede a: `http://localhost:4321/productos`
3. Los productos deberían cargarse correctamente desde la API

## Si Aún No Funciona

1. **Reinicia ambos servidores**:
   - Detén el servidor PHP (Ctrl+C)
   - Detén el servidor Astro (Ctrl+C)
   - Vuelve a iniciarlos

2. **Limpia la caché del navegador**: Ctrl+Shift+R

3. **Verifica que el servidor PHP esté corriendo**: 
   - Abre: `http://localhost:8080/api/products.php`
   - Deberías ver JSON, no código PHP

