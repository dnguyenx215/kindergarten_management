<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Lấy danh sách tất cả users với role và permissions.
     */
    public function index(Request $request)
    {
        $role = $request->query('role');
        
        // Lấy danh sách users
        $users = User::all();
        
        // Lấy thông tin roles và user_roles từ DB
        $userRoles = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('user_roles.user_id', 'roles.id as role_id', 'roles.name as role_name', 'roles.display_name')
            ->get();
        
        // Lấy thông tin permissions
        $rolePermissions = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->select('role_permissions.role_id', 'permissions.id as permission_id', 'permissions.name as permission_name')
            ->get();
        
        // Tạo mapping các role và permission cho mỗi user
        $userRolesMap = [];
        foreach ($userRoles as $userRole) {
            if (!isset($userRolesMap[$userRole->user_id])) {
                $userRolesMap[$userRole->user_id] = [];
            }
            
            $userRolesMap[$userRole->user_id][] = [
                'id' => $userRole->role_id,
                'name' => $userRole->role_name,
                'display_name' => $userRole->display_name
            ];
        }
        
        // Tạo mapping các permission cho mỗi role
        $rolePermissionsMap = [];
        foreach ($rolePermissions as $rolePermission) {
            if (!isset($rolePermissionsMap[$rolePermission->role_id])) {
                $rolePermissionsMap[$rolePermission->role_id] = [];
            }
            
            $rolePermissionsMap[$rolePermission->role_id][] = [
                'id' => $rolePermission->permission_id,
                'name' => $rolePermission->permission_name
            ];
        }
        
        // Lọc user theo role nếu có yêu cầu
        $filteredUsers = $users;
        if ($role) {
            $filteredUsers = $users->filter(function($user) use ($role, $userRolesMap) {
                // Kiểm tra role trực tiếp (legacy support)
                if (property_exists($user, 'role') && $user->role === $role) {
                    return true;
                }
                
                // Kiểm tra qua bảng user_roles
                if (isset($userRolesMap[$user->id])) {
                    foreach ($userRolesMap[$user->id] as $userRole) {
                        if ($userRole['name'] === $role) {
                            return true;
                        }
                    }
                }
                
                return false;
            });
        }
        
        // Thêm thông tin roles và permissions vào response
        $result = $filteredUsers->map(function($user) use ($userRolesMap, $rolePermissionsMap) {
            $data = $user->toArray();
            
            // Thêm roles
            $data['roles'] = isset($userRolesMap[$user->id]) ? $userRolesMap[$user->id] : [];
            
            // Thêm permissions
            $permissions = [];
            if (isset($userRolesMap[$user->id])) {
                foreach ($userRolesMap[$user->id] as $role) {
                    $roleId = $role['id'];
                    if (isset($rolePermissionsMap[$roleId])) {
                        // Thêm các permission chưa có
                        foreach ($rolePermissionsMap[$roleId] as $permission) {
                            $exists = false;
                            foreach ($permissions as $existingPermission) {
                                if ($existingPermission['id'] === $permission['id']) {
                                    $exists = true;
                                    break;
                                }
                            }
                            
                            if (!$exists) {
                                $permissions[] = $permission;
                            }
                        }
                    }
                }
            }
            
            $data['permissions'] = $permissions;
            
            return $data;
        });
        
        return response()->json(['data' => $result], 200);
    }

    /**
     * Lấy thông tin chi tiết của một user.
     */
    public function show($id)
    {
        // Tìm user
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }
        
        // Lấy roles của user từ bảng user_roles
        $roles = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $id)
            ->select('roles.id', 'roles.name', 'roles.display_name')
            ->get();
        
        // Lấy permissions qua roles
        $permissions = collect();
        foreach ($roles as $role) {
            $rolePermissions = DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role_id', $role->id)
                ->select('permissions.id', 'permissions.name', 'permissions.display_name')
                ->get();
            
            foreach ($rolePermissions as $permission) {
                // Kiểm tra xem permission đã tồn tại chưa để tránh trùng lặp
                if (!$permissions->contains('id', $permission->id)) {
                    $permissions->push($permission);
                }
            }
        }
        
        // Cấu trúc response
        $userData = $user->toArray();
        $userData['roles'] = $roles;
        $userData['permissions'] = $permissions;
        
        return response()->json(['data' => $userData], 200);
    }

    /**
     * Cập nhật thông tin user bao gồm roles và permissions.
     */
    public function update(Request $request, $id)
    {
        // Validate đầu vào
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$id,
            'password' => 'sometimes|required|min:6',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Tìm user
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }

        // Cập nhật thông tin user
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }
        $user->save();

        // Cập nhật roles nếu có
        if (isset($validated['roles'])) {
            // Xóa tất cả roles hiện tại
            DB::table('user_roles')->where('user_id', $id)->delete();
            
            // Thêm roles mới
            foreach ($validated['roles'] as $roleId) {
                DB::table('user_roles')->insert([
                    'user_id' => $id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Lấy thông tin user sau khi cập nhật
        return $this->show($id);
    }

    /**
     * Kiểm tra nếu user có một permission cụ thể.
     */
    public function checkPermission(Request $request, $id)
    {
        $validated = $request->validate([
            'permission' => 'required|string',
        ]);

        // Tìm user
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }

        // Lấy roles của user
        $roles = DB::table('user_roles')
            ->where('user_id', $id)
            ->pluck('role_id');
        
        // Kiểm tra permission trong các roles
        $hasPermission = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_permissions.role_id', $roles)
            ->where('permissions.name', $validated['permission'])
            ->exists();

        return response()->json([
            'user_id' => $user->id,
            'permission' => $validated['permission'],
            'has_permission' => $hasPermission,
        ], 200);
    }

    /**
     * Lấy danh sách tất cả permissions của một user
     */
    public function getUserPermissions($id)
    {
        // Tìm user
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }

        // Lấy roles của user
        $roles = DB::table('user_roles')
            ->where('user_id', $id)
            ->pluck('role_id');
        
        // Lấy permissions dựa trên roles
        $permissions = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_permissions.role_id', $roles)
            ->select('permissions.id', 'permissions.name', 'permissions.display_name')
            ->distinct()
            ->get();

        return response()->json([
            'user_id' => $user->id,
            'permissions' => $permissions,
        ], 200);
    }

    /**
     * Thêm roles mới cho user
     */
    public function assignRoles(Request $request, $id)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Tìm user
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }

        // Lấy roles hiện tại
        $existingRoleIds = DB::table('user_roles')
            ->where('user_id', $id)
            ->pluck('role_id')
            ->toArray();
        
        // Thêm các roles mới (chỉ thêm những roles chưa có)
        foreach ($validated['roles'] as $roleId) {
            if (!in_array($roleId, $existingRoleIds)) {
                DB::table('user_roles')->insert([
                    'user_id' => $id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Lấy danh sách roles sau khi cập nhật
        $roles = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $id)
            ->select('roles.id', 'roles.name', 'roles.display_name')
            ->get();

        return response()->json([
            'message' => 'Roles assigned successfully',
            'user_id' => $user->id,
            'roles' => $roles,
        ], 200);
    }

    /**
     * Xoá roles của user
     */
    public function removeRoles(Request $request, $id)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Tìm user
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }

        // Xóa các roles được chỉ định
        DB::table('user_roles')
            ->where('user_id', $id)
            ->whereIn('role_id', $validated['roles'])
            ->delete();

        // Lấy danh sách roles còn lại
        $roles = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $id)
            ->select('roles.id', 'roles.name', 'roles.display_name')
            ->get();

        return response()->json([
            'message' => 'Roles removed successfully',
            'user_id' => $user->id,
            'roles' => $roles,
        ], 200);
    }
}