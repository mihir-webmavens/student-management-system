<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Standard;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:super-admin|admin', only: ['create', 'store']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::query()->with(['roles', 'studentProfile.division.standard'])->latest()->paginate(15);

        return view('users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('users.create', [
            'roles' => $request->user()->assignableRoleNames(),
            'standards' => Standard::query()->with('divisions')->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Str::password(32),
            ]);

            $user->assignRole($validated['role']);

            if ($validated['role'] === 'student') {
                $user->studentProfile()->create([
                    'division_id' => $validated['division_id'],
                ]);
            }

            $user->notify(new SetPasswordNotification(Password::broker()->createToken($user)));

            return $user;
        });

        return to_route('users.index')->with('status', __('User :name created. A link to set their password has been emailed to :email.', [
            'name' => $user->name,
            'email' => $user->email,
        ]));
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
