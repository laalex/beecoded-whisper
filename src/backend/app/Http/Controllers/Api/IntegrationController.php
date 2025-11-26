<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\User;
use App\Services\HubSpot\HubSpotService;
use App\Services\Gmail\GmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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

    public function connectHubSpot(Request $request): JsonResponse
    {
        $state = $this->generateOAuthState($request->user()->id, 'hubspot');

        $url = Socialite::driver('hubspot')
            ->scopes([
                'oauth',
                'crm.objects.contacts.read',
                'crm.objects.contacts.write',
                'crm.objects.companies.read',
                'crm.objects.companies.write',
                'crm.objects.deals.read',
                'crm.objects.deals.write',
                'crm.objects.leads.read',
                'crm.objects.leads.write',
                'crm.objects.owners.read',
                'crm.objects.quotes.read',
                'crm.objects.quotes.write',
                'crm.objects.appointments.read',
                'crm.objects.appointments.write',
                'sales-email-read',
                'conversations.read',
                'timeline',
            ])
            ->stateless()
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function hubSpotCallback(Request $request): Response
    {
        $state = $request->get('state');
        $userId = $this->validateOAuthState($state, 'hubspot');

        if (!$userId) {
            return $this->oauthErrorResponse('Invalid or expired state token');
        }

        try {
            $socialiteUser = Socialite::driver('hubspot')->stateless()->user();
            $user = User::find($userId);

            $user->integrations()->updateOrCreate(
                ['provider' => 'hubspot'],
                [
                    'provider_user_id' => $socialiteUser->getId(),
                    'provider_email' => $socialiteUser->getEmail(),
                    'access_token' => $socialiteUser->token,
                    'refresh_token' => $socialiteUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($socialiteUser->expiresIn ?? 3600),
                    'is_active' => true,
                ]
            );

            return $this->oauthSuccessResponse('hubspot');
        } catch (\Exception $e) {
            return $this->oauthErrorResponse('Failed to connect HubSpot: ' . $e->getMessage());
        }
    }

    public function connectGmail(Request $request): JsonResponse
    {
        $state = $this->generateOAuthState($request->user()->id, 'gmail');

        $url = Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/gmail.readonly', 'https://www.googleapis.com/auth/gmail.send'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent', 'state' => $state])
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function gmailCallback(Request $request): Response
    {
        $state = $request->get('state');
        $userId = $this->validateOAuthState($state, 'gmail');

        if (!$userId) {
            return $this->oauthErrorResponse('Invalid or expired state token');
        }

        try {
            $socialiteUser = Socialite::driver('google')->stateless()->user();
            $user = User::find($userId);

            $user->integrations()->updateOrCreate(
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

            return $this->oauthSuccessResponse('gmail');
        } catch (\Exception $e) {
            return $this->oauthErrorResponse('Failed to connect Gmail: ' . $e->getMessage());
        }
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

    private function generateOAuthState(int $userId, string $provider): string
    {
        $state = Str::random(40);
        Cache::put("oauth_state:{$state}", ['user_id' => $userId, 'provider' => $provider], now()->addMinutes(10));
        return $state;
    }

    private function validateOAuthState(?string $state, string $provider): ?int
    {
        if (!$state) {
            return null;
        }

        $data = Cache::pull("oauth_state:{$state}");

        if (!$data || $data['provider'] !== $provider) {
            return null;
        }

        return $data['user_id'];
    }

    private function oauthSuccessResponse(string $provider): Response
    {
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Connection Successful</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
        .container { text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #22c55e; font-size: 48px; margin-bottom: 16px; }
        h1 { color: #1a1f36; margin: 0 0 8px; }
        p { color: #6b7280; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">✓</div>
        <h1>Connected Successfully</h1>
        <p>You can close this window now.</p>
    </div>
    <script>
        if (window.opener) {
            window.opener.postMessage({ type: 'oauth_success', provider: '{$provider}' }, '*');
            setTimeout(() => window.close(), 1500);
        }
    </script>
</body>
</html>
HTML;

        return response($html)->header('Content-Type', 'text/html');
    }

    private function oauthErrorResponse(string $message): Response
    {
        $escapedMessage = htmlspecialchars($message);
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Connection Failed</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
        .container { text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error { color: #ef4444; font-size: 48px; margin-bottom: 16px; }
        h1 { color: #1a1f36; margin: 0 0 8px; }
        p { color: #6b7280; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error">✗</div>
        <h1>Connection Failed</h1>
        <p>{$escapedMessage}</p>
    </div>
    <script>
        if (window.opener) {
            window.opener.postMessage({ type: 'oauth_error', message: '{$escapedMessage}' }, '*');
            setTimeout(() => window.close(), 3000);
        }
    </script>
</body>
</html>
HTML;

        return response($html)->header('Content-Type', 'text/html');
    }
}
