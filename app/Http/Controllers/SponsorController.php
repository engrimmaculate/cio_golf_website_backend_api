<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    public function index()
    {
        $sponsors = Sponsor::orderBy('featured', 'desc')
            ->orderBy('name')
            ->paginate(10);

        return response()->json($sponsors);
    }

    public function byTier($tier)
    {
        $sponsors = Sponsor::where('tier', $tier)
            ->orderBy('featured', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json($sponsors);
    }
}
