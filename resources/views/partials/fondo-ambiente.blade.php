{{-- Fondo ambiental global de la app autenticada: 3 orbs a la deriva, fijos
     detrás de todo el contenido. Antes cada vista (dashboard, welcome...)
     reimplementaba su propia versión de esto en un <style> inline; ahora es
     una sola capa compartida del design system (ver .fondo-ambiente en
     resources/css/app.css) incluida una vez desde layouts/app.blade.php. --}}
<div class="fondo-ambiente" aria-hidden="true">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>
