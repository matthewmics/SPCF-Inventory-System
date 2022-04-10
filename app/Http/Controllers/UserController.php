<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ActivityLogController;
use App\Models\User;

class UserController extends Controller
{
    public function list()
    {
        return User::orderBy('role')->get();
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'password' => 'required|confirmed|string'
        ]);

        $current_user = auth()->user();

        ActivityLogController::store(auth()->user(), "<b>$current_user->name</b> created user <b>$request->name</b>");

        $request['password'] = bcrypt($request['password']);

        return User::create($request->all());
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|confirmed|string'
        ]);

        $user = User::find($id);

        $current_user = auth()->user();

        ActivityLogController::store(auth()->user(), "<b>$current_user->name</b> changed password of user <b>$user->name</b>");

        $request['password'] = bcrypt($request['password']);

        $user->password =  $request['password'];
        $user->save();

        return response()->noContent();
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);


        $current_user = auth()->user();

        ActivityLogController::store(auth()->user(), "<b>$current_user->name</b> deleted user <b>$user->name</b>");

        $user->delete();
        return response()->noContent();
    }
}
