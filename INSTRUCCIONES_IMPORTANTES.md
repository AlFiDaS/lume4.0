# ⚠️ INSTRUCCIONES IMPORTANTES - Servidores

## 🔴 Problema: API devuelve código PHP en lugar de JSON

Esto ocurre porque estás accediendo desde **Astro (puerto 4321)** pero la API PHP está en otro puerto.

## ✅ Solución: Tener AMBOS servidores corriendo

### 1. Servidor PHP (puerto 8080) - DEBE ESTAR CORRIENDO

**Abre una terminal y ejecuta:**
```bash
.\start-server.bat
```

O desde PowerShell:
```powershell
.\start-server.ps1
```

**Verifica que esté corriendo:**
- Abre: `http://localhost:8080/api/products.php`
- Debes ver JSON, NO código PHP

### 2. Servidor Astro (puerto 4321) - DEBE ESTAR CORRIENDO

**Abre OTRA terminal y ejecuta:**
```bash
npm run dev
```

## 📍 URLs Correctas

- **Frontend (Astro):** `http://localhost:4321/`
- **API PHP:** `http://localhost:8080/api/products.php`
- **Panel Admin:** `http://localhost:8080/admin/`

## ⚡ El Proxy ya está configurado

Ya configuré un proxy en Astro que redirige las peticiones a `/api/` al servidor PHP automáticamente.

## 🔍 Verificar

1. **Abre DOS terminales:**
   - Terminal 1: Ejecuta `.\start-server.bat` (PHP puerto 8080)
   - Terminal 2: Ejecuta `npm run dev` (Astro puerto 4321)

2. **Verifica la API directamente:**
   - `http://localhost:8080/api/products.php` → Debe mostrar JSON

3. **Accede desde Astro:**
   - `http://localhost:4321/productos` → Los productos deben cargarse

## ❌ Si Aún No Funciona

1. **Reinicia AMBOS servidores**
2. **Limpia caché del navegador:** Ctrl+Shift+R
3. **Verifica la consola del navegador (F12)** para ver errores

