<footer class="landing-footer">
    <div class="landing-contenedor">
        <div class="landing-footer__grid">
            <div>
                <div class="landing-logo" style="margin-bottom:14px;">
                    <span class="landing-logo__marca" aria-hidden="true">
                        @if ($config?->logo_url)
                            <img src="{{ $config->logo_url }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="20" height="20">
                                <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                            </svg>
                        @endif
                    </span>
                    <span class="landing-logo__texto">
                        <strong style="color:#fff;">INSTITUTO SUPERIOR</strong>
                        <span>{{ mb_strtoupper($config?->nombre_institucion ?? 'SAN GREGORIO') }}</span>
                    </span>
                </div>
                <p style="font-size:.88rem;color:#9fb0cc;line-height:1.6;">
                    Una comunidad educativa comprometida con la formación en salud, humana y profesional de sus estudiantes.
                </p>
            </div>
            <div>
                <p class="landing-footer__titulo">Accesos</p>
                <a href="#inicio" class="landing-footer__link">Inicio</a>
                <a href="#nosotros" class="landing-footer__link">Quiénes somos</a>
                <a href="#servicios" class="landing-footer__link">Información</a>
                <a href="#carreras" class="landing-footer__link">Carreras</a>
                <a href="#ubicacion" class="landing-footer__link">Ubicación</a>
            </div>
            <div>
                <p class="landing-footer__titulo">Contacto</p>
                <p class="landing-footer__link">{{ $config?->direccion ?: 'San Gregorio' }}</p>
                <p class="landing-footer__link">{{ $config?->telefono_contacto ?: '(388) 000-0000' }}</p>
                <p class="landing-footer__link">{{ $config?->email_contacto ?: 'info@institutosangregorio.edu.ar' }}</p>
            </div>
            <div>
                <p class="landing-footer__titulo">Servicios</p>
                <a href="{{ route('login') }}" class="landing-footer__link">Campus virtual</a>
                <a href="#ubicacion" class="landing-footer__link">Inscripciones</a>
                <a href="#ubicacion" class="landing-footer__link">Biblioteca</a>
            </div>
        </div>
    </div>
</footer>
