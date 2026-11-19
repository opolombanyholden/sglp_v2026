<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // PostgreSQL refuse de comparer une colonne entière à du texte
        // (`/dossiers/brouillons` → where id = 'brouillons', SQLSTATE 22P02),
        // là où MySQL ne trouvait simplement aucune ligne. Quand la valeur
        // refusée vient de l'URL, on répond 404 comme auparavant.
        $this->map(QueryException::class, function (QueryException $e) {
            return $this->valeurInvalideVenantDeLUrl($e)
                ? new NotFoundHttpException('Ressource introuvable.', $e)
                : $e;
        });
    }

    private function valeurInvalideVenantDeLUrl(QueryException $e): bool
    {
        if (($e->errorInfo[0] ?? null) !== '22P02') {
            return false;
        }
        if (!preg_match('/invalid input syntax for type \w+( \w+)?: "(.*?)"/', $e->getMessage(), $m)) {
            return false;
        }
        $route = request()->route();
        $parametres = $route && method_exists($route, 'originalParameters') ? $route->originalParameters() : [];

        return in_array($m[2], array_map('strval', $parametres), true);
    }
}
