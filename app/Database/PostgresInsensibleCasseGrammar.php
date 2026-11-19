<?php

namespace App\Database;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;

/**
 * Sous MySQL (collation utf8mb4_unicode_ci), `->where('nom', 'like', '%abc%')`
 * ignorait la casse et les accents ; sous PostgreSQL, LIKE respecte les deux.
 * Pour garder le comportement des recherches de l'application, `like` /
 * `not like` sont compilés en :
 *
 *     unaccent(colonne::text) ilike unaccent(?)
 *
 * (« Eglise » trouve « Église », « eleves » trouve « ÉLÈVES »).
 * Nécessite l'extension `unaccent` (migration 2026_11_19_000001).
 */
class PostgresInsensibleCasseGrammar extends PostgresGrammar
{
    protected function whereBasic(Builder $query, $where)
    {
        $operateur = strtolower(trim($where['operator']));
        if ($operateur !== 'like' && $operateur !== 'not like') {
            return parent::whereBasic($query, $where);
        }

        return sprintf(
            'unaccent(%s::text) %s unaccent(%s)',
            $this->wrap($where['column']),
            $operateur === 'like' ? 'ilike' : 'not ilike',
            $this->parameter($where['value'])
        );
    }
}
