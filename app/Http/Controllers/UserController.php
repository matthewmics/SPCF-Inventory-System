<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        $request['password'] = bcrypt($request['password']);

        return User::create($request->all());
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|confirmed|string'
        ]);

        $user = User::find($id);
        $user->password = bcrypt($user->password);
        $user->save();

        return response()->noContent();
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        $user->delete();
        return response()->noContent();
    }
}
