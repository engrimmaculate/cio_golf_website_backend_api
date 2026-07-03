<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    public function index()
    {
        $sponsors = Sponsor::where('featured', true)
            ->orderByRaw("FIELD(tier, 'title', 'platinum', 'gold', 'silver', 'strategic')")
            ->get();

        return response()->json($sponsors);
    }

    public function byTier($tier)
    {
        $sponsors = Sponsor::where('tier', $tier)->get();

        return response()->json($sponsors);
    }
}
