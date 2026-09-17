<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeachingAllocationRequest;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAllocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Display a listing of the teaching allocations, narrowed down by the filters in the query string.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'standard_id' => $request->integer('standard_id') ?: null,
            'division_id' => $request->integer('division_id') ?: null,
            'subject_id' => $request->integer('subject_id') ?: null,
        ];

        $allocations = TeachingAllocation::query()
            ->with(['teacherProfile.user', 'division.standard', 'subject'])
            ->when($filters['search'] !== '', fn (Builder $query) => $query->whereHas('teacherProfile', fn (Builder $teacherQuery) => $teacherQuery
                ->where(fn (Builder $searchQuery) => $searchQuery
                    ->where('employee_code', 'like', "%{$filters['search']}%")
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                        ->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%")))))
            ->when($filters['standard_id'], fn (Builder $query, int $standardId) => $query->whereRelation('division', 'standard_id', $standardId))
            ->when($filters['division_id'], fn (Builder $query, int $divisionId) => $query->where('division_id', $divisionId))
            ->when($filters['subject_id'], fn (Builder $query, int $subjectId) => $query->where('subject_id', $subjectId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('teaching-allocations.index', [
            'allocations' => $allocations,
            'filters' => $filters,
            'standards' => Standard::query()->with('divisions')->orderBy('sort_order')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
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
