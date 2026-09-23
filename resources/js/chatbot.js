import Alpine from 'alpinejs';

Alpine.data('chatbotWidget', (restauranteId = null) => ({
    open: false,
    cargando: false,
    mensaje: '',
    restauranteId,
    mensajes: [
        { rol: 'bot', texto: '¡Hola! Soy el asistente de Mi Plataforma. Pregúntame sobre restaurantes, menús o precios 🍔' },
    ],

    sugerencias: [
        '¿Qué restaurantes están abiertos ahora?',
        '¿Cuáles tienen domicilio gratis?',
        '¿Cómo va mi pedido?',
    ],

    async enviar(textoRapido = null) {
        const texto = (textoRapido ?? this.mensaje).trim();
        if (!texto || this.cargando) return;

        // Turnos previos (sin el saludo inicial) para que entienda preguntas de seguimiento.
        const historial = this.mensajes.slice(1).slice(-8).map(m => ({ rol: m.rol, texto: m.texto }));

        this.mensajes.push({ rol: 'user', texto });
        this.mensaje = '';
        this.cargando = true;
        this.$nextTick(() => this.scrollAbajo());

        try {
            const res = await fetch('/chatbot/mensaje', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    mensaje: texto,
                    restaurante_id: this.restauranteId || null,
                    historial,
                }),
            });

            if (!res.ok) throw new Error('Respuesta no válida del servidor');

            const data = await res.json();
            this.mensajes.push({ rol: 'bot', texto: data.respuesta });
        } catch (e) {
            this.mensajes.push({ rol: 'bot', texto: 'Tuve un problema para responder. Intenta de nuevo en un momento.' });
        } finally {
            this.cargando = false;
            this.$nextTick(() => this.scrollAbajo());
        }
    },

    scrollAbajo() {
        const el = this.$refs.mensajesEl;
        if (el) el.scrollTop = el.scrollHeight;
    },
}));
