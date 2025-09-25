<?php

namespace App\Http\Controllers;


use App\Traits\ApiResponder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    use ApiResponder;

    /**
     * @throws AuthorizationException
     */
    public function authorize(array|string $permissions, ?bool $isMandatory = false, ?Model $usr = null)
    {
        $user ??= Auth::user();
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($isMandatory) {
                $hasPermission = $hasPermission && $user->can($permission);
            } else {
                $hasPermission = $hasPermission || $user->can($permission);
            }


            if (!$hasPermission) {
                throw new AuthorizationException(__('You do not have permission to perform this action.'));
            }
        }

        return true;
    }
}
