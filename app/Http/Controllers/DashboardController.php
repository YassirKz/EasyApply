<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $total = Entreprise::count();
        $pending = Entreprise::where('est_envoye', false)
            ->where(function ($q) {
                $q->whereNull('programmation_envoi')
                  ->orWhere('programmation_envoi', '<=', now());
            })
            ->count();
        $sent = Entreprise::where('est_envoye', true)->count();
        $scheduled = Entreprise::where('est_envoye', false)
            ->where('programmation_envoi', '>', now())
            ->count();

        $sendRate = $total > 0 ? round(($sent / $total) * 100, 1) : 0;

        $lastSevenDays = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $count = Entreprise::where('est_envoye', true)
                ->whereDate('date_envoi', $day)
                ->count();
            $lastSevenDays->push([
                'date' => $day->format('d M'),
                'count' => $count,
            ]);
        }

        return view('dashboard', compact('total', 'pending', 'sent', 'scheduled', 'sendRate', 'lastSevenDays'));
    }
}
