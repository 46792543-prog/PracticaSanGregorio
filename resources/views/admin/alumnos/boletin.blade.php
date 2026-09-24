<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín de Calificaciones - {{ $alumno->apellido }}, {{ $alumno->nombre }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        .header { display: table; width: 100%; border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 14px; }
        .header .logo { display: table-cell; width: 40px; }
        .header .logo span { display: inline-block; width: 32px; height: 32px; line-height: 32px; text-align: center; border-radius: 50%; background: #1E4D8C; color: #fff; font-weight: bold; font-size: 10px; }
        .header .logo img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
        .header .info { display: table-cell; vertical-align: middle; padding-left: 8px; }
        .header .info p { margin: 0; }
        .header .info .institucion { font-weight: bold; font-size: 13px; }
        .header .info .direccion { color: #64748b; font-size: 9px; }

        h1 { text-align: center; font-size: 14px; margin: 10px 0 4px; }

        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10px; }
        table.datos td { border: 1px solid #cbd5e1; padding: 6px 10px; }
        table.datos td.label { color: #64748b; text-transform: uppercase; font-size: 9px; }

        table.materias { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        table.materias th, table.materias td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; }
        table.materias th { background: #f8fafc; text-transform: uppercase; font-size: 8.5px; color: #64748b; }
        table.materias td.centro { text-align: center; }

        .badge { border-radius: 3px; padding: 1px 6px; font-size: 8.5px; font-weight: bold; }
        .badge-aprobada { background: #d1fae5; color: #047857; }
        .badge-regular { background: #dbeafe; color: #1d4ed8; }
        .badge-cursando { background: #fef3c7; color: #b45309; }
        .badge-pendiente { background: #f1f5f9; color: #64748b; }

        .pie { text-align: center; font-size: 9px; font-weight: bold; margin-top: 20px; }
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
            <p class="direccion">Boletín de Calificaciones</p>
        </div>
    </div>

    <h1>BOLETÍN DE CALIFICACIONES</h1>

    <table class="datos">
        <tr>
            <td class="label">Alumno/a<br><strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong></td>
            <td class="label">DNI<br><strong>{{ $alumno->dni }}</strong></td>
            <td class="label">Carrera<br><strong>{{ $alumno->inscripcionesCarrera->first()?->carrera?->nombre_carrera ?? '—' }}</strong></td>
        </tr>
    </table>

    <table class="materias">
        <thead>
            <tr>
                <th>Año lectivo</th>
                <th>Materia</th>
                <th class="centro">Nota</th>
                <th class="centro">Condición</th>
            </tr>
        </thead>
        <tbody>
            @php
                $clasesBadge = [
                    'Aprobada' => 'badge-aprobada',
                    'Regular' => 'badge-regular',
                    'Cursando' => 'badge-cursando',
                    'Pendiente' => 'badge-pendiente',
                ];
            @endphp
            @forelse ($alumno->historialAlumno as $h)
                <tr>
                    <td>{{ $h->anioLectivo->anio }}</td>
                    <td>{{ $h->materia->nombre }}</td>
                    <td class="centro">{{ $h->nota_cursada ?? '—' }}</td>
                    <td class="centro">
                        <span class="badge {{ $clasesBadge[$h->condicion->nombre_condicion] ?? 'badge-pendiente' }}">
                            {{ $h->condicion->nombre_condicion }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center;">El alumno todavía no tiene materias registradas en su historial.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="pie">Generado el {{ \App\Support\FechaEsp::corta(now()) }} desde el Panel de Gestión — {{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</p>
</body>
</html>
