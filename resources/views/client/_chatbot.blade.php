<!-- BucaBot Chatbot Component -->
<div class="bucabot-wrapper">
    <!-- Floating Action Button -->
    <button class="bucabot-fab" id="bucabot-fab" onclick="toggleBucaBot()" title="Besoin d'aide ?">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-circle">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
        <span class="bucabot-badge" id="bucabot-badge">1</span>
    </button>

    <!-- Chat Window Drawer -->
    <div class="bucabot-window" id="bucabot-window">
        <!-- Header -->
        <div class="bucabot-header">
            <div class="bucabot-profile">
                <div class="bucabot-avatar">B</div>
                <div>
                    <h3>BucaBot</h3>
                    <p>Assistant virtuel VIP</p>
                </div>
            </div>
            <button class="bucabot-close" onclick="toggleBucaBot()" title="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Chat History -->
        <div class="bucabot-body" id="bucabot-body">
            <!-- Messages container -->
            <div class="bucabot-messages" id="bucabot-messages">
                <!-- Bot Welcome Message -->
                <div class="bucabot-message bot animate-fade">
                    <p>Bonjour ! Je suis <strong>BucaBot</strong>, votre assistant virtuel. Comment puis-je vous aider aujourd'hui ?</p>
                </div>
            </div>

            <!-- Typing indicator -->
            <div class="bucabot-typing" id="bucabot-typing" style="display: none;">
                <div class="bucabot-avatar">B</div>
                <div class="bucabot-typing-bubble">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>

            <!-- Suggestions chips -->
            <div class="bucabot-suggestions" id="bucabot-suggestions">
                <button onclick="sendSuggestion('Comment fonctionne ma carte VIP ?')">Comment fonctionne ma carte VIP ?</button>
                <button onclick="sendSuggestion('Comment ajouter une personne autorisée (affilié) ?')">Comment ajouter un affilié ?</button>
                <button onclick="sendSuggestion('Comment envoyer un colis ?')">Comment envoyer un colis ?</button>
                <button onclick="sendSuggestion('Comment se passe le retrait de colis ?')">Comment se passe le retrait ?</button>
            </div>
        </div>

        <!-- Input Box -->
        <div class="bucabot-footer">
            <input type="text" id="bucabot-input" placeholder="Posez votre question ici..." onkeydown="if(event.key === 'Enter') sendMessage()">
            <button id="bucabot-send-btn" onclick="sendMessage()" title="Envoyer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
/* Encapsulated Chatbot CSS Styles */
.bucabot-wrapper {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 60px;
    height: 60px;
    z-index: 1000;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* FAB button styling */
.bucabot-fab {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--brand, #D90429);
    color: #ffffff;
    border: none;
    box-shadow: 0 4px 16px rgba(217, 4, 41, 0.35);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}
.bucabot-fab:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 20px rgba(217, 4, 41, 0.45);
}
.bucabot-fab svg {
    width: 28px;
    height: 28px;
}

/* Pulsing notification badge */
.bucabot-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background-color: #0A1A2F;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
    animation: bucabot-pulse 2s infinite;
}

@keyframes bucabot-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); }
}

/* Chat window drawer styling */
.bucabot-window {
    position: absolute;
    bottom: 75px;
    right: 0;
    left: auto;
    width: 350px;
    height: 500px;
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(10, 26, 47, 0.12);
    border: 1px solid #E2E8F0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    pointer-events: none;
    transition: all 0.28s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.bucabot-window.open {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

/* Header style */
.bucabot-header {
    background: linear-gradient(135deg, #0A1A2F 0%, #172A45 100%);
    padding: 16px 20px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 3px solid #D90429;
}
.bucabot-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}
.bucabot-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #D90429;
    color: #ffffff;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bucabot-header h3 {
    margin: 0;
    font-size: 15px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    color: #ffffff;
}
.bucabot-header p {
    margin: 0;
    font-size: 11px;
    color: #93C5FD;
}
.bucabot-close {
    background: none;
    border: none;
    color: #94A3B8;
    cursor: pointer;
    transition: color 0.15s;
    padding: 0;
    display: flex;
}
.bucabot-close:hover {
    color: #ffffff;
}
.bucabot-close svg {
    width: 20px;
    height: 20px;
}

/* Chat body area */
.bucabot-body {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background-color: #F8FAFC;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.bucabot-messages {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* Bulle messages design */
.bucabot-message {
    max-width: 85%;
    padding: 11px 14px;
    border-radius: 12px;
    font-size: 13.5px;
    line-height: 1.5;
}
.bucabot-message p {
    margin: 0;
}
.bucabot-message.bot {
    background-color: #ffffff;
    color: #0A1A2F;
    align-self: flex-start;
    border-bottom-left-radius: 3px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}
.bucabot-message.user {
    background-color: #0A1A2F;
    color: #ffffff;
    align-self: flex-end;
    border-bottom-right-radius: 3px;
    box-shadow: 0 2px 5px rgba(10, 26, 47, 0.1);
}

/* Suggestion chips style */
.bucabot-suggestions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: auto;
    padding-top: 10px;
}
.bucabot-suggestions button {
    align-self: flex-start;
    background-color: #ffffff;
    color: #D90429;
    border: 1px solid #FCA5A5;
    border-radius: 18px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: left;
}
.bucabot-suggestions button:hover {
    background-color: #FEF2F2;
    border-color: #D90429;
    transform: translateY(-1px);
}

/* Typing bubble style */
.bucabot-typing {
    display: flex;
    align-items: center;
    gap: 8px;
    align-self: flex-start;
}
.bucabot-typing .bucabot-avatar {
    width: 26px;
    height: 26px;
    font-size: 11px;
}
.bucabot-typing-bubble {
    background-color: #ffffff;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    border-bottom-left-radius: 3px;
    padding: 10px 14px;
    display: flex;
    gap: 4px;
    align-items: center;
}
.bucabot-typing-bubble .dot {
    width: 6px;
    height: 6px;
    background-color: #94A3B8;
    border-radius: 50%;
    display: inline-block;
    animation: bucabot-typing-animation 1.4s infinite ease-in-out both;
}
.bucabot-typing-bubble .dot:nth-child(1) { animation-delay: -0.32s; }
.bucabot-typing-bubble .dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bucabot-typing-animation {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

/* Chat footer input box */
.bucabot-footer {
    padding: 12px 16px;
    background-color: #ffffff;
    border-top: 1px solid #E2E8F0;
    display: flex;
    gap: 10px;
    align-items: center;
}
.bucabot-footer input {
    flex: 1;
    border: 1px solid #CBD5E1;
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.15s;
}
.bucabot-footer input:focus {
    border-color: #D90429;
}
.bucabot-footer button {
    background-color: #0A1A2F;
    color: #ffffff;
    border: none;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.15s;
    padding: 0;
}
.bucabot-footer button:hover {
    background-color: #D90429;
}
.bucabot-footer button svg {
    width: 16px;
    height: 16px;
    margin-right: -1px;
}

/* Animation utilities */
.animate-fade {
    animation: bucabot-fade 0.22s ease-out;
}
@keyframes bucabot-fade {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive adjustment */
@media (max-width: 480px) {
    .bucabot-wrapper {
        right: 15px;
        bottom: 15px;
    }
    .bucabot-window {
        width: calc(100vw - 30px);
        height: calc(100vh - 100px);
        bottom: 65px;
    }
}
</style>

<script>
// Chatbot Javascript Logic
function toggleBucaBot() {
    const windowEl = document.getElementById('bucabot-window');
    const badgeEl = document.getElementById('bucabot-badge');
    
    // Toggle class
    windowEl.classList.toggle('open');
    
    // Hide notification badge once opened
    if (windowEl.classList.contains('open')) {
        badgeEl.style.display = 'none';
        scrollToBottom();
    }
}

function scrollToBottom() {
    const bodyEl = document.getElementById('bucabot-body');
    bodyEl.scrollTop = bodyEl.scrollHeight;
}

function sendSuggestion(text) {
    // Hide suggestion chips during conversation to keep screen tidy
    document.getElementById('bucabot-suggestions').style.display = 'none';
    
    // Post user query
    addUserMessage(text);
    
    // Process response with delay
    simulateBotResponse(text);
}

function sendMessage() {
    const inputEl = document.getElementById('bucabot-input');
    const text = inputEl.value.trim();
    if (text === '') return;
    
    inputEl.value = '';
    document.getElementById('bucabot-suggestions').style.display = 'none';
    
    addUserMessage(text);
    simulateBotResponse(text);
}

function addUserMessage(text) {
    const messagesContainer = document.getElementById('bucabot-messages');
    
    const msgDiv = document.createElement('div');
    msgDiv.className = 'bucabot-message user animate-fade';
    msgDiv.innerHTML = `<p>${escapeHtml(text)}</p>`;
    
    messagesContainer.appendChild(msgDiv);
    scrollToBottom();
}

function simulateBotResponse(query) {
    const typingIndicator = document.getElementById('bucabot-typing');
    
    // Show typing dots
    typingIndicator.style.display = 'flex';
    scrollToBottom();
    
    // Delay response to simulate AI processing
    setTimeout(() => {
        typingIndicator.style.display = 'none';
        
        const responseText = getBotResponse(query);
        addBotMessage(responseText);
        
        // Show suggestion chips again for next question
        document.getElementById('bucabot-suggestions').style.display = 'flex';
    }, 1100);
}

function addBotMessage(htmlContent) {
    const messagesContainer = document.getElementById('bucabot-messages');
    
    const msgDiv = document.createElement('div');
    msgDiv.className = 'bucabot-message bot animate-fade';
    msgDiv.innerHTML = htmlContent;
    
    messagesContainer.appendChild(msgDiv);
    scrollToBottom();
}

// Keyword-based offline NLP matching rules
function getBotResponse(query) {
    const text = query.toLowerCase();
    
    // Specific check for online subscription / self assignment
    if ((text.includes('moi-meme') || text.includes('moi meme') || text.includes('en ligne') || text.includes('internet') || text.includes('portail') || text.includes('espace') || text.includes('soi-meme') || text.includes('soi meme')) && (text.includes('forfait') || text.includes('souscrire') || text.includes('acheter') || text.includes('abonner') || text.includes('attribuer') || text.includes('carte') || text.includes('renouveler'))) {
        return `<p><strong>Oui, tout à fait !</strong> Si vous possédez déjà une carte VIP active, vous pouvez vous-même acheter ou renouveler un forfait en ligne :</p>
                <p>1. Cliquez sur le bouton <strong>"Souscrire à un forfait"</strong> (ou <strong>"Renouveler mon forfait"</strong>) dans la section <em>"Forfait en cours"</em> de votre Espace VIP.<br>
                   2. Choisissez la formule souhaitée et saisissez la référence de votre paiement Mobile Money (Orange Money ou MTN MoMo).<br>
                   3. Votre forfait s'affiche alors avec le statut <strong>"Paiement en attente"</strong>. Rendez-vous en agence pour qu'un agent valide la transaction et active vos trajets.</p>`;
    }

    if (text.includes('carte') || text.includes('vip') || text.includes('forfait') || text.includes('voyage') || text.includes('solde')) {
        return `<p>Votre <strong>carte VIP Buca Voyages</strong> fonctionne de manière simple :</p>
                <p>1. <strong>Achat & Renouvellement</strong> : Vous pouvez acheter ou renouveler votre forfait de voyages en ligne depuis votre Espace Client (via Mobile Money) ou directement en agence.<br>
                   2. <strong>Validation</strong> : Si vous souscrivez en ligne, le paiement reste en attente de confirmation de paiement (<em>"Paiement en attente"</em>) jusqu'à ce qu'un agent l'approuve en agence.<br>
                   3. <strong>Usage</strong> : À chaque voyage, l'agent scanne votre carte ou saisit son numéro pour déduire 1 trajet.<br>
                   4. <strong>Suivi</strong> : Vous pouvez suivre votre solde restant et votre historique en temps réel sur cet Espace VIP.</p>`;
    }
    
    if (text.includes('affilie') || text.includes('proche') || text.includes('ajouter') || text.includes('personne')) {
        return `<p><strong>Gestion des personnes autorisées (Affiliés) :</strong></p>
                <p>Vous pouvez ajouter directement des personnes de confiance depuis votre espace client (section <em>"Personnes autorisées à voyager pour moi"</em>).<br>
                   Ces affiliés pourront utiliser les trajets de votre carte VIP. L'activation est immédiate après l'ajout.</p>`;
    }
    
    if (text.includes('colis') || text.includes('envoyer') || text.includes('envoi') || text.includes('expedier') || text.includes('messagerie')) {
        return `<p><strong>Comment envoyer un colis ?</strong></p>
                <p>1. Déposez votre colis dans une agence Buca Voyages.<br>
                   2. L'agent évalue le colis et saisit le tarif sur place (paiement en espèces ou Mobile Money).<br>
                   3. Le colis est enregistré avec un code unique (ex: CLS-XXXXXX).<br>
                   4. Dès que le destinataire retire le colis avec sa pièce d'identité, vous recevez un e-mail de confirmation.</p>`;
    }
    
    if (text.includes('paiement') || text.includes('momo') || text.includes('orange') || text.includes('om') || text.includes('payer') || text.includes('cash') || text.includes('tarif')) {
        return `<p><strong>Modes de paiement acceptés :</strong></p>
                <p>1. <strong>Colis en agence</strong> : Les envois sont payés directement au dépôt en espèces, Orange Money ou MTN Mobile Money.<br>
                   2. <strong>Forfaits en ligne</strong> : Vous pouvez payer vos forfaits via Mobile Money. La référence de transaction saisie lors de la souscription sera vérifiée par un agent en agence pour activer vos voyages.</p>`;
    }
    
    if (text.includes('retrait') || text.includes('destinataire') || text.includes('cni') || text.includes('piece') || text.includes('recuperer') || text.includes('recepisse')) {
        return `<p><strong>Sécurité du Retrait :</strong></p>
                <p>Pour récupérer un colis à l'agence de destination, le destinataire doit obligatoirement présenter le code de suivi et sa **pièce d'identité originale** (CNI, Passeport, Permis ou Récépissé). L'agent enregistrera le numéro de la pièce pour valider la livraison.</p>`;
    }

    if (text.includes('contact') || text.includes('agence') || text.includes('telephone') || text.includes('mvan') || text.includes('mboppi')) {
        return `<p>Nos agences principales :</p>
                <ul>
                    <li><strong>Yaoundé (Mvan)</strong> : +237 690 000 001</li>
                    <li><strong>Douala (Mboppi)</strong> : +237 690 000 002</li>
                </ul>`;
    }
    
    if (text.includes('bonjour') || text.includes('salut') || text.includes('hello') || text.includes('coucou')) {
        return `<p>Bonjour ! Ravi de vous aider. Posez-moi vos questions concernant vos cartes VIP (souscription en ligne, solde, historique), vos personnes autorisées ou la messagerie de colis chez Buca Voyages !</p>`;
    }
    
    // Default reply
    return `<p>Désolé, je ne saisis pas tout à fait votre demande.</p>
            <p>Essayez de chercher par mot-clé comme <strong>"carte"</strong>, <strong>"forfait en ligne"</strong>, <strong>"colis"</strong>, <strong>"récépissé"</strong> ou <strong>"affilié"</strong>, ou cliquez sur l'une des suggestions ci-dessous.</p>`;
}

function escapeHtml(string) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return string.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
