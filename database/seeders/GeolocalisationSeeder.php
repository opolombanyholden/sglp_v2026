<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SEEDER GÉOLOCALISATION GABON - PNGDI
 *
 * Peuple toutes les tables de géolocalisation avec des données réalistes
 * des 9 provinces du Gabon et leurs subdivisions administratives.
 *
 * Hiérarchie :
 *   Province → Département → Commune/Ville (urbain) → Arrondissement → Quartier (localité)
 *   Province → Département → Canton (rural) → Regroupement → Village (localité)
 */
class GeolocalisationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🌍 ===============================================');
        $this->command->info('🌍   GÉOLOCALISATION GABON - PEUPLEMENT COMPLET  ');
        $this->command->info('🌍 ===============================================');
        $this->command->info('');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            $startTime = microtime(true);

            // Nettoyer les tables dans l'ordre inverse des dépendances
            $this->command->info('🧹 Nettoyage des tables existantes...');
            DB::table('localites')->delete();
            DB::table('regroupements')->delete();
            DB::table('cantons')->delete();
            DB::table('arrondissements')->delete();
            DB::table('communes_villes')->delete();
            DB::table('departements')->delete();
            DB::table('provinces')->delete();
            $this->command->info('✅ Tables nettoyées');
            $this->command->info('');

            // ============================================================
            // ÉTAPE 1 : PROVINCES (9 provinces du Gabon)
            // ============================================================
            $this->command->info('📋 ÉTAPE 1/6 : Création des 9 Provinces...');
            $provinces = $this->seedProvinces();
            $this->command->info("✅ {$provinces->count()} provinces créées");
            $this->command->info('');

            // ============================================================
            // ÉTAPE 2 : DÉPARTEMENTS
            // ============================================================
            $this->command->info('📋 ÉTAPE 2/6 : Création des Départements...');
            $deptCount = $this->seedDepartements($provinces);
            $this->command->info("✅ {$deptCount} départements créés");
            $this->command->info('');

            // ============================================================
            // ÉTAPE 3 : COMMUNES / VILLES (Zone Urbaine)
            // ============================================================
            $this->command->info('📋 ÉTAPE 3/6 : Création des Communes et Villes...');
            $communeCount = $this->seedCommunesVilles();
            $this->command->info("✅ {$communeCount} communes/villes créées");
            $this->command->info('');

            // ============================================================
            // ÉTAPE 4 : ARRONDISSEMENTS + QUARTIERS (Zone Urbaine)
            // ============================================================
            $this->command->info('📋 ÉTAPE 4/6 : Création des Arrondissements et Quartiers...');
            $arrQuartierCounts = $this->seedArrondissementsEtQuartiers();
            $this->command->info("✅ {$arrQuartierCounts['arrondissements']} arrondissements, {$arrQuartierCounts['quartiers']} quartiers créés");
            $this->command->info('');

            // ============================================================
            // ÉTAPE 5 : CANTONS (Zone Rurale)
            // ============================================================
            $this->command->info('📋 ÉTAPE 5/6 : Création des Cantons...');
            $cantonCount = $this->seedCantons();
            $this->command->info("✅ {$cantonCount} cantons créés");
            $this->command->info('');

            // ============================================================
            // ÉTAPE 6 : REGROUPEMENTS + VILLAGES (Zone Rurale)
            // ============================================================
            $this->command->info('📋 ÉTAPE 6/6 : Création des Regroupements et Villages...');
            $ruralCounts = $this->seedRegroupementsEtVillages();
            $this->command->info("✅ {$ruralCounts['regroupements']} regroupements, {$ruralCounts['villages']} villages créés");
            $this->command->info('');

            // Statistiques finales
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $this->displayStats($duration);

        } catch (\Exception $e) {
            $this->command->error('❌ Erreur : ' . $e->getMessage());
            Log::error('GeolocalisationSeeder error: ' . $e->getMessage());
            throw $e;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    // =================================================================
    // PROVINCES
    // =================================================================

    private function seedProvinces()
    {
        $data = [
            ['nom' => 'Estuaire',        'code' => 'EST', 'chef_lieu' => 'Libreville',      'latitude' => 0.4162,   'longitude' => 9.4673,   'superficie_km2' => 20740, 'population_estimee' => 1200000, 'ordre_affichage' => 1],
            ['nom' => 'Haut-Ogooué',     'code' => 'HOG', 'chef_lieu' => 'Franceville',     'latitude' => -1.6333,  'longitude' => 13.5833,  'superficie_km2' => 36547, 'population_estimee' => 250000,  'ordre_affichage' => 2],
            ['nom' => 'Moyen-Ogooué',    'code' => 'MOG', 'chef_lieu' => 'Lambaréné',       'latitude' => -0.7000,  'longitude' => 10.2333,  'superficie_km2' => 18535, 'population_estimee' => 70000,   'ordre_affichage' => 3],
            ['nom' => 'Ngounié',          'code' => 'NGO', 'chef_lieu' => 'Mouila',          'latitude' => -1.8667,  'longitude' => 11.0500,  'superficie_km2' => 37750, 'population_estimee' => 100000,  'ordre_affichage' => 4],
            ['nom' => 'Nyanga',           'code' => 'NYA', 'chef_lieu' => 'Tchibanga',       'latitude' => -2.8500,  'longitude' => 11.0167,  'superficie_km2' => 21285, 'population_estimee' => 55000,   'ordre_affichage' => 5],
            ['nom' => 'Ogooué-Ivindo',   'code' => 'OIV', 'chef_lieu' => 'Makokou',         'latitude' => 0.5667,   'longitude' => 12.8500,  'superficie_km2' => 46075, 'population_estimee' => 65000,   'ordre_affichage' => 6],
            ['nom' => 'Ogooué-Lolo',     'code' => 'OLO', 'chef_lieu' => 'Koulamoutou',     'latitude' => -1.1333,  'longitude' => 12.4667,  'superficie_km2' => 25380, 'population_estimee' => 65000,   'ordre_affichage' => 7],
            ['nom' => 'Ogooué-Maritime',  'code' => 'OMA', 'chef_lieu' => 'Port-Gentil',     'latitude' => -0.7193,  'longitude' => 8.7815,   'superficie_km2' => 22890, 'population_estimee' => 160000,  'ordre_affichage' => 8],
            ['nom' => 'Woleu-Ntem',       'code' => 'WNT', 'chef_lieu' => 'Oyem',            'latitude' => 1.6000,   'longitude' => 11.5833,  'superficie_km2' => 38465, 'population_estimee' => 155000,  'ordre_affichage' => 9],
        ];

        foreach ($data as &$row) {
            $row['is_active'] = true;
            $row['created_at'] = now();
            $row['updated_at'] = now();
        }

        DB::table('provinces')->insert($data);

        return DB::table('provinces')->orderBy('ordre_affichage')->get();
    }

    // =================================================================
    // DÉPARTEMENTS
    // =================================================================

    private function seedDepartements($provinces)
    {
        $departementsParProvince = [
            'EST' => [
                ['nom' => 'Komo-Océan',       'code' => 'EST-KOC', 'chef_lieu' => 'Libreville'],
                ['nom' => 'Komo-Mondah',       'code' => 'EST-KMO', 'chef_lieu' => 'Ntoum'],
                ['nom' => 'Noya',              'code' => 'EST-NOY', 'chef_lieu' => 'Cocobeach'],
                ['nom' => 'Komo',              'code' => 'EST-KOM', 'chef_lieu' => 'Kango'],
            ],
            'HOG' => [
                ['nom' => 'Mpassa',           'code' => 'HOG-MPA', 'chef_lieu' => 'Franceville'],
                ['nom' => 'Lékoni-Lékori',    'code' => 'HOG-LLE', 'chef_lieu' => 'Lékoni'],
                ['nom' => 'Plateaux',         'code' => 'HOG-PLA', 'chef_lieu' => 'Léconi'],
                ['nom' => 'Djouori-Agnili',   'code' => 'HOG-DJA', 'chef_lieu' => 'Bongoville'],
                ['nom' => 'Lékoko',           'code' => 'HOG-LEK', 'chef_lieu' => 'Bakoumba'],
                ['nom' => 'Sébé-Brikolo',     'code' => 'HOG-SBR', 'chef_lieu' => 'Okondja'],
            ],
            'MOG' => [
                ['nom' => 'Ogooué et Lacs',   'code' => 'MOG-OGL', 'chef_lieu' => 'Lambaréné'],
                ['nom' => 'Abanga-Bigné',     'code' => 'MOG-ABG', 'chef_lieu' => 'Ndjolé'],
            ],
            'NGO' => [
                ['nom' => 'Douya-Onoye',      'code' => 'NGO-DON', 'chef_lieu' => 'Mouila'],
                ['nom' => 'Dola',              'code' => 'NGO-DOL', 'chef_lieu' => 'Ndendé'],
                ['nom' => 'Louétsi-Wano',     'code' => 'NGO-LWA', 'chef_lieu' => 'Lébamba'],
                ['nom' => 'Tsamba-Magotsi',   'code' => 'NGO-TSM', 'chef_lieu' => 'Fougamou'],
                ['nom' => 'Ogoulou',          'code' => 'NGO-OGO', 'chef_lieu' => 'Mimongo'],
                ['nom' => 'Louétsi-Bibaka',   'code' => 'NGO-LBI', 'chef_lieu' => 'Malinga'],
            ],
            'NYA' => [
                ['nom' => 'Basse-Banio',      'code' => 'NYA-BBA', 'chef_lieu' => 'Tchibanga'],
                ['nom' => 'Haute-Banio',      'code' => 'NYA-HBA', 'chef_lieu' => 'Ndindi'],
                ['nom' => 'Mougoutsi',        'code' => 'NYA-MOU', 'chef_lieu' => 'Tchibanga'],
                ['nom' => 'Douigny',          'code' => 'NYA-DOU', 'chef_lieu' => 'Moabi'],
            ],
            'OIV' => [
                ['nom' => 'Ivindo',           'code' => 'OIV-IVI', 'chef_lieu' => 'Makokou'],
                ['nom' => 'Lopé',             'code' => 'OIV-LOP', 'chef_lieu' => 'Booué'],
                ['nom' => 'Mvoung',           'code' => 'OIV-MVO', 'chef_lieu' => 'Ovan'],
                ['nom' => 'Zadié',            'code' => 'OIV-ZAD', 'chef_lieu' => 'Mékambo'],
            ],
            'OLO' => [
                ['nom' => 'Lolo-Bouenguidi',  'code' => 'OLO-LBG', 'chef_lieu' => 'Koulamoutou'],
                ['nom' => 'Mulundu',          'code' => 'OLO-MUL', 'chef_lieu' => 'Lastoursville'],
                ['nom' => 'Lombo-Bouenguidi', 'code' => 'OLO-LOM', 'chef_lieu' => 'Pana'],
                ['nom' => 'Offoué-Onoye',    'code' => 'OLO-OFF', 'chef_lieu' => 'Iboundji'],
            ],
            'OMA' => [
                ['nom' => 'Bendjé',           'code' => 'OMA-BEN', 'chef_lieu' => 'Port-Gentil'],
                ['nom' => 'Ndougou',          'code' => 'OMA-NDO', 'chef_lieu' => 'Gamba'],
                ['nom' => 'Etimboué',         'code' => 'OMA-ETI', 'chef_lieu' => 'Omboué'],
            ],
            'WNT' => [
                ['nom' => 'Woleu',            'code' => 'WNT-WOL', 'chef_lieu' => 'Oyem'],
                ['nom' => 'Ntem',             'code' => 'WNT-NTE', 'chef_lieu' => 'Bitam'],
                ['nom' => 'Okano',            'code' => 'WNT-OKA', 'chef_lieu' => 'Mitzic'],
                ['nom' => 'Haut-Ntem',        'code' => 'WNT-HNT', 'chef_lieu' => 'Minvoul'],
                ['nom' => 'Haut-Como',        'code' => 'WNT-HCO', 'chef_lieu' => 'Medouneu'],
            ],
        ];

        $count = 0;
        foreach ($provinces as $province) {
            $key = $province->code;
            if (!isset($departementsParProvince[$key])) continue;

            foreach ($departementsParProvince[$key] as $ordre => $dept) {
                DB::table('departements')->insert([
                    'province_id'      => $province->id,
                    'nom'              => $dept['nom'],
                    'code'             => $dept['code'],
                    'chef_lieu'        => $dept['chef_lieu'],
                    'is_active'        => true,
                    'ordre_affichage'  => $ordre + 1,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    // =================================================================
    // COMMUNES ET VILLES
    // =================================================================

    private function seedCommunesVilles()
    {
        $communesParDepartement = [
            // === ESTUAIRE ===
            'EST-KOC' => [
                ['nom' => 'Libreville',            'type' => 'ville',   'statut' => 'Capitale', 'latitude' => 0.3924, 'longitude' => 9.4536, 'population_estimee' => 850000],
            ],
            'EST-KMO' => [
                ['nom' => 'Ntoum',                 'type' => 'commune', 'statut' => null, 'latitude' => 0.3906, 'longitude' => 9.7614, 'population_estimee' => 25000],
                ['nom' => 'Owendo',                'type' => 'ville',   'statut' => null, 'latitude' => 0.2973, 'longitude' => 9.5003, 'population_estimee' => 80000],
                ['nom' => 'Akanda',                'type' => 'commune', 'statut' => null, 'latitude' => 0.5000, 'longitude' => 9.5000, 'population_estimee' => 40000],
            ],
            'EST-NOY' => [
                ['nom' => 'Cocobeach',             'type' => 'commune', 'statut' => null, 'latitude' => 1.0000, 'longitude' => 9.5833, 'population_estimee' => 6000],
            ],
            'EST-KOM' => [
                ['nom' => 'Kango',                 'type' => 'commune', 'statut' => null, 'latitude' => 0.1667, 'longitude' => 10.1500, 'population_estimee' => 12000],
            ],
            // === HAUT-OGOOUÉ ===
            'HOG-MPA' => [
                ['nom' => 'Franceville',           'type' => 'ville',   'statut' => 'Chef-lieu', 'latitude' => -1.6333, 'longitude' => 13.5833, 'population_estimee' => 110000],
            ],
            'HOG-LLE' => [
                ['nom' => 'Lékoni',                'type' => 'commune', 'statut' => null, 'latitude' => -1.5833, 'longitude' => 14.2500, 'population_estimee' => 8000],
            ],
            'HOG-DJA' => [
                ['nom' => 'Bongoville',            'type' => 'commune', 'statut' => null, 'latitude' => -1.6167, 'longitude' => 13.9667, 'population_estimee' => 5000],
            ],
            'HOG-LEK' => [
                ['nom' => 'Bakoumba',              'type' => 'commune', 'statut' => null, 'latitude' => -1.7500, 'longitude' => 12.9000, 'population_estimee' => 4000],
            ],
            'HOG-SBR' => [
                ['nom' => 'Okondja',               'type' => 'commune', 'statut' => null, 'latitude' => -0.6500, 'longitude' => 13.6833, 'population_estimee' => 9000],
            ],
            // === MOYEN-OGOOUÉ ===
            'MOG-OGL' => [
                ['nom' => 'Lambaréné',             'type' => 'ville',   'statut' => 'Chef-lieu', 'latitude' => -0.7000, 'longitude' => 10.2333, 'population_estimee' => 40000],
            ],
            'MOG-ABG' => [
                ['nom' => 'Ndjolé',                'type' => 'commune', 'statut' => null, 'latitude' => -0.1833, 'longitude' => 10.7667, 'population_estimee' => 8000],
            ],
            // === NGOUNIÉ ===
            'NGO-DON' => [
                ['nom' => 'Mouila',                'type' => 'ville',   'statut' => 'Chef-lieu', 'latitude' => -1.8667, 'longitude' => 11.0500, 'population_estimee' => 35000],
            ],
            'NGO-DOL' => [
                ['nom' => 'Ndendé',                'type' => 'commune', 'statut' => null, 'latitude' => -2.4000, 'longitude' => 11.3500, 'population_estimee' => 8000],
            ],
            'NGO-LWA' => [
                ['nom' => 'Lébamba',               'type' => 'commune', 'statut' => null, 'latitude' => -2.2000, 'longitude' => 11.4833, 'population_estimee' => 7000],
            ],
            'NGO-TSM' => [
                ['nom' => 'Fougamou',              'type' => 'commune', 'statut' => null, 'latitude' => -1.2167, 'longitude' => 10.5833, 'population_estimee' => 7000],
            ],
            // === NYANGA ===
            'NYA-BBA' => [
                ['nom' => 'Tchibanga',             'type' => 'ville',   'statut' => 'Chef-lieu', 'latitude' => -2.8500, 'longitude' => 11.0167, 'population_estimee' => 30000],
            ],
            'NYA-DOU' => [
                ['nom' => 'Moabi',                 'type' => 'commune', 'statut' => null, 'latitude' => -2.4333, 'longitude' => 10.9500, 'population_estimee' => 5000],
            ],
            // === OGOOUÉ-IVINDO ===
            'OIV-IVI' => [
                ['nom' => 'Makokou',               'type' => 'ville',   'statut' => 'Chef-lieu', 'latitude' => 0.5667, 'longitude' => 12.8500, 'population_estimee' => 17000],
            ],
            'OIV-LOP' => [
                ['nom' => 'Booué',                 'type' => 'commune', 'statut' => null, 'latitude' => -0.0833, 'longitude' => 11.9333, 'population_estimee' => 6000],
            ],
            'OIV-ZAD' => [
                ['nom' => 'Mékambo',               'type' => 'commune', 'statut' => null, 'latitude' => 1.0167, 'longitude' => 13.9333, 'population_estimee' => 5000],
            ],
            // === OGOOUÉ-LOLO ===
            'OLO-LBG' => [
                ['nom' => 'Koulamoutou',           'type' => 'ville',   'statut' => 'Chef-lieu', 'latitude' => -1.1333, 'longitude' => 12.4667, 'population_estimee' => 20000],
            ],
            'OLO-MUL' => [
                ['nom' => 'Lastoursville',         'type' => 'commune', 'statut' => null, 'latitude' => -0.8167, 'longitude' => 12.7167, 'population_estimee' => 10000],
            ],
            // === OGOOUÉ-MARITIME ===
            'OMA-BEN' => [
                ['nom' => 'Port-Gentil',           'type' => 'ville',   'statut' => 'Chef-lieu', 'latitude' => -0.7193, 'longitude' => 8.7815, 'population_estimee' => 140000],
            ],
            'OMA-NDO' => [
                ['nom' => 'Gamba',                 'type' => 'commune', 'statut' => null, 'latitude' => -2.6500, 'longitude' => 10.0000, 'population_estimee' => 10000],
            ],
            'OMA-ETI' => [
                ['nom' => 'Omboué',                'type' => 'commune', 'statut' => null, 'latitude' => -1.5667, 'longitude' => 9.2500, 'population_estimee' => 5000],
            ],
            // === WOLEU-NTEM ===
            'WNT-WOL' => [
                ['nom' => 'Oyem',                  'type' => 'ville',   'statut' => 'Chef-lieu', 'latitude' => 1.6000, 'longitude' => 11.5833, 'population_estimee' => 45000],
            ],
            'WNT-NTE' => [
                ['nom' => 'Bitam',                 'type' => 'ville',   'statut' => null, 'latitude' => 2.0833, 'longitude' => 11.5000, 'population_estimee' => 15000],
            ],
            'WNT-OKA' => [
                ['nom' => 'Mitzic',                'type' => 'commune', 'statut' => null, 'latitude' => 0.7833, 'longitude' => 11.5500, 'population_estimee' => 8000],
            ],
            'WNT-HNT' => [
                ['nom' => 'Minvoul',               'type' => 'commune', 'statut' => null, 'latitude' => 2.1500, 'longitude' => 12.1333, 'population_estimee' => 5000],
            ],
            'WNT-HCO' => [
                ['nom' => 'Medouneu',              'type' => 'commune', 'statut' => null, 'latitude' => 1.0000, 'longitude' => 10.7833, 'population_estimee' => 4000],
            ],
        ];

        $count = 0;
        $departements = DB::table('departements')->get()->keyBy('code');

        foreach ($communesParDepartement as $deptCode => $communes) {
            $dept = $departements->get($deptCode);
            if (!$dept) continue;

            foreach ($communes as $ordre => $commune) {
                DB::table('communes_villes')->insert([
                    'departement_id'    => $dept->id,
                    'nom'               => $commune['nom'],
                    'code'              => $deptCode . '-' . strtoupper(substr(str_replace([' ', '-', "'"], '', $commune['nom']), 0, 4)),
                    'type'              => $commune['type'],
                    'statut'            => $commune['statut'],
                    'latitude'          => $commune['latitude'],
                    'longitude'         => $commune['longitude'],
                    'population_estimee'=> $commune['population_estimee'],
                    'is_active'         => true,
                    'ordre_affichage'   => $ordre + 1,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    // =================================================================
    // ARRONDISSEMENTS + QUARTIERS (ZONE URBAINE)
    // =================================================================

    private function seedArrondissementsEtQuartiers()
    {
        // Structure : commune_nom => [ arrondissement => [quartiers] ]
        $structure = [
            // ====== LIBREVILLE - 6 arrondissements ======
            'Libreville' => [
                '1er Arrondissement' => ['Quartier Louis', 'Mont-Bouët', 'Nombakélé', 'Lalala', 'Batterie IV'],
                '2ème Arrondissement' => ['Akébé-Plaine', 'Nzeng-Ayong', 'Awendjé', 'Derrière la Prison'],
                '3ème Arrondissement' => ['Ancien Sobraga', 'Belle-Vue 1', 'Belle-Vue 2', 'Kinguélé', 'IAI'],
                '4ème Arrondissement' => ['Ambowé', 'Sotéga', 'Plein Ciel', 'Mindoubé 1', 'Mindoubé 2'],
                '5ème Arrondissement' => ['Sibang', 'Ondogo', 'Malibé 1', 'Malibé 2', 'PK8'],
                '6ème Arrondissement' => ['Alibandeng', 'Bikélé', 'PK12', 'PK14', 'Igoumié'],
            ],
            // ====== OWENDO - 2 arrondissements ======
            'Owendo' => [
                '1er Arrondissement' => ['Centre-ville Owendo', 'Akournam', 'Lowé'],
                '2ème Arrondissement' => ['Alénakiri', 'Donguila', 'Terre Nouvelle'],
            ],
            // ====== AKANDA ======
            'Akanda' => [
                '1er Arrondissement' => ['Angondjé', 'Okala', 'Malibé Nord'],
                '2ème Arrondissement' => ['Cap Estérias', 'La Sablière', 'Agondjé-Plage'],
            ],
            // ====== FRANCEVILLE ======
            'Franceville' => [
                '1er Arrondissement' => ['Quartier Yéné', 'Mingara', 'Centre-Ville'],
                '2ème Arrondissement' => ['Potos', 'Mbaya', 'Mboumba'],
                '3ème Arrondissement' => ['Lécouna', 'Carrière', 'Camp des Gardes'],
            ],
            // ====== PORT-GENTIL ======
            'Port-Gentil' => [
                '1er Arrondissement' => ['Grand Village', 'Chapuis', 'Centre-Ville'],
                '2ème Arrondissement' => ['Ntchengué', 'Balise', 'Mosquée'],
                '3ème Arrondissement' => ['Matanda', 'Camp Banque', 'Barracuda'],
            ],
            // ====== OYEM ======
            'Oyem' => [
                '1er Arrondissement' => ['Centre-Ville', 'Melen', 'Angouzok'],
                '2ème Arrondissement' => ['Nkolmeyang', 'Ekouk', 'Nkembo'],
            ],
            // ====== LAMBARÉNÉ ======
            'Lambaréné' => [
                '1er Arrondissement' => ['Ile Lambaréné', 'Adouma', 'Isaac'],
                '2ème Arrondissement' => ['Rive Droite', 'Georges Rawiri', 'Sahoty'],
            ],
            // ====== MOUILA ======
            'Mouila' => [
                '1er Arrondissement' => ['Centre-Ville', 'Saint-Martin', 'Camp Militaire'],
                '2ème Arrondissement' => ['Dibwa-Dibwa', 'Mouyanama', 'Mission Catholique'],
            ],
            // ====== TCHIBANGA ======
            'Tchibanga' => [
                '1er Arrondissement' => ['Centre-Ville', 'Mbongo', 'Moulengui-Binza'],
                '2ème Arrondissement' => ['Moussoumbou', 'Divindé', 'Bongo'],
            ],
            // ====== MAKOKOU ======
            'Makokou' => [
                '1er Arrondissement' => ['Centre-Ville', 'Mbolo', 'Zoatab'],
                '2ème Arrondissement' => ['Epassengué', 'Mbéza', 'Camp Gendarmerie'],
            ],
            // ====== KOULAMOUTOU ======
            'Koulamoutou' => [
                '1er Arrondissement' => ['Centre-Ville', 'Lébombi', 'Camp Militaire'],
                '2ème Arrondissement' => ['Mandji', 'Dihoundou', 'Mougola'],
            ],
            // ====== BITAM ======
            'Bitam' => [
                '1er Arrondissement' => ['Centre-Ville', 'Meyo-Biboulou', 'Nkolbikié'],
                '2ème Arrondissement' => ['Edzangui', 'Minkoméyos', 'Bikang'],
            ],
            // ====== NTOUM ======
            'Ntoum' => [
                '1er Arrondissement' => ['Centre-Ville Ntoum', 'PK23', 'Andem'],
            ],
        ];

        $arrCount = 0;
        $quartierCount = 0;
        $communes = DB::table('communes_villes')->get()->keyBy('nom');

        foreach ($structure as $communeNom => $arrondissements) {
            $commune = $communes->get($communeNom);
            if (!$commune) continue;

            $numero = 1;
            foreach ($arrondissements as $arrNom => $quartiers) {
                $arrCode = $commune->code . '-ARR' . str_pad($numero, 2, '0', STR_PAD_LEFT);

                $arrId = DB::table('arrondissements')->insertGetId([
                    'commune_ville_id'       => $commune->id,
                    'nom'                    => $arrNom,
                    'code'                   => $arrCode,
                    'numero_arrondissement'  => $numero,
                    'is_active'              => true,
                    'ordre_affichage'        => $numero,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);
                $arrCount++;

                // Insérer les quartiers comme localités type='quartier'
                foreach ($quartiers as $qOrdre => $quartierNom) {
                    $qCode = $arrCode . '-Q' . str_pad($qOrdre + 1, 2, '0', STR_PAD_LEFT);

                    DB::table('localites')->insert([
                        'arrondissement_id'  => $arrId,
                        'regroupement_id'    => null,
                        'type'               => 'quartier',
                        'nom'                => $quartierNom,
                        'code'               => $qCode,
                        'is_active'          => true,
                        'ordre_affichage'    => $qOrdre + 1,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                    $quartierCount++;
                }

                $numero++;
            }
        }

        return ['arrondissements' => $arrCount, 'quartiers' => $quartierCount];
    }

    // =================================================================
    // CANTONS (ZONE RURALE)
    // =================================================================

    private function seedCantons()
    {
        // Cantons par département (zone rurale)
        $cantonsParDepartement = [
            // === ESTUAIRE (départements ruraux) ===
            'EST-KOM' => [
                ['nom' => 'Bokoué',          'chef_lieu' => 'Bokoué'],
                ['nom' => 'Nsile',           'chef_lieu' => 'Nsile'],
                ['nom' => 'Kango Centre',    'chef_lieu' => 'Kango'],
            ],
            'EST-NOY' => [
                ['nom' => 'Muni',            'chef_lieu' => 'Cocobeach'],
                ['nom' => 'Noya-Littoral',   'chef_lieu' => 'Ntoum-Littoral'],
            ],
            // === HAUT-OGOOUÉ ===
            'HOG-LLE' => [
                ['nom' => 'Lékori',          'chef_lieu' => 'Lékori'],
                ['nom' => 'Bayi-Brikolo',    'chef_lieu' => 'Akiéni'],
            ],
            'HOG-PLA' => [
                ['nom' => 'Ekéla',           'chef_lieu' => 'Léconi'],
                ['nom' => 'Djouya',          'chef_lieu' => 'Djouya'],
            ],
            'HOG-LEK' => [
                ['nom' => 'Boumango',        'chef_lieu' => 'Boumango'],
                ['nom' => 'Mayoko',          'chef_lieu' => 'Mayoko'],
            ],
            // === MOYEN-OGOOUÉ ===
            'MOG-OGL' => [
                ['nom' => 'Lac Onangué',     'chef_lieu' => 'Onangué'],
                ['nom' => 'Lac Azingo',      'chef_lieu' => 'Azingo'],
                ['nom' => 'Ogooué Nord',     'chef_lieu' => 'Bifoun'],
            ],
            'MOG-ABG' => [
                ['nom' => 'Bifoun',          'chef_lieu' => 'Bifoun'],
                ['nom' => 'Abanga',          'chef_lieu' => 'Sam'],
            ],
            // === NGOUNIÉ ===
            'NGO-DON' => [
                ['nom' => 'Dibwa',           'chef_lieu' => 'Dibwa'],
                ['nom' => 'Boudyanga',       'chef_lieu' => 'Boudyanga'],
            ],
            'NGO-DOL' => [
                ['nom' => 'Dola-Nord',       'chef_lieu' => 'Ndendé'],
                ['nom' => 'Dola-Sud',        'chef_lieu' => 'Dola'],
            ],
            'NGO-LWA' => [
                ['nom' => 'Wano',            'chef_lieu' => 'Lébamba'],
                ['nom' => 'Louétsi',         'chef_lieu' => 'Louétsi'],
            ],
            'NGO-OGO' => [
                ['nom' => 'Ogoulou-Amont',   'chef_lieu' => 'Mimongo'],
                ['nom' => 'Ogoulou-Aval',    'chef_lieu' => 'Mbigou'],
            ],
            // === NYANGA ===
            'NYA-BBA' => [
                ['nom' => 'Basse-Banio Nord','chef_lieu' => 'Tchibanga'],
                ['nom' => 'Basse-Banio Sud', 'chef_lieu' => 'Mayumba'],
            ],
            'NYA-HBA' => [
                ['nom' => 'Haute-Banio Est', 'chef_lieu' => 'Ndindi'],
                ['nom' => 'Haute-Banio Ouest','chef_lieu' => 'Mabanda'],
            ],
            'NYA-DOU' => [
                ['nom' => 'Douigny-Nord',    'chef_lieu' => 'Moabi'],
                ['nom' => 'Douigny-Sud',     'chef_lieu' => 'Doussala'],
            ],
            // === OGOOUÉ-IVINDO ===
            'OIV-IVI' => [
                ['nom' => 'Ivindo-Amont',    'chef_lieu' => 'Makokou'],
                ['nom' => 'Ivindo-Aval',     'chef_lieu' => 'Liboumba'],
            ],
            'OIV-LOP' => [
                ['nom' => 'Lopé-Okanda',     'chef_lieu' => 'Booué'],
                ['nom' => 'Mingoué',         'chef_lieu' => 'Kazamabika'],
            ],
            'OIV-MVO' => [
                ['nom' => 'Mvoung Centre',   'chef_lieu' => 'Ovan'],
            ],
            'OIV-ZAD' => [
                ['nom' => 'Zadié-Nord',      'chef_lieu' => 'Mékambo'],
                ['nom' => 'Zadié-Sud',       'chef_lieu' => 'Ekata'],
            ],
            // === OGOOUÉ-LOLO ===
            'OLO-LBG' => [
                ['nom' => 'Bouenguidi',      'chef_lieu' => 'Koulamoutou'],
                ['nom' => 'Lolo-Amont',      'chef_lieu' => 'Lolo'],
            ],
            'OLO-MUL' => [
                ['nom' => 'Mulundu Centre',  'chef_lieu' => 'Lastoursville'],
                ['nom' => 'Leyou',           'chef_lieu' => 'Leyou'],
            ],
            // === OGOOUÉ-MARITIME ===
            'OMA-NDO' => [
                ['nom' => 'Ndougou-Lagune',  'chef_lieu' => 'Gamba'],
                ['nom' => 'Ndougou-Savane',  'chef_lieu' => 'Sette-Cama'],
            ],
            'OMA-ETI' => [
                ['nom' => 'Etimboué-Lac',    'chef_lieu' => 'Omboué'],
                ['nom' => 'Fernan-Vaz',      'chef_lieu' => 'Fernan-Vaz'],
            ],
            // === WOLEU-NTEM ===
            'WNT-WOL' => [
                ['nom' => 'Woleu-Nord',      'chef_lieu' => 'Oyem'],
                ['nom' => 'Woleu-Sud',       'chef_lieu' => 'Akok'],
            ],
            'WNT-NTE' => [
                ['nom' => 'Ntem Centre',     'chef_lieu' => 'Bitam'],
                ['nom' => 'Ntem-Nord',       'chef_lieu' => 'Ambam'],
            ],
            'WNT-OKA' => [
                ['nom' => 'Okano-Amont',     'chef_lieu' => 'Mitzic'],
                ['nom' => 'Okano-Aval',      'chef_lieu' => 'Dong'],
            ],
            'WNT-HNT' => [
                ['nom' => 'Haut-Ntem Centre','chef_lieu' => 'Minvoul'],
            ],
            'WNT-HCO' => [
                ['nom' => 'Como-Amont',      'chef_lieu' => 'Medouneu'],
            ],
        ];

        $count = 0;
        $departements = DB::table('departements')->get()->keyBy('code');

        foreach ($cantonsParDepartement as $deptCode => $cantons) {
            $dept = $departements->get($deptCode);
            if (!$dept) continue;

            foreach ($cantons as $ordre => $canton) {
                $cantonCode = $deptCode . '-C' . str_pad($ordre + 1, 2, '0', STR_PAD_LEFT);

                DB::table('cantons')->insert([
                    'departement_id'     => $dept->id,
                    'nom'                => $canton['nom'],
                    'code'               => $cantonCode,
                    'chef_lieu'          => $canton['chef_lieu'],
                    'is_active'          => true,
                    'acces_electricite'  => ($ordre === 0),  // Premier canton du département avec électricité
                    'acces_eau_potable'  => ($ordre === 0),
                    'reseau_telephonique'=> true,
                    'ordre_affichage'    => $ordre + 1,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    // =================================================================
    // REGROUPEMENTS + VILLAGES (ZONE RURALE)
    // =================================================================

    private function seedRegroupementsEtVillages()
    {
        // Pour chaque canton, on crée 2 regroupements avec 3 villages chacun
        $cantons = DB::table('cantons')
            ->join('departements', 'cantons.departement_id', '=', 'departements.id')
            ->join('provinces', 'departements.province_id', '=', 'provinces.id')
            ->select('cantons.*', 'departements.code as dept_code', 'provinces.code as prov_code')
            ->orderBy('cantons.id')
            ->get();

        $regCount = 0;
        $vilCount = 0;

        // Noms de villages typiques gabonais pour varier
        $nomsVillages = [
            'Ndong',    'Essassa',   'Mbéga',     'Ékang',      'Oyane',
            'Nzang',    'Mvome',     'Akome',     'Nkolo',      'Abanga',
            'Mindzi',   'Bikélé',    'Ngoua',     'Assok',      'Mfoula',
            'Obala',    'Endama',    'Mikong',    'Ndjambou',   'Maghouba',
            'Épassi',   'Moanda',    'Léconi',    'Mvengué',    'Okondja',
            'Mboungou', 'Nyanga',    'Ossimba',   'Boumba',     'Dibamba',
            'Ébel',     'Nkam',      'Mékomo',    'Éboré',      'Massaha',
            'Mitomb',   'Ngomo',     'Avorbam',   'Ndougou',    'Bilengi',
            'Maliga',   'Ossélé',    'Boulinga',  'Yombi',      'Kinguélé',
            'Mossendjo','Ngoumbi',   'Lebombi',   'Mbiné',      'Okouma',
            'Ékala',    'Nziélé',    'Mvouti',    'Mbolo',      'Donguila',
            'Nanga',    'Tsamba',    'Mbigou',    'Divindé',    'Penigha',
        ];

        $vilIndex = 0;

        foreach ($cantons as $canton) {
            // 2 regroupements par canton
            for ($r = 1; $r <= 2; $r++) {
                $regCode = $canton->code . '-R' . str_pad($r, 2, '0', STR_PAD_LEFT);
                $regNom = 'Regroupement ' . $canton->nom . ' ' . ($r === 1 ? 'Nord' : 'Sud');

                $regId = DB::table('regroupements')->insertGetId([
                    'canton_id'            => $canton->id,
                    'nom'                  => $regNom,
                    'code'                 => $regCode,
                    'village_centre'       => null,
                    'nombre_villages'      => 3,
                    'ecole_primaire'       => ($r === 1),
                    'centre_sante'         => ($r === 1),
                    'marche'               => ($r === 1),
                    'route_praticable'     => true,
                    'electricite_disponible'=> ($r === 1),
                    'couverture_reseau'    => ($r === 1) ? 'moyenne' : 'faible',
                    'acces_eau'            => ($r === 1) ? 'forage' : 'riviere',
                    'is_active'            => true,
                    'ordre_affichage'      => $r,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
                $regCount++;

                // 3 villages par regroupement
                for ($v = 1; $v <= 3; $v++) {
                    $vilNom = $nomsVillages[$vilIndex % count($nomsVillages)];
                    $vilCode = $regCode . '-V' . str_pad($v, 2, '0', STR_PAD_LEFT);

                    DB::table('localites')->insert([
                        'arrondissement_id'  => null,
                        'regroupement_id'    => $regId,
                        'type'               => 'village',
                        'nom'                => $vilNom,
                        'code'               => $vilCode,
                        'population_estimee' => rand(100, 2000),
                        'electricite'        => ($v === 1 && $r === 1),
                        'transport_public'   => false,
                        'ecole_primaire'     => ($v <= 2),
                        'centre_sante'       => ($v === 1),
                        'is_active'          => true,
                        'ordre_affichage'    => $v,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                    $vilCount++;
                    $vilIndex++;
                }
            }
        }

        return ['regroupements' => $regCount, 'villages' => $vilCount];
    }

    // =================================================================
    // STATISTIQUES
    // =================================================================

    private function displayStats($duration)
    {
        $stats = [
            ['Provinces',       DB::table('provinces')->count()],
            ['Départements',    DB::table('departements')->count()],
            ['Communes/Villes', DB::table('communes_villes')->count()],
            ['Arrondissements', DB::table('arrondissements')->count()],
            ['Cantons',         DB::table('cantons')->count()],
            ['Regroupements',   DB::table('regroupements')->count()],
            ['Quartiers',       DB::table('localites')->where('type', 'quartier')->count()],
            ['Villages',        DB::table('localites')->where('type', 'village')->count()],
        ];

        $this->command->info('📊 ===============================================');
        $this->command->info('📊   STATISTIQUES GÉOLOCALISATION GABON');
        $this->command->info('📊 ===============================================');
        $this->command->table(['Table', 'Enregistrements'], $stats);

        $total = array_sum(array_column($stats, 1));
        $this->command->info("🌍 Total enregistrements : {$total}");
        $this->command->info("⏱️  Durée : {$duration}ms");
        $this->command->info('');
        $this->command->info('🎉 Géolocalisation du Gabon peuplée avec succès !');
        $this->command->info('');
    }
}
