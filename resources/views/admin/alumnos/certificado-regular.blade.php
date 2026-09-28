<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Alumno Regular - {{ $alumno->apellido }}, {{ $alumno->nombre }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { display: table; width: 100%; border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 30px; }
        .header .logo { display: table-cell; width: 40px; }
        .header .logo span { display: inline-block; width: 32px; height: 32px; line-height: 32px; text-align: center; border-radius: 50%; background: #1E4D8C; color: #fff; font-weight: bold; font-size: 10px; }
        .header .logo img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
        .header .info { display: table-cell; vertical-align: middle; padding-left: 8px; }
        .header .info p { margin: 0; }
        .header .info .institucion { font-weight: bold; font-size: 13px; }
        .header .info .direccion { color: #64748b; font-size: 9px; }

        h1 { text-align: center; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 40px; }

        .cuerpo { font-size: 13px; line-height: 1.9; text-align: justify; margin: 0 30px 60px; }
        .cuerpo strong { text-transform: uppercase; }

        .lugar-fecha { text-align: center; font-size: 11px; margin-bottom: 70px; }

        table.firmas { width: 100%; border-collapse: collapse; text-align: center; font-size: 10px; margin-top: 40px; }
        table.firmas td { border-top: 1px solid #1e293b; padding-top: 4px; width: 50%; }
        table.firmas .rol { color: #64748b; font-size: 9px; }

        .estado-pie { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            @if ($config?->logo_url)
                <img src="{{ $config->logo_url }}" alt="Logo">
            @else
                <span>ISG</span>
            @endif
        </div>
        <div class="info">
            <p class="institucion">{{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</p>
            <p class="direccion">{{ $config?->direccion ?? 'San Pedro de Jujuy — Pedro Goyena 33 — Tel. 03888-480686' }}</p>
        </div>
    </div>

    <h1>Certificado de Alumno Regular</h1>

    <div class="cuerpo">
        <p>
            La Dirección de <strong>{{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</strong> certifica que
            <strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong>, DNI N° {{ $alumno->dni }},
            se encuentra inscripto/a como <strong>alumno/a regular</strong> de la carrera
            <strong>{{ $inscripcion->carrera->nombre_carrera }}</strong>
            @if ($inscripcion->anioCursada)
                , {{ $inscripcion->anioCursada->nombre_anio }}
            @endif
            @if ($inscripcion->turnoCursada)
                , turno {{ $inscripcion->turnoCursada->nombre_turno }}
            @endif
            , correspondiente al ciclo lectivo {{ $inscripcion->anioLectivo->anio }}, con fecha de inscripción
            {{ \App\Support\FechaEsp::corta($inscripcion->fecha_inscripcion) }}.
        </p>
        <p>Se extiende el presente certificado a solicitud del interesado/a, para ser presentado ante quien corresponda.</p>
    </div>

    <p class="lugar-fecha">San Pedro de Jujuy, {{ \App\Support\FechaEsp::larga(now()) }}</p>

    <table class="firmas">
        <tr>
            <td>
                &nbsp;
                <div class="rol">Secretaría</div>
            </td>
            <td>
                {{ $config?->nombre_director ?? '' }}
                <div class="rol">Dirección General</div>
            </td>
        </tr>
    </table>

    <p class="estado-pie">Documento generado el {{ \App\Support\FechaEsp::corta(now()) }} desde el Panel de Gestión — {{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</p>
</body>
</html>
