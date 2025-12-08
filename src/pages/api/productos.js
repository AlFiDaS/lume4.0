import { productos } from '../../data/productos.js';

export async function GET() {
  return new Response(JSON.stringify(productos), {
    status: 200,
    headers: {
      'Content-Type': 'application/json',
      'Cache-Control': 'public, max-age=300' // Cache por 5 minutos
    }
  });
}
