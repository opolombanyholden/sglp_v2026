<?php

namespace App\Http\Controllers;

use App\Models\DomaineActivite;
use App\Models\Fonction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Gestion des suggestions de nouvelles valeurs par les usagers
 * (Fonctions de bureau, Domaines d'activité)
 * Les suggestions sont enregistrées avec statut "pending" et doivent
 * être approuvées par un admin avant d'être visibles par les autres usagers.
 */
class SuggestionController extends Controller
{
    /**
     * Suggérer une nouvelle fonction (rôle de bureau)
     * POST /suggestions/fonction
     */
    public function suggestFonction(Request $request)
    {
        $request->validate([
            'nom' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\p{N}\s\-\'\,\.\(\)]+$/u'],
            'description' => 'nullable|string|max:1000',
        ], [
            'nom.regex' => 'Le nom contient des caractères non autorisés.',
        ]);

        try {
            $nom = trim($request->input('nom'));

            // Vérifier si une fonction similaire existe déjà
            $existing = Fonction::whereRaw('LOWER(nom) = ?', [mb_strtolower($nom)])->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'already_exists' => true,
                    'id' => $existing->id,
                    'nom' => $existing->nom,
                    'message' => 'Cette fonction existe déjà.',
                ]);
            }

            $code = Str::slug($nom, '_');
            // Assurer l'unicité du code
            $baseCode = $code;
            $i = 1;
            while (Fonction::where('code', $code)->exists()) {
                $code = $baseCode . '_' . $i++;
            }

            $fonction = Fonction::create([
                'code' => $code,
                'nom' => $nom,
                'description' => $request->input('description'),
                'categorie' => 'autre',
                'ordre' => 999,
                'is_bureau' => true,
                'is_active' => false,
                'suggested_by_user_id' => auth()->id(),
                'suggestion_status' => 'pending',
            ]);

            Log::info('Fonction suggérée', ['id' => $fonction->id, 'nom' => $nom, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'id' => $fonction->id,
                'nom' => $fonction->nom,
                'message' => 'Suggestion enregistrée. Elle sera visible par les autres usagers après validation par un administrateur.',
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suggestion fonction: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Suggérer un nouveau domaine d'activité
     * POST /suggestions/domaine
     */
    public function suggestDomaine(Request $request)
    {
        $request->validate([
            'nom' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\p{N}\s\-\'\,\.\(\)]+$/u'],
            'description' => 'nullable|string|max:1000',
        ], [
            'nom.regex' => 'Le nom contient des caractères non autorisés.',
        ]);

        try {
            $nom = trim($request->input('nom'));

            $existing = DomaineActivite::whereRaw('LOWER(nom) = ?', [mb_strtolower($nom)])->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'already_exists' => true,
                    'id' => $existing->id,
                    'nom' => $existing->nom,
                    'message' => 'Ce domaine existe déjà.',
                ]);
            }

            $code = Str::slug($nom, '_');
            $baseCode = $code;
            $i = 1;
            while (DomaineActivite::where('code', $code)->exists()) {
                $code = $baseCode . '_' . $i++;
            }

            $domaine = DomaineActivite::create([
                'nom' => $nom,
                'code' => $code,
                'description' => $request->input('description'),
                'ordre' => 999,
                'is_active' => false,
                'suggested_by_user_id' => auth()->id(),
                'suggestion_status' => 'pending',
            ]);

            Log::info('Domaine suggéré', ['id' => $domaine->id, 'nom' => $nom, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'id' => $domaine->id,
                'nom' => $domaine->nom,
                'message' => 'Suggestion enregistrée. Elle sera visible par les autres usagers après validation par un administrateur.',
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suggestion domaine: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    // =================================================
    // PARTIE ADMIN
    // =================================================

    /**
     * Liste des suggestions en attente
     * GET /admin/suggestions
     */
    public function adminIndex()
    {
        $fonctions = Fonction::where('suggestion_status', 'pending')
            ->with([])
            ->orderBy('created_at', 'desc')
            ->get();

        $domaines = DomaineActivite::where('suggestion_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.suggestions.index', compact('fonctions', 'domaines'));
    }

    /**
     * Approuver une suggestion (fonction ou domaine)
     * POST /admin/suggestions/{type}/{id}/approve
     */
    public function adminApprove(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type);
        $item = $model::findOrFail($id);
        $item->suggestion_status = 'approved';
        $item->is_active = true;
        $item->save();

        return back()->with('success', 'Suggestion approuvée : « ' . $item->nom . ' » est maintenant disponible pour tous les usagers.');
    }

    /**
     * Rejeter une suggestion
     * POST /admin/suggestions/{type}/{id}/reject
     */
    public function adminReject(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type);
        $item = $model::findOrFail($id);
        $item->suggestion_status = 'rejected';
        $item->is_active = false;
        $item->save();

        return back()->with('success', 'Suggestion rejetée.');
    }

    private function resolveModel(string $type): string
    {
        return match ($type) {
            'fonction' => Fonction::class,
            'domaine' => DomaineActivite::class,
            default => throw new \InvalidArgumentException('Type invalide'),
        };
    }
}
