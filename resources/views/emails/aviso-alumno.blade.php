<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f4f6fb; padding:32px 0; margin:0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td style="background:#1E4D8C; padding:24px 32px;">
                            <p style="color:#ffffff; font-size:16px; font-weight:bold; margin:0;">Instituto Superior San Gregorio</p>
                            <p style="color:#cfe0f5; font-size:12px; margin:4px 0 0;">Secretaría</p>
                        </td>
                    </tr>
                    <tr>
                        {{-- El mensaje se escapa (no se puede inyectar HTML) y se respetan los saltos de línea --}}
                        <td style="padding:32px; font-size:14px; line-height:1.6; color:#334155;">{!! nl2br(e($mensaje)) !!}</td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; background:#f8fafc; font-size:12px; color:#94a3b8;">Este correo fue enviado por la Secretaría del instituto. Si tenés dudas, respondé a este mensaje o acercate a Secretaría.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
