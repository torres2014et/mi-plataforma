<?php

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Restaurante;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private function restaurante(): Restaurante
    {
        return Restaurante::firstOrCreate(
            ['user_id' => auth()->id()],
            ['nombre' => auth()->user()->name, 'activo' => true]
        );
    }

    public function index()
    {
        $restaurante = $this->restaurante();
        $rid = $restaurante->id;

        $hoy          = Carbon::today();
        $inicioSemana = Carbon::today()->subDays(6);   // últimos 7 días
        $inicioMes    = Carbon::today()->startOfMonth();

        // Ingreso del restaurante = la comida (total - domicilio), solo de
        // pedidos ENTREGADOS. El domicilio es para el repartidor, no del local.
        $ingresoExpr = 'COALESCE(total,0) - COALESCE(costo_domicilio,0)';

        // ── KPIs ──────────────────────────────────────────────
        $pedidosHoy = Pedido::where('restaurante_id', $rid)
            ->whereDate('created_at', $hoy)
            ->where('estado', '!=', 'cancelado')
            ->count();

        $ingresosHoy = (float) Pedido::where('restaurante_id', $rid)
            ->where('estado', 'entregado')
            ->whereDate('created_at', $hoy)
            ->selectRaw("SUM($ingresoExpr) as s")->value('s');

        $ingresosSemana = (float) Pedido::where('restaurante_id', $rid)
            ->where('estado', 'entregado')
            ->where('created_at', '>=', $inicioSemana)
            ->selectRaw("SUM($ingresoExpr) as s")->value('s');

        $ingresosMes = (float) Pedido::where('restaurante_id', $rid)
            ->where('estado', 'entregado')
            ->where('created_at', '>=', $inicioMes)
            ->selectRaw("SUM($ingresoExpr) as s")->value('s');

        $entregadosMes = Pedido::where('restaurante_id', $rid)
            ->where('estado', 'entregado')
            ->where('created_at', '>=', $inicioMes)
            ->count();

        $ticketPromedio   = $entregadosMes > 0 ? $ingresosMes / $entregadosMes : 0;
        $productosActivos = $restaurante->productos()->where('disponible', true)->count();
        $totalProductos   = $restaurante->productos()->count();
        $pendientes       = Pedido::where('restaurante_id', $rid)
            ->whereNotIn('estado', ['entregado', 'cancelado'])->count();

        // ── Top productos (por unidades vendidas, de pedidos entregados) ──
        $topProductos = PedidoItem::query()
            ->join('pedidos', 'pedidos.id', '=', 'pedido_items.pedido_id')
            ->where('pedidos.restaurante_id', $rid)
            ->where('pedidos.estado', 'entregado')
            ->selectRaw('pedido_items.nombre_producto as nombre,
                         SUM(pedido_items.cantidad) as unidades,
                         SUM(pedido_items.subtotal) as ingresos')
            ->groupBy('pedido_items.nombre_producto')
            ->orderByDesc('unidades')
            ->limit(8)
            ->get();

        // ── Ventas de los últimos 7 días (para la mini-gráfica) ──
        $porDia = Pedido::where('restaurante_id', $rid)
            ->where('estado', 'entregado')
            ->where('created_at', '>=', $inicioSemana)
            ->selectRaw("DATE(created_at) as dia, COUNT(*) as pedidos, SUM($ingresoExpr) as ingresos")
            ->groupBy('dia')->get()->keyBy('dia');

        $diasEs = ['D', 'L', 'M', 'X', 'J', 'V', 'S']; // dom..sáb
        $ventasSemana = [];
        for ($i = 6; $i >= 0; $i--) {
            $d   = Carbon::today()->subDays($i);
            $key = $d->toDateString();
            $ventasSemana[] = [
                'label'    => $diasEs[$d->dayOfWeek],
                'esHoy'    => $i === 0,
                'pedidos'  => (int) ($porDia[$key]->pedidos ?? 0),
                'ingresos' => (float) ($porDia[$key]->ingresos ?? 0),
            ];
        }

        // ── Actividad reciente ──
        $actividadReciente = Pedido::where('restaurante_id', $rid)
            ->with(['cliente', 'items'])
            ->latest()->limit(8)->get();

        return view('restaurante.dashboard', compact(
            'restaurante', 'pedidosHoy', 'ingresosHoy', 'ingresosSemana', 'ingresosMes',
            'ticketPromedio', 'productosActivos', 'totalProductos', 'pendientes',
            'topProductos', 'ventasSemana', 'actividadReciente'
        ));
    }
}
