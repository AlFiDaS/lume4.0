/**
 * Script Node.js para convertir archivos JS de productos a JSON
 * Uso: node convertir-js-a-json.js
 */

const fs = require('fs');
const path = require('path');

const files = [
    { input: 'src/data/productos.js', output: 'productos.json', categoria: 'productos' },
    { input: 'src/data/souvenirs.js', output: 'souvenirs.json', categoria: 'souvenirs' },
    { input: 'src/data/navidad.js', output: 'navidad.json', categoria: 'navidad' }
];

files.forEach(({ input, output, categoria }) => {
    const inputPath = path.join(__dirname, input);
    
    if (!fs.existsSync(inputPath)) {
        console.log(`⚠️  Archivo no encontrado: ${input}`);
        return;
    }
    
    try {
        // Leer el archivo
        let content = fs.readFileSync(inputPath, 'utf8');
        
        // Extraer el array de productos usando regex
        // Buscar el export const y extraer el array
        let match;
        if (content.includes('export const productos')) {
            match = content.match(/export const productos = \[([\s\S]*?)\];/);
        } else if (content.includes('export const souvenirs')) {
            match = content.match(/export const souvenirs = \[([\s\S]*?)\];/);
        } else if (content.includes('export const productosNavidad')) {
            match = content.match(/export const productosNavidad = \[([\s\S]*?)\];/);
        }
        
        if (!match) {
            console.log(`❌ No se pudo extraer productos de ${input}`);
            return;
        }
        
        // Convertir el código JS a objetos JavaScript válidos
        // Esto es más complejo, mejor usar eval en un contexto seguro
        // O mejor aún, usar un parser de JavaScript
        
        console.log(`📄 Leyendo ${input}...`);
        console.log(`   Contenido encontrado: ${match[1].length} caracteres`);
        console.log(`   💡 Necesitamos parsear manualmente o usar un enfoque diferente\n`);
        
    } catch (error) {
        console.error(`❌ Error procesando ${input}:`, error.message);
    }
});

console.log('\n📝 Nota: Este script necesita un parser más complejo.');
console.log('💡 Alternativa: Crear manualmente un archivo JSON con los productos');
console.log('   o usar el panel admin para agregarlos uno por uno.');

