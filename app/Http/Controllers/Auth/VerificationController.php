<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    /**
     * Rediriger un user déjà connecté vers son dashboard
     */
    private function dashboardFor(?User $user)
    {
        if (!$user) {
            return redirect()->route('login');
        }
        if (in_array($user->role, ['admin', 'agent'])) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('operator.dashboard');
    }

    /**
     * Afficher la notice de vérification email (user authentifié uniquement)
     */
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->dashboardFor($request->user());
        }
        return view('auth.verify-email');
    }

    /**
     * Vérifier l'email via le lien signé.
     *
     * Route publique : on identifie le user depuis l'id de l'URL et on valide
     * le hash. Le middleware `signed` garantit que l'URL n'a pas été altérée.
     * Cela permet à un utilisateur de cliquer sur le lien dans son mail même
     * s'il n'est PAS connecté dans le navigateur courant (cas le plus fréquent
     * quand le client mail ouvre dans un onglet/session différent).
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            abort(404, 'Utilisateur introuvable.');
        }

        // Vérifier que le hash correspond bien à l'email du user
        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            abort(403, 'Lien de vérification invalide.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('email.verified', ['already' => 1])
                ->with('info', 'Votre email était déjà vérifié.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Si la session courante appartenait à ce user, on garde la connexion.
        // Sinon on n'invalide pas la session du navigateur (un autre user peut
        // y être connecté), on redirige juste vers la page de confirmation.
        return redirect()->route('email.verified', ['activated' => 1])
            ->with('success', 'Votre adresse email a été vérifiée. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Renvoyer l'email de vérification (user authentifié)
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->dashboardFor($request->user());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Page de confirmation après vérification (publique)
     */
    public function verified()
    {
        return view('auth.email-verified');
    }
}