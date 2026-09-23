/**
 * Primitivas de interacción del design system compartido:
 *  - [data-reveal]: fade-up al entrar en viewport (IntersectionObserver único).
 *  - .card-interactive: spotlight + tilt 3D que sigue el mouse (listener
 *    delegado único, sin registrar un listener por tarjeta).
 *
 * Se importa una sola vez desde app.js. Ambas se auto-inicializan en DOM
 * listo y vuelven a engancharse tras cada `htmx`/navegación Alpine si hiciera
 * falta (hoy no aplica: MPA de Laravel, cada carga de página reinicia el JS).
 */

function initScrollReveal() {
    const elementos = document.querySelectorAll('[data-reveal]');
    if (!elementos.length) return;

    if (!('IntersectionObserver' in window)) {
        elementos.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entradas) => {
            entradas.forEach((entrada) => {
                if (!entrada.isIntersecting) return;
                const el = entrada.target;
                const delay = el.getAttribute('data-reveal-delay');
                el.style.animationDelay = delay ? `${delay}ms` : '0ms';
                el.classList.add('is-visible');
                observer.unobserve(el);
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
    );

    elementos.forEach((el) => observer.observe(el));
}

function initCardInteractive() {
    if (!document.querySelector('.card-interactive')) return;
    if (window.matchMedia('(hover: none)').matches) return; // táctil: sin tilt

    const MAX_TILT = 5; // grados — sutil, no gimmick

    // Un único listener delegado en document (no uno por tarjeta): más barato
    // con grids grandes (20+ restaurantes) y sirve tarjetas que se agreguen
    // después sin volver a inicializar nada.
    document.addEventListener(
        'mousemove',
        (e) => {
            const tarjeta = e.target.closest('.card-interactive');
            if (!tarjeta) return;
            const rect = tarjeta.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width;
            const py = (e.clientY - rect.top) / rect.height;

            tarjeta.style.setProperty('--x', `${px * 100}%`);
            tarjeta.style.setProperty('--y', `${py * 100}%`);
            tarjeta.style.setProperty('--ry', `${(px - 0.5) * MAX_TILT * 2}deg`);
            tarjeta.style.setProperty('--rx', `${(0.5 - py) * MAX_TILT * 2}deg`);
        },
        { passive: true }
    );

    document.addEventListener(
        'mouseout',
        (e) => {
            const tarjeta = e.target.closest('.card-interactive');
            if (!tarjeta) return;
            if (tarjeta.contains(e.relatedTarget)) return; // se movió dentro de la misma tarjeta
            tarjeta.style.setProperty('--rx', '0deg');
            tarjeta.style.setProperty('--ry', '0deg');
        },
        { passive: true }
    );
}

document.addEventListener('DOMContentLoaded', () => {
    initScrollReveal();
    initCardInteractive();
});
