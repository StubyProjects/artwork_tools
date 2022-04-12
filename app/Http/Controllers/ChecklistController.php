<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ChecklistController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create()
    {
        return inertia('Checklists/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $checklist = Checklist::create([
            'name' => $request->name,
            'project_id' => $request->project_id
        ]);

        $checklist->tasks()->createMany($request->tasks);

        if (Auth::user()->can('update users')) {
            $checklist->users()->sync(
                collect($request->assigned_user_ids)
                    ->map(function ($user_id) {
                        return $user_id;
                    })
            );
        } else {
            return response()->json(['error' => 'Not authorized to assign users to a checklist.'], 403);
        }

        return Redirect::back()->with('success', 'Checklist created.');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Checklist $checklist
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Checklist $checklist)
    {
        return inertia('Checklists/Show', [
            'checklist' => [
                'id' => $checklist->id,
                'name' => $checklist->name,
                'tasks' => $checklist->tasks->map(fn($task) => [
                    'id' => $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'deadline' => $task->deadline,
                    'done' => $task->done,
                ]),
                'users' => $checklist->users->map(fn($user) => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url
                ])
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Checklist $checklist
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function edit(Checklist $checklist)
    {
        return inertia('Checklists/Edit', [
            'checklist' => [
                'id' => $checklist->id,
                'name' => $checklist->name,
                'tasks' => $checklist->tasks->map(fn($task) => [
                    'id' => $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'deadline' => $task->deadline,
                    'done' => $task->done,
                ]),
                'users' => $checklist->users->map(fn($user) => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url
                ])
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Checklist $checklist
     */
    public function update(Request $request, Checklist $checklist)
    {
        $checklist->update($request->only('name'));

        $checklist->tasks()->createMany($request->tasks);

        if(Auth::user()->can('update users')) {
            $checklist->users()->sync(
                collect($request->assigned_user_ids)
                    ->map(function ($user_id) {
                        return $user_id;
                    })
            );
        }
        else {
            return response()->json(['error' => 'Not authorized to assign users to a checklist.'],403);
        }

        return Redirect::back()->with('success', 'Checklist updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Checklist $checklist
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Checklist $checklist)
    {
        $checklist->delete();
        return Redirect::back()->with('success', 'Checklist deleted');
    }
}