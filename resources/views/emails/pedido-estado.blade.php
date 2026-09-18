<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de pedido</title>
</head>
<body style="margin:0;padding:0;background:#0f0f11;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#0f0f11;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                {{-- Header / Logo --}}
                <tr>
                    <td align="center" style="padding-bottom:28px;">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="background:linear-gradient(135deg,#F25C2E,#e04020);width:40px;height:40px;border-radius:10px;text-align:center;vertical-align:middle;">
                                    <span style="color:white;font-size:20px;line-height:40px;">🍴</span>
                                </td>
                                <td style="padding-left:10px;vertical-align:middle;">
                                    <span style="color:white;font-size:18px;font-weight:900;letter-spacing:-0.5px;">Mi Plataforma</span><br>
                                    <span style="color:#6b7280;font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;">UBATÉ · DELIVERY</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Estado banner --}}
                @php
                    $config = match($pedido->estado) {
                        'confirmado' => ['emoji'=>'✅','titulo'=>'Pedido confirmado','color'=>'#10B981','bg'=>'#052e16','texto'=>'El restaurante recibió y confirmó tu pedido. ¡Ya están preparándolo!'],
                        'en_camino'  => ['emoji'=>'🛵','titulo'=>'Tu pedido está en camino','color'=>'#F25C2E','bg'=>'#2d1200','texto'=>'Tu domiciliario ya salió con tu pedido. Puedes seguirlo en la app.'],
                        'entregado'  => ['emoji'=>'🎉','titulo'=>'¡Pedido entregado!','color'=>'#10B981','bg'=>'#052e16','texto'=>'Tu pedido llegó. ¡Esperamos que lo disfrutes! No olvides calificarlo.'],
                        'cancelado'  => ['emoji'=>'❌','titulo'=>'Pedido cancelado','color'=>'#EF4444','bg'=>'#2d0707','texto'=>'Lamentablemente tu pedido fue cancelado. Si tienes dudas, contáctanos.'],
                        default      => ['emoji'=>'📦','titulo'=>'Actualización de tu pedido','color'=>'#F25C2E','bg'=>'#1a0a00','texto'=>'Hubo una actualización en tu pedido.'],
                    };
                @endphp

                {{-- Card principal --}}
                <tr>
                    <td style="background:#18181b;border-radius:16px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;">

                        {{-- Banner de estado --}}
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="background:{{ $config['bg'] }};border-bottom:1px solid rgba(255,255,255,0.06);padding:24px 28px;text-align:center;">
                                    <div style="font-size:40px;margin-bottom:10px;">{{ $config['emoji'] }}</div>
                                    <h1 style="margin:0;color:{{ $config['color'] }};font-size:20px;font-weight:900;">{{ $config['titulo'] }}</h1>
                                    <p style="margin:8px 0 0;color:#9ca3af;font-size:14px;line-height:1.5;">{{ $config['texto'] }}</p>
                                </td>
                            </tr>
                        </table>

                        {{-- Detalles del pedido --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 28px;">
                            <tr>
                                <td>
                                    <p style="margin:0 0 16px;color:#6b7280;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;">Detalle del pedido</p>

                                    <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f0f11;border-radius:10px;border:1px solid rgba(255,255,255,0.06);overflow:hidden;">
                                        {{-- Restaurante --}}
                                        <tr>
                                            <td colspan="2" style="padding:12px 16px;border-bottom:1px solid rgba(255,255,255,0.05);">
                                                <span style="color:#9ca3af;font-size:12px;">Pedido en </span>
                                                <span style="color:white;font-size:13px;font-weight:700;">{{ $pedido->restaurante->nombre }}</span>
                                                <span style="color:#6b7280;font-size:12px;"> · #{{ $pedido->id }}</span>
                                            </td>
                                        </tr>
                                        {{-- Items --}}
                                        @foreach($pedido->items as $item)
                                        <tr>
                                            <td style="padding:9px 16px;color:#d1d5db;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.04);">
                                                <span style="color:#6b7280;">×{{ $item->cantidad }}</span> {{ $item->nombre_producto }}
                                            </td>
                                            <td style="padding:9px 16px;color:white;font-size:13px;font-weight:700;text-align:right;border-bottom:1px solid rgba(255,255,255,0.04);">
                                                ${{ number_format($item->subtotal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                        {{-- Total --}}
                                        <tr>
                                            <td style="padding:12px 16px;color:#9ca3af;font-size:13px;font-weight:600;">
                                                @if($pedido->costo_domicilio > 0)
                                                    Domicilio: ${{ number_format($pedido->costo_domicilio, 0, ',', '.') }}
                                                @else
                                                    Domicilio gratis
                                                @endif
                                            </td>
                                            <td style="padding:12px 16px;text-align:right;">
                                                <span style="color:#F25C2E;font-size:16px;font-weight:900;">
                                                    ${{ number_format($pedido->total, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Dirección --}}
                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;background:#0f0f11;border-radius:10px;border:1px solid rgba(255,255,255,0.06);">
                                        <tr>
                                            <td style="padding:12px 16px;">
                                                <span style="color:#6b7280;font-size:12px;">📍 Entrega en: </span>
                                                <span style="color:#d1d5db;font-size:13px;">{{ $pedido->direccion_entrega }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- CTA --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="padding:0 28px 28px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ route('cliente.pedidos.show', $pedido) }}"
                                       style="display:inline-block;background:linear-gradient(135deg,#F25C2E,#e04020);color:white;font-size:14px;font-weight:800;text-decoration:none;padding:14px 32px;border-radius:12px;letter-spacing:0.3px;box-shadow:0 4px 20px rgba(242,92,46,0.4);">
                                        Ver mi pedido →
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td align="center" style="padding-top:24px;">
                        <p style="margin:0;color:#4b5563;font-size:12px;">
                            Mi Plataforma · Domicilios en Ubaté, Cundinamarca<br>
                            <span style="font-size:11px;">Este email fue enviado a {{ $pedido->cliente->email }}</span>
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
