<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeachingAllocationRequest;
use App\Models\Standard;
use App\Models\TeacherProfile;
use App\Models\TeachingAllocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class TeachingAllocationController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:super-admin|admin'),
        ];
    }

    /**
     * Display a listing of the teaching allocations.
     */
    public function index(): View
    {
        $allocations = TeachingAllocation::query()
            ->with(['teacherProfile.user', 'division.standard', 'subject'])
            ->latest()
            ->paginate(20);

        return view('teaching-allocations.index', ['allocations' => $allocations]);
    }

    /**
     * Show the form for creating a new teaching allocation.
     */
    public function create(): View
    {
        $teachers = TeacherProfile::query()
            ->with('user')
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role('teacher'))
            ->get()
            ->sortBy('user.name');

        $standards = Standard::query()
            ->with(['divisions.subjects' => fn ($query) => $query->orderBy('name')])
            ->orderBy('sort_order')
            ->get();

        return view('teaching-allocations.create', [
            'teachers' => $teachers,
            'standards' => $standards,
        ]);
    }

    /**
     * Store a newly created teaching allocation.
     */
    public function store(StoreTeachingAllocationRequest $request): RedirectResponse
    {
        $allocation = TeachingAllocation::create($request->safe()->only(['teacher_profile_id', 'division_id', 'subject_id']));

        $allocation->load(['teacherProfile.user', 'division.standard', 'subject']);

        return to_route('teaching-allocations.index')->with('status', __(':teacher now teaches :subject in :standard - :division.', [
            'teacher' => $allocation->teacherProfile->user->name,
            'subject' => $allocation->subject->name,
            'standard' => $allocation->division->standard->name,
            'division' => $allocation->division->name,
        ]));
    }

    /**
     * Remove the specified teaching allocation.
     */
    public function destroy(TeachingAllocation $teachingAllocation): RedirectResponse
    {
        $teachingAllocation->delete();

        return to_route('teaching-allocations.index')->with('status', __('Teaching allocation removed.'));
    }
}
