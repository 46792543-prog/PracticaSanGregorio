<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoRecuperacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $codigo)
    {
    }

    public function build(): self
    {
        return $this->subject('Código para recuperar tu contraseña')
            ->view('emails.recuperacion-codigo');
    }
}
