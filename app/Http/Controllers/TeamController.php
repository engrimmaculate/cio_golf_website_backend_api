<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teamMembers = TeamMember::orderBy('name')
            ->paginate(10);

        return response()->json($teamMembers);
    }

    public function byDepartment($department)
    {
        $teamMembers = TeamMember::where('department', $department)
            ->orderBy('name')
            ->get();

        return response()->json($teamMembers);
    }
}
