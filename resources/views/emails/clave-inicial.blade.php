<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f4f6fb; padding:32px 0; margin:0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td style="background:#1E4D8C; padding:24px 32px;">
                            <p style="color:#ffffff; font-size:16px; font-weight:bold; margin:0;">Instituto Superior San Gregorio</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:15px; color:#334155; margin:0 0 16px;">Hola {{ $nombre }}, ya tenés tu usuario para entrar al sistema del instituto.</p>
                            <p style="font-size:14px; color:#334155; margin:0 0 6px;"><strong>Usuario:</strong> {{ $email }}</p>
                            <p style="font-size:14px; color:#334155; margin:0 0 8px;"><strong>Clave inicial:</strong></p>
                            <p style="font-size:24px; font-weight:bold; letter-spacing:3px; color:#1E4D8C; background:#f4f6fb; border-radius:10px; padding:16px; text-align:center; margin:0 0 16px;">{{ $clave }}</p>
                            <p style="font-size:14px; color:#334155; margin:0 0 24px;">La primera vez que entres, el sistema te va a pedir esta clave y una contraseña nueva que solo vos conozcas.</p>
                            <p style="margin:0 0 16px;"><a href="{{ route('login') }}" style="display:inline-block; background:#1E4D8C; color:#ffffff; text-decoration:none; font-weight:bold; padding:12px 24px; border-radius:10px;">Ingresar al sistema</a></p>
                            <p style="font-size:13px; color:#94a3b8; margin:0;">Si no te inscribiste en el instituto, podés ignorar este mensaje.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
