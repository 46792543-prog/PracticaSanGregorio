<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta {{ $mesa->materia->nombre }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        .header { display: table; width: 100%; border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 14px; }
        .header .logo { display: table-cell; width: 40px; }
        .header .logo span { display: inline-block; width: 32px; height: 32px; line-height: 32px; text-align: center; border-radius: 50%; background: #1E4D8C; color: #fff; font-weight: bold; font-size: 10px; }
        .header .info { display: table-cell; vertical-align: middle; padding-left: 8px; }
        .header .info p { margin: 0; }
        .header .info .institucion { font-weight: bold; font-size: 13px; }
        .header .info .direccion { color: #64748b; font-size: 9px; }

        .fila-superior { display: table; width: 100%; margin-bottom: 10px; font-size: 11px; }
        .fila-superior .libro-folio { display: table-cell; }
        .fila-superior .fecha { display: table-cell; text-align: right; }
        .fecha span { border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 8px; }

        h1 { text-align: center; font-size: 14px; margin: 10px 0; }
        p.dato { margin: 2px 0; }

        table.alumnos { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 10px; }
        table.alumnos th, table.alumnos td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; }
        table.alumnos th { background: #f8fafc; }
        table.alumnos td.nombre { text-align: left; }

        .observaciones { margin: 14px 0; font-size: 10px; }
        .observaciones .label { font-weight: bold; color: #64748b; text-transform: uppercase; font-size: 9px; }
        .observaciones .texto { border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; min-height: 14px; }

        .totales { text-align: right; font-size: 10px; margin-bottom: 26px; }
        .totales span { margin-left: 14px; }

        table.firmas { width: 100%; border-collapse: collapse; text-align: center; font-size: 10px; }
        table.firmas td { border-top: 1px solid #1e293b; padding-top: 4px; }
        table.firmas .rol { color: #64748b; font-size: 9px; }

        .pie { text-align: center; font-size: 9px; font-weight: bold; margin-top: 20px; }
        .estado-pie { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"><span>ISG</span></div>
        <div class="info">
            <p class="institucion">Instituto Superior San Gregorio</p>
            <p class="direccion">San Pedro de Jujuy — Pedro Goyena 33 — Tel. 03888-480686</p>
        </div>
    </div>

    <div class="fila-superior">
        <div class="libro-folio">L: <strong>{{ $acta->libro ?? '—' }}</strong> &nbsp;&nbsp; F: <strong>{{ $acta->folio ?? '—' }}</strong></div>
        <div class="fecha"><span>{{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}</span></div>
    </div>

    <h1>ACTA DE EXÁMENES {{ mb_strtoupper($mesa->turnoExamen->nombre_turno) }} {{ $mesa->anioLectivo->anio }}</h1>

    <p class="dato"><strong>Carrera:</strong> {{ $mesa->materia->carrera->nombre }}.</p>
    <p class="dato"><strong>Asignatura:</strong> {{ mb_strtoupper($mesa->materia->nombre) }}</p>
    <p class="dato"><strong>Llamado:</strong> {{ $mesa->llamadoExamen->nombre_llamado }}</p>
    <p class="dato"><strong>Examen de Alumnos:</strong> REGULAR</p>

    <table class="alumnos">
        <thead>
            <tr>
                <th>N°</th>
                <th>Documento</th>
                <th>Apellido y Nombre</th>
                <th>Escrito</th>
                <th>Oral</th>
                <th>Resultado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscripcionesAceptadas as $i => $inscripcion)
                @php $detalle = $acta?->detalles->firstWhere('id_persona_alumno', $inscripcion->id_persona_alumno); @endphp
                <tr>
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>DNI {{ $inscripcion->personaAlumno->dni }}</td>
                    <td class="nombre">{{ mb_strtoupper($inscripcion->personaAlumno->apellido) }}, {{ mb_strtoupper($inscripcion->personaAlumno->nombre) }}</td>
                    <td>{{ $detalle->nota_escrito ?? '—' }}</td>
                    <td>{{ $detalle->nota_oral ?? '—' }}</td>
                    <td>
                        @if ($detalle?->nota_final)
                            {{ $detalle->nota_final }} · {{ ucfirst($detalle->resultado ?: '—') }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Nadie se inscribió a esta mesa todavía.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="observaciones">
        <p class="label">Observaciones:</p>
        <p class="texto">{{ $acta->observaciones ?? '—' }}</p>
    </div>

    <div class="totales">
        <span>TOTAL DE ALUMNOS: <strong>{{ $inscripcionesAceptadas->count() }}</strong></span>
        <span>APROBADOS: <strong>{{ $acta?->detalles->where('resultado', 'aprobado')->count() ?: '—' }}</strong></span>
        <span>DESAPROBADOS: <strong>{{ $acta?->detalles->where('resultado', 'desaprobado')->count() ?: '—' }}</strong></span>
        <span>AUSENTES: <strong>{{ $acta?->detalles->where('resultado', 'ausente')->count() ?: '—' }}</strong></span>
    </div>

    <table class="firmas">
        <tr>
            @foreach (['Presidente', 'Vocal 1', 'Vocal 2'] as $rol)
                <td style="width: 33%;">
                    {{ isset($tribunalPorRol[$rol]) ? $tribunalPorRol[$rol]->profesor->apellido . ', ' . $tribunalPorRol[$rol]->profesor->nombre : '—' }}
                    <div class="rol">{{ $rol }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    <p class="pie">
        SAN PEDRO DE JUJUY, {{ mb_strtoupper(\Illuminate\Support\Str::after(\App\Support\FechaEsp::larga($mesa->fecha_examen), ', ')) }}
    </p>

    @if ($acta?->fecha_generacion)
        <p class="estado-pie">Generada el {{ \App\Support\FechaEsp::corta($acta->fecha_generacion) }}</p>
    @else
        <p class="estado-pie">Documento generado a partir de datos aún no confirmados como "acta generada" en el sistema.</p>
    @endif
</body>
</html>
