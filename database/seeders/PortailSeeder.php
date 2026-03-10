<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PortailActualite;
use App\Models\PortailDocument;
use App\Models\PortailFaq;
use App\Models\PortailGuide;
use App\Models\PortailEvenement;
use App\Models\PortailParametre;

class PortailSeeder extends Seeder
{
    public function run()
    {
        $this->seedParametres();
        $this->seedActualites();
        $this->seedDocuments();
        $this->seedFaqs();
        $this->seedGuides();
        $this->seedEvenements();
    }

    private function seedParametres()
    {
        $params = [
            // Hero
            ['cle' => 'hero_titre',       'valeur' => 'Portail National de Gestion des Libertés Individuelles', 'type' => 'text', 'groupe' => 'hero', 'description' => 'Titre principal de la page d\'accueil'],
            ['cle' => 'hero_sous_titre',  'valeur' => 'Simplifiez vos démarches administratives pour la gestion de vos organisations',                'type' => 'text', 'groupe' => 'hero', 'description' => 'Sous-titre de la page d\'accueil'],
            ['cle' => 'hero_bouton1_texte', 'valeur' => 'Créer mon organisation', 'type' => 'text', 'groupe' => 'hero', 'description' => 'Texte du bouton primaire'],
            ['cle' => 'hero_bouton2_texte', 'valeur' => 'En savoir plus',          'type' => 'text', 'groupe' => 'hero', 'description' => 'Texte du bouton secondaire'],

            // Stats accueil
            ['cle' => 'stats_associations', 'valeur' => '150', 'type' => 'text', 'groupe' => 'stats', 'description' => 'Nombre d\'associations'],
            ['cle' => 'stats_confessions',  'valeur' => '45',  'type' => 'text', 'groupe' => 'stats', 'description' => 'Nombre de confessions religieuses'],
            ['cle' => 'stats_partis',       'valeur' => '12',  'type' => 'text', 'groupe' => 'stats', 'description' => 'Nombre de partis politiques'],
            ['cle' => 'stats_ong',          'valeur' => '87',  'type' => 'text', 'groupe' => 'stats', 'description' => 'Nombre d\'ONG'],

            // About / Présentation
            ['cle' => 'about_titre',        'valeur' => 'À propos du PNGDI',                                                            'type' => 'text', 'groupe' => 'about', 'description' => 'Titre de la section À propos'],
            ['cle' => 'about_intro',        'valeur' => 'Le Portail National de Gestion des Libertés Individuelles (PNGDI) est une initiative du Ministère de l\'Intérieur et de la Sécurité du Gabon visant à moderniser et faciliter la gestion des organisations associatives, religieuses et politiques.', 'type' => 'html', 'groupe' => 'about', 'description' => 'Introduction de la page À propos'],
            ['cle' => 'about_mission',      'valeur' => 'Notre mission est de simplifier les démarches administratives, assurer la transparence dans la gestion des organisations et promouvoir l\'engagement civique.', 'type' => 'html', 'groupe' => 'about', 'description' => 'Mission du PNGDI'],

            // Contact
            ['cle' => 'contact_adresse',    'valeur' => 'Ministère de l\'Intérieur et de la Sécurité, BP 2110, Libreville, Gabon', 'type' => 'text', 'groupe' => 'contact', 'description' => 'Adresse postale'],
            ['cle' => 'contact_telephone',  'valeur' => '+241 01 76 00 00',             'type' => 'phone', 'groupe' => 'contact', 'description' => 'Numéro de téléphone principal'],
            ['cle' => 'contact_email',      'valeur' => 'contact@pngdi.ga',             'type' => 'email', 'groupe' => 'contact', 'description' => 'Email de contact'],
            ['cle' => 'contact_horaires',   'valeur' => 'Du lundi au vendredi : 8h00 - 17h00', 'type' => 'text', 'groupe' => 'contact', 'description' => 'Horaires d\'ouverture'],
            ['cle' => 'contact_email_admin','valeur' => 'admin@pngdi.ga',               'type' => 'email', 'groupe' => 'contact', 'description' => 'Email de réception des messages du portail'],

            // Footer
            ['cle' => 'footer_copyright',   'valeur' => '© ' . date('Y') . ' PNGDI - Ministère de l\'Intérieur du Gabon. Tous droits réservés.', 'type' => 'text', 'groupe' => 'footer', 'description' => 'Texte de copyright du footer'],
            ['cle' => 'footer_description', 'valeur' => 'Plateforme nationale de gestion des libertés individuelles au Gabon.',               'type' => 'text', 'groupe' => 'footer', 'description' => 'Description courte dans le footer'],
        ];

        foreach ($params as $param) {
            PortailParametre::updateOrCreate(['cle' => $param['cle']], $param);
        }
    }

    private function seedActualites()
    {
        $actualites = [
            [
                'slug'             => 'nouvelle-procedure-formalisation-simplifiee',
                'titre'            => 'Nouvelle procédure de formalisation simplifiée',
                'date_publication' => '2025-01-15',
                'extrait'          => 'Le Ministère de l\'Intérieur annonce la simplification des démarches administratives pour la création d\'associations.',
                'contenu'          => '<p>Le Ministère de l\'Intérieur et de la Sécurité a le plaisir d\'annoncer la mise en place d\'une nouvelle procédure simplifiée pour la formalisation des organisations associatives.</p><h3>Les principales améliorations</h3><ul><li>Réduction du nombre de documents requis de 12 à 7</li><li>Délai de traitement ramené de 30 à 15 jours ouvrables</li><li>Possibilité de soumettre tous les documents en ligne</li></ul>',
                'categorie'        => 'Réglementation',
                'auteur'           => 'Administration PNGDI',
                'statut'           => 'publie',
                'vues'             => 234,
                'en_une'           => true,
            ],
            [
                'slug'             => 'seminaire-national-associations-2025',
                'titre'            => 'Séminaire national sur les associations',
                'date_publication' => '2025-01-10',
                'extrait'          => 'Un séminaire de formation pour les responsables d\'associations se tiendra du 25 au 27 janvier 2025 à Libreville.',
                'contenu'          => '<p>Le Ministère de l\'Intérieur organise un séminaire national de formation pour les responsables d\'associations du 25 au 27 janvier 2025 à Libreville.</p><h3>Programme</h3><ul><li>Jour 1 : Cadre juridique et réglementaire</li><li>Jour 2 : Gestion administrative et financière</li><li>Jour 3 : Communication et développement</li></ul>',
                'categorie'        => 'Événement',
                'auteur'           => 'Direction des Associations',
                'statut'           => 'publie',
                'vues'             => 156,
                'en_une'           => false,
            ],
            [
                'slug'             => 'mise-a-jour-documents-requis-ong',
                'titre'            => 'Mise à jour des documents requis pour les ONG',
                'date_publication' => '2025-01-05',
                'extrait'          => 'La liste des documents nécessaires pour la création d\'ONG a été actualisée.',
                'contenu'          => '<p>Suite aux recommandations du comité de simplification administrative, la liste des documents requis pour la création d\'ONG a été révisée.</p>',
                'categorie'        => 'Documentation',
                'auteur'           => 'Service Juridique',
                'statut'           => 'publie',
                'vues'             => 412,
                'en_une'           => false,
            ],
            [
                'slug'             => 'bilan-annuel-2024-organisations',
                'titre'            => 'Bilan 2024 : 200 nouvelles organisations créées',
                'date_publication' => '2025-01-02',
                'extrait'          => 'Le PNGDI dresse un bilan positif de l\'année 2024 avec plus de 200 nouvelles organisations formalisées.',
                'contenu'          => '<p>Le PNGDI présente son bilan annuel pour 2024, marquée par une croissance de 35% du nombre d\'organisations.</p>',
                'categorie'        => 'Statistiques',
                'auteur'           => 'Administration PNGDI',
                'statut'           => 'publie',
                'vues'             => 523,
                'en_une'           => false,
            ],
            [
                'slug'             => 'nouvelle-plateforme-pngdi-lancee',
                'titre'            => 'Lancement officiel de la nouvelle plateforme PNGDI',
                'date_publication' => '2024-12-20',
                'extrait'          => 'La nouvelle version du portail PNGDI est maintenant disponible avec une interface modernisée.',
                'contenu'          => '<p>Nous sommes heureux d\'annoncer le lancement officiel de la nouvelle version du PNGDI.</p>',
                'categorie'        => 'Annonce',
                'auteur'           => 'Équipe Technique',
                'statut'           => 'publie',
                'vues'             => 789,
                'en_une'           => true,
            ],
            [
                'slug'             => 'guide-declaration-annuelle-2025',
                'titre'            => 'Guide pratique : Déclaration annuelle 2025',
                'date_publication' => '2024-12-15',
                'extrait'          => 'Tout ce que vous devez savoir pour préparer et soumettre votre déclaration annuelle 2025.',
                'contenu'          => '<p>La période de déclaration annuelle 2025 approche. Voici tout ce que vous devez savoir pour être en conformité.</p>',
                'categorie'        => 'Guide',
                'auteur'           => 'Service Conformité',
                'statut'           => 'publie',
                'vues'             => 345,
                'en_une'           => false,
            ],
        ];

        foreach ($actualites as $data) {
            PortailActualite::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }

    private function seedDocuments()
    {
        $documents = [
            ['titre' => 'Guide de création d\'association',        'description' => 'Guide complet pour créer votre association étape par étape.', 'categorie' => 'Guides',         'type_organisation' => 'association', 'format' => 'PDF',  'chemin_fichier' => 'guide-creation-association.pdf',    'nombre_telechargements' => 1234, 'est_actif' => true, 'ordre' => 1],
            ['titre' => 'Manuel de gestion des ONG',              'description' => 'Manuel détaillé sur la gestion administrative et financière des ONG.', 'categorie' => 'Guides',         'type_organisation' => 'ong',         'format' => 'PDF',  'chemin_fichier' => 'manuel-gestion-ong.pdf',            'nombre_telechargements' => 876,  'est_actif' => true, 'ordre' => 2],
            ['titre' => 'Guide de création de parti politique',   'description' => 'Procédures et exigences pour la création d\'un parti politique au Gabon.', 'categorie' => 'Guides',         'type_organisation' => 'parti',       'format' => 'PDF',  'chemin_fichier' => 'guide-parti-politique.pdf',         'nombre_telechargements' => 234,  'est_actif' => true, 'ordre' => 3],
            ['titre' => 'Formulaire de déclaration d\'association','description' => 'Formulaire officiel pour déclarer une nouvelle association.',          'categorie' => 'Formulaires',    'type_organisation' => 'association', 'format' => 'PDF',  'chemin_fichier' => 'form-declaration-association.pdf', 'nombre_telechargements' => 2156, 'est_actif' => true, 'ordre' => 4],
            ['titre' => 'Formulaire de demande d\'agrément ONG',  'description' => 'Formulaire de demande d\'agrément pour les ONG.',                        'categorie' => 'Formulaires',    'type_organisation' => 'ong',         'format' => 'PDF',  'chemin_fichier' => 'form-agrement-ong.pdf',             'nombre_telechargements' => 987,  'est_actif' => true, 'ordre' => 5],
            ['titre' => 'Formulaire de déclaration annuelle',     'description' => 'Formulaire pour la déclaration annuelle d\'activités.',                  'categorie' => 'Formulaires',    'type_organisation' => 'tous',        'format' => 'PDF',  'chemin_fichier' => 'form-declaration-annuelle.pdf',    'nombre_telechargements' => 3421, 'est_actif' => true, 'ordre' => 6],
            ['titre' => 'Modèle de statuts d\'association',       'description' => 'Modèle type de statuts pour association, personnalisable.',              'categorie' => 'Modèles',        'type_organisation' => 'association', 'format' => 'DOCX', 'chemin_fichier' => 'modele-statuts-association.docx',  'nombre_telechargements' => 1876, 'est_actif' => true, 'ordre' => 7],
            ['titre' => 'Modèle de règlement intérieur',          'description' => 'Modèle de règlement intérieur adaptable.',                               'categorie' => 'Modèles',        'type_organisation' => 'tous',        'format' => 'DOCX', 'chemin_fichier' => 'modele-reglement-interieur.docx',  'nombre_telechargements' => 1234, 'est_actif' => true, 'ordre' => 8],
            ['titre' => 'Modèle de PV d\'assemblée générale',     'description' => 'Modèle de procès-verbal pour vos assemblées générales.',                 'categorie' => 'Modèles',        'type_organisation' => 'tous',        'format' => 'DOCX', 'chemin_fichier' => 'modele-pv-ag.docx',                'nombre_telechargements' => 2345, 'est_actif' => true, 'ordre' => 9],
            ['titre' => 'Loi sur les associations',               'description' => 'Texte intégral de la loi régissant les associations au Gabon.',          'categorie' => 'Réglementation', 'type_organisation' => 'association', 'format' => 'PDF',  'chemin_fichier' => 'loi-associations.pdf',              'nombre_telechargements' => 567,  'est_actif' => true, 'ordre' => 10],
            ['titre' => 'Décret sur les ONG',                     'description' => 'Décret d\'application concernant les organisations non gouvernementales.','categorie' => 'Réglementation', 'type_organisation' => 'ong',         'format' => 'PDF',  'chemin_fichier' => 'decret-ong.pdf',                   'nombre_telechargements' => 432,  'est_actif' => true, 'ordre' => 11],
            ['titre' => 'Charte des partis politiques',           'description' => 'Charte nationale régissant les partis politiques.',                       'categorie' => 'Réglementation', 'type_organisation' => 'parti',       'format' => 'PDF',  'chemin_fichier' => 'charte-partis-politiques.pdf',     'nombre_telechargements' => 189,  'est_actif' => true, 'ordre' => 12],
        ];

        foreach ($documents as $data) {
            PortailDocument::updateOrCreate(['titre' => $data['titre']], $data);
        }
    }

    private function seedFaqs()
    {
        $faqs = [
            // Général
            ['question' => 'Qu\'est-ce que le PNGDI ?',          'reponse' => 'Le Portail National de Gestion des Libertés Individuelles (PNGDI) est une plateforme numérique mise en place par le Ministère de l\'Intérieur pour faciliter la formalisation et la gestion des organisations associatives, religieuses et politiques au Gabon.', 'categorie' => 'Général', 'ordre' => 1, 'est_actif' => true],
            ['question' => 'Qui peut utiliser le PNGDI ?',       'reponse' => 'Le portail est accessible à toute personne souhaitant créer ou gérer une association, une ONG, un parti politique ou une confession religieuse au Gabon.', 'categorie' => 'Général', 'ordre' => 2, 'est_actif' => true],
            ['question' => 'Le service est-il gratuit ?',        'reponse' => 'L\'inscription et l\'utilisation du portail sont gratuites. Cependant, certaines procédures administratives peuvent être soumises à des frais réglementaires selon la législation en vigueur.', 'categorie' => 'Général', 'ordre' => 3, 'est_actif' => true],
            // Création
            ['question' => 'Quels sont les documents nécessaires pour créer une association ?', 'reponse' => 'Pour créer une association, vous devez fournir : les statuts, le procès-verbal de l\'assemblée constitutive, la liste des membres fondateurs, les copies de pièces d\'identité des dirigeants, et un justificatif de domicile du siège social.', 'categorie' => 'Création', 'ordre' => 1, 'est_actif' => true],
            ['question' => 'Combien de temps prend le traitement d\'un dossier ?', 'reponse' => 'Le délai de traitement varie selon le type d\'organisation : 15 jours ouvrables pour une association, 30 jours pour une ONG, 45 jours pour un parti politique, et 30 jours pour une confession religieuse.', 'categorie' => 'Création', 'ordre' => 2, 'est_actif' => true],
            ['question' => 'Puis-je créer plusieurs organisations ?', 'reponse' => 'Un utilisateur peut créer plusieurs associations ou ONG. Cependant, il ne peut créer qu\'un seul parti politique actif à la fois, conformément à la réglementation.', 'categorie' => 'Création', 'ordre' => 3, 'est_actif' => true],
            // Technique
            ['question' => 'Puis-je suivre l\'avancement de mon dossier en ligne ?', 'reponse' => 'Oui, une fois votre dossier soumis, vous pouvez suivre son avancement en temps réel depuis votre espace personnel.', 'categorie' => 'Technique', 'ordre' => 1, 'est_actif' => true],
            ['question' => 'Comment récupérer mon mot de passe ?', 'reponse' => 'Cliquez sur "Mot de passe oublié" sur la page de connexion. Entrez votre adresse email, et vous recevrez un lien de réinitialisation valable 24 heures.', 'categorie' => 'Technique', 'ordre' => 2, 'est_actif' => true],
            ['question' => 'Quels formats de documents sont acceptés ?', 'reponse' => 'Le portail accepte les formats PDF, JPEG, PNG pour les documents scannés. La taille maximale par fichier est de 5 MB.', 'categorie' => 'Technique', 'ordre' => 3, 'est_actif' => true],
            // Gestion
            ['question' => 'Quand dois-je faire ma déclaration annuelle ?', 'reponse' => 'La déclaration annuelle doit être soumise avant le 31 mars de chaque année. Elle comprend le rapport d\'activités, le bilan financier et la liste actualisée des membres du bureau.', 'categorie' => 'Gestion', 'ordre' => 1, 'est_actif' => true],
            ['question' => 'Que se passe-t-il si je ne fais pas ma déclaration annuelle ?', 'reponse' => 'Le non-respect de l\'obligation de déclaration annuelle peut entraîner des sanctions allant de l\'avertissement à la suspension temporaire ou définitive de l\'agrément.', 'categorie' => 'Gestion', 'ordre' => 2, 'est_actif' => true],
            ['question' => 'Comment modifier les informations de mon organisation ?', 'reponse' => 'Connectez-vous à votre espace personnel et accédez à la section "Gérer mon organisation". Vous pourrez y modifier les informations et soumettre les documents justificatifs nécessaires.', 'categorie' => 'Gestion', 'ordre' => 3, 'est_actif' => true],
        ];

        foreach ($faqs as $data) {
            PortailFaq::updateOrCreate(['question' => $data['question']], $data);
        }
    }

    private function seedGuides()
    {
        $guides = [
            ['titre' => 'Guide de création d\'association',  'description' => 'Tout ce que vous devez savoir pour créer votre association étape par étape', 'nombre_pages' => 24, 'categorie' => 'Association',    'nombre_telechargements' => 1543, 'est_actif' => true, 'ordre' => 1],
            ['titre' => 'Manuel de l\'utilisateur PNGDI',   'description' => 'Guide complet d\'utilisation de la plateforme avec captures d\'écran',        'nombre_pages' => 45, 'categorie' => 'Général',         'nombre_telechargements' => 2876, 'est_actif' => true, 'ordre' => 2],
            ['titre' => 'Procédures pour les ONG',          'description' => 'Procédures spécifiques et exigences pour les organisations non gouvernementales', 'nombre_pages' => 32, 'categorie' => 'ONG',           'nombre_telechargements' => 987,  'est_actif' => true, 'ordre' => 3],
            ['titre' => 'Guide fiscal pour associations',   'description' => 'Comprendre les obligations fiscales et les exonérations possibles',           'nombre_pages' => 18, 'categorie' => 'Association',    'nombre_telechargements' => 654,  'est_actif' => true, 'ordre' => 4],
            ['titre' => 'Modèle de gestion partis politiques', 'description' => 'Bonnes pratiques de gestion administrative et financière',                'nombre_pages' => 28, 'categorie' => 'Parti politique', 'nombre_telechargements' => 234,  'est_actif' => true, 'ordre' => 5],
        ];

        foreach ($guides as $data) {
            PortailGuide::updateOrCreate(['titre' => $data['titre']], $data);
        }
    }

    private function seedEvenements()
    {
        $evenements = [
            ['titre' => 'Date limite déclarations annuelles',    'description' => 'Toutes les organisations doivent soumettre leur déclaration annuelle avant cette date.', 'type' => 'echeance',   'date_debut' => '2026-03-31', 'est_important' => true,  'est_actif' => true],
            ['titre' => 'Formation en ligne - Gestion associative','description' => 'Webinaire gratuit sur les bonnes pratiques de gestion d\'association.',                'type' => 'formation',  'date_debut' => '2026-02-15', 'est_important' => false, 'est_actif' => true],
            ['titre' => 'Maintenance programmée du portail',     'description' => 'Le portail sera indisponible de 22h à 2h pour maintenance.',                           'type' => 'maintenance', 'date_debut' => '2026-02-01', 'est_important' => true,  'est_actif' => true],
            ['titre' => 'Séminaire national des ONG',            'description' => 'Rencontre annuelle des ONG au Centre International de Conférences.',                  'type' => 'evenement',  'date_debut' => '2026-04-10', 'est_important' => false, 'est_actif' => true, 'lieu' => 'Centre International de Conférences, Libreville'],
            ['titre' => 'Ouverture inscriptions formations Q2',  'description' => 'Inscriptions pour les formations du deuxième trimestre.',                              'type' => 'formation',  'date_debut' => '2026-03-15', 'est_important' => false, 'est_actif' => true],
        ];

        foreach ($evenements as $data) {
            PortailEvenement::updateOrCreate(['titre' => $data['titre']], $data);
        }
    }
}
