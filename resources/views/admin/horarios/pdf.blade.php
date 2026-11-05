<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario - {{ $carrera->nombre_carrera }} - {{ $turno->nombre_turno }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        .header { display: table; width: 100%; border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 14px; }
        .header .logo { display: table-cell; width: 40px; }
        .header .logo span { display: block; width: 32px; height: 22px; padding-top: 10px; line-height: 12px; text-align: center; border-radius: 50%; background: #0B3D91; color: #fff; font-weight: bold; font-size: 10px; }
        .header .info { display: table-cell; vertical-align: middle; padding-left: 8px; }
        .header .info p { margin: 0; }
        .header .info .institucion { font-weight: bold; font-size: 13px; }
        .header .info .direccion { color: #64748b; font-size: 9px; }

        h1 { text-align: center; font-size: 14px; margin: 10px 0 4px; }
        p.turno { text-align: center; font-size: 11px; color: #64748b; margin: 0 0 14px; }

        table.horario { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        table.horario th, table.horario td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: top; word-wrap: break-word; }
        table.horario th { background: #f8fafc; text-transform: uppercase; font-size: 8.5px; color: #64748b; text-align: center; }
        table.horario th.modulo { width: 6%; }
        table.horario th.hora { width: 11%; }
        table.horario th.dia { width: 16.6%; }
        table.horario td.modulo { font-weight: bold; text-align: center; }
        table.horario td.hora { text-align: center; white-space: nowrap; }
        table.horario tr { page-break-inside: avoid; }

        .pie { text-align: center; font-size: 9px; font-weight: bold; margin-top: 20px; }
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

    <h1>Horario de {{ $carrera->nombre_carrera }}</h1>
    <p class="turno">Turno {{ $turno->nombre_turno }}</p>

    <table class="horario">
        <thead>
            <tr>
                <th class="modulo">Módulo</th>
                <th class="hora">Horario</th>
                @foreach ($dias as $dia)
                    <th class="dia">{{ $dia }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($modulos as $modulo)
                <tr>
                    <td class="modulo">{{ $modulo->modulo }}°</td>
                    <td class="hora">{{ $modulo->hora_inicio->format('H:i') }} a {{ $modulo->hora_fin->format('H:i') }}</td>
                    @foreach ($dias as $dia)
                        <td>{{ $modulo->celdasPorDia->get($dia)?->contenido }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($dias) + 2 }}" style="text-align: center;">No hay módulos cargados para esta carrera y turno.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="pie">Generado el {{ \App\Support\FechaEsp::corta(now()) }} desde el Panel de Gestión — Instituto Superior San Gregorio</p>
</body>
</html>
