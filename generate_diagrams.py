import base64
import os
import urllib.request

# Diagramme 1 : Parcours Client VIP (Livrable 2)
parcours_mermaid = """flowchart TD
    A([Départ : Client Régulier]) --> B[Rendez-vous en Agence Buca Voyages]
    B --> C[Enregistrement de la Fiche Client & Pièce d'Identité]
    C --> D[Attribution d'une Carte VIP Physique + QR Code]
    D --> E[L'Agent associe une Formule de Voyages à la Carte]
    E --> F{Paiement du Forfait}
    F -- En attente --> G[Statut : Paiement en Attente]
    G --> H[Paiement effectué en Agence ou Mobile Money]
    H --> I[Validation par l'Agent]
    F -- Immédiat --> J[Statut : Forfait Actif]
    I --> J
    
    J --> K[Client crée son compte en ligne avec son email]
    K --> L[Association automatique Espace Client <--> Carte VIP]
    L --> M[Client consulte son solde et historique en ligne]
    L --> N[Facultatif : Client ajoute des proches autorisés - Affiliés]
    
    J --> O[Client ou Affilié se présente en Agence pour voyager]
    O --> P[L'Agent saisit le numéro de carte ou scanne le QR Code]
    P --> Q{La carte est-elle Active ?}
    Q -- Non --> R[Voyage Refusé : Carte Suspendue ou Expirée]
    Q -- Oui --> S{Solde de voyages restant > 0 ?}
    S -- Non --> T[Voyage Refusé : Forfait Épuisé ou Expiré]
    S -- Oui --> U[Débit automatique d'un voyage sur la carte]
    U --> V[Impression du ticket de bus physique]
    V --> W[Voyage effectué]
    W --> X[Mise à jour en temps réel du solde restant sur l'Espace Client]"""

# Diagramme 2 : Structure de Données / MCD (Livrable 3)
mcd_mermaid = """erDiagram
    USERS ||--o| VIP_CLIENTS : "lié à (optionnel)"
    USERS ||--o{ VIP_CARDS : "créé par"
    USERS ||--o{ CARD_SUBSCRIPTIONS : "créé par"
    
    VIP_CLIENTS ||--o{ VIP_CARDS : "possède"
    VIP_CLIENTS ||--o{ TRAVEL_AFFILIATES : "autorise"
    VIP_CLIENTS ||--o{ TRIP_CONSUMPTIONS : "consomme"
    VIP_CLIENTS ||--o{ PAYMENTS : "paie"
    
    VIP_CARDS ||--o{ CARD_SUBSCRIPTIONS : "contient"
    
    PACKAGES ||--o{ CARD_SUBSCRIPTIONS : "définit la formule de"
    
    CARD_SUBSCRIPTIONS ||--o{ TRIP_CONSUMPTIONS : "débitée par"
    CARD_SUBSCRIPTIONS ||--o{ PAYMENTS : "génère"
    CARD_SUBSCRIPTIONS ||--o| CARD_SUBSCRIPTIONS : "renouvelé depuis"
    
    TRAVEL_ROUTES ||--o{ TRIP_CONSUMPTIONS : "concerne"
    AGENCIES ||--o{ TRIP_CONSUMPTIONS : "effectué à"

    USERS {
        bigint id PK
        string name
        string email
        string password
        string role_id FK
    }

    VIP_CLIENTS {
        bigint id PK
        string first_name
        string last_name
        string gender
        date birth_date
        string phone
        string email
        string address
        string identity_type
        string identity_number
        string status
        bigint user_id FK
    }

    VIP_CARDS {
        bigint id PK
        bigint vip_client_id FK
        string card_number
        string qr_code
        string card_type
        string status
        datetime issued_at
        datetime expires_at
        datetime suspended_at
        string suspension_reason
    }

    PACKAGES {
        bigint id PK
        string name
        integer duration_days
        integer trips_total
        decimal price
        string status
    }

    CARD_SUBSCRIPTIONS {
        bigint id PK
        bigint vip_card_id FK
        bigint package_id FK
        datetime starts_at
        datetime expires_at
        integer trips_total
        integer trips_remaining
        decimal unit_price
        decimal total_amount
        string status
        bigint renewed_from_id FK
    }

    TRIP_CONSUMPTIONS {
        bigint id PK
        bigint card_subscription_id FK
        bigint vip_client_id FK
        bigint travel_route_id FK
        bigint departure_agency_id FK
        datetime consumed_at
        date travel_date
        integer trips_debited
        string reference
    }

    PAYMENTS {
        bigint id PK
        bigint card_subscription_id FK
        bigint vip_client_id FK
        decimal amount
        string payment_method
        string transaction_reference
        string status
        datetime confirmed_at
    }

    TRAVEL_AFFILIATES {
        bigint id PK
        bigint vip_client_id FK
        string first_name
        string last_name
        string phone
        string relationship
        string status
    }

    TRAVEL_ROUTES {
        bigint id PK
        string departure_city
        string arrival_city
    }

    AGENCIES {
        bigint id PK
        string name
        string location
    }"""

def download_diagram(mermaid_code, filename):
    # Encode le code Mermaid en base64 standard
    encoded = base64.b64encode(mermaid_code.encode('utf-8')).decode('utf-8')
    url = f"https://mermaid.ink/img/{encoded}"
    
    print(f"Téléchargement de {filename} depuis {url}...")
    try:
        os.makedirs("docs/images", exist_ok=True)
        # Ajout d'en-têtes HTTP standard pour éviter les blocages de sécurité
        req = urllib.request.Request(
            url, 
            headers={'User-Agent': 'Mozilla/5.0'}
        )
        with urllib.request.urlopen(req) as response:
            with open(f"docs/images/{filename}", 'wb') as f:
                f.write(response.read())
        print(f"-> Succès : Sauvegardé dans docs/images/{filename}")
    except Exception as e:
        print(f"-> Erreur lors du téléchargement de {filename} : {e}")

if __name__ == "__main__":
    download_diagram(parcours_mermaid, "parcours_client_vip.png")
    download_diagram(mcd_mermaid, "schema_base_donnees.png")
