<?php

namespace App\Services;

use App\Models\Organisation;

/**
 * Source unique de vérité pour la numérotation des récépissés.
 *
 * Trois générateurs coexistaient, avec trois formats incompatibles
 * (`ASS/2026/00082`, `REC-AS-2026-00002`, `ASS-2026-0084`) et le même défaut :
 * la séquence dérivait d'un COUNT de lignes. Or `organisations.numero_recepisse`
 * porte un index UNIQUE — une organisation supprimée, un brouillon sans
 * récépissé ou deux créations simultanées suffisaient à proposer un numéro déjà
 * attribué (erreur 1062 « Duplicate entry »).
 *
 * Règles retenues :
 *  - format en vigueur : REC-{PREFIXE}-{ANNEE}-{SEQUENCE sur 5 chiffres} ;
 *  - la séquence repart du plus haut numéro déjà émis pour ce type et cette
 *    année, quel que soit son format d'origine — les numéros historiques ne
 *    sont jamais réattribués ;
 *  - les numéros déjà délivrés ne sont pas réécrits : ils figurent sur des
 *    documents remis aux usagers et servent à la vérification publique.
 */
class RecepisseNumberService
{
    /**
     * Préfixe officiel par type d'organisation.
     */
    public const PREFIXES = [
        'association' => 'AS',
        'ong' => 'ONG',
        'parti_politique' => 'PP',
        'confession_religieuse' => 'CR',
    ];

    public const PREFIXE_PAR_DEFAUT = 'ORG';

    public function prefixe(string $type): string
    {
        return self::PREFIXES[$type] ?? self::PREFIXE_PAR_DEFAUT;
    }

    /**
     * Numéro suivant pour ce type d'organisation.
     *
     * À appeler dans une transaction : lockForUpdate() sérialise alors deux
     * créations simultanées.
     */
    public function generer(string $type, ?int $annee = null): string
    {
        $prefixe = $this->prefixe($type);
        $annee = $annee ?: (int) date('Y');

        return sprintf(
            'REC-%s-%d-%05d',
            $prefixe,
            $annee,
            $this->derniereSequence($type, $prefixe, $annee) + 1
        );
    }

    /**
     * Plus haute séquence déjà émise, tous formats confondus.
     *
     * Les trois formats se terminent par la séquence, précédée de « - » ou
     * de « / ». On normalise le séparateur avant d'extraire le dernier segment :
     * REPLACE + SUBSTRING_INDEX fonctionnent dès MySQL 5.7, contrairement à
     * REGEXP_SUBSTR qui exige MySQL 8.
     */
    private function derniereSequence(string $type, string $prefixe, int $annee): int
    {
        return (int) Organisation::query()
            ->where('type', $type)
            ->whereNotNull('numero_recepisse')
            ->where(function ($requete) use ($prefixe, $annee) {
                $requete
                    ->where('numero_recepisse', 'like', "REC-{$prefixe}-{$annee}-%")
                    ->orWhere('numero_recepisse', 'like', "%/{$annee}/%")
                    ->orWhere('numero_recepisse', 'like', "%-{$annee}-%");
            })
            ->lockForUpdate()
            ->selectRaw(
                "COALESCE(MAX(CAST(SUBSTRING_INDEX(REPLACE(numero_recepisse, '/', '-'), '-', -1) AS UNSIGNED)), 0) AS derniere"
            )
            ->value('derniere');
    }
}
