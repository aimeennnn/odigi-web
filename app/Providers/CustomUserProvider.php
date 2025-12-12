<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class CustomUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $user = parent::retrieveById($identifier);
        
        // Cek status user, hanya return jika status active
        if ($user && $user->status !== 'active') {
            return null;
        }
        
        return $user;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $user = parent::retrieveByToken($identifier, $token);
        
        // Cek status user, hanya return jika status active
        if ($user && $user->status !== 'active') {
            return null;
        }
        
        return $user;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $user = parent::retrieveByCredentials($credentials);
        
        // Cek status user, hanya return jika status active
        if ($user && $user->status !== 'active') {
            return null;
        }
        
        return $user;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Cek status user terlebih dahulu
        if ($user->status !== 'active') {
            return false;
        }
        
        return parent::validateCredentials($user, $credentials);
    }
}
