<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::all();
            return UserResource::collection($users);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Users not found'], 404);
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return new UserResource($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'firstname' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|min:6',
                'image' => 'nullabe|image|max:2048'
            ]);

            $user->firstname = $request->firstname;
            $user->email = $request->email;
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();
            return response()->json([
                'message' => 'User updated successfully',
                'data' => new UserResource($user)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
    public function updateAvatar(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        $file = $request->file('image');
        $fileName = 'avatar_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = 'avatars/' . $fileName; // Simpan dalam folder "avatars"

        try{
            $user = User::findOrFail($id);
            $oldavatar = $user->image;
            if ($oldavatar){
                Storage::disk('gcs')->delete($user->image);
            }
            $path = Storage::disk('gcs')->put($filePath,  file_get_contents($file));
            $url = Storage::disk('gcs')->url($filePath);
            $user->image = $url;
            $user->save();
            return response()->json([
                'message' => 'Image uploaded successfully',
                'url' => $url,
            ]);

        }catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'Failed to upload avatar, id not found',
            ],404);
        }
        
    }
}
