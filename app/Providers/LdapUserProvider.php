<?php

/**
 * Copyright (c) 2017-present, DocsPen.
 * All rights reserved.
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace DocsPen\Providers;

use DocsPen\User;
use DocsPen\Services\LdapService;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class LdapUserProvider implements UserProvider
{
    /**
     * The user model.
     *
     * @var string
     */
    protected $model;

    /**
     * @var LdapService
     */
    protected $ldapService;

    /**
     * LdapUserProvider constructor.
     *
     * @param $model
     * @param LdapService $ldapService
     */
    public function __construct($model, LdapService $ldapService)
    {
        $this->model = $model;
        $this->ldapService = $ldapService;
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class();
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param mixed  $identifier
     * @param string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        return $model->newQuery()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where($model->getRememberTokenName(), $token)
            ->first();
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $token
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        if ($user->exists) {
            $user->setRememberToken($token);
            $user->save();
        }
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // Get user via LDAP
        $userDetails = $this->ldapService->getUserDetails($credentials['username']);
        if ($userDetails === null) {
            return;
        }

        // Search current user base by looking up a uid
        $model = $this->createModel();
        $currentUser = $model->newQuery()
            ->where('external_auth_id', $userDetails['uid'])
            ->first();

        if ($currentUser !== null) {
            return $currentUser;
        }

        $model->name = $userDetails['name'];
        $model->external_auth_id = $userDetails['uid'];
        $model->email = $userDetails['email'];
        $model->email_confirmed = false;

        return $model;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->ldapService->validateUserCredentials($user, $credentials['username'], $credentials['password']);
    }
}
