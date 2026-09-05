<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Livraison de Colis</title>
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
            margin-bottom: 30px;
        }
        .parcel-box {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .parcel-box h2 {
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
        .btn {
            display: inline-block;
            background-color: #D90429;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Buca Voyages VIP</h1>
            <div class="subtitle">Notification de Messagerie</div>
        </div>
        <div class="content">
            <div class="greeting">Bonjour {{ $parcel->sender_name }},</div>
            <div class="message">
                Nous avons le plaisir de vous informer que votre colis portant le code de suivi <strong>{{ $parcel->tracking_code }}</strong> a été récupéré et livré avec succès à son destinataire.
            </div>
            
            <div class="parcel-box">
                <h2>Détails de l'expédition</h2>
                
                <div class="detail-row">
                    <span class="detail-label">Code de suivi</span>
                    <span class="detail-value" style="color: #D90429;">{{ $parcel->tracking_code }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Destinataire</span>
                    <span class="detail-value">{{ $parcel->receiver_name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Agence d'origine</span>
                    <span class="detail-value">{{ $parcel->originAgency->name }} ({{ $parcel->originAgency->city }})</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Agence de destination</span>
                    <span class="detail-value">{{ $parcel->destinationAgency->name }} ({{ $parcel->destinationAgency->city }})</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Date & Heure de retrait</span>
                    <span class="detail-value">{{ $parcel->delivered_at->format('d/m/Y à H:i') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Pièce d'identité présentée</span>
                    <span class="detail-value">{{ $parcel->receiver_identity_number }}</span>
                </div>
            </div>

            <div class="message">
                Merci de faire confiance à <strong>Buca Voyages</strong> pour le transport sécurisé de vos colis et messageries.
            </div>
        </div>
        <div class="footer">
            <p>Ce courriel a été envoyé automatiquement. Veuillez ne pas y répondre directement.</p>
            <p>© {{ date('Y') }} Buca Voyages S.A. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
