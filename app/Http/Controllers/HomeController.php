<?php

namespace App\Http\Controllers;

use App\Models\SteamUser;
use App\Models\SteamUserBan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{

    public function index()
    {
        // Existing latest 20 banned users
        $bannedUsers = SteamUser::whereHas('bans', function ($query) {
            $query->where('vac_banned', true)
                ->orWhere('number_of_game_bans', '>', 0);
        })
            ->with('bans')
            ->orderByDesc(function ($query) {
                $query->select('last_ban_date')
                    ->from('steam_user_bans')
                    ->whereColumn('steam_user_bans.steam_user_id', 'steam_users.id')
                    ->limit(1);
            })
            ->take(20)
            ->get();

        // Latest 20 banned users in the current month, sorted by Steam level
        $currentMonth = \Carbon\Carbon::now()->month;
        $currentYear = \Carbon\Carbon::now()->year;

        $bansOfTheMonth = SteamUser::whereHas('bans', function ($query) use ($currentMonth, $currentYear) {
            $query->where(function ($q) {
                $q->where('vac_banned', true)
                    ->orWhere('number_of_game_bans', '>', 0);
            })
                ->whereYear('last_ban_date', $currentYear)
                ->whereMonth('last_ban_date', $currentMonth);
        })
            ->with('bans')
            ->orderByDesc('steam_level')
            ->take(20)
            ->get();

        // TOP CARD VALUE
        $totalAccounts = SteamUser::count();

        $totalBanned = SteamUser::whereHas('bans', function ($query) {
            $query->where('vac_banned', true)
                ->orWhere('number_of_game_bans', '>', 0);
        })->count();

        $communityBans = SteamUserBan::where('community_banned', true)->count();

        $vacBans = SteamUserBan::where('vac_banned', true)->count();

        return view('home', compact(
            'bannedUsers',
            'bansOfTheMonth',
            'totalAccounts',
            'totalBanned',
            'communityBans',
            'vacBans'
        ));
    }


    public function all_player()
    {
        $players = SteamUser::with('bans')
            ->latest()
            ->paginate(10);

        return view('all', compact('players'));
    }

    public function leaderboard()
    {
        $startDate = Carbon::now()->subDays(90);
        $endDate   = Carbon::now();

        $bannedUsers = SteamUser::whereHas('bans', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('last_ban_date', [$startDate, $endDate])
                ->where(function ($q) {
                    $q->where('vac_banned', true)
                        ->orWhere('number_of_game_bans', '>', 0)
                        ->orWhere('community_banned', true);
                });
        })
            ->with('bans')
            ->orderByDesc('steam_level')
            ->take(100)
            ->get();

        return view('leaderboard', compact('bannedUsers'));
    }



    public function history()
    {
        $bannedUsers = SteamUser::whereHas('bans', function ($query) {
            $query->where('vac_banned', true)
                ->orWhere('number_of_game_bans', '>', 0);
        })
            ->with(['bans' => function ($query) {
                $query->orderByDesc('last_ban_date');
            }])
            ->orderByDesc(function ($query) {
                $query->select('last_ban_date')
                    ->from('steam_user_bans')
                    ->whereColumn('steam_user_bans.steam_user_id', 'steam_users.id')
                    ->orderByDesc('last_ban_date')
                    ->limit(1);
            })
            ->paginate(10);

        return view('history', compact('bannedUsers'));
    }

    public function stats()
    {
        return view('stats');
    }


    public function search(Request $request)
    {
        $request->validate([
            'steam_id' => 'required|string',
        ]);

        $steamId = $request->input('steam_id');

        // 1️⃣ Try to fetch from DB
        $bannedUser = SteamUser::with('bans')
            ->where('steamid', $steamId)
            ->first();

        if ($bannedUser) {
            return view('search', compact('bannedUser'));
        }

        // 2️⃣ If not found, call Steam API
        $apiKey = env('STEAM_API_KEY');

        try {
            // 🔹 Get Profile
            $profileResponse = Http::get("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/", [
                'key' => $apiKey,
                'steamids' => $steamId
            ]);

            $profileData = $profileResponse->json('response.players.0');

            if (!$profileData) {
                return redirect()->back()->with('error', 'User not found or profile is private.');
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

            // 🔹 Save to DB
            $bannedUser = SteamUser::updateOrCreate(
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

            if ($banData) {
                $lastBanDate = isset($banData['DaysSinceLastBan'])
                    ? now()->subDays($banData['DaysSinceLastBan'])->toDateString()
                    : null;

                $bannedUser->bans()->updateOrCreate([], [
                    'community_banned'      => $banData['CommunityBanned'] ?? false,
                    'vac_banned'            => $banData['VACBanned'] ?? false,
                    'number_of_vac_bans'    => $banData['NumberOfVACBans'] ?? 0,
                    'number_of_game_bans'   => $banData['NumberOfGameBans'] ?? 0,
                    'days_since_last_ban'   => $banData['DaysSinceLastBan'] ?? null,
                    'last_ban_date'         => $lastBanDate,
                ]);
            }

            // 🔹 Reload with bans
            $bannedUser->load('bans');

            return view('search', compact('bannedUser'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
