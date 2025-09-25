<?php

namespace App\Http\Controllers\API\Dropdown;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Models\User;

class DropdownController extends Controller
{
    use ApiResponder;

    public function users(Request $request)
    {
        try {
            $users = User::select('id', 'name', 'email')
                ->when($request->search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                })
                ->when($request->limit, function ($query, $limit) {
                    return $query->limit($limit);
                }, function ($query) {
                    return $query->limit(50);
                })
                ->get();

            return $this->respondWithCollection($users, 'Users retrieved successfully');
        } catch (\Throwable $exception) {
            return $this->errorDatabase($exception->getMessage());
        }
    }

    public function roles(Request $request)
    {
        try {
            $roles = \Spatie\Permission\Models\Role::select('id', 'name')
                ->when($request->search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->get();

            return $this->respondWithCollection($roles, 'Roles retrieved successfully');
        } catch (\Throwable $exception) {
            return $this->errorDatabase($exception->getMessage());
        }
    }

    public function permissions(Request $request)
    {
        try {
            $permissions = \Spatie\Permission\Models\Permission::select('id', 'name')
                ->when($request->search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->get();

            return $this->respondWithCollection($permissions, 'Permissions retrieved successfully');
        } catch (\Throwable $exception) {
            return $this->errorDatabase($exception->getMessage());
        }
    }

    public function countries(Request $request)
    {
        try {
            $countries = [
                ['id' => 1, 'name' => 'United States', 'code' => 'US'],
                ['id' => 2, 'name' => 'United Kingdom', 'code' => 'UK'],
                ['id' => 3, 'name' => 'Canada', 'code' => 'CA'],
                ['id' => 4, 'name' => 'Australia', 'code' => 'AU'],
                ['id' => 5, 'name' => 'Germany', 'code' => 'DE'],
                ['id' => 6, 'name' => 'France', 'code' => 'FR'],
                ['id' => 7, 'name' => 'Italy', 'code' => 'IT'],
                ['id' => 8, 'name' => 'Spain', 'code' => 'ES'],
                ['id' => 9, 'name' => 'Japan', 'code' => 'JP'],
                ['id' => 10, 'name' => 'China', 'code' => 'CN'],
            ];

            if ($request->search) {
                $countries = array_filter($countries, function ($country) use ($request) {
                    return stripos($country['name'], $request->search) !== false ||
                           stripos($country['code'], $request->search) !== false;
                });
            }

            return $this->respondWithCollection(array_values($countries), 'Countries retrieved successfully');
        } catch (\Throwable $exception) {
            return $this->errorDatabase($exception->getMessage());
        }
    }

    public function statuses(Request $request)
    {
        try {
            $statuses = [
                ['id' => 1, 'name' => 'Active', 'value' => 'active'],
                ['id' => 2, 'name' => 'Inactive', 'value' => 'inactive'],
                ['id' => 3, 'name' => 'Pending', 'value' => 'pending'],
                ['id' => 4, 'name' => 'Suspended', 'value' => 'suspended'],
                ['id' => 5, 'name' => 'Deleted', 'value' => 'deleted'],
            ];

            if ($request->search) {
                $statuses = array_filter($statuses, function ($status) use ($request) {
                    return stripos($status['name'], $request->search) !== false ||
                           stripos($status['value'], $request->search) !== false;
                });
            }

            return $this->respondWithCollection(array_values($statuses), 'Statuses retrieved successfully');
        } catch (\Throwable $exception) {
            return $this->errorDatabase($exception->getMessage());
        }
    }
}