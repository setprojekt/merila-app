<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

class PinUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return null;
        }

        // Če je prijava samo s PIN-om
        if (isset($credentials['pin_only'])) {
            // Najdemo vse uporabnike z omogočeno PIN prijavo
            $users = $this->newModelQuery()
                ->where('can_login_with_pin', true)
                ->get();
            
            // Preverimo, kateri uporabnik ima pravilen PIN
            foreach ($users as $user) {
                if (Hash::check($credentials['pin_only'], $user->pin_code)) {
                    return $user;
                }
            }
            
            return null;
        }

        // Sicer uporabi standardno email + geslo avtentikacijo
        if (isset($credentials['email'])) {
            $query = $this->newModelQuery();
            $query->where('email', $credentials['email']);
            return $query->first();
        }

        return parent::retrieveByCredentials($credentials);
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
        // Če je prijava samo s PIN-om, je že preverjeno v retrieveByCredentials
        if (isset($credentials['pin_only'])) {
            return true;
        }

        // Za email prijavo preveri geslo
        if (isset($credentials['password'])) {
            return Hash::check($credentials['password'], $user->getAuthPassword());
        }

        return false;
    }
}
