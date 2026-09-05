<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de votre compte Buca Voyages VIP</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #F2F5F7;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(10, 26, 47, 0.05);
            overflow: hidden;
            border: 1px solid #E2E8F0;
        }
        .header {
            background-color: #0A1A2F;
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #D90429;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header .subtitle {
            color: #93C5FD;
            font-size: 14px;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #0A1A2F;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .message {
            font-size: 15px;
            color: #4A5568;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .credentials-box {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .credentials-box h2 {
            font-size: 14px;
            color: #D90429;
            text-transform: uppercase;
            margin-top: 0;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #E2E8F0;
            font-size: 14px;
        }
        .detail-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .detail-label {
            color: #718096;
            font-weight: 500;
        }
        .detail-value {
            color: #0A1A2F;
            font-weight: 600;
            word-break: break-all;
        }
        .action-area {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #D90429;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            font-weight: 600;
            border-radius: 6px;
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(217, 4, 41, 0.2);
            transition: all 0.3s ease;
        }
        .security-notice {
            background-color: #FFFDF5;
            border: 1px solid #FBE09C;
            border-radius: 8px;
            padding: 15px;
            font-size: 13.5px;
            color: #744210;
            line-height: 1.5;
            margin-bottom: 25px;
        }
        .footer {
            background-color: #F8FAFC;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #A0AEC0;
            border-top: 1px solid #E2E8F0;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Buca Voyages VIP</h1>
            <div class="subtitle">Création de Compte Client</div>
        </div>
        <div class="content">
            <div class="greeting">Félicitations {{ $user->name }},</div>
            <div class="message">
                Votre compte client en ligne a été créé avec succès par notre agent en agence. Vous faites désormais partie de nos clients VIP et pouvez accéder à votre espace personnel en ligne pour gérer vos abonnements, consulter vos cartes et coordonner vos affiliés.
            </div>
            
            <div class="credentials-box">
                <h2>Vos identifiants de connexion</h2>
                
                <div class="detail-row">
                    <span class="detail-label">Adresse e-mail (Identifiant)</span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Mot de passe temporaire</span>
                    <span class="detail-value" style="font-family: monospace; font-size: 15px; letter-spacing: 0.5px; color: #D90429;">{{ $plainPassword }}</span>
                </div>
            </div>

            <div class="security-notice">
                <strong>💡 Conseil de sécurité :</strong> Pour des raisons de confidentialité, nous vous conseillons vivement de modifier ce mot de passe temporaire dès votre première connexion en vous rendant dans l'onglet <strong>"Mes coordonnées & Sécurité"</strong> de votre espace client.
            </div>

            <div class="action-area">
                <a href="{{ route('login') }}" class="btn" target="_blank">Se connecter à mon espace</a>
            </div>

            <div class="message">
                Merci de faire confiance à <strong>Buca Voyages VIP</strong> pour vos déplacements.
            </div>
        </div>
        <div class="footer">
            <p>Ce courriel a été envoyé automatiquement. Veuillez ne pas y répondre directement.</p>
            <p>© {{ date('Y') }} Buca Voyages S.A. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
