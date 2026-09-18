import '../../models/estado_pedido.dart';
import '../../models/grupo_opciones.dart';
import '../../models/medio_transporte.dart';
import '../../models/opcion_producto.dart';
import '../../models/pedido.dart';
import '../../models/pedido_item.dart';
import '../../models/producto.dart';
import '../../models/restaurante.dart';

/// Datos de prueba quemados (Fase 1). Todo inventado, ambientado en Ubaté.
///
/// Cuando se conecte el backend real, esta clase desaparece y los datos
/// llegan de la API; las pantallas no cambian porque siempre piden a los
/// services, nunca a esta clase directamente.
class FakeData {
  FakeData._();

  // Centro real del casco urbano de Ubaté (OpenStreetMap, place=town ≈ parque
  // principal, Calle 6 #4-93). Base para las entregas de prueba.
  static const double _ubateLat = 5.30859;
  static const double _ubateLng = -73.81430;

  // Tamaño de una cuadra de Ubaté en grados (~95 m). Las coordenadas de cada
  // restaurante se derivan de su dirección con este paso, tomando el parque
  // como ancla (Calle 6, Carrera ~4.5): las calles aumentan hacia el norte
  // (+lat = (calle-6)·_cuadra) y las carreras hacia el occidente
  // (-lng = (carrera-4.5)·_cuadra). Modelo aproximado pero coherente y dentro
  // del núcleo urbano real; en Fase 2 el backend manda las coordenadas exactas
  // y nada de la UI cambia.
  static const double _cuadra = 0.00086;

  /// Foto de comida **curada** (Fase 1, sin backend): [id] es el identificador
  /// de una foto de Unsplash, elegida y verificada a mano para que coincida con
  /// el plato (una hamburguesa muestra una hamburguesa, etc.). Requiere internet
  /// (igual que los mapas); si falla, la UI cae al degradado del placeholder.
  /// En Fase 2 esto lo reemplaza el `imagen_url` que mande el backend.
  static String _foto(String id) =>
      'https://images.unsplash.com/photo-$id?w=640&q=70&auto=format&fit=crop';

  /// Imagen del restaurante con [id] (para los pedidos semilla, que muestran la
  /// foto del restaurante en "Mis pedidos").
  static String? _imgRestaurante(int id) =>
      restaurantes.firstWhere((r) => r.id == id).imagenUrl;

  /// Opciones compartidas por las pizzas (tamaño obligatorio + adiciones).
  /// [base] = id del producto, para que los ids de grupos/opciones no choquen.
  static List<GrupoOpciones> _opcionesPizza(int base) => [
        GrupoOpciones(
          id: base * 10 + 1,
          nombre: 'Tamaño',
          seleccionMin: 1,
          seleccionMax: 1,
          opciones: [
            OpcionProducto(
                id: base * 100 + 11,
                nombre: 'Personal (25 cm)',
                porDefecto: true),
            OpcionProducto(
                id: base * 100 + 12,
                nombre: 'Mediana (32 cm)',
                precioExtra: 8000),
            OpcionProducto(
                id: base * 100 + 13,
                nombre: 'Familiar (40 cm)',
                precioExtra: 16000),
          ],
        ),
        GrupoOpciones(
          id: base * 10 + 2,
          nombre: 'Adiciones',
          seleccionMin: 0,
          seleccionMax: 4,
          opciones: [
            OpcionProducto(
                id: base * 100 + 21, nombre: 'Extra queso', precioExtra: 4000),
            OpcionProducto(
                id: base * 100 + 22, nombre: 'Champiñones', precioExtra: 3000),
            OpcionProducto(
                id: base * 100 + 23,
                nombre: 'Borde de queso',
                precioExtra: 6000),
          ],
        ),
      ];

  // ---------------------------------------------------------------------------
  // RESTAURANTES (con su menú)
  // ---------------------------------------------------------------------------
  static final List<Restaurante> restaurantes = [
    Restaurante(
      id: 1,
      nombre: 'Burger House Ubaté',
      descripcion: 'Hamburguesas artesanales y papas a la francesa.',
      categoria: 'Hamburguesas',
      imagenUrl: _foto('1568901346375-23c9450c58cd'),
      rating: 4.7,
      tiempoEntregaMin: 25,
      tiempoPreparacionMin: 18,
      costoDomicilio: 4000,
      direccion: 'Cra. 7 #6-32, Ubaté',
      // Carrera 7 (oeste), altura Calle 6.32.
      lat: _ubateLat + (6.32 - 6) * _cuadra, // 5.30887
      lng: _ubateLng - (7 - 4.5) * _cuadra, // -73.81645
      productos: [
        Producto(
          id: 101,
          restauranteId: 1,
          nombre: 'Hamburguesa Clásica',
          imagenUrl: _foto('1571091718767-18b5b1457add'),
          descripcion: 'Carne 150g, queso, lechuga, tomate y salsa de la casa.',
          precio: 16000,
          categoria: 'Hamburguesas',
          gruposOpciones: const [
            GrupoOpciones(
              id: 9101,
              nombre: 'Ingredientes',
              seleccionMin: 0,
              seleccionMax: 4,
              opciones: [
                OpcionProducto(id: 91011, nombre: 'Lechuga', porDefecto: true),
                OpcionProducto(id: 91012, nombre: 'Tomate', porDefecto: true),
                OpcionProducto(id: 91013, nombre: 'Cebolla', porDefecto: true),
                OpcionProducto(
                    id: 91014, nombre: 'Salsa de la casa', porDefecto: true),
              ],
            ),
            GrupoOpciones(
              id: 9102,
              nombre: 'Punto de la carne',
              seleccionMin: 1,
              seleccionMax: 1,
              opciones: [
                OpcionProducto(
                    id: 91021, nombre: 'Término medio', porDefecto: true),
                OpcionProducto(id: 91022, nombre: 'Tres cuartos'),
                OpcionProducto(id: 91023, nombre: 'Bien asada'),
              ],
            ),
            GrupoOpciones(
              id: 9103,
              nombre: 'Adiciones',
              seleccionMin: 0,
              seleccionMax: 4,
              opciones: [
                OpcionProducto(
                    id: 91031, nombre: 'Tocineta', precioExtra: 3000),
                OpcionProducto(
                    id: 91032, nombre: 'Queso extra', precioExtra: 2500),
                OpcionProducto(id: 91033, nombre: 'Huevo', precioExtra: 2000),
                OpcionProducto(
                    id: 91034, nombre: 'Aguacate', precioExtra: 3000),
              ],
            ),
          ],
        ),
        Producto(
          id: 102,
          restauranteId: 1,
          nombre: 'Hamburguesa Doble Tocineta',
          imagenUrl: _foto('1553979459-d2229ba7433b'),
          descripcion: 'Doble carne, doble queso y tocineta crocante.',
          precio: 24000,
          categoria: 'Hamburguesas',
          gruposOpciones: const [
            GrupoOpciones(
              id: 9111,
              nombre: 'Ingredientes',
              seleccionMin: 0,
              seleccionMax: 4,
              opciones: [
                OpcionProducto(id: 91111, nombre: 'Lechuga', porDefecto: true),
                OpcionProducto(id: 91112, nombre: 'Tomate', porDefecto: true),
                OpcionProducto(id: 91113, nombre: 'Cebolla', porDefecto: true),
                OpcionProducto(
                    id: 91114, nombre: 'Salsa BBQ', porDefecto: true),
              ],
            ),
            GrupoOpciones(
              id: 9112,
              nombre: 'Adiciones',
              seleccionMin: 0,
              seleccionMax: 4,
              opciones: [
                OpcionProducto(
                    id: 91121, nombre: 'Tocineta extra', precioExtra: 3500),
                OpcionProducto(
                    id: 91122, nombre: 'Queso extra', precioExtra: 2500),
                OpcionProducto(
                    id: 91123, nombre: 'Aros de cebolla', precioExtra: 4000),
              ],
            ),
          ],
        ),
        Producto(
          id: 103,
          restauranteId: 1,
          nombre: 'Papas a la Francesa',
          imagenUrl: _foto('1573080496219-bb080dd4f877'),
          descripcion: 'Porción grande con salsas.',
          precio: 8000,
          categoria: 'Acompañamientos',
        ),
        Producto(
          id: 104,
          restauranteId: 1,
          nombre: 'Gaseosa 400ml',
          imagenUrl: _foto('1581636625402-29b2a704ef13'),
          descripcion: 'Bebida fría a elección.',
          precio: 4500,
          categoria: 'Bebidas',
          gruposOpciones: const [
            GrupoOpciones(
              id: 9141,
              nombre: 'Sabor',
              seleccionMin: 1,
              seleccionMax: 1,
              opciones: [
                OpcionProducto(id: 91411, nombre: 'Cola', porDefecto: true),
                OpcionProducto(id: 91412, nombre: 'Naranja'),
                OpcionProducto(id: 91413, nombre: 'Manzana'),
                OpcionProducto(id: 91414, nombre: 'Uva'),
              ],
            ),
          ],
        ),
      ],
    ),
    Restaurante(
      id: 2,
      nombre: 'Pizzería Don Italo',
      descripcion: 'Pizza al horno de leña, masa madre.',
      categoria: 'Pizza',
      imagenUrl: _foto('1513104890138-7c749659a591'),
      rating: 4.5,
      tiempoEntregaMin: 35,
      tiempoPreparacionMin: 25,
      costoDomicilio: 5000,
      direccion: 'Calle 8 #5-14, Ubaté',
      // Calle 8 (norte), altura Carrera 5.14.
      lat: _ubateLat + (8 - 6) * _cuadra, // 5.31031
      lng: _ubateLng - (5.14 - 4.5) * _cuadra, // -73.81485
      productos: [
        Producto(
          id: 201,
          restauranteId: 2,
          nombre: 'Pizza Margarita',
          imagenUrl: _foto('1604068549290-dea0e4a305ca'),
          descripcion: 'Salsa de tomate, mozzarella y albahaca.',
          precio: 28000,
          categoria: 'Pizzas',
          gruposOpciones: _opcionesPizza(201),
        ),
        Producto(
          id: 202,
          restauranteId: 2,
          nombre: 'Pizza Hawaiana',
          imagenUrl: _foto('1565299624946-b28f40a0ae38'),
          descripcion: 'Jamón, piña y mozzarella.',
          precio: 32000,
          categoria: 'Pizzas',
          gruposOpciones: _opcionesPizza(202),
        ),
        Producto(
          id: 203,
          restauranteId: 2,
          nombre: 'Pizza Pepperoni',
          imagenUrl: _foto('1628840042765-356cda07504e'),
          descripcion: 'Doble pepperoni y queso.',
          precio: 34000,
          categoria: 'Pizzas',
          gruposOpciones: _opcionesPizza(203),
        ),
        Producto(
          id: 204,
          restauranteId: 2,
          nombre: 'Limonada Natural',
          imagenUrl: _foto('1621263764928-df1444c5e859'),
          descripcion: 'Jarra de limonada de la casa.',
          precio: 9000,
          categoria: 'Bebidas',
        ),
      ],
    ),
    Restaurante(
      id: 3,
      nombre: 'Asadero El Buen Sabor',
      descripcion: 'Pollo asado, broaster y costillas BBQ.',
      categoria: 'Asados',
      imagenUrl: _foto('1598103442097-8b74394b95c6'),
      rating: 4.8,
      tiempoEntregaMin: 30,
      tiempoPreparacionMin: 22,
      costoDomicilio: 3500,
      direccion: 'Cra. 5 #9-20, Ubaté',
      // Carrera 5 (oeste), altura Calle 9.20.
      lat: _ubateLat + (9.20 - 6) * _cuadra, // 5.31134
      lng: _ubateLng - (5 - 4.5) * _cuadra, // -73.81473
      productos: [
        Producto(
          id: 301,
          restauranteId: 3,
          nombre: 'Pollo Asado Entero',
          imagenUrl: _foto('1598103442097-8b74394b95c6'),
          descripcion: 'Con papas, arepa y ensalada.',
          precio: 38000,
          categoria: 'Pollo',
          gruposOpciones: const [
            GrupoOpciones(
              id: 3011,
              nombre: 'Acompañamiento',
              seleccionMin: 1,
              seleccionMax: 1,
              opciones: [
                OpcionProducto(
                    id: 30111, nombre: 'Papa a la francesa', porDefecto: true),
                OpcionProducto(id: 30112, nombre: 'Papa criolla'),
                OpcionProducto(id: 30113, nombre: 'Yuca frita'),
                OpcionProducto(id: 30114, nombre: 'Ensalada extra'),
              ],
            ),
            GrupoOpciones(
              id: 3012,
              nombre: 'Salsas',
              seleccionMin: 0,
              seleccionMax: 3,
              opciones: [
                OpcionProducto(id: 30121, nombre: 'Ají', porDefecto: true),
                OpcionProducto(id: 30122, nombre: 'Mostaza y miel'),
                OpcionProducto(id: 30123, nombre: 'BBQ'),
              ],
            ),
          ],
        ),
        Producto(
          id: 302,
          restauranteId: 3,
          nombre: 'Cuarto de Pollo',
          imagenUrl: _foto('1626082927389-6cd097cdc6ec'),
          descripcion: 'Pierna y muslo con papa criolla.',
          precio: 14000,
          categoria: 'Pollo',
        ),
        Producto(
          id: 303,
          restauranteId: 3,
          nombre: 'Costillas BBQ',
          imagenUrl: _foto('1544025162-d76694265947'),
          descripcion: 'Costillas de cerdo en salsa BBQ.',
          precio: 30000,
          categoria: 'Cerdo',
        ),
        Producto(
          id: 304,
          restauranteId: 3,
          nombre: 'Jugo de Mora',
          imagenUrl: _foto('1553530666-ba11a7da3888'),
          descripcion: 'Vaso de jugo natural en agua.',
          precio: 5000,
          categoria: 'Bebidas',
        ),
      ],
    ),
    Restaurante(
      id: 4,
      nombre: 'Sushi Ubaté',
      descripcion: 'Rollos y bowls japoneses, ingredientes frescos.',
      categoria: 'Japonesa',
      imagenUrl: _foto('1579871494447-9811cf80d66c'),
      rating: 4.3,
      tiempoEntregaMin: 40,
      tiempoPreparacionMin: 28,
      costoDomicilio: 6000,
      direccion: 'Calle 6 #7-50, Ubaté',
      // Calle 6 (sobre el parque), altura Carrera 7.50.
      lat: _ubateLat + (6 - 6) * _cuadra, // 5.30859
      lng: _ubateLng - (7.50 - 4.5) * _cuadra, // -73.81688
      abierto: false, // cerrado por ahora, para probar ese estado en la UI
      productos: [
        Producto(
          id: 401,
          restauranteId: 4,
          nombre: 'California Roll x8',
          imagenUrl: _foto('1579871494447-9811cf80d66c'),
          descripcion: 'Cangrejo, aguacate y pepino.',
          precio: 22000,
          categoria: 'Rollos',
        ),
        Producto(
          id: 402,
          restauranteId: 4,
          nombre: 'Tempura Roll x8',
          imagenUrl: _foto('1617196034796-73dfa7b1fd56'),
          descripcion: 'Camarón tempura y queso crema.',
          precio: 26000,
          categoria: 'Rollos',
        ),
        Producto(
          id: 403,
          restauranteId: 4,
          nombre: 'Gyozas x5',
          imagenUrl: _foto('1496116218417-1a781b1c416c'),
          descripcion: 'Empanaditas japonesas de cerdo.',
          precio: 16000,
          categoria: 'Entradas',
        ),
      ],
    ),
    Restaurante(
      id: 5,
      nombre: 'La Lechona Express',
      descripcion: 'Lechona tolimense y tamales caseros.',
      categoria: 'Típica',
      imagenUrl: _foto('1432139555190-58524dae6a55'),
      rating: 4.6,
      tiempoEntregaMin: 20,
      tiempoPreparacionMin: 12,
      costoDomicilio: 3000,
      direccion: 'Cra. 6 #4-18, Ubaté',
      // Carrera 6 (oeste), altura Calle 4.18.
      lat: _ubateLat + (4.18 - 6) * _cuadra, // 5.30702
      lng: _ubateLng - (6 - 4.5) * _cuadra, // -73.81559
      productos: [
        Producto(
          id: 501,
          restauranteId: 5,
          nombre: 'Lechona Personal',
          imagenUrl: _foto('1432139555190-58524dae6a55'),
          descripcion: 'Con arepa y gaseosa.',
          precio: 15000,
          categoria: 'Platos',
        ),
        Producto(
          id: 502,
          restauranteId: 5,
          nombre: 'Tamal Tolimense',
          imagenUrl:
              'https://images.pexels.com/photos/14179987/pexels-photo-14179987.jpeg?auto=compress&cs=tinysrgb&w=640',
          descripcion: 'Tamal grande con todo.',
          precio: 12000,
          categoria: 'Platos',
        ),
        Producto(
          id: 503,
          restauranteId: 5,
          nombre: 'Mazamorra',
          imagenUrl: _foto('1488477181946-6428a0291777'),
          descripcion: 'Postre tradicional.',
          precio: 6000,
          categoria: 'Postres',
        ),
      ],
    ),
  ];

  // ---------------------------------------------------------------------------
  // PEDIDOS de ejemplo (en distintos estados)
  // ---------------------------------------------------------------------------
  static final List<Pedido> pedidos = [
    // 1) Entregado → sirve para probar la calificación.
    Pedido(
      id: 9001,
      restauranteId: 3,
      restauranteNombre: 'Asadero El Buen Sabor',
      restauranteImagenUrl: _imgRestaurante(3),
      clienteId: 1,
      domiciliarioId: 2,
      medioTransporte: MedioTransporte.auto,
      estado: EstadoPedido.entregado,
      direccionEntrega: 'Cra. 8 #10-45, Ubaté',
      latEntrega: _ubateLat + 0.008,
      lngEntrega: _ubateLng - 0.005,
      createdAt: DateTime.now().subtract(const Duration(days: 1, hours: 2)),
      costoDomicilio: 3500,
      subtotal: 52000,
      total: 55500,
      calificado: false,
      items: const [
        PedidoItem(
          id: 1,
          productoId: 301,
          nombreProducto: 'Pollo Asado Entero',
          cantidad: 1,
          precioUnitario: 38000,
        ),
        PedidoItem(
          id: 2,
          productoId: 302,
          nombreProducto: 'Cuarto de Pollo',
          cantidad: 1,
          precioUnitario: 14000,
        ),
      ],
    ),

    // 2) En preparación → recién confirmado.
    Pedido(
      id: 9003,
      restauranteId: 2,
      restauranteNombre: 'Pizzería Don Italo',
      restauranteImagenUrl: _imgRestaurante(2),
      clienteId: 1,
      medioTransporte: MedioTransporte.bici,
      estado: EstadoPedido.enPreparacion,
      direccionEntrega: 'Cra. 9 #7-10, Ubaté',
      latEntrega: _ubateLat - 0.004,
      lngEntrega: _ubateLng + 0.007,
      createdAt: DateTime.now().subtract(const Duration(minutes: 8)),
      costoDomicilio: 5000,
      subtotal: 66000,
      total: 71000,
      codigoConfirmacion: 'PIZZA1',
      items: const [
        PedidoItem(
          id: 5,
          productoId: 203,
          nombreProducto: 'Pizza Pepperoni',
          cantidad: 1,
          precioUnitario: 34000,
        ),
        PedidoItem(
          id: 6,
          productoId: 202,
          nombreProducto: 'Pizza Hawaiana',
          cantidad: 1,
          precioUnitario: 32000,
        ),
      ],
    ),
  ];
}
