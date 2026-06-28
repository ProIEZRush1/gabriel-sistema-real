// Prueba de navegador (Playwright) contra la app servida localmente.
//
// Verifica el flujo real del usuario administrador:
//   1. Abre /login e inicia sesión con el admin sembrado.
//   2. Navega al dashboard y a cada módulo (Seguros, Rentas, Auxiliar Bancario).
//   3. Crea un registro POR LA INTERFAZ (módulo de Agentes de venta).
//   4. Recarga la página y confirma que el dato sigue ahí (persistencia real en BD).
//
// Uso: BASE_URL=http://127.0.0.1:8066 node tests/Browser/panel.spec.mjs
//   (requiere PLAYWRIGHT_BROWSERS_PATH apuntando a los navegadores instalados)

import { chromium } from 'playwright';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8066';
const EMAIL = 'gabriel-chernitsky@overcloud.us';
const PASSWORD = '2OFqZ17eNcNP';

function assert(cond, msg) {
    if (!cond) {
        throw new Error('ASSERT FALLÓ: ' + msg);
    }
    console.log('  ✓ ' + msg);
}

const browser = await chromium.launch();
const page = await browser.newPage();
const errores = [];
page.on('response', (r) => {
    if (r.status() >= 500) errores.push(`${r.status()} ${r.url()}`);
});

try {
    // 1) Login
    await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
    await page.fill('input#email', EMAIL);
    await page.fill('input#password', PASSWORD);
    await Promise.all([
        page.waitForURL('**/dashboard', { timeout: 15000 }),
        page.click('button[type=submit]'),
    ]);
    assert(page.url().includes('/dashboard'), 'Inicio de sesión correcto, redirige al dashboard');
    assert(await page.getByText('Panel de administración').first().isVisible(), 'Dashboard visible');

    // 2) Navegar a los módulos
    for (const [ruta, texto] of [
        ['/seguros', 'Seguros'],
        ['/rentas', 'Rentas y reportes'],
        ['/movimientos', 'Movimientos bancarios'],
    ]) {
        await page.goto(`${BASE}${ruta}`, { waitUntil: 'networkidle' });
        assert(await page.getByText(texto).first().isVisible(), `Módulo "${texto}" carga correctamente`);
    }

    // 3) Crear un agente POR LA INTERFAZ
    const nombre = 'Agente Navegador ' + Date.now();
    await page.goto(`${BASE}/agentes`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: '+ Nuevo agente' }).click();
    await page.fill('input', nombre); // primer input del modal = nombre
    // Rellenar de forma robusta por etiqueta
    await page.locator('label:has-text("Nombre") + input').fill(nombre);
    await page.locator('label:has-text("Comisión") + input').fill('9');
    await page.getByRole('button', { name: 'Guardar' }).click();
    await page.waitForLoadState('networkidle');
    assert(await page.getByText(nombre).first().isVisible(), 'El agente aparece en la tabla tras crearlo');

    // 4) Recargar y confirmar persistencia
    await page.reload({ waitUntil: 'networkidle' });
    assert(await page.getByText(nombre).first().isVisible(), 'El agente PERSISTE tras recargar (datos en BD)');

    assert(errores.length === 0, 'Sin respuestas 500 durante la navegación' + (errores.length ? ': ' + errores.join(', ') : ''));

    console.log('\n✅ PRUEBA DE NAVEGADOR SUPERADA');
} catch (e) {
    console.error('\n❌ PRUEBA DE NAVEGADOR FALLÓ:', e.message);
    if (errores.length) console.error('Respuestas 500:', errores);
    process.exitCode = 1;
} finally {
    await browser.close();
}
