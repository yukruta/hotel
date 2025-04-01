<?php

namespace App\Http\Controllers\Backend;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Imports\PermissionImport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function allPermission()
    {
        $permissions = Permission::latest()->get();
        return view('backend.pages.permission.all_permission', compact('permissions'));
    }

    public function addPermission()
    {
        return view('backend.pages.permission.add_permission');
    }

    public function storePermission(Request $request)
    {
        Permission::create([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);
        $notification = array(
            'message' => 'Permission Added Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.permission')->with($notification);
    }

    public function editPermission($id)
    {
        $permission = Permission::find($id);
        return view('backend.pages.permission.edit_permission', compact('permission'));

    }

    public function updatePermission(Request $request, $id)
    {
        $per_id = $request->id;
        Permission::find($id)->update([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);
        $notification = array(
            'message' => 'Permission Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.permission')->with($notification);
    }

    public function deletePermission($id)
    {

        Permission::find($id)->delete();
        $notification = array(
            'message' => 'Permission Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function importPermission()
    {

        return view('backend.pages.permission.import_permission');
    }

    public function export()
    {
        return Excel::download(new UsersExport(), 'permission.xlsx');

    }

    public function import(Request $request)
    {
        Excel::import(new PermissionImport(), $request->file('import_file'));

        $notification = array(
            'message' => 'Permission Imported Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }

    /////All Roles Methods/////

    public function allRoles()
    {
        $roles = Role::latest()->get();
        return view('backend.pages.roles.all_roles', compact('roles'));
    }

    public function addRole()
    {
        return view('backend.pages.roles.add_role');
    }

    public function storeRole(Request $request)
    {
        Role::create([
            'name' => $request->name,
        ]);
        $notification = array(
            'message' => 'Role Added Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.roles')->with($notification);
    }

    public function editRole($id)
    {
        $roles = Role::find($id);
        return view('backend.pages.roles.edit_roles', compact('roles'));
    }

    public function updateRole(Request $request)
    {
        $role_id = $request->id;
        Role::find($role_id)->update([
            'name' => $request->name,
        ]);
        $notification = array(
            'message' => 'Role Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.roles')->with($notification);
    }

    public function deleteRole($id)
    {
        Role::find($id)->delete();
        $notification = array(
            'message' => 'Role Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function addRolesPermission()
    {

        $roles = Role::all();
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();
        return view('backend.pages.rolesetup.add_roles_permission', compact('roles', 'permissions', 'permission_groups'));
    }

    public function rolePermissionStore(Request $request)
    {
        $data = array();
        $permissions = $request->permission;
        foreach ($permissions as $key => $item) {
            $data['role_id'] = $request->role_id;
            $data['permission_id'] = $item;

            DB::table('role_has_permissions')->insert($data);
        }
        $notification = array(
            'message' => 'Role Permission Added Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.roles.permission')->with($notification);
    }

    public function allRolesPermission($id)
    {
        $roles = Role::all();
        return view('backend.pages.rolesetup.all_roles_permission', compact('roles'));
    }

    public function adminEditRoles($id)
    {
        $role = Role::find($id);
        $permissions = Permission::all();
        $permission_groups = User::getpermissionGroups();
        return view('backend.pages.rolesetup.edit_roles_permission', compact('role', 'permissions', 'permission_groups'));
    }

    public function adminRolesUpdate(Request $request, $id)
    {
        $role = Role::find($id);
        $permissions = $request->permission;
        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }
        $notification = array(
            'message' => 'Role Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.roles.permission')->with($notification);
    }

    public function adminDeleteRoles($id)
    {

        $role = Role::find($id);
        if (!is_null($role)) {
            $role->delete();
        }
        $notification = array(
            'message' => 'Role Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }
}
