<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AdminProfileUpdateRequest;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function adminDashboard()
    {
        return view('admin.index');
    }

    public function adminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    public function adminLogin()
    {
        return view('admin.admin_login');
    }

    public function adminProfile()
    {
        $profileData = Auth::user();
        return view('admin.admin_profile_view', compact('profileData'));
    }

    public function adminProfileStore(AdminProfileUpdateRequest $request)
    {

        $user = $request->user();

        $user->update($request->validated());

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            if ($user->photo) {
                $oldPhotoPath = public_path('upload/admin_images/' . $user->photo);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            $filename = now()->format('YmdHi') . '_' . $file->getClientOriginalName();

            $file->move(public_path('upload/admin_images'), $filename);

            $user->update(['photo' => $filename]);
        }

        $notification = [
            'message' => 'Admin Profile Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    public function adminChangePassword()
    {
        $profileData = Auth::user();

        if (!$profileData) {
            return redirect()->route('admin.login')->with('error', 'User not found.');
        }

        return view('admin.admin_change_password', compact('profileData'));

    }

    public function adminPasswordUpdate(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
        if (!Hash::check($request->old_password, auth::user()->password)) {
            $notification = array(
                'message' => 'Current password does not matches with the password you provided. Please try again.',
                'alert-type' => 'error'
            );

            return redirect()->back()->with($notification);
        }

        User::whereId(Auth::user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);
        $notification = array(
            'message' => 'Password Changed Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    ///////Admin User All Methods///////
    public function allAdmin()
    {
        $alladmin = User::where('role', 'admin')->get();
        return view('backend.pages.admin.all_admin', compact('alladmin'));
    }

    public function addAdmin()
    {
        $roles = Role::all();
        return view('backend.pages.admin.add_admin', compact('roles'));
    }

    public function storeAdmin(StoreAdminRequest $request)
    {
        $validated = $request->validated();

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->address = $validated['address'] ?? null;
        $user->password = Hash::make($validated['password']);
        $user->role = 'admin';
        $user->status = 'active';
        $user->save();

        if (!empty($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }

        $notification = [
            'message' => 'Admin Added Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.all.admin')->with($notification);
    }

    public function editAdmin($id)
    {
        $user = User::find($id);
        $roles = Role::all();
        return view('backend.pages.admin.edit_admin', compact('user', 'roles'));
    }

    public function updateAdmin(UpdateAdminRequest $request, $id)
    {

        $validated = $request->validated();

        $user = User::findOrFail($id);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password, // Якщо пароль змінюється
            'role' => 'admin',
            'status' => 'active',
        ]);

        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        $notification = array(
            'message' => 'Admin Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.all.admin')->with($notification);
    }

    public function deleteAdmin($id)
    {
        $user = User::find($id);
        if (!is_null($user)) {
            $user->delete();
        }

        $notification = array(
            'message' => 'Admin Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
