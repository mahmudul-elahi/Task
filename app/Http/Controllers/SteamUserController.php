<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SteamUser;
use App\Models\SteamUserBan;

class SteamUserController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'steam_id' => 'required|string'
        ]);

        $steamId = $request->steam_id;
        $apiKey = env('STEAM_API_KEY');

        try {
            // 🔹 Get Profile
            $profileResponse = Http::get("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/", [
                'key' => $apiKey,
                'steamids' => $steamId
            ]);

            $profileData = $profileResponse->json('response.players.0');

            if (!$profileData) {
                return back()->with('error', 'Steam user not found or profile is private.');
            }

            // 🔹 Get Steam Level
            $levelResponse = Http::get("https://api.steampowered.com/IPlayerService/GetSteamLevel/v1/", [
                'key' => $apiKey,
                'steamid' => $steamId
            ]);

            $steamLevel = $levelResponse->json('response.player_level', null);

            // 🔹 Get Ban Info
            $banResponse = Http::get("https://api.steampowered.com/ISteamUser/GetPlayerBans/v1/", [
                'key' => $apiKey,
                'steamids' => $steamId
            ]);

            $banData = $banResponse->json('players.0');

            // 🔹 Save or Update Steam User
            $user = SteamUser::updateOrCreate(
                ['steamid' => $steamId],
                [
                    'personaname'   => $profileData['personaname'] ?? '',
                    'profile_url'   => $profileData['profileurl'] ?? '',
                    'avatar'        => $profileData['avatar'] ?? '',
                    'avatar_medium' => $profileData['avatarmedium'] ?? '',
                    'avatar_full'   => $profileData['avatarfull'] ?? '',
                    'steam_level'   => $steamLevel,
                    'profile_state' => $profileData['profilestate'] ?? 0
                ]
            );

            // 🔹 Save or Update Ban Info
            if ($banData) {
                $lastBanDate = isset($banData['DaysSinceLastBan'])
                    ? now()->subDays($banData['DaysSinceLastBan'])->toDateString()
                    : null;

                $user->bans()->updateOrCreate([], [
                    'community_banned'      => $banData['CommunityBanned'] ?? false,
                    'vac_banned'            => $banData['VACBanned'] ?? false,
                    'number_of_vac_bans'    => $banData['NumberOfVACBans'] ?? 0,
                    'number_of_game_bans'   => $banData['NumberOfGameBans'] ?? 0,
                    'days_since_last_ban'   => $banData['DaysSinceLastBan'] ?? null,
                    'last_ban_date'         => $lastBanDate,
                ]);
            }

            return back()->with('success', 'Steam user info saved successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
