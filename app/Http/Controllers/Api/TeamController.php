<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $team = TeamMember::orderBy('department')->orderBy('name')->get();

        return response()->json($team);
    }

    public function byDepartment($department)
    {
        $team = TeamMember::where('department', $department)
            ->orderBy('name')
            ->get();

        return response()->json($team);
    }
}
