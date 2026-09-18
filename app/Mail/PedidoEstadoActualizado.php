<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoEstadoActualizado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function envelope(): Envelope
    {
        $asunto = match($this->pedido->estado) {
            'confirmado' => '✅ Tu pedido #' . $this->pedido->id . ' fue confirmado',
            'en_camino'  => '🛵 Tu pedido #' . $this->pedido->id . ' está en camino',
            'entregado'  => '🎉 Tu pedido #' . $this->pedido->id . ' fue entregado',
            'cancelado'  => '❌ Tu pedido #' . $this->pedido->id . ' fue cancelado',
            default      => 'Actualización de tu pedido #' . $this->pedido->id,
        };

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pedido-estado');
    }

    public function attachments(): array
    {
        return [];
    }
}
