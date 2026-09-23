<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de demo para pruebas más grandes/realistas: 20 restaurantes (cada uno
 * con su propio usuario `restaurante`) con 20 productos cada uno (80 platos
 * fuertes/entradas propios por cocina + 8 bebidas/postres comunes,
 * reutilizados tal cual haría cualquier negocio real).
 *
 * Los nombres de restaurante están elegidos a propósito para calzar con el
 * mapa de palabras clave → categoría/emoji de
 * `resources/views/cliente/restaurantes/index.blade.php` (ej. "Pizzería..."
 * → 🍕 Pizza), así el filtro por categoría también queda poblado.
 *
 * Idempotente: usa `firstOrCreate`, así que correrlo varias veces no duplica
 * usuarios/restaurantes/productos.
 */
class RestaurantesDemoSeeder extends Seeder
{
    // Centro real de Ubaté (parque principal), igual que
    // `app_movil/lib/services/fake/fake_data.dart` (_ubateLat/_ubateLng), para
    // que los pines de la demo caigan dentro del casco urbano.
    private const UBATE_LAT = 5.30859;
    private const UBATE_LNG = -73.81430;

    public function run(): void
    {
        $bebidas = [
            ['nombre' => 'Gaseosa 400ml', 'categoria' => 'Bebidas', 'precio' => 3500],
            ['nombre' => 'Limonada natural', 'categoria' => 'Bebidas', 'precio' => 4500],
            ['nombre' => 'Jugo en agua (mango o maracuyá)', 'categoria' => 'Bebidas', 'precio' => 4000],
            ['nombre' => 'Agua en botella 600ml', 'categoria' => 'Bebidas', 'precio' => 2500],
        ];

        $postres = [
            ['nombre' => 'Torta de chocolate', 'categoria' => 'Postres', 'precio' => 6000],
            ['nombre' => 'Flan de vainilla', 'categoria' => 'Postres', 'precio' => 5000],
            ['nombre' => 'Brownie con helado', 'categoria' => 'Postres', 'precio' => 7000],
            ['nombre' => 'Gelatina de mora', 'categoria' => 'Postres', 'precio' => 3500],
        ];

        $restaurantes = [
            [
                'nombre' => 'Asadero La Brasa Ubatense',
                'descripcion' => 'Pollo y carnes asadas al carbón, receta de la casa desde hace 15 años.',
                'direccion' => 'Carrera 6 #8-20, Ubaté',
                'costo_domicilio' => 3000, 'tiempo_entrega_min' => 30, 'tiempo_preparacion_min' => 20,
                'entradas' => [
                    ['nombre' => 'Papa criolla con suero', 'categoria' => 'Entradas', 'precio' => 5000],
                    ['nombre' => 'Chicharrón crocante', 'categoria' => 'Entradas', 'precio' => 6000],
                    ['nombre' => 'Arepa boyacense', 'categoria' => 'Entradas', 'precio' => 4000],
                ],
                'principales' => [
                    ['nombre' => 'Pollo asado 1/4', 'precio' => 14000],
                    ['nombre' => 'Pollo asado 1/2', 'precio' => 22000],
                    ['nombre' => 'Pollo asado entero', 'precio' => 38000],
                    ['nombre' => 'Costillas de cerdo BBQ', 'precio' => 26000],
                    ['nombre' => 'Combo mixto (pollo + costilla)', 'precio' => 32000],
                    ['nombre' => 'Bandeja de asado surtido', 'precio' => 35000],
                    ['nombre' => 'Chorizo santarrosano', 'precio' => 8000],
                    ['nombre' => 'Morcilla asada', 'precio' => 7000],
                    ['nombre' => 'Papas a la francesa grandes', 'precio' => 9000],
                ],
            ],
            [
                'nombre' => 'Pizzería Nápoles Ubaté',
                'descripcion' => 'Pizza artesanal horneada en horno de leña, masa madre 48 horas.',
                'direccion' => 'Calle 7 #5-14, Ubaté',
                'costo_domicilio' => 3500, 'tiempo_entrega_min' => 35, 'tiempo_preparacion_min' => 25,
                'entradas' => [
                    ['nombre' => 'Pan de ajo', 'categoria' => 'Entradas', 'precio' => 6000],
                    ['nombre' => 'Palitos de queso', 'categoria' => 'Entradas', 'precio' => 8000],
                    ['nombre' => 'Ensalada César', 'categoria' => 'Entradas', 'precio' => 9000],
                ],
                'principales' => [
                    ['nombre' => 'Pizza Margarita mediana', 'precio' => 22000],
                    ['nombre' => 'Pizza Hawaiana mediana', 'precio' => 24000],
                    ['nombre' => 'Pizza Pepperoni mediana', 'precio' => 25000],
                    ['nombre' => 'Pizza Cuatro Quesos mediana', 'precio' => 27000],
                    ['nombre' => 'Pizza Vegetariana mediana', 'precio' => 23000],
                    ['nombre' => 'Pizza Familiar Especial', 'precio' => 38000],
                    ['nombre' => 'Pizza BBQ de Pollo', 'precio' => 26000],
                    ['nombre' => 'Calzone de jamón y queso', 'precio' => 18000],
                    ['nombre' => 'Lasaña de carne', 'precio' => 19000],
                ],
            ],
            [
                'nombre' => 'Burger House Urbano',
                'descripcion' => 'Hamburguesas artesanales con pan brioche y carne 100% de res.',
                'direccion' => 'Carrera 8 #10-30, Ubaté',
                'costo_domicilio' => 3000, 'tiempo_entrega_min' => 25, 'tiempo_preparacion_min' => 18,
                'entradas' => [
                    ['nombre' => 'Papas con queso y tocineta', 'categoria' => 'Entradas', 'precio' => 9000],
                    ['nombre' => 'Aros de cebolla', 'categoria' => 'Entradas', 'precio' => 7000],
                    ['nombre' => 'Nuggets de pollo x6', 'categoria' => 'Entradas', 'precio' => 10000],
                ],
                'principales' => [
                    ['nombre' => 'Hamburguesa Clásica', 'precio' => 14000],
                    ['nombre' => 'Hamburguesa Doble Carne', 'precio' => 18000],
                    ['nombre' => 'Hamburguesa BBQ Bacon', 'precio' => 19000],
                    ['nombre' => 'Hamburguesa de Pollo Crispy', 'precio' => 15000],
                    ['nombre' => 'Hamburguesa Vegetariana', 'precio' => 15000],
                    ['nombre' => 'Hamburguesa Especial de la Casa', 'precio' => 21000],
                    ['nombre' => 'Combo Clásica + Papas + Gaseosa', 'precio' => 19000],
                    ['nombre' => 'Hot dog especial', 'precio' => 10000],
                    ['nombre' => 'Perro maicito', 'precio' => 9000],
                ],
            ],
            [
                'nombre' => 'Taquería El Azteca',
                'descripcion' => 'Sabor mexicano auténtico, tortillas hechas a mano cada día.',
                'direccion' => 'Calle 9 #6-18, Ubaté',
                'costo_domicilio' => 3500, 'tiempo_entrega_min' => 30, 'tiempo_preparacion_min' => 20,
                'entradas' => [
                    ['nombre' => 'Nachos con queso', 'categoria' => 'Entradas', 'precio' => 9000],
                    ['nombre' => 'Guacamole con totopos', 'categoria' => 'Entradas', 'precio' => 10000],
                    ['nombre' => 'Elote asado', 'categoria' => 'Entradas', 'precio' => 6000],
                ],
                'principales' => [
                    ['nombre' => 'Tacos de carne asada x3', 'precio' => 14000],
                    ['nombre' => 'Tacos al pastor x3', 'precio' => 14000],
                    ['nombre' => 'Tacos de pollo x3', 'precio' => 13000],
                    ['nombre' => 'Burrito de carne', 'precio' => 16000],
                    ['nombre' => 'Quesadilla de pollo', 'precio' => 13000],
                    ['nombre' => 'Fajitas de res', 'precio' => 22000],
                    ['nombre' => 'Enchiladas verdes', 'precio' => 17000],
                    ['nombre' => 'Taco bowl', 'precio' => 15000],
                    ['nombre' => 'Chile con carne', 'precio' => 16000],
                ],
            ],
            [
                'nombre' => 'Sushi Sakura Ubaté',
                'descripcion' => 'Sushi fresco y comida japonesa, ingredientes traídos a diario.',
                'direccion' => 'Carrera 5 #9-42, Ubaté',
                'costo_domicilio' => 4000, 'tiempo_entrega_min' => 35, 'tiempo_preparacion_min' => 25,
                'entradas' => [
                    ['nombre' => 'Sopa miso', 'categoria' => 'Entradas', 'precio' => 7000],
                    ['nombre' => 'Edamame', 'categoria' => 'Entradas', 'precio' => 8000],
                    ['nombre' => 'Gyoza x5', 'categoria' => 'Entradas', 'precio' => 11000],
                ],
                'principales' => [
                    ['nombre' => 'California roll (8 pz)', 'precio' => 18000],
                    ['nombre' => 'Philadelphia roll (8 pz)', 'precio' => 20000],
                    ['nombre' => 'Spicy Tuna roll (8 pz)', 'precio' => 21000],
                    ['nombre' => 'Combo 20 piezas variado', 'precio' => 35000],
                    ['nombre' => 'Nigiri de salmón x4', 'precio' => 16000],
                    ['nombre' => 'Tempura de camarón', 'precio' => 19000],
                    ['nombre' => 'Poke bowl de salmón', 'precio' => 24000],
                    ['nombre' => 'Ramen de pollo', 'precio' => 20000],
                    ['nombre' => 'Yakisoba de res', 'precio' => 19000],
                ],
            ],
            [
                'nombre' => 'Café del Parque',
                'descripcion' => 'Café de especialidad y comida rápida frente al parque principal.',
                'direccion' => 'Calle 5 #4-10, Ubaté',
                'costo_domicilio' => 2000, 'tiempo_entrega_min' => 20, 'tiempo_preparacion_min' => 10,
                'entradas' => [
                    ['nombre' => 'Croissant de jamón y queso', 'categoria' => 'Entradas', 'precio' => 6000],
                    ['nombre' => 'Tostadas francesas', 'categoria' => 'Entradas', 'precio' => 7000],
                    ['nombre' => 'Muffin de arándanos', 'categoria' => 'Entradas', 'precio' => 5000],
                ],
                'principales' => [
                    ['nombre' => 'Café Americano', 'precio' => 4000],
                    ['nombre' => 'Café Latte', 'precio' => 5500],
                    ['nombre' => 'Capuchino', 'precio' => 5500],
                    ['nombre' => 'Mocachino', 'precio' => 6000],
                    ['nombre' => 'Chocolate caliente', 'precio' => 5000],
                    ['nombre' => 'Sandwich club', 'precio' => 12000],
                    ['nombre' => 'Ensalada de pollo', 'precio' => 13000],
                    ['nombre' => 'Wrap vegetariano', 'precio' => 11000],
                    ['nombre' => 'Bagel con queso crema', 'precio' => 7000],
                ],
            ],
            [
                'nombre' => 'Panadería San José',
                'descripcion' => 'Pan recién horneado todo el día, desde las 5 a.m.',
                'direccion' => 'Carrera 7 #3-25, Ubaté',
                'costo_domicilio' => 2000, 'tiempo_entrega_min' => 20, 'tiempo_preparacion_min' => 10,
                'entradas' => [
                    ['nombre' => 'Pan de queso x4', 'categoria' => 'Entradas', 'precio' => 4000],
                    ['nombre' => 'Almojábana x2', 'categoria' => 'Entradas', 'precio' => 3500],
                    ['nombre' => 'Buñuelo x3', 'categoria' => 'Entradas', 'precio' => 3000],
                ],
                'principales' => [
                    ['nombre' => 'Pan francés', 'precio' => 2000],
                    ['nombre' => 'Pan integral', 'precio' => 2500],
                    ['nombre' => 'Croissant sencillo', 'precio' => 3500],
                    ['nombre' => 'Torta de pan (libra)', 'precio' => 9000],
                    ['nombre' => 'Pandebono x4', 'precio' => 4000],
                    ['nombre' => 'Empanada de pollo', 'precio' => 2500],
                    ['nombre' => 'Empanada de carne', 'precio' => 2500],
                    ['nombre' => 'Pastel de pollo', 'precio' => 4500],
                    ['nombre' => 'Torta de queso', 'precio' => 5000],
                ],
            ],
            [
                'nombre' => 'Postres Encanto',
                'descripcion' => 'Repostería fina para toda ocasión, tortas por encargo.',
                'direccion' => 'Calle 4 #7-11, Ubaté',
                'costo_domicilio' => 2500, 'tiempo_entrega_min' => 25, 'tiempo_preparacion_min' => 15,
                'entradas' => [
                    ['nombre' => 'Mini brownies x3', 'categoria' => 'Entradas', 'precio' => 6000],
                    ['nombre' => 'Fresas con crema', 'categoria' => 'Entradas', 'precio' => 8000],
                    ['nombre' => 'Copa de frutas', 'categoria' => 'Entradas', 'precio' => 7000],
                ],
                'principales' => [
                    ['nombre' => 'Torta de tres leches (porción)', 'precio' => 7000],
                    ['nombre' => 'Cheesecake de fresa (porción)', 'precio' => 7500],
                    ['nombre' => 'Torta de chocolate especial (porción)', 'precio' => 8000],
                    ['nombre' => 'Milhojas', 'precio' => 6500],
                    ['nombre' => 'Torta de zanahoria (porción)', 'precio' => 7000],
                    ['nombre' => 'Arroz con leche', 'precio' => 5000],
                    ['nombre' => 'Obleas con arequipe', 'precio' => 4000],
                    ['nombre' => 'Torta de cumpleaños personalizada', 'precio' => 45000],
                    ['nombre' => 'Copa brownie con helado', 'precio' => 8500],
                ],
            ],
            [
                'nombre' => 'Heladería Polo Norte',
                'descripcion' => 'Helados artesanales y postres fríos, más de 20 sabores.',
                'direccion' => 'Carrera 9 #6-05, Ubaté',
                'costo_domicilio' => 2500, 'tiempo_entrega_min' => 20, 'tiempo_preparacion_min' => 10,
                'entradas' => [
                    ['nombre' => 'Barquillo sencillo', 'categoria' => 'Entradas', 'precio' => 4000],
                    ['nombre' => 'Vasito de helado pequeño', 'categoria' => 'Entradas', 'precio' => 4500],
                    ['nombre' => 'Malteada pequeña', 'categoria' => 'Entradas', 'precio' => 6000],
                ],
                'principales' => [
                    ['nombre' => 'Copa de helado 3 sabores', 'precio' => 9000],
                    ['nombre' => 'Sundae de chocolate', 'precio' => 8500],
                    ['nombre' => 'Banana split', 'precio' => 11000],
                    ['nombre' => 'Malteada grande', 'precio' => 9000],
                    ['nombre' => 'Helado en cono doble', 'precio' => 6500],
                    ['nombre' => 'Copa especial Polo Norte', 'precio' => 13000],
                    ['nombre' => 'Paleta artesanal', 'precio' => 5000],
                    ['nombre' => 'Milkshake de fresa', 'precio' => 8500],
                    ['nombre' => 'Postre helado con brownie', 'precio' => 10000],
                ],
            ],
            [
                'nombre' => 'Pastas Bella Italia',
                'descripcion' => 'Pasta fresca casera y salsas tradicionales italianas.',
                'direccion' => 'Calle 6 #8-33, Ubaté',
                'costo_domicilio' => 3500, 'tiempo_entrega_min' => 30, 'tiempo_preparacion_min' => 20,
                'entradas' => [
                    ['nombre' => 'Bruschetta', 'categoria' => 'Entradas', 'precio' => 8000],
                    ['nombre' => 'Sopa minestrone', 'categoria' => 'Entradas', 'precio' => 9000],
                    ['nombre' => 'Ensalada caprese', 'categoria' => 'Entradas', 'precio' => 10000],
                ],
                'principales' => [
                    ['nombre' => 'Espagueti a la boloñesa', 'precio' => 17000],
                    ['nombre' => 'Fettuccine alfredo', 'precio' => 18000],
                    ['nombre' => 'Ravioles de ricotta', 'precio' => 19000],
                    ['nombre' => 'Lasaña clásica', 'precio' => 19000],
                    ['nombre' => 'Penne al pesto', 'precio' => 16000],
                    ['nombre' => 'Spaghetti carbonara', 'precio' => 18000],
                    ['nombre' => 'Risotto de champiñones', 'precio' => 20000],
                    ['nombre' => 'Ñoquis con salsa de tomate', 'precio' => 17000],
                    ['nombre' => 'Pasta especial de la casa', 'precio' => 22000],
                ],
            ],
            [
                'nombre' => 'Parrilla El Fogón',
                'descripcion' => 'Carnes a la parrilla al estilo llanero, término a elección.',
                'direccion' => 'Carrera 4 #9-50, Ubaté',
                'costo_domicilio' => 4000, 'tiempo_entrega_min' => 35, 'tiempo_preparacion_min' => 25,
                'entradas' => [
                    ['nombre' => 'Chorizo a la parrilla', 'categoria' => 'Entradas', 'precio' => 8000],
                    ['nombre' => 'Mazorca asada', 'categoria' => 'Entradas', 'precio' => 6000],
                    ['nombre' => 'Papas criollas', 'categoria' => 'Entradas', 'precio' => 7000],
                ],
                'principales' => [
                    ['nombre' => 'Churrasco', 'precio' => 28000],
                    ['nombre' => 'Punta de anca', 'precio' => 26000],
                    ['nombre' => 'Baby beef', 'precio' => 30000],
                    ['nombre' => 'Pechuga a la plancha', 'precio' => 18000],
                    ['nombre' => 'Mixta parrillera', 'precio' => 35000],
                    ['nombre' => 'Costillas BBQ', 'precio' => 27000],
                    ['nombre' => 'Chuleta de cerdo', 'precio' => 20000],
                    ['nombre' => 'Trucha a la plancha', 'precio' => 22000],
                    ['nombre' => 'Bandeja parrillera completa', 'precio' => 42000],
                ],
            ],
            [
                'nombre' => 'Mariscos La Marea',
                'descripcion' => 'Pescados y mariscos frescos, sabor de la costa en Ubaté.',
                'direccion' => 'Calle 10 #5-22, Ubaté',
                'costo_domicilio' => 4000, 'tiempo_entrega_min' => 35, 'tiempo_preparacion_min' => 25,
                'entradas' => [
                    ['nombre' => 'Ceviche de camarón', 'categoria' => 'Entradas', 'precio' => 14000],
                    ['nombre' => 'Cóctel de camarón', 'categoria' => 'Entradas', 'precio' => 13000],
                    ['nombre' => 'Patacones con hogao', 'categoria' => 'Entradas', 'precio' => 7000],
                ],
                'principales' => [
                    ['nombre' => 'Cazuela de mariscos', 'precio' => 28000],
                    ['nombre' => 'Arroz con camarones', 'precio' => 22000],
                    ['nombre' => 'Pescado frito entero', 'precio' => 24000],
                    ['nombre' => 'Filete de pescado a la plancha', 'precio' => 23000],
                    ['nombre' => 'Camarones al ajillo', 'precio' => 25000],
                    ['nombre' => 'Paella marinera', 'precio' => 30000],
                    ['nombre' => 'Sancocho de pescado', 'precio' => 20000],
                    ['nombre' => 'Combo mar y tierra', 'precio' => 32000],
                    ['nombre' => 'Langostinos apanados', 'precio' => 27000],
                ],
            ],
            [
                'nombre' => 'Arepas Doña Rosa',
                'descripcion' => 'Arepas rellenas hechas al momento, receta familiar.',
                'direccion' => 'Carrera 3 #6-40, Ubaté',
                'costo_domicilio' => 2000, 'tiempo_entrega_min' => 20, 'tiempo_preparacion_min' => 12,
                'entradas' => [
                    ['nombre' => 'Arepa con queso', 'categoria' => 'Entradas', 'precio' => 3500],
                    ['nombre' => 'Arepa de choclo', 'categoria' => 'Entradas', 'precio' => 4000],
                    ['nombre' => 'Buñuelo', 'categoria' => 'Entradas', 'precio' => 2000],
                ],
                'principales' => [
                    ['nombre' => 'Arepa reina pepiada', 'precio' => 9000],
                    ['nombre' => 'Arepa de pollo desmechado', 'precio' => 8500],
                    ['nombre' => 'Arepa de carne mechada', 'precio' => 9000],
                    ['nombre' => 'Arepa mixta', 'precio' => 10000],
                    ['nombre' => 'Arepa de chicharrón', 'precio' => 9500],
                    ['nombre' => 'Combo 2 arepas surtidas', 'precio' => 16000],
                    ['nombre' => 'Arepa con huevo', 'precio' => 5000],
                    ['nombre' => 'Arepa boyacense con queso', 'precio' => 5500],
                    ['nombre' => 'Arepa gigante familiar', 'precio' => 20000],
                ],
            ],
            [
                'nombre' => 'Desayunos La Madrugada',
                'descripcion' => 'Desayunos típicos colombianos desde las 5:30 a.m.',
                'direccion' => 'Calle 3 #9-15, Ubaté',
                'costo_domicilio' => 2500, 'tiempo_entrega_min' => 25, 'tiempo_preparacion_min' => 15,
                'entradas' => [
                    ['nombre' => 'Jugo de naranja natural', 'categoria' => 'Entradas', 'precio' => 4000],
                    ['nombre' => 'Caldo de costilla', 'categoria' => 'Entradas', 'precio' => 7000],
                    ['nombre' => 'Chocolate con pan y queso', 'categoria' => 'Entradas', 'precio' => 6000],
                ],
                'principales' => [
                    ['nombre' => 'Calentado paisa', 'precio' => 12000],
                    ['nombre' => 'Huevos pericos con arepa', 'precio' => 8000],
                    ['nombre' => 'Huevos al gusto con tostadas', 'precio' => 8500],
                    ['nombre' => 'Desayuno campesino', 'precio' => 13000],
                    ['nombre' => 'Changua con huevo', 'precio' => 7500],
                    ['nombre' => 'Tamal santafereño', 'precio' => 9000],
                    ['nombre' => 'Caldo de pollo', 'precio' => 7000],
                    ['nombre' => 'Desayuno americano', 'precio' => 11000],
                    ['nombre' => 'Desayuno especial La Madrugada', 'precio' => 15000],
                ],
            ],
            [
                'nombre' => 'Perros Calientes El Crack',
                'descripcion' => 'Perros y salchipapas cargados, el favorito de la noche en Ubaté.',
                'direccion' => 'Carrera 6 #4-18, Ubaté',
                'costo_domicilio' => 2500, 'tiempo_entrega_min' => 22, 'tiempo_preparacion_min' => 12,
                'entradas' => [
                    ['nombre' => 'Papas fritas porción', 'categoria' => 'Entradas', 'precio' => 5000],
                    ['nombre' => 'Salchipapa pequeña', 'categoria' => 'Entradas', 'precio' => 8000],
                    ['nombre' => 'Chorizo sencillo', 'categoria' => 'Entradas', 'precio' => 5000],
                ],
                'principales' => [
                    ['nombre' => 'Perro sencillo', 'precio' => 7000],
                    ['nombre' => 'Perro especial con todo', 'precio' => 10000],
                    ['nombre' => 'Perro americano', 'precio' => 11000],
                    ['nombre' => 'Salchipapa completa', 'precio' => 15000],
                    ['nombre' => 'Perro ranchero', 'precio' => 12000],
                    ['nombre' => 'Choripapa', 'precio' => 12000],
                    ['nombre' => 'Hamburguesa criolla', 'precio' => 13000],
                    ['nombre' => 'Combo perro + gaseosa', 'precio' => 12000],
                    ['nombre' => 'Perro El Crack (doble salchicha)', 'precio' => 18000],
                ],
            ],
            [
                'nombre' => 'Sándwiches Gourmet Central',
                'descripcion' => 'Sandwiches gourmet con pan artesanal e ingredientes frescos.',
                'direccion' => 'Calle 8 #4-27, Ubaté',
                'costo_domicilio' => 3000, 'tiempo_entrega_min' => 25, 'tiempo_preparacion_min' => 15,
                'entradas' => [
                    ['nombre' => 'Sopa del día', 'categoria' => 'Entradas', 'precio' => 7000],
                    ['nombre' => 'Ensalada mixta', 'categoria' => 'Entradas', 'precio' => 8000],
                    ['nombre' => 'Papas rústicas', 'categoria' => 'Entradas', 'precio' => 6000],
                ],
                'principales' => [
                    ['nombre' => 'Sandwich de pollo', 'precio' => 13000],
                    ['nombre' => 'Sandwich de jamón y queso', 'precio' => 11000],
                    ['nombre' => 'Club sandwich', 'precio' => 15000],
                    ['nombre' => 'Sandwich vegetariano', 'precio' => 12000],
                    ['nombre' => 'Sandwich de pavo', 'precio' => 14000],
                    ['nombre' => 'Sandwich BBQ de res', 'precio' => 16000],
                    ['nombre' => 'Croissant relleno', 'precio' => 12000],
                    ['nombre' => 'Wrap de pollo', 'precio' => 13000],
                    ['nombre' => 'Sandwich especial gourmet', 'precio' => 18000],
                ],
            ],
            [
                'nombre' => 'Jugos Tropifrut',
                'descripcion' => 'Jugos y batidos naturales con fruta fresca de la región.',
                'direccion' => 'Carrera 7 #10-08, Ubaté',
                'costo_domicilio' => 2000, 'tiempo_entrega_min' => 18, 'tiempo_preparacion_min' => 8,
                'entradas' => [
                    ['nombre' => 'Fruta picada', 'categoria' => 'Entradas', 'precio' => 5000],
                    ['nombre' => 'Ensalada de frutas', 'categoria' => 'Entradas', 'precio' => 7000],
                    ['nombre' => 'Cóctel de frutas con helado', 'categoria' => 'Entradas', 'precio' => 8000],
                ],
                'principales' => [
                    ['nombre' => 'Jugo de mora en agua', 'precio' => 4000],
                    ['nombre' => 'Jugo de lulo en leche', 'precio' => 5500],
                    ['nombre' => 'Jugo de fresa en leche', 'precio' => 5500],
                    ['nombre' => 'Jugo de mango biche', 'precio' => 4500],
                    ['nombre' => 'Smoothie de banano', 'precio' => 6000],
                    ['nombre' => 'Jugo verde detox', 'precio' => 7000],
                    ['nombre' => 'Limonada de coco', 'precio' => 6500],
                    ['nombre' => 'Batido de frutos rojos', 'precio' => 6500],
                    ['nombre' => 'Jugo especial Tropifrut', 'precio' => 7500],
                ],
            ],
            [
                'nombre' => 'Pollo Broaster Express',
                'descripcion' => 'Pollo broaster crocante, combos rápidos para toda la familia.',
                'direccion' => 'Calle 11 #6-45, Ubaté',
                'costo_domicilio' => 3000, 'tiempo_entrega_min' => 25, 'tiempo_preparacion_min' => 18,
                'entradas' => [
                    ['nombre' => 'Papas broaster', 'categoria' => 'Entradas', 'precio' => 6000],
                    ['nombre' => 'Yuca frita', 'categoria' => 'Entradas', 'precio' => 5000],
                    ['nombre' => 'Ensalada coleslaw', 'categoria' => 'Entradas', 'precio' => 5000],
                ],
                'principales' => [
                    ['nombre' => 'Combo 1 presa + papas', 'precio' => 12000],
                    ['nombre' => 'Combo 2 presas + papas', 'precio' => 16000],
                    ['nombre' => 'Pollo broaster 4 presas', 'precio' => 24000],
                    ['nombre' => 'Pollo broaster 8 presas', 'precio' => 42000],
                    ['nombre' => 'Alitas broaster x8', 'precio' => 18000],
                    ['nombre' => 'Alitas BBQ x8', 'precio' => 19000],
                    ['nombre' => 'Pollo apanado entero', 'precio' => 36000],
                    ['nombre' => 'Combo familiar broaster', 'precio' => 48000],
                    ['nombre' => 'Nuggets broaster x10', 'precio' => 14000],
                ],
            ],
            [
                'nombre' => 'Burritos Fronterizos',
                'descripcion' => 'Burritos y bowls estilo tex-mex, porciones grandes.',
                'direccion' => 'Carrera 9 #3-12, Ubaté',
                'costo_domicilio' => 3500, 'tiempo_entrega_min' => 28, 'tiempo_preparacion_min' => 18,
                'entradas' => [
                    ['nombre' => 'Totopos con queso', 'categoria' => 'Entradas', 'precio' => 8000],
                    ['nombre' => 'Frijoles refritos', 'categoria' => 'Entradas', 'precio' => 5000],
                    ['nombre' => 'Ensalada mexicana', 'categoria' => 'Entradas', 'precio' => 7000],
                ],
                'principales' => [
                    ['nombre' => 'Burrito de carne', 'precio' => 16000],
                    ['nombre' => 'Burrito de pollo', 'precio' => 15000],
                    ['nombre' => 'Burrito vegetariano', 'precio' => 13000],
                    ['nombre' => 'Burrito supremo', 'precio' => 18000],
                    ['nombre' => 'Bowl mexicano de carne', 'precio' => 16000],
                    ['nombre' => 'Bowl mexicano de pollo', 'precio' => 15000],
                    ['nombre' => 'Quesabirria x3', 'precio' => 18000],
                    ['nombre' => 'Torta mexicana', 'precio' => 14000],
                    ['nombre' => 'Nachos supremos', 'precio' => 16000],
                ],
            ],
            [
                'nombre' => 'Panadería y Café Aroma',
                'descripcion' => 'Panadería y café en un mismo lugar, ideal para media mañana.',
                'direccion' => 'Calle 2 #8-09, Ubaté',
                'costo_domicilio' => 2000, 'tiempo_entrega_min' => 20, 'tiempo_preparacion_min' => 10,
                'entradas' => [
                    ['nombre' => 'Pan de yuca x4', 'categoria' => 'Entradas', 'precio' => 4000],
                    ['nombre' => 'Croissant de nutella', 'categoria' => 'Entradas', 'precio' => 5000],
                    ['nombre' => 'Muffin de chocolate', 'categoria' => 'Entradas', 'precio' => 4500],
                ],
                'principales' => [
                    ['nombre' => 'Café Americano', 'precio' => 4000],
                    ['nombre' => 'Cappuccino Aroma', 'precio' => 6000],
                    ['nombre' => 'Chocolate santafereño', 'precio' => 5500],
                    ['nombre' => 'Sandwich de pollo', 'precio' => 12000],
                    ['nombre' => 'Torta de naranja', 'precio' => 5500],
                    ['nombre' => 'Pan artesanal integral', 'precio' => 6000],
                    ['nombre' => 'Empanada de queso', 'precio' => 2500],
                    ['nombre' => 'Croissant de jamón y queso', 'precio' => 6500],
                    ['nombre' => 'Combo café + pan', 'precio' => 8500],
                ],
            ],
        ];

        $creados = 0;

        foreach ($restaurantes as $i => $data) {
            $n = $i + 1; // 1..20

            $user = User::firstOrCreate(
                ['email' => "demo-vendedor{$n}@test.com"],
                [
                    'name' => $data['nombre'],
                    'password' => Hash::make('password'),
                    'telefono' => sprintf('320 %03d %04d', $n, $n * 111 % 10000),
                    'direccion' => $data['direccion'],
                    'nombre_negocio' => $data['nombre'],
                ]
            );
            if (! $user->hasRole('restaurante')) {
                $user->assignRole('restaurante');
            }

            // Grilla 5x4 alrededor del parque principal para repartir los pines
            // dentro del casco urbano (mismo orden de magnitud que la app móvil).
            $col = $i % 5;
            $fila = intdiv($i, 5);
            $lat = self::UBATE_LAT + ($fila - 1.5) * 0.0016;
            $lng = self::UBATE_LNG + ($col - 2) * 0.0016;

            $restaurante = Restaurante::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nombre' => $data['nombre'],
                    'descripcion' => $data['descripcion'],
                    'direccion' => $data['direccion'],
                    'telefono' => $user->telefono,
                    'activo' => true,
                    'costo_domicilio' => $data['costo_domicilio'],
                    'tiempo_entrega_min' => $data['tiempo_entrega_min'],
                    'tiempo_preparacion_min' => $data['tiempo_preparacion_min'],
                    'lat' => $lat,
                    'lng' => $lng,
                    'horarios' => $this->horariosEstandar(),
                ]
            );

            $productos = array_merge(
                $data['entradas'],
                array_map(
                    fn ($p) => ['nombre' => $p['nombre'], 'categoria' => 'Platos fuertes', 'precio' => $p['precio']],
                    $data['principales']
                ),
                $bebidas,
                $postres,
            );

            foreach ($productos as $p) {
                Producto::firstOrCreate(
                    ['restaurante_id' => $restaurante->id, 'nombre' => $p['nombre']],
                    [
                        'categoria' => $p['categoria'],
                        'precio' => $p['precio'],
                        'disponible' => true,
                    ]
                );
            }

            $creados++;
        }

        $this->command->info("✓ {$creados} restaurantes demo con 20 productos cada uno (o ya existían).");
    }

    /** Lunes a sábado 08:00-20:00, domingo 09:00-15:00 (media jornada). */
    private function horariosEstandar(): array
    {
        $normal = ['abierto' => true, 'apertura' => '08:00', 'cierre' => '20:00'];

        return [
            'lunes' => $normal,
            'martes' => $normal,
            'miercoles' => $normal,
            'jueves' => $normal,
            'viernes' => $normal,
            'sabado' => $normal,
            'domingo' => ['abierto' => true, 'apertura' => '09:00', 'cierre' => '15:00'],
        ];
    }
}
