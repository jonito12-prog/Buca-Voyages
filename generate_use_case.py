import zlib
import urllib.request
import os

plantuml_code = """@startuml
left to right direction
skinparam packageStyle rectangle
skinparam actorStyle awesome

' Définition des acteurs
actor "Utilisateur Connecté" as user
actor "Client VIP" as client
actor "Agent d'Agence" as agent
actor "Administrateur" as admin

' Relations d'héritage entre acteurs
client -|> user
agent -|> user
admin -|> agent

rectangle "Système Buca Voyages VIP" {

    package "Authentification & Profil" {
        usecase "S'authentifier\\n(Se connecter/Se déconnecter)" as UC_Auth
        usecase "Réinitialiser le mot de passe" as UC_Reset
        usecase "Gérer son profil" as UC_Profile
        
        user --> UC_Auth
        user --> UC_Profile
        UC_Reset ..> UC_Auth : <<extend>>
    }

    package "Gestion des Clients & Cartes" {
        usecase "Créer/Modifier une fiche client VIP" as UC_ManageVIP
        usecase "Attribuer une carte VIP physique" as UC_AssignCard
        usecase "Générer le QR Code de la carte" as UC_GenQR
        usecase "Suspendre/Réactiver une carte VIP" as UC_ToggleCard
        usecase "Archiver une fiche client VIP" as UC_ArchiveVIP
        usecase "Supprimer un client VIP\\n(sans historique)" as UC_DeleteVIP
        
        agent --> UC_ManageVIP
        agent --> UC_AssignCard
        agent --> UC_ToggleCard
        agent --> UC_ArchiveVIP
        
        admin --> UC_DeleteVIP
        
        UC_AssignCard ..> UC_GenQR : <<include>>
    }

    package "Gestion des Forfaits & Abonnements" {
        usecase "Associer un forfait de voyages\\nà une carte" as UC_Sub
        usecase "Valider le paiement du forfait\\nen agence" as UC_PayAgence
        usecase "Acheter/Renouveler\\nun forfait en ligne" as UC_SubOnline
        usecase "Payer par Mobile Money\\n(OM / MoMo)" as UC_Momo
        
        agent --> UC_Sub
        agent --> UC_PayAgence
        client --> UC_SubOnline
        
        UC_PayAgence ..> UC_Sub : <<extend>>
        UC_SubOnline ..> UC_Momo : <<include>>
    }

    package "Gestion des Voyages" {
        usecase "Enregistrer un voyage\\n(Débit de trajet)" as UC_Debit
        usecase "Vérifier le solde\\net la validité de la carte" as UC_VerifyCard
        usecase "Scanner le QR Code" as UC_ScanQR
        usecase "Consulter son solde\\net son historique en ligne" as UC_HistoryClient
        usecase "Consulter l'historique\\nglobal des voyages" as UC_HistoryGlobal
        
        agent --> UC_Debit
        agent --> UC_HistoryGlobal
        client --> UC_HistoryClient
        
        UC_Debit ..> UC_VerifyCard : <<include>>
        UC_ScanQR ..> UC_Debit : <<extend>>
    }

    package "Gestion de la Messagerie (Colis)" {
        usecase "Enregistrer l'expédition\\nd'un colis" as UC_SendParcel
        usecase "Lier l'expédition\\nau compte VIP de l'expéditeur" as UC_LinkVIP
        usecase "Livrer un colis" as UC_DeliverParcel
        usecase "Saisir la CNI du destinataire\\net vérifier l'identité" as UC_VerifyCNI
        usecase "Consulter la liste\\ndes colis et statuts" as UC_ListParcels
        
        agent --> UC_SendParcel
        agent --> UC_DeliverParcel
        agent --> UC_ListParcels
        
        UC_SendParcel ..> UC_LinkVIP : <<include>>
        UC_DeliverParcel ..> UC_VerifyCNI : <<include>>
    }

    package "Gestion des Paramètres & Audit" {
        usecase "Créer/Modifier des forfaits\\nde voyages (Packages)" as UC_ConfigPackages
        usecase "Consulter les journaux d'audit" as UC_AuditLogs
        
        admin --> UC_ConfigPackages
        admin --> UC_AuditLogs
    }
}
@enduml"""

def encode_plantuml(text):
    # Compression zlib sans en-tête ni checksum
    compressor = zlib.compressobj(9, zlib.DEFLATED, -15)
    compressed = compressor.compress(text.encode('utf-8')) + compressor.flush()
    
    # Encodage personnalisé PlantUML
    puml_alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_"
    
    # Conversion en base 64 personnalisée (par blocs de 3 octets)
    encoded = ""
    i = 0
    while i < len(compressed):
        chunk = compressed[i:i+3]
        # Padding
        if len(chunk) == 1:
            chunk += b'\x00\x00'
        elif len(chunk) == 2:
            chunk += b'\x00'
            
        b1, b2, b3 = chunk[0], chunk[1], chunk[2]
        
        # Découpage par paquets de 6 bits
        c1 = b1 >> 2
        c2 = ((b1 & 0x3) << 4) | (b2 >> 4)
        c3 = ((b2 & 0xF) << 2) | (b3 >> 6)
        c4 = b3 & 0x3F
        
        encoded += puml_alphabet[c1] + puml_alphabet[c2] + puml_alphabet[c3] + puml_alphabet[c4]
        
        # Ajustement de la taille du résultat en fonction des octets réels
        i += 3
        
    # PlantUML s'attend à recevoir la chaîne encodée tronquée si le padding n'est pas nécessaire
    # mais l'encodage par défaut de PlantUML traite le flux de manière standard
    return encoded

def download_diagram():
    encoded = encode_plantuml(plantuml_code)
    url = f"https://www.plantuml.com/plantuml/png/{encoded}"
    output_path = "docs/diagramme de cas d'utilisation.png"
    
    print(f"Téléchargement du diagramme depuis : {url}")
    try:
        req = urllib.request.Request(
            url, 
            headers={'User-Agent': 'Mozilla/5.0'}
        )
        with urllib.request.urlopen(req) as response:
            with open(output_path, 'wb') as f:
                f.write(response.read())
        print(f"Succès ! Le diagramme a été sauvegardé sous : {output_path}")
    except Exception as e:
        print(f"Erreur lors du téléchargement : {e}")

if __name__ == "__main__":
    download_diagram()
