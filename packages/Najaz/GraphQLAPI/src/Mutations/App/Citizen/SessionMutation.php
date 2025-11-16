<?php

namespace Najaz\GraphQLAPI\Mutations\App\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\GraphQLAPI\Validators\CustomException;

class SessionMutation extends Controller
{
    public function __construct()
    {
        Auth::setDefaultDriver('citizen-api');
    }

    /**
     * Handle citizen login.
     */
    public function login(mixed $rootValue, array $args, GraphQLContext $context): array
    {
        najaz_graphql()->validate($args, [
            'email'        => 'nullable|email|required_without:national_id',
            'national_id'  => 'nullable|required_without:email',
            'password'     => 'required|string',
            'remember'     => 'nullable|boolean',
            'device_token' => 'nullable|string',
        ]);

        $credentials = $this->buildIdentifier($args);

        $credentials['password'] = $args['password'];

        if (! $jwtToken = auth('citizen-api')->attempt($credentials)) {
            throw new CustomException(trans('najaz_graphql::app.citizens.login.invalid-creds'));
        }

        $citizen = najaz_graphql()->authorize('citizen-api', $jwtToken);

        if (! empty($args['device_token'])) {
            $citizen->device_token = $args['device_token'];
            $citizen->save();
        }

        return [
            'success'      => true,
            'message'      => trans('najaz_graphql::app.citizens.login.success'),
            'access_token' => $jwtToken,
            'token_type'   => 'Bearer',
            'expires_in'   => auth('citizen-api')->factory()->getTTL() * 60,
            'citizen'      => $citizen->fresh(['citizenType.services']),
            'services'     => $this->getCitizenServices($citizen),
        ];
    }

    /**
     * Logout the authenticated citizen.
     */
    public function logout(): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        auth('citizen-api')->logout();

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.login.logout-success'),
        ];
    }

    protected function buildIdentifier(array $args): array
    {
        if (! empty($args['email'])) {
            return ['email' => $args['email']];
        }

        return ['national_id' => $args['national_id']];
    }

    protected function getCitizenServices($citizen)
    {
        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            return collect();
        }

        return $citizenType->services()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get();
    }
}

