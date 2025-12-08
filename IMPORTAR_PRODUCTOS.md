# 📦 Importar Productos desde Archivos JS

## Problema

Tienes productos en archivos JavaScript (`productos.js`, `souvenirs.js`, `navidad.js`) que necesitas migrar a la base de datos MySQL.

## Soluciones

### Opción 1: Marcar productos existentes como visibles (Rápido)

Si ya tienes productos en la base de datos pero no aparecen:

```bash
php marcar-productos-visibles.php
```

Esto marcará todos los productos existentes como visibles.

---

### Opción 2: Importar productos manualmente (Recomendado)

1. **Ir al Panel Admin:**
   ```
   http://localhost:8080/admin/list.php
   ```

2. **Agregar productos uno por uno:**
   - Click en "➕ Agregar Producto"
   - Completar el formulario
   - **Importante:** Asegúrate de marcar "Visible en la Web" ✅

---

### Opción 3: Script de migración automática (Complejo)

Para migrar todos los productos automáticamente, necesitamos:

1. Convertir los archivos JS a JSON
2. Importar el JSON a la base de datos

**Paso 1:** Instalar Node.js (si no lo tienes)

**Paso 2:** Ejecutar script de conversión (aún no está completo)

---

## ✅ Solución Inmediata

**Para que los productos nuevos sean visibles por defecto:**

Ya está configurado. El checkbox "Visible en la Web" ahora está marcado por defecto cuando creas un producto nuevo.

**Para productos ya creados:**

1. Ejecuta: `php marcar-productos-visibles.php`
2. O ve al panel admin y marca manualmente cada producto como visible

---

## 🔍 Verificar productos visibles

Puedes verificar cuántos productos visibles tienes en:
- Panel Admin → Dashboard: Muestra contadores
- Panel Admin → Lista de Productos: Filtra por "Visible"

---

## 📝 Nota

Los productos en los archivos JS son solo para referencia. Una vez migrados a la base de datos, esos archivos JS ya no se usan (el sistema dinámico carga desde MySQL).

