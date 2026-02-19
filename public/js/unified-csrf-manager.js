/**
 * ========================================================================
 * MODULE CSRF UNIFIÉ - SGLP v2.1 CORRIGÉ
 * Centralise la gestion CSRF pour toutes les fonctionnalités
 * ✅ CORRECTION CRITIQUE: Transmission correcte des données JSON
 * ========================================================================
 */

window.UnifiedCSRFManager = {
    
    // Configuration
    config: {
        refreshEndpoint: '/csrf-token',
        maxRetries: 3,
        retryDelay: 1000,
        tokenMinLength: 10,
        debug: true,
        version: '2.1-CORRECTION-CRITIQUE'
    },
    
    // État interne
    state: {
        lastRefresh: null,
        refreshPromise: null,
        retryCount: 0
    },
    
    /**
     * ✅ MÉTHODE PRINCIPALE : Obtenir token CSRF actuel
     */
    async getCurrentToken() {
        this.log('🔍 Récupération token CSRF unifié');
        
        // Essayer les sources locales d'abord
        let token = this.getLocalToken();
        
        if (this.isValidToken(token)) {
            this.log('✅ Token local valide trouvé');
            return token;
        }
        
        // Refresh depuis le serveur si nécessaire
        this.log('🔄 Token local invalide, refresh depuis serveur...');
        return await this.refreshFromServer();
    },
    
    /**
     * ✅ RÉCUPÉRATION TOKEN LOCAL (multi-sources)
     */
    getLocalToken() {
        // Source 1: Meta tag
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (this.isValidToken(token)) return token;
        
        // Source 2: Input caché
        token = document.querySelector('input[name="_token"]')?.value;
        if (this.isValidToken(token)) return token;
        
        // Source 3: Variable Laravel globale
        token = window.Laravel?.csrfToken;
        if (this.isValidToken(token)) return token;
        
        return null;
    },
    
    /**
     * ✅ REFRESH TOKEN DEPUIS SERVEUR
     */
    async refreshFromServer() {
        // Éviter les appels multiples simultanés
        if (this.state.refreshPromise) {
            this.log('⏳ Refresh en cours, attente...');
            return await this.state.refreshPromise;
        }
        
        this.state.refreshPromise = this._performRefresh();
        
        try {
            const token = await this.state.refreshPromise;
            this.state.refreshPromise = null;
            return token;
        } catch (error) {
            this.state.refreshPromise = null;
            throw error;
        }
    },
    
    /**
     * ✅ EXÉCUTION DU REFRESH
     */
    async _performRefresh() {
        try {
            this.log('📡 Requête refresh CSRF vers serveur...');
            
            const response = await fetch(this.config.refreshEndpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            const newToken = data.token || data.csrf_token;
            
            if (!this.isValidToken(newToken)) {
                throw new Error('Token CSRF reçu invalide du serveur');
            }
            
            // Mettre à jour tous les emplacements
            this.updateAllLocations(newToken);
            this.state.lastRefresh = Date.now();
            
            this.log('✅ Token CSRF unifié mis à jour:', newToken.substring(0, 10) + '...');
            return newToken;
            
        } catch (error) {
            this.log('❌ Erreur refresh CSRF unifié:', error.message);
            throw error;
        }
    },
    
    /**
     * ✅ MISE À JOUR DE TOUS LES EMPLACEMENTS
     */
    updateAllLocations(token) {
        // Meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', token);
        }
        
        // Inputs cachés
        document.querySelectorAll('input[name="_token"]').forEach(input => {
            input.value = token;
        });
        
        // Variable Laravel globale
        if (window.Laravel) {
            window.Laravel.csrfToken = token;
        }
        
        // Notifier les autres modules
        this.notifyModules(token);
        
        this.log('🔄 Tous les emplacements CSRF mis à jour');
    },
    
    /**
     * ✅ NOTIFICATION AUX AUTRES MODULES
     */
    notifyModules(token) {
        // Notifier workflow-2phases.js
        if (window.Workflow2Phases && typeof window.Workflow2Phases.onCSRFUpdated === 'function') {
            window.Workflow2Phases.onCSRFUpdated(token);
        }
        
        // Notifier chunking-import.js
        if (window.ChunkingImport && typeof window.ChunkingImport.onCSRFUpdated === 'function') {
            window.ChunkingImport.onCSRFUpdated(token);
        }
        
        // Émettre événement global
        window.dispatchEvent(new CustomEvent('csrf-token-updated', { 
            detail: { token, timestamp: Date.now() } 
        }));
    },
    
    /**
     * ✅ VALIDATION TOKEN
     */
    isValidToken(token) {
        return token && 
               typeof token === 'string' && 
               token.length >= this.config.tokenMinLength &&
               token !== 'undefined';
    },
    
    /**
     * ✅ SOUMISSION AVEC RETRY CSRF AUTOMATIQUE - CORRECTION CRITIQUE
     */
    async submitWithCSRFRetry(url, data, options = {}) {
        const maxAttempts = this.config.maxRetries;

        // ✅ CORRECTION: Utiliser l'URL du formulaire HTML si disponible
        const formEl = document.getElementById('organisationForm');
        const resolvedUrl = (formEl && formEl.action) ? formEl.action : url;
        this.log('📡 URL de soumission résolue:', resolvedUrl, '(original:', url, ')');

        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            try {
                this.log(`🔄 Tentative ${attempt}/${maxAttempts} - Soumission avec CSRF`);

                // Obtenir token actuel
                const token = await this.getCurrentToken();

                // ✅ CORRECTION CRITIQUE: Préparation correcte des données
                const { requestData, requestOptions } = this.prepareRequest(data, token, options);

                this.log('📦 Données préparées v2.1:', {
                    url: resolvedUrl,
                    method: 'POST',
                    headers: requestOptions.headers,
                    dataKeys: typeof requestData === 'object' ? Object.keys(requestData) : 'non-object',
                    bodyPreview: typeof requestOptions.body === 'string' ?
                        requestOptions.body.substring(0, 100) + '...' :
                        typeof requestOptions.body
                });

                // ✅ CORRECTION: redirect 'manual' pour détecter les redirections (session expirée)
                const response = await fetch(resolvedUrl, {
                    method: 'POST',
                    redirect: 'manual',
                    ...requestOptions
                });

                // ✅ CORRECTION: Détecter redirection (302/301 vers login = session expirée)
                if (response.type === 'opaqueredirect' || response.status === 0 ||
                    response.status === 301 || response.status === 302) {
                    this.log('⚠️ Redirection détectée (session expirée ?), status:', response.status, 'type:', response.type);
                    throw new Error('Session expirée. Veuillez rafraîchir la page et vous reconnecter.');
                }

                // Retry automatique sur erreur 419
                if (response.status === 419 && attempt < maxAttempts) {
                    this.log('⚠️ Erreur 419 CSRF, retry avec nouveau token...');
                    await this.refreshFromServer();
                    await this.delay(this.config.retryDelay);
                    continue;
                }

                if (!response.ok) {
                    const errorBody = await response.text().catch(() => '');
                    this.log('❌ Erreur HTTP:', response.status, response.statusText, 'Body:', errorBody.substring(0, 200));
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                this.log(`✅ Soumission réussie après ${attempt} tentative(s)`);
                return await response.json();

            } catch (error) {
                this.log(`❌ Tentative ${attempt} échouée:`, error.message);

                if (attempt === maxAttempts) {
                    throw error;
                }

                await this.delay(this.config.retryDelay * attempt);
            }
        }
    },
    
    /**
     * ✅ PRÉPARATION REQUÊTE CORRIGÉE
     */
    prepareRequest(data, token, options = {}) {
        // ✅ CORRECTION 1: Ajouter token aux données
        let requestData;
        
        if (data instanceof FormData) {
            data.set('_token', token);
            requestData = data;
        } else if (typeof data === 'object' && data !== null) {
            requestData = { ...data, _token: token };
        } else {
            requestData = { data, _token: token };
        }
        
        // ✅ CORRECTION 2: Headers obligatoires
        const requestOptions = {
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            credentials: 'same-origin',
            ...options
        };
        
        // ✅ CORRECTION 3: Body selon le type de données
        if (requestData instanceof FormData) {
            // FormData : pas de Content-Type (multipart/form-data automatique)
            requestOptions.body = requestData;
        } else {
            // Objet : JSON obligatoire
            requestOptions.headers['Content-Type'] = 'application/json';
            requestOptions.body = JSON.stringify(requestData);
        }
        
        return { requestData, requestOptions };
    },
    
    /**
     * ✅ MÉTHODE SIMPLE POUR COMPATIBILITÉ WORKFLOW-2PHASES
     */
    async refreshToken() {
        try {
            const token = await this.refreshFromServer();
            this.log('✅ Refresh manuel réussi');
            return true;
        } catch (error) {
            this.log('❌ Erreur refresh manuel:', error.message);
            return false;
        }
    },
    
    /**
     * ✅ UTILITAIRES
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },
    
    log(...args) {
        if (this.config.debug) {
            console.log('[UnifiedCSRF]', ...args);
        }
    },
    
    /**
     * ✅ DIAGNOSTIC COMPLET
     */
    diagnose() {
        const context = {
            version: this.config.version,
            localToken: this.getLocalToken(),
            validToken: this.isValidToken(this.getLocalToken()),
            lastRefresh: this.state.lastRefresh,
            refreshAge: this.state.lastRefresh ? (Date.now() - this.state.lastRefresh) / 1000 : null,
            refreshEndpoint: this.config.refreshEndpoint,
            metaExists: !!document.querySelector('meta[name="csrf-token"]'),
            inputExists: !!document.querySelector('input[name="_token"]'),
            laravelExists: !!window.Laravel?.csrfToken,
            timestamp: new Date().toISOString()
        };
        
        this.log('🔍 Diagnostic CSRF unifié v2.1:', context);
        return context;
    }
};

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    window.UnifiedCSRFManager.log('🚀 Module CSRF unifié v2.1 CORRIGÉ initialisé');
    
    // Diagnostic initial
    setTimeout(() => {
        window.UnifiedCSRFManager.diagnose();
    }, 1000);
});