<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relance candidature</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; background: #f8fafc; margin: 0; padding: 24px;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 24px; padding: 32px; box-shadow: 0 16px 48px rgba(15, 23, 42, 0.08);">
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 24px; margin-bottom: 8px; color: #111827;">Relance candidature</h1>
            <p style="margin: 0; color: #475569;">Bonjour {{ htmlspecialchars_decode($entreprise->directeur ?? 'Madame, Monsieur', ENT_QUOTES) }},</p>
        </div>

        <div style="margin-bottom: 24px; color: #334155; line-height: 1.7; font-size: 15px;">
            {!! $messageTexte !!}
        </div>

        <div style="padding: 20px; background: #f8fafc; border-radius: 16px; color: #334155; font-size: 14px;">
            <p style="margin: 0 0 6px;">📌 Informations :</p>
            <p style="margin: 0;">Entreprise : <strong>{{ $entreprise->nom }}</strong></p>
            <p style="margin: 0;">Email : <strong>{{ $entreprise->email }}</strong></p>
            <p style="margin: 0;">Nbre de relances : <strong>{{ $entreprise->nb_relances }}</strong></p>
        </div>

        <p style="margin-top: 24px; color: #475569; font-size: 14px;">Cordialement,<br>Yassir Kezzi</p>
    </div>
</body>
</html>
