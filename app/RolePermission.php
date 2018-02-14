<?php

/**
 * Copyright (c) 2017-present, DocsPen.
 * All rights reserved.
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace DocsPen;

class RolePermission extends Model
{
    /**
     * The roles that belong to the permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }

    /**
     * Get the permission object by name.
     *
     * @param $name
     *
     * @return mixed
     */
    public static function getByName($name)
    {
        return static::where('name', '=', $name)->first();
    }
}