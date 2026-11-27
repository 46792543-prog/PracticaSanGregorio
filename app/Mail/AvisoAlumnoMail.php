<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Correo que Secretaría le envía a un alumno (asunto y mensaje escritos o revisados por ella).
class AvisoAlumnoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $asunto, public string $mensaje)
    {
    }

    public function build(): self
    {
        return $this->subject($this->asunto)->view('emails.aviso-alumno');
    }
}
