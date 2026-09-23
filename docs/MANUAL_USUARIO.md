# Manual de Usuario — Mi Plataforma (Ubaté Eats)

Guía de uso de la plataforma de domicilios de comida rápida para Ubaté, Cundinamarca. Disponible como sitio web y como app móvil (Android/iOS) para clientes y domiciliarios.

## Índice

1. [Introducción](#1-introducción)
2. [Registro y cuenta](#2-registro-y-cuenta)
3. [Manual del Cliente](#3-manual-del-cliente)
4. [Manual del Restaurante](#4-manual-del-restaurante)
5. [Manual del Domiciliario](#5-manual-del-domiciliario)
6. [Manual del Administrador](#6-manual-del-administrador)
7. [Asistente virtual (chatbot)](#7-asistente-virtual-chatbot)
8. [Preguntas frecuentes](#8-preguntas-frecuentes)

---

## 1. Introducción

La plataforma conecta cuatro tipos de usuario:

- **Cliente** — pide comida a los restaurantes locales de Ubaté y sigue su pedido en un mapa en tiempo real.
- **Restaurante** — recibe y gestiona pedidos, administra su menú y asigna domiciliarios.
- **Domiciliario** — recibe entregas asignadas y las lleva usando navegación GPS.
- **Administrador** — supervisa toda la operación de la plataforma.

Cada rol tiene su propio panel (dashboard) al iniciar sesión.

## 2. Registro y cuenta

1. Entrar a la página principal y hacer clic en **Registrarse**.
2. Elegir el tipo de cuenta:
   - **Cliente** — solo nombre, correo y contraseña.
   - **Vendedor (restaurante)** — al registrarte se crea automáticamente tu perfil de restaurante para configurarlo después.
   - **Domiciliario** — pide además cédula y tipo de vehículo.
3. Tras registrarte, la plataforma te lleva directo a tu dashboard según tu rol.
4. Para iniciar sesión después, usar **Iniciar sesión** con el correo y contraseña registrados.

---

## 3. Manual del Cliente

### 3.1 Buscar un restaurante

En el dashboard, ir a **Restaurantes**. Se muestra el listado con:
- Calificación promedio (estrellas) de cada restaurante.
- Costo de domicilio ("Domicilio gratis" en verde, o el valor en pesos).
- Buscador instantáneo por nombre, descripción o dirección.

### 3.2 Hacer un pedido

1. Entrar al restaurante deseado y ver el menú organizado por categorías.
2. Agregar productos al carrito con el botón correspondiente (se actualiza al instante, sin recargar la página).
3. Si ya tienes productos de **otro** restaurante en el carrito, la plataforma te avisa y te deja elegir si reemplazar el carrito o mantener el actual.
4. Ir al **Carrito** para revisar cantidades, ajustar con los botones +/-, y ver el desglose: Subtotal, Domicilio, Total.
5. Completar la dirección de entrega y notas opcionales, y confirmar el pedido (**Checkout**).

### 3.3 Seguimiento del pedido

En **Mis pedidos** puedes ver el estado de cada pedido en una línea de tiempo:

`Pendiente → Confirmado → En preparación → En camino → Entregado`

- Mientras el restaurante prepara el pedido, el mapa muestra la dirección de entrega.
- Cuando el domiciliario sale (**En camino**), el mapa muestra su ubicación en tiempo real, la ruta hacia tu dirección, la distancia y el tiempo estimado.
- La página se actualiza sola cuando cambia el estado (no hace falta refrescar).
- Recibirás un correo en cada cambio de estado importante (confirmado, en camino, entregado o cancelado).

### 3.4 Calificar un pedido

Una vez el pedido llega a **Entregado**, aparece la opción de calificarlo: 1 a 5 estrellas más un comentario opcional. Solo se puede calificar una vez por pedido.

---

## 4. Manual del Restaurante

### 4.1 Configurar tu negocio

En **Configuración**:
- Editar nombre, descripción, teléfono, dirección e imagen del negocio.
- Activar/desactivar el restaurante (si está inactivo, no aparece para los clientes).
- Definir el **costo de domicilio** (puede ser $0 = gratis).
- Configurar horarios por día de la semana (abierto/cerrado, hora de apertura y cierre).

### 4.2 Gestionar el catálogo

En **Productos**:
- Crear producto: nombre, descripción, categoría, precio e imagen.
- Editar o eliminar productos existentes.
- Marcar un producto como disponible/no disponible sin borrarlo (útil si se agota temporalmente).

### 4.3 Gestionar pedidos entrantes

En **Pedidos**:
- Los pedidos activos aparecen primero, con opción de expandir para ver el detalle de cada uno.
- Cuando llega un pedido nuevo, aparece un **banner naranja en tiempo real** con cuenta regresiva y botón "Ver ahora" — no hace falta recargar la página.
- Flujo de estados que el restaurante controla: **confirmar** el pedido → pasarlo a **en preparación** → **asignar un domiciliario** → el pedido pasa a **en camino**.
- El cliente recibe un correo automático en cada paso.
- El historial de pedidos completados queda disponible paginado más abajo.

---

## 5. Manual del Domiciliario

### 5.1 Disponibilidad

En el dashboard hay un interruptor de **disponible / no disponible**. Solo cuando está activado puedes recibir asignaciones de pedidos.

### 5.2 Pedido activo y mapa

Cuando tienes un pedido asignado, el dashboard muestra:
- Pin **azul** = tu ubicación GPS actual.
- Pin **naranja** = dirección de entrega del cliente.
- Línea naranja = ruta real por calles hasta el destino, con distancia y tiempo estimado.
- Botones directos a **Google Maps** y **Waze** con las coordenadas ya cargadas.

### 5.3 Entregar el pedido

1. Marca que vas a recoger el pedido en el restaurante (esto avisa en vivo al restaurante).
2. En camino, tu ubicación se transmite en tiempo real al cliente, que ve tu posición en su mapa.
3. Al llegar, confirma la entrega. Si el cliente tiene un código de confirmación (tipo QR, 6 caracteres), pídeselo para validar la entrega; si no lo tiene, puedes confirmar igual.
4. El pedido pasa a **Entregado** y queda en tu historial.

---

## 6. Manual del Administrador

En el dashboard de administrador:

- **Estadísticas generales** de la plataforma (usuarios, restaurantes, pedidos).
- **Gestión de usuarios**, organizada en pestañas por rol (clientes, restaurantes, domiciliarios).
- **Activar/desactivar restaurantes** — un restaurante desactivado deja de ser visible para los clientes.
- Vista de **actividad reciente** de la plataforma.

---

## 7. Asistente virtual (chatbot)

Disponible en la esquina inferior derecha, en web y app móvil, para los 4 roles. Tócalo o haz clic para abrir el chat.

- Responde preguntas sobre **restaurantes, menús, precios, horarios (incluso "¿qué está abierto ahora?" o "¿hasta qué hora atienden?"), costo y tiempo de domicilio, calificaciones y funcionamiento de la plataforma**, usando la información real del catálogo.
- Si tienes pedidos, puedes preguntarle **"¿cómo va mi pedido?"** y te dice el estado de tus pedidos recientes.
- Recuerda lo que hablaron dentro de la misma conversación, así puedes hacer preguntas de seguimiento ("¿y cuál tiene domicilio gratis?"). En la web, al abrir el chat verás preguntas sugeridas para empezar.
- Puede recomendarte platos según lo que se te antoje (por ejemplo, "una pizza barata"). No crea ni cancela pedidos: eso se hace desde la app o la web.
- Si preguntas algo fuera de ese tema, te lo dice amablemente y te redirige a lo que sí puede ayudarte.
- La conversación **no se guarda** — si cierras el chat o recargas la página, el historial se pierde.
- Si el asistente no está disponible en ese momento, el resto de la plataforma sigue funcionando con normalidad.

---

## 8. Preguntas frecuentes

**¿Por qué no veo el mapa de seguimiento apenas hago el pedido?**
El mapa con la ruta del domiciliario solo aparece cuando el pedido pasa a "En camino". Antes de eso solo se muestra tu dirección de entrega.

**¿Puedo cambiar de restaurante con productos ya en el carrito?**
Sí, la plataforma te avisa del conflicto y te deja elegir si vaciar el carrito actual para agregar del nuevo restaurante.

**¿Qué pasa si no llega el correo de confirmación?**
Revisa la carpeta de spam. Si el problema persiste, contacta al restaurante o al soporte de la plataforma.

**¿Puedo calificar un pedido más de una vez?**
No, solo se permite una calificación por pedido, y únicamente después de que fue marcado como entregado.

**Como domiciliario, ¿qué pasa si el cliente no tiene el código de confirmación?**
No es obligatorio. Puedes confirmar la entrega sin código; el código solo agrega una validación extra cuando el cliente lo tiene disponible.
