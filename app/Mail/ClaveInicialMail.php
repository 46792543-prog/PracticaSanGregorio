<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaveInicialMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $nombre, public string $email, public string $clave)
    {
    }

    public function build(): self
    {
        return $this->subject('Tu acceso al sistema del Instituto San Gregorio')
            ->view('emails.clave-inicial');
    }
}
