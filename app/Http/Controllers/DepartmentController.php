<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Inertia\ResponseFactory;

class DepartmentController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Department::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|ResponseFactory
     */
    public function index()
    {
        return inertia('Departments/DepartmentManagement', [
            'departments' => Department::paginate(10)->through(fn($department) => [
                'id' => $department->id,
                'name' => $department->name,
                'logo_url' => $department->logo_url,
                'users' => $department->users->map(fn($user) => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url
                ])
            ]),
            'users' => User::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response|ResponseFactory
     */
    public function create()
    {
        return inertia('Departments/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDepartmentRequest $request
     * @return RedirectResponse
     */
    public function store(StoreDepartmentRequest $request)
    {
        $logo = $request->file('logo');

        $department = Department::create([
            'name' => $request->name
        ]);

        $department->users()->sync(
            collect($request->assigned_users)
                ->map(function ($user) {

                    $this->authorize('update', User::find($user['id']));

                    return $user['id'];
                })
        );

        if ($logo) {
            $department->logo_path = $logo->storePublicly('logos', ['disk' => 'public']);
        }

        return Redirect::route('departments')->with('success', 'Department created.');
    }

    /**
     * Show the specified resource.
     *
     * @param Department $department
     * @return Response|ResponseFactory
     */
    public function show(Department $department)
    {
        return inertia('Departments/Show', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'logo_url' => $department->logo_url,
                'users' => $department->users->map(fn($user) => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url
                ]),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Department $department
     * @return Response|ResponseFactory
     */
    public function edit(Department $department)
    {
        return inertia('Departments/Edit', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'logo_url' => $department->logo_url,
                'users' => $department->users->map(fn($user) => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url
                ])
            ],
            'users' => User::all()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Department $department
     * @return RedirectResponse
     */
    public function update(Request $request, Department $department)
    {
        $logo = $request->file('logo');

        $department->update($request->only('name'));

        $department->users()->sync(
            collect($request->assigned_users)
                ->map(function ($user) {

                    $this->authorize('update', User::find($user['id']));

                    return $user['id'];
                })
        );

        if ($logo) {
            tap($department->logo_path, function ($previous) use ($logo, $department) {
                $department->forceFill([
                    'logo_path' => $logo->storePublicly(
                        'logos', ['disk' => 'public']
                    ),
                ])->save();

                if ($previous) {
                    Storage::disk('public')->delete($previous);
                }
            });
        }

        return Redirect::route('departments')->with('success', 'Department updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Department $department
     * @return RedirectResponse
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return Redirect::back()->with('success', 'Department deleted');
    }
}
