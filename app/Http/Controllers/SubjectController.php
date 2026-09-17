<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Students see the subjects of their own division along with who teaches them.
     * Everyone else sees every subject and how many divisions it is taught in.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user->hasRole('student')) {
            return view('subjects.index', [
                'isStudent' => false,
                'division' => null,
                'subjects' => Subject::query()->withCount('divisions')->orderBy('name')->get(),
            ]);
        }

        $division = $user->studentProfile?->division()->with('standard')->first();

        $subjects = $division
            ? $division->subjects()
                ->with(['teachingAllocations' => fn ($query) => $query
                    ->where('division_id', $division->id)
                    ->with('teacherProfile.user')])
                ->orderBy('name')
                ->get()
            : new Collection;

        return view('subjects.index', [
            'isStudent' => true,
            'division' => $division,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
