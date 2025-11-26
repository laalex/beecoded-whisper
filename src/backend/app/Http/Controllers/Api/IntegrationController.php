<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\HubSpot\HubSpotService;
use App\Services\Gmail\GmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class IntegrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $integrations = $request->user()->integrations()
            ->select(['id', 'provider', 'provider_email', 'is_active', 'last_synced_at', 'created_at'])
            ->get();

        return response()->json($integrations);
    }

    public function connectHubSpot(): JsonResponse
    {
        $url = Socialite::driver('hubspot')
            ->scopes(['crm.objects.contacts.read', 'crm.objects.contacts.write', 'crm.objects.deals.read'])
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function hubSpotCallback(Request $request): JsonResponse
    {
        $socialiteUser = Socialite::driver('hubspot')->stateless()->user();

        $integration = $request->user()->integrations()->updateOrCreate(
            ['provider' => 'hubspot'],
            [
                'provider_user_id' => $socialiteUser->getId(),
                'provider_email' => $socialiteUser->getEmail(),
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'token_expires_at' => now()->addSeconds($socialiteUser->expiresIn),
                'is_active' => true,
            ]
        );

        return response()->json($integration);
    }

    public function connectGmail(): JsonResponse
    {
        $url = Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/gmail.readonly', 'https://www.googleapis.com/auth/gmail.send'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function gmailCallback(Request $request): JsonResponse
    {
        $socialiteUser = Socialite::driver('google')->stateless()->user();

        $integration = $request->user()->integrations()->updateOrCreate(
            ['provider' => 'gmail'],
            [
                'provider_user_id' => $socialiteUser->getId(),
                'provider_email' => $socialiteUser->getEmail(),
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'token_expires_at' => now()->addSeconds($socialiteUser->expiresIn ?? 3600),
                'is_active' => true,
            ]
        );

        return response()->json($integration);
    }

    public function destroy(Integration $integration): JsonResponse
    {
        if ($integration->user_id !== request()->user()->id) {
            abort(403);
        }

        $integration->delete();

        return response()->json(['message' => 'Integration disconnected']);
    }

    public function sync(Request $request, Integration $integration): JsonResponse
    {
        if ($integration->user_id !== $request->user()->id) {
            abort(403);
        }

        match ($integration->provider) {
            'hubspot' => app(HubSpotService::class)->syncContacts($integration),
            'gmail' => app(GmailService::class)->syncEmails($integration),
            default => null,
        };

        $integration->update(['last_synced_at' => now()]);

        return response()->json(['message' => 'Sync started']);
    }
}
