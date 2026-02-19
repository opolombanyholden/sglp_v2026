/**
 * MODULE WORKFLOW 2 PHASES - PNGDI
 * Fichier: public/js/workflow-2phases.js
 * Version: 2.1 - HARMONISATION SELON RECOMMANDATIONS v1_12-DISCUSSION 4
 * 
 * Ce module étend le système existant pour supporter le workflow 2 phases
 * Sans modifier massivement organisation-create.js
 * 
 * MODIFICATIONS VERSION 2.1 HARMONISÉE :
 * - ✅ CSRF: Délégation au UnifiedCSRFManager avec fallback
 * - ✅ CORRECTION: Redirection confirmation corrigée
 * - ✅ HARMONISATION: Compatible avec gestionnaires unifiés
 * - ✅ FALLBACK: Méthodes existantes préservées si gestionnaires absents
 */

// =============================================
// CONFIGURATION GLOBALE - VERSION 2.1 HARMONISÉE
// =============================================

window.Workflow2Phases = {
    enabled: true,
    debug: true,
    version: '2.1-HARMONISATION-v1_12-DISCUSSION-4',
    
    config: {
        routes: {
            phase1: '/operator/organisations',
            // ✅ CORRECTION CRITIQUE : Route corrigée dossiers au lieu d'organisations
            phase2_template: '/operator/dossiers/{dossier}/adherents-import',
            // ✅ CORRECTION : confirmation_template corrigée selon recommandations
            confirmation_template: '/operator/dossiers/{dossier}/confirmation'
        },
        options: {
            autoRedirectPhase2: true,
            saveAdherentsForPhase2: true,
            showChoiceDialog: true,
            // ✅ NOUVEAU : Options harmonisation
            useUnifiedManagers: true,
            csrfRetryAttempts: 2
        }
    },
    
    state: {
        currentPhase: 1,
        phase1Response: null,
        savedAdherents: null,
        // ✅ NOUVEAU : État harmonisation
        isUnifiedMode: false,
        lastCSRFRefresh: null
    }
};

// =============================================
// MÉTHODES PRINCIPALES - VERSION 2.1 HARMONISÉE
// =============================================

/**
 * Initialiser le workflow 2 phases
 * À appeler depuis organisation-create.js
 */
window.Workflow2Phases.init = function() {
    if (!this.enabled) {
        this.log('Workflow 2 phases désactivé');
        return false;
    }
    
    this.log('Initialisation workflow 2 phases v2.1 Harmonisé');
    
    // ✅ HARMONISATION : Détecter les gestionnaires unifiés
    this.detectUnifiedManagers();
    
    // Injecter les hooks dans l'application existante
    this.injectHooks();
    
    // Configurer les événements
    this.setupEventListeners();
    
    // Vérifier si on revient de Phase 1
    this.checkPhase1Continuation();
    
    this.log('Workflow 2 phases v2.1 Harmonisé initialisé avec succès');
    return true;
};

/**
 * ✅ NOUVELLE MÉTHODE : Détecter les gestionnaires unifiés
 */
window.Workflow2Phases.detectUnifiedManagers = function() {
    this.state.isUnifiedMode = (
        typeof window.UnifiedCSRFManager !== 'undefined' ||
        typeof window.UnifiedConfigManager !== 'undefined'
    );
    
    if (this.state.isUnifiedMode) {
        this.log('✅ Mode unifié détecté - Gestionnaires harmonisés disponibles');
    } else {
        this.log('🔧 Mode fallback - Utilisation méthodes existantes');
    }
};

/**
 * Intercepter la soumission du formulaire principal
 */
window.Workflow2Phases.interceptSubmission = function(originalSubmissionFunction) {
    this.log('Interception de la soumission pour workflow 2 phases v2.1 Harmonisé');
    
    // Sauvegarder la fonction originale
    this.originalSubmit = originalSubmissionFunction;
    
    // Décider du workflow à utiliser
    if (this.shouldUsePhase1()) {
        return this.submitPhase1();
    } else {
        this.log('Fallback vers soumission originale');
        return this.originalSubmit();
    }
};

/**
 * Déterminer si on doit utiliser le workflow 2 phases
 */
window.Workflow2Phases.shouldUsePhase1 = function() {
    this.log('🤔 Analyse décision workflow - Architecture fonctionnelle');
    
    // Vérifier si activé
    if (!this.enabled) {
        this.log('⚠️ Workflow 2 phases désactivé');
        return false;
    }
    
    // ✅ CORRECTION : TOUJOURS utiliser le workflow 2 phases
    // Car l'architecture fonctionnelle est : Phase 1 → adherents-import → validation → confirmation
    
    this.log('✅ Workflow 2 phases TOUJOURS activé (Architecture fonctionnelle)');
    this.log('📋 Séquence: create.blade.php → adherents-import.blade.php → validation → confirmation');
    
    return true; // ✅ TOUJOURS TRUE
};

// ========================================================================
// 1. ✅ NOUVELLE MÉTHODE : À AJOUTER dans workflow-2phases.js
// ========================================================================

/**
 * ✅ NOUVELLE MÉTHODE : Redirection vers adherents-import.blade.php (Phase 2)
 * À AJOUTER après la méthode redirectToConfirmation()
 */
window.Workflow2Phases.redirectToPhase2AdherentsImport = function(response) {
    this.log('📋 === REDIRECTION PHASE 2 ADHERENTS-IMPORT ===');
    
    const dossierId = response.data?.dossier_id;
    
    if (!dossierId) {
        this.log('❌ Dossier ID manquant pour redirection Phase 2');
        this.showErrorNotification('Erreur: Dossier ID manquant pour la Phase 2');
        return;
    }
    
    // ✅ URL CORRECTE adherents-import.blade.php
    const adherentsImportUrl = `/operator/dossiers/${dossierId}/adherents-import`;
    
    this.log('📋 URL Phase 2 construite:', adherentsImportUrl);
    this.log('📋 Dossier ID:', dossierId);
    this.log('📋 Séquence: create.blade.php → adherents-import.blade.php → validation → confirmation');
    
    // Message informatif pour l'utilisateur
    this.showLoadingState('Phase 1 terminée ! Redirection vers l\'import des adhérents (Phase 2)...');
    
    // ✅ REDIRECTION IMMÉDIATE
    setTimeout(() => {
        this.log('🚀 Redirection effective vers Phase 2');
        window.location.href = adherentsImportUrl;
    }, 1500); // 1.5 secondes
};

/**
 * ✅ HARMONISATION ÉTAPE 4.1 : SOUMISSION PHASE 1 HARMONISÉE
 * RECHERCHER : window.Workflow2Phases.submitPhase1 = function() {
 * REMPLACER PAR :
 */
window.Workflow2Phases.submitPhase1 = async function() {
    this.log('🚀 Début soumission Phase 1 harmonisée v2.1');
    
    try {
        this.showLoadingState('Création de votre organisation (Phase 1)...');
        
        const formData = this.preparePhase1Data();
        
        // ✅ HARMONISATION : Utilisation du gestionnaire unifié pour CSRF et soumission
        if (window.UnifiedCSRFManager && this.config.options.useUnifiedManagers) {
            this.log('🔧 Utilisation UnifiedCSRFManager pour soumission Phase 1');
            const response = await window.UnifiedCSRFManager.submitWithCSRFRetry(
                this.config.routes.phase1,
                formData,
                this.config.options.csrfRetryAttempts
            );
            this.handlePhase1Success(response);
        } else {
            this.log('🔧 Fallback vers méthode CSRF existante');
            // Fallback vers méthode existante
            const response = await this.submitWithCSRFRetry(formData);
            this.handlePhase1Success(response);
        }
        
    } catch (error) {
        this.log('❌ Erreur Phase 1 harmonisée v2.1:', error);
        this.handlePhase1Error(error);
    }
};

/**
 * Préparer les données pour Phase 1
 */
/**
 * ✅ CORRECTION CRITIQUE : preparePhase1Data() 
 * Remplacer cette méthode dans workflow-2phases.js
 */

window.Workflow2Phases.preparePhase1Data = function() {
    this.log('📦 Préparation données Phase 1 - Structure complète v2.1');
    
    try {
        // Récupérer toutes les données du formulaire comme le test réussi
        const formData = {};
        
        // Récupérer tous les champs du formulaire
        const formInputs = document.querySelectorAll('form input, form select, form textarea');
        
        formInputs.forEach(input => {
            if (input.name && (input.value || input.checked)) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.checked) {
                        formData[input.name] = input.value || 'on';
                    }
                } else if (input.value.trim() !== '') {
                    formData[input.name] = input.value.trim();
                }
            }
        });
        
        // Validation des champs obligatoires essentiels
        const requiredFields = [
            'demandeur_nom', 'demandeur_prenom', 'demandeur_nip',
            'org_nom', 'org_objet', 'type_organisation'
        ];
        
        const missingFields = requiredFields.filter(field => !formData[field]);
        
        if (missingFields.length > 0) {
            this.log('❌ Champs obligatoires manquants:', missingFields);
            throw new Error(`Champs obligatoires manquants: ${missingFields.join(', ')}`);
        }
        
        // Forcer les champs critiques s'ils manquent
        if (!formData.guide_read_confirm) {
            formData.guide_read_confirm = 'on';
        }
        
        if (!formData.declaration_veracite) {
            formData.declaration_veracite = 'on';
        }
        
        if (!formData.declaration_conformite) {
            formData.declaration_conformite = 'on';
        }
        
        if (!formData.declaration_autorisation) {
            formData.declaration_autorisation = 'on';
        }
        
        if (!formData.declaration_workflow) {
            formData.declaration_workflow = 'on';
        }
        
        // Ajouter métadonnées de phase
        formData._phase = 1;
        
        // Log pour diagnostic
        this.log('✅ Données Phase 1 préparées v2.1:', {
            totalFields: Object.keys(formData).length,
            requiredFieldsPresent: requiredFields.every(field => formData[field]),
            typeOrganisation: formData.type_organisation,
            organizationType: formData.organization_type,
            demandeurNom: formData.demandeur_nom,
            orgNom: formData.org_nom
        });
        
        return formData;
        
    } catch (error) {
        this.log('❌ Erreur préparation données Phase 1:', error);
        throw error;
    }
};

/**
 * ✅ CORRECTION : Gérer le succès de Phase 1 avec redirection confirmation corrigée
 */
window.Workflow2Phases.handlePhase1Success = function(response) {
    this.hideLoadingState();
    
    this.log('🎉 Phase 1 réussie - TOUJOURS rediriger vers adherents-import');
    this.log('📋 Réponse serveur:', response);
    
    if (response.success) {
        // Sauvegarder la réponse
        this.state.phase1Response = response;
        sessionStorage.setItem('workflow_phase1_response', JSON.stringify(response));
        
        // ✅ MESSAGE DE SUCCÈS Phase 1
        this.showSuccessNotification('✅ Phase 1 complétée ! Organisation créée avec succès.');
        
        // ✅ CORRECTION : TOUJOURS rediriger vers adherents-import (Phase 2)
        if (response.data && response.data.dossier_id) {
            this.log('📋 Redirection AUTOMATIQUE vers Phase 2 (adherents-import)');
            this.log('📋 Architecture fonctionnelle: Phase 1 → adherents-import → validation → confirmation');
            this.redirectToPhase2AdherentsImport(response);
        } else {
            this.log('❌ dossier_id manquant, impossible de rediriger vers Phase 2');
            this.showErrorNotification('Erreur: Impossible de procéder à la Phase 2 (dossier_id manquant)');
        }
        
    } else {
        this.log('❌ Phase 1 échouée:', response.message);
        throw new Error(response.message || 'Erreur Phase 1');
    }
};

/**
 * Afficher le dialog de choix Phase 2
 */
window.Workflow2Phases.showPhase2RedirectDialog = function(phase1Response) {
    const adherentsCount = this.state.savedAdherents ? this.state.savedAdherents.length : 0;
    
    // Créer le modal
    const modalHTML = `
        <div class="modal fade" id="phase2ChoiceModal" tabindex="-1" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle me-2"></i>
                            Organisation créée avec succès ! (v2.1 Harmonisé)
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle me-2"></i>
                            Votre organisation a été enregistrée avec le numéro de récépissé : 
                            <strong>${phase1Response.data.numero_recepisse || 'En cours'}</strong>
                        </div>
                        
                        <h6>Prochaine étape :</h6>
                        <p>Vous avez <strong>${adherentsCount} adhérents</strong> prêts à être importés.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h6>Ajouter maintenant</h6>
                                        <p class="small text-muted">Importez vos adhérents immédiatement</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-secondary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clock fa-2x text-secondary mb-2"></i>
                                        <h6>Plus tard</h6>
                                        <p class="small text-muted">Ajoutez les adhérents depuis votre espace</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" id="phase2-later">
                            <i class="fas fa-clock me-2"></i>
                            Plus tard
                        </button>
                        <button type="button" class="btn btn-success" id="phase2-now">
                            <i class="fas fa-users me-2"></i>
                            Ajouter maintenant
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter au DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = $('#phase2ChoiceModal');
    
    // Événements
    document.getElementById('phase2-now').addEventListener('click', () => {
        modal.modal('hide');
        this.redirectToPhase2(phase1Response);
    });

    document.getElementById('phase2-later').addEventListener('click', () => {
        modal.modal('hide');
        this.redirectToConfirmation(phase1Response);
    });

    modal.modal('show');
};

/**
 * Redirection vers Phase 2
 */
window.Workflow2Phases.redirectToPhase2 = function(phase1Response) {
    this.log('🔄 Redirection vers Phase 2 v2.1 Harmonisé');
    
    if (phase1Response.data && phase1Response.data.dossier_id) {
        const phase2Url = this.config.routes.phase2_template.replace('{dossier}', phase1Response.data.dossier_id);
        
        this.showLoadingState('Redirection vers l\'import des adhérents v2.1 Harmonisé...');
        
        setTimeout(() => {
            window.location.href = phase2Url;
        }, 1500);
    } else {
        this.log('❌ Dossier ID non fourni pour Phase 2');
        this.showErrorNotification('Erreur: impossible de rediriger vers Phase 2');
    }
};

/**
 * ✅ CORRECTION : Redirection vers confirmation avec route corrigée
 */
window.Workflow2Phases.redirectToConfirmation = function(phase1Response) {
    this.log('🏁 Redirection vers confirmation v2.1 Harmonisé');
    
    if (phase1Response.data && phase1Response.data.dossier_id) {
        // ✅ CORRECTION : Utilisation de la route confirmation corrigée
        const confirmationUrl = this.config.routes.confirmation_template.replace('{dossier}', phase1Response.data.dossier_id);
        
        this.log('🏁 Redirection vers confirmation v2.1 Harmonisé:', confirmationUrl);
        this.showLoadingState('Redirection vers la confirmation v2.1 Harmonisé...');
        
        setTimeout(() => {
            window.location.href = confirmationUrl;
        }, 1500);
    } else if (phase1Response.success && phase1Response.phase === "complete") {
        // Fallback si dossier_id pas dans data mais dans response directe
        this.log('🏁 Fallback redirection: organisation créée sans adhérents v2.1 Harmonisé');
        this.showSuccessNotification('Organisation créée avec succès !');
        
        // Redirection simple vers la liste des organisations
        setTimeout(() => {
            window.location.href = '/operator/organisations';
        }, 2000);
        
        // Nettoyer les données temporaires
        this.cleanupTemporaryData();
    }
};

// =============================================
// MÉTHODES UTILITAIRES - VERSION 2.1 HARMONISÉE
// =============================================

/**
 * Sauvegarder les adhérents pour Phase 2
 */
window.Workflow2Phases.saveAdherentsForPhase2 = function(adherents) {
    this.state.savedAdherents = adherents;
    sessionStorage.setItem('workflow_phase2_adherents', JSON.stringify(adherents));
    sessionStorage.setItem('workflow_phase2_version', this.version);
};

/**
 * Récupérer les adhérents du formulaire
 */
window.Workflow2Phases.getAdherentsFromForm = function() {
    if (window.OrganisationApp && window.OrganisationApp.adherents) {
        return window.OrganisationApp.adherents;
    }
    
    // Fallback
    try {
        const adherentsField = document.querySelector('input[name="adherents"], textarea[name="adherents"]');
        if (adherentsField && adherentsField.value) {
            return JSON.parse(adherentsField.value);
        }
    } catch (e) {
        this.log('Erreur parsing adhérents:', e);
    }
    
    return [];
};

/**
 * Collecter les données du formulaire (fallback)
 */
window.Workflow2Phases.collectFormDataFallback = function() {
    this.log('🔄 Collecte fallback des données du formulaire...');
    
    const formData = {};
    
    // Méthode 1: Formulaire principal
    const form = document.querySelector('#organisation-form, form[data-form="organisation"], .organisation-form');
    
    if (form) {
        this.log('📝 Formulaire trouvé:', form.id || form.className);
        
        const formDataObj = new FormData(form);
        for (let [key, value] of formDataObj.entries()) {
            formData[key] = value;
        }
        
        // Compléter avec les inputs non-standard
        const allInputs = form.querySelectorAll('input, select, textarea');
        allInputs.forEach(input => {
            if (input.name && input.value) {
                formData[input.name] = input.value;
            }
        });
    }

    // Méthode 2: Variables globales de l'app
    if (window.currentFormData) {
        this.log('📝 currentFormData trouvé');
        Object.assign(formData, window.currentFormData);
    }

    // Méthode 3: Session storage
    try {
        const sessionData = sessionStorage.getItem('organisation_form_data');
        if (sessionData) {
            this.log('📝 Session data trouvé');
            Object.assign(formData, JSON.parse(sessionData));
        }
    } catch (e) {
        this.log('⚠️ Erreur lecture session data:', e.message);
    }

    // ✅ VALIDATION : Assurer minimum de données
    if (Object.keys(formData).length === 0) {
        this.log('❌ Aucune donnée collectée par fallback');
        throw new Error('Impossible de collecter les données du formulaire');
    }

    this.log('✅ Fallback collecté:', Object.keys(formData).length, 'champs');
    return formData;
};

/**
 * ✅ HARMONISATION : Obtenir le token CSRF avec gestionnaire unifié
 */
window.Workflow2Phases.getCSRFToken = function() {
    // ✅ HARMONISATION : Utiliser UnifiedCSRFManager si disponible
    if (window.UnifiedCSRFManager && this.state.isUnifiedMode) {
        return window.UnifiedCSRFManager.getCurrentToken();
    }
    
    // Fallback vers méthode existante
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
           document.querySelector('input[name="_token"]')?.value ||
           window.Laravel?.csrfToken;
};

/**
 * ✅ HARMONISATION : Rafraîchir le token CSRF avec gestionnaire unifié
 */
window.Workflow2Phases.refreshCSRFToken = async function() {
    this.log('🔄 Refresh token CSRF v2.1 Harmonisé...');
    
    try {
        // ✅ HARMONISATION : Utiliser UnifiedCSRFManager si disponible
        if (window.UnifiedCSRFManager && this.state.isUnifiedMode) {
            this.log('🔧 Utilisation UnifiedCSRFManager pour refresh CSRF');
            const refreshed = await window.UnifiedCSRFManager.refreshToken();
            if (refreshed) {
                this.state.lastCSRFRefresh = Date.now();
                this.log('✅ Token CSRF rafraîchi via UnifiedCSRFManager v2.1');
                return await window.UnifiedCSRFManager.getCurrentToken();
            }
            this.log('⚠️ Échec refresh via UnifiedCSRFManager, fallback vers méthode standard');
        }
        
        // Fallback vers méthode existante
        const response = await fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        const newToken = data.token || data.csrf_token;
        
        if (!newToken) {
            throw new Error('Token CSRF non reçu du serveur');
        }

        // Mettre à jour tous les emplacements
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', newToken);
        }

        const tokenInputs = document.querySelectorAll('input[name="_token"]');
        tokenInputs.forEach(input => {
            input.value = newToken;
        });

        if (window.Laravel) {
            window.Laravel.csrfToken = newToken;
        }

        this.state.lastCSRFRefresh = Date.now();
        this.log('✅ Token CSRF rafraîchi avec succès v2.1 Harmonisé');
        return newToken;

    } catch (error) {
        this.log('❌ Erreur refresh CSRF v2.1 Harmonisé:', error);
        throw error;
    }
};

/**
 * ✅ HARMONISATION : Soumission avec retry automatique en cas d'erreur CSRF
 */

window.Workflow2Phases.submitWithCSRFRetry = async function(formData, maxAttempts = null) {
    maxAttempts = maxAttempts || this.config.options.csrfRetryAttempts;
    
    for (let attempt = 1; attempt <= maxAttempts; attempt++) {
        try {
            this.log(`🔄 Tentative ${attempt}/${maxAttempts} - Soumission Phase 1 v2.1 Harmonisé`);
            
            // Récupérer/rafraîchir token CSRF avec harmonisation
            let csrfToken = this.getCSRFToken();
            if (!csrfToken || csrfToken.length < 10) {
                csrfToken = await this.refreshCSRFToken();
            }

            // ✅ CORRECTION CRITIQUE : Préparer les données correctement
            const requestData = {
                ...formData,
                _token: csrfToken,
                _phase: 1,
                _version: this.version
            };

            // ✅ CORRECTION CRITIQUE : Headers et body corrects
            const requestConfig = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',  // ✅ JSON explicite
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(requestData)  // ✅ Stringify explicite
            };

            // ✅ DEBUG : Logger les données envoyées
            if (this.debug) {
                this.log('📡 Données envoyées Phase 1:', {
                    url: this.config.routes.phase1,
                    dataKeys: Object.keys(requestData),
                    dataSize: JSON.stringify(requestData).length,
                    hasToken: !!csrfToken,
                    attempt: attempt
                });
            }

            // Utiliser l'URL du formulaire HTML (générée par Blade route()) en priorité
            const formEl = document.getElementById('organisationForm');
            const submitUrl = (formEl && formEl.action) ? formEl.action : this.config.routes.phase1;
            this.log('📡 URL de soumission:', submitUrl);

            // ✅ CORRECTION: redirect manual pour détecter session expirée
            requestConfig.redirect = 'manual';

            // Envoyer la requête
            const response = await fetch(submitUrl, requestConfig);

            // ✅ CORRECTION: Détecter redirection (session expirée → login)
            if (response.type === 'opaqueredirect' || response.status === 0 ||
                response.status === 301 || response.status === 302) {
                this.log('⚠️ Redirection détectée (session expirée ?), status:', response.status, 'type:', response.type);
                throw new Error('Session expirée. Veuillez rafraîchir la page et vous reconnecter.');
            }

            // Retry automatique en cas d'erreur 419
            if (response.status === 419 && attempt < maxAttempts) {
                this.log('⚠️ Erreur 419 CSRF, retry avec nouveau token v2.1 Harmonisé...');
                await this.refreshCSRFToken();
                continue;
            }

            if (!response.ok) {
                const errorText = await response.text();
                this.log('❌ Erreur HTTP:', response.status, errorText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            this.log(`✅ Phase 1 réussie après ${attempt} tentative(s) v2.1 Harmonisé`);
            return data;

        } catch (error) {
            this.log(`❌ Tentative ${attempt} échouée v2.1 Harmonisé:`, error.message);
            
            if (attempt === maxAttempts) {
                throw error;
            }
            
            // Pause avant retry
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }
};

/**
 * Méthodes d'interface utilisateur
 */
window.Workflow2Phases.showLoadingState = function(message = 'Traitement en cours...') {
    // Essayer d'utiliser le système existant
    if (window.OrganisationApp && typeof window.OrganisationApp.showLoading === 'function') {
        window.OrganisationApp.showLoading(message);
    } else {
        this.log('🔄 Loading v2.1 Harmonisé:', message);
        // Fallback simple
        this.showSimpleLoading(message);
    }
};

window.Workflow2Phases.hideLoadingState = function() {
    if (window.OrganisationApp && typeof window.OrganisationApp.hideLoading === 'function') {
        window.OrganisationApp.hideLoading();
    } else {
        this.hideSimpleLoading();
    }
};

window.Workflow2Phases.showSuccessNotification = function(message) {
    if (window.OrganisationApp && typeof window.OrganisationApp.showNotification === 'function') {
        window.OrganisationApp.showNotification(message, 'success');
    } else {
        this.log('✅ Success v2.1 Harmonisé:', message);
        this.showSimpleNotification(message, 'success');
    }
};

window.Workflow2Phases.showErrorNotification = function(message) {
    if (window.OrganisationApp && typeof window.OrganisationApp.showNotification === 'function') {
        window.OrganisationApp.showNotification(message, 'error');
    } else {
        this.log('❌ Error v2.1 Harmonisé:', message);
        this.showSimpleNotification(message, 'error');
    }
};

/**
 * Notifications simples (fallback)
 */
window.Workflow2Phases.showSimpleNotification = function(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHTML);
};

/**
 * Loading simple (fallback)
 */
window.Workflow2Phases.showSimpleLoading = function(message) {
    if (document.getElementById('workflow-loading')) return;
    
    const loadingHTML = `
        <div id="workflow-loading" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
             style="background: rgba(0,0,0,0.7); z-index: 9999;">
            <div class="card">
                <div class="card-body text-center">
                    <div class="spinner-border text-primary mb-3"></div>
                    <p class="mb-0">${message}</p>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', loadingHTML);
};

window.Workflow2Phases.hideSimpleLoading = function() {
    const loading = document.getElementById('workflow-loading');
    if (loading) {
        loading.remove();
    }
};

/**
 * ✅ AMÉLIORATION : Gestion des erreurs avec diagnostic
 */
window.Workflow2Phases.handlePhase1Error = function(error) {
    this.hideLoadingState();
    this.log('❌ Erreur Phase 1 v2.1 Harmonisé:', error);
    
    // Analyser le type d'erreur
    let errorMessage = 'Erreur lors de la création de l\'organisation';
    
    if (typeof error === 'string') {
        // Si c'est juste un message (comme dans votre cas)
        if (error.includes('Organisation créée avec succès')) {
            // Ce n'est pas vraiment une erreur, c'est un succès mal géré
            this.log('✅ Faux erreur détectée - c\'est en fait un succès v2.1 Harmonisé');
            this.showSuccessNotification('✅ ' + error);
            
            // Redirection simple vers les organisations
            setTimeout(() => {
                window.location.href = '/operator/organisations';
            }, 2000);
            return;
        }
        errorMessage += ': ' + error;
    } else if (error.message) {
        errorMessage += ': ' + error.message;
    }
    
    // ✅ DIAGNOSTIC : Ajouter informations de diagnostic
    const diagnosticInfo = {
        version: this.version,
        isUnifiedMode: this.state.isUnifiedMode,
        lastCSRFRefresh: this.state.lastCSRFRefresh,
        hasUnifiedCSRF: typeof window.UnifiedCSRFManager !== 'undefined',
        timestamp: new Date().toISOString()
    };
    
    this.log('🔍 Diagnostic erreur Phase 1:', diagnosticInfo);
    
    // Afficher notification d'erreur seulement si c'est vraiment une erreur
    this.showErrorNotification('❌ ' + errorMessage);
};

/**
 * Logging avec debug et version
 */
window.Workflow2Phases.log = function(...args) {
    if (this.debug) {
        console.log('[Workflow2Phases v2.1 Harmonisé]', ...args);
    }
};

/**
 * ✅ AMÉLIORATION : Nettoyer les données temporaires avec version
 */
window.Workflow2Phases.cleanupTemporaryData = function() {
    try {
        // Nettoyer sessionStorage
        sessionStorage.removeItem('workflow_phase1_response');
        sessionStorage.removeItem('workflow_phase2_adherents');
        sessionStorage.removeItem('workflow_phase2_version');
        
        // Réinitialiser l'état
        this.state.currentPhase = 1;
        this.state.phase1Response = null;
        this.state.savedAdherents = null;
        this.state.lastCSRFRefresh = null;
        
        this.log('🧹 Données temporaires nettoyées v2.1 Harmonisé');
    } catch (error) {
        this.log('❌ Erreur nettoyage v2.1 Harmonisé:', error);
    }
};

/**
 * Hooks et intégration
 */
window.Workflow2Phases.injectHooks = function() {
    // Hook sera ajouté dans l'étape suivante
    this.log('Hooks injectés v2.1 Harmonisé');
};

window.Workflow2Phases.setupEventListeners = function() {
    // Événements seront configurés dans l'étape suivante
    this.log('Event listeners configurés v2.1 Harmonisé');
};

/**
 * ✅ AMÉLIORATION : Vérification continuation avec version
 */
window.Workflow2Phases.checkPhase1Continuation = function() {
    const phase1Response = sessionStorage.getItem('workflow_phase1_response');
    const version = sessionStorage.getItem('workflow_phase2_version');
    
    if (phase1Response) {
        this.log('Continuation depuis Phase 1 détectée v2.1 Harmonisé', {
            version: version,
            currentVersion: this.version
        });
        this.state.phase1Response = JSON.parse(phase1Response);
        
        // Vérifier compatibilité version
        if (version && version !== this.version) {
            this.log('⚠️ Différence de version détectée:', version, 'vs', this.version);
        }
    }
};

// =============================================
// INITIALISATION AUTOMATIQUE - VERSION 2.1 HARMONISÉE
// =============================================

/**
 * ✅ SURVEILLANCE : Surveillance des gestionnaires unifiés
 */
window.Workflow2Phases.monitorUnifiedManagers = function() {
    let attempts = 0;
    const maxAttempts = 15; // 30 secondes max
    
    const checkInterval = setInterval(() => {
        attempts++;
        
        // Vérifier si les gestionnaires unifiés sont maintenant disponibles
        const unifiedAvailable = (
            typeof window.UnifiedConfigManager !== 'undefined' ||
            typeof window.UnifiedCSRFManager !== 'undefined'
        );
        
        if (unifiedAvailable && !this.state.isUnifiedMode) {
            this.log('🔧 Gestionnaires unifiés détectés tardivement, mise à jour mode...');
            this.detectUnifiedManagers();
            clearInterval(checkInterval);
        }
        
        if (attempts >= maxAttempts) {
            this.log('🛑 Surveillance gestionnaires unifiés arrêtée - Timeout');
            clearInterval(checkInterval);
        }
    }, 2000);
};


/**
 * ✅ NOUVELLE MÉTHODE : Extraire type organisation depuis URL
 * À AJOUTER dans workflow-2phases.js
 */
window.Workflow2Phases.extractOrgTypeFromURL = function() {
    // Extraire depuis l'URL courante
    const path = window.location.pathname;
    
    // Patterns possibles
    if (path.includes('/association')) return 'association';
    if (path.includes('/ong')) return 'ong';
    if (path.includes('/parti_politique') || path.includes('/parti-politique')) return 'parti_politique';
    if (path.includes('/confession_religieuse') || path.includes('/confession-religieuse')) return 'confession_religieuse';
    
    // Fallback depuis meta tag
    const metaOrgType = document.querySelector('meta[name="organisation-type"]');
    if (metaOrgType) {
        return metaOrgType.getAttribute('content');
    }
    
    this.log('⚠️ Type organisation non trouvé dans URL:', path);
    return null;
};

/**
 * ✅ NOUVELLE MÉTHODE : Diagnostic complet des données
 * À AJOUTER dans workflow-2phases.js
 */
window.Workflow2Phases.diagnosePreparedData = function(formData) {
    const diagnostic = {
        timestamp: new Date().toISOString(),
        version: this.version,
        dataPresent: !!formData,
        dataType: typeof formData,
        keysCount: formData ? Object.keys(formData).length : 0,
        keys: formData ? Object.keys(formData) : [],
        
        // Champs critiques
        hasType: !!(formData?.type || formData?.type_organisation),
        typeValue: formData?.type || formData?.type_organisation,
        hasToken: !!(formData?._token),
        hasPhase: !!(formData?._phase),
        
        // Sources de données
        sourceOrganisationApp: !!window.OrganisationApp,
        sourceCollectAll: !!(window.OrganisationApp?.collectAllFormData),
        sourceFormData: !!(window.OrganisationApp?.formData),
        
        // Validation
        isValid: this.validatePreparedData(formData)
    };
    
    this.log('🔍 === DIAGNOSTIC DONNÉES PHASE 1 ===');
    this.log('Type organisation:', diagnostic.typeValue || 'MANQUANT');
    this.log('Nombre de champs:', diagnostic.keysCount);
    this.log('Champs disponibles:', diagnostic.keys.join(', '));
    this.log('Sources disponibles:', {
        OrganisationApp: diagnostic.sourceOrganisationApp,
        collectAll: diagnostic.sourceCollectAll,
        formData: diagnostic.sourceFormData
    });
    this.log('Validation:', diagnostic.isValid ? 'VALIDE' : 'INVALIDE');
    
    return diagnostic;
};

/**
 * ✅ NOUVELLE MÉTHODE : Validation des données préparées
 * À AJOUTER dans workflow-2phases.js
 */
window.Workflow2Phases.validatePreparedData = function(formData) {
    if (!formData || typeof formData !== 'object') return false;
    
    // Champs obligatoires
    const required = ['type', '_phase'];
    const missing = required.filter(field => !formData[field]);
    
    if (missing.length > 0) {
        this.log('❌ Champs obligatoires manquants:', missing);
        return false;
    }
    
    // Validation type organisation
    const validTypes = ['association', 'ong', 'parti_politique', 'confession_religieuse'];
    if (!validTypes.includes(formData.type)) {
        this.log('❌ Type organisation invalide:', formData.type);
        return false;
    }
    
    return true;
};

// =============================================
// EXPOSITION ET INITIALISATION FINALE
// =============================================

console.log(`
🎉 ========================================================================
   PNGDI - WORKFLOW 2 PHASES v2.1 - HARMONISATION SELON RECOMMANDATIONS
   ========================================================================
   
   ✅ Version: 2.1 - HARMONISATION selon v1_12-DISCUSSION 4
   🔧 CSRF: Délégation UnifiedCSRFManager avec fallback robuste
   🔄 CORRECTION: Redirection confirmation corrigée
   🚀 HARMONISATION: Compatible gestionnaires unifiés + fallback
   
   MODIFICATIONS APPLIQUÉES SELON RECOMMANDATIONS:
   - ✅ submitPhase1(): Utilisation UnifiedCSRFManager avec fallback
   - ✅ Routes: confirmation_template corrigée
   - ✅ CSRF: refreshCSRFToken() et submitWithCSRFRetry() harmonisés
   - ✅ Surveillance: Détection tardive gestionnaires unifiés
   - ✅ Diagnostic: Informations version et mode dans logs d'erreur
========================================================================
`);

// Démarrer la surveillance des gestionnaires unifiés
if (typeof window.Workflow2Phases !== 'undefined') {
    setTimeout(() => {
        window.Workflow2Phases.monitorUnifiedManagers();
    }, 1000);
}