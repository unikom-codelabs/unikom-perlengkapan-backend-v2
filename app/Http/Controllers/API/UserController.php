<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponse;

class UserController extends Controller
{
    public function index()
    {
        $data = User::with([
            'jabatan',
            'unit'
        ])
            ->latest()
            ->paginate(10);

        return ApiResponse::success($data);
    }


    public function show($id)
    {
        $data = User::with([
            'jabatan',
            'unit'
        ])->findOrFail($id);

        return ApiResponse::success($data);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nip' => 'nullable',
            'jenis_kelamin' => 'required|in:Pria,Wanita',
            'jabatan_id' => 'required|exists:jabatans,id',
            'unit_id' => 'required|exists:unit_types,id',
            'role' => 'required|in:admin,user'

        ]);

        $validated['password'] = Hash::make(
            $validated['password']
        );

        $data = User::create($validated);

        return ApiResponse::success(
            $data->load(['position', 'unit']),
            'user berhasil dibuat'
        );
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes',
            'email' => "sometimes|email|unique:users,email,$id",
            'nip' => 'sometimes',
            'jenis_kelamin' => 'sometimes|in:Pria,Wanita',
            'jabatan_id' => 'sometimes|exists:jabatans,id',
            'unit_id' => 'sometimes|exists:unit_types,id',
            'role' => 'sometimes|in:admin,user',
            'password' => 'sometimes|min:6'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt(
                $validated['password']
            );
        }

        $user->update($validated);

        return ApiResponse::success(
            $user->load(['position', 'unit']),
            'user berhasil diupdate'
        );
    }


    public function destroy($id)
    {
        User::destroy($id);

        return ApiResponse::success(
            null,
            'user berhasil dihapus'
        );
    }


    public function resetPassword(Request $request, $id)
    {

        $request->validate([
            'password' => 'required|min:6'
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'password' => Hash::make(
                $request->password
            )
        ]);

        return ApiResponse::success(null,'password berhasil direset');
    }

    public function updateProfile(Request $request)
    {

        $user = auth()->user();

        $validated = $request->validate([
            'username' => 'sometimes',
            'foto' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048'
        ]);



        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $path = $request->file('foto')->store('profile', 'public');
            $user->foto = $path;
        }

        if (isset($validated['username'])) {
            $user->username = $validated['username'];
        }

        $user->save();

        return ApiResponse::success($user,'profile berhasil diupdate');
    }
}
