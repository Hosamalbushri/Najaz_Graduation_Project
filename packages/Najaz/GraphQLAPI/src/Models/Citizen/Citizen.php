<?php

namespace Najaz\GraphQLAPI\Models\Citizen;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Najaz\Citizen\Models\Citizen as BaseModel;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Citizen extends BaseModel implements Authenticatable, JWTSubject
{
    use AuthenticatableTrait;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}

