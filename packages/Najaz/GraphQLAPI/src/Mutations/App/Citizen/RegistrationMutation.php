<?php

namespace Najaz\GraphQLAPI\Mutations\App\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Najaz\Citizen\Repositories\CitizenRepository;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\GraphQLAPI\Validators\CustomException;

class RegistrationMutation extends Controller
{
    public function __construct(
        protected CitizenRepository $citizenRepository,
    ) {
        Auth::setDefaultDriver('citizen-api');
    }

    /**
     * Handle citizen sign up.
     */
    public function signUp(mixed $rootValue, array $args, GraphQLContext $context): array
    {
        najaz_graphql()->validate($args, [
            'first_name'             => 'required|string',
            'middle_name'            => 'required|string',
            'last_name'              => 'required|string',
            'gender'                 => 'required|string',
            'email'                  => 'nullable|email|unique:citizens,email',
            'phone'                  => 'required|string|unique:citizens,phone',
            'national_id'            => 'required|string|unique:citizens,national_id',
            'date_of_birth'          => 'required|date|before:today',
            'citizen_type_id'        => 'required|integer|exists:citizen_types,id',
            'password'               => 'required|string|min:6|confirmed',
            'device_token'           => 'nullable|string',
            'remember'               => 'nullable|boolean',
        ]);

        $citizen = $this->citizenRepository->create([
            'first_name'                  => $args['first_name'],
            'middle_name'                 => $args['middle_name'],
            'last_name'                   => $args['last_name'],
            'gender'                      => $args['gender'],
            'email'                       => $args['email'] ?? null,
            'phone'                       => $args['phone'],
            'national_id'                 => $args['national_id'],
            'date_of_birth'               => $args['date_of_birth'],
            'citizen_type_id'             => $args['citizen_type_id'],
            'password'                    => bcrypt($args['password']),
            'api_token'                   => Str::random(80),
            'token'                       => Str::random(40),
            'status'                      => 1,
            'is_verified'                 => 1,
            'identity_verification_status'=> 0,
            'device_token'                => $args['device_token'] ?? null,
        ])->fresh(['citizenType']);

        return $this->issueTokenResponse($args, $citizen, trans('najaz_graphql::app.citizens.registration.success'));
    }

    /**
     * Generate a JWT token for the citizen.
     */
    protected function issueTokenResponse(array $args, $citizen, string $message): array
    {
        $credentials = $this->buildIdentifier($args, $citizen);

        $credentials['password'] = $args['password'];

        if (! $jwtToken = auth('citizen-api')->attempt($credentials)) {
            throw new CustomException(trans('najaz_graphql::app.citizens.login.invalid-creds'));
        }

        $authorizedCitizen = najaz_graphql()->authorize('citizen-api', $jwtToken);

        if (! empty($args['device_token'])) {
            $authorizedCitizen->device_token = $args['device_token'];
            $authorizedCitizen->save();
        }

        return [
            'success'      => true,
            'message'      => $message,
            'access_token' => $jwtToken,
            'token_type'   => 'Bearer',
            'expires_in'   => auth('citizen-api')->factory()->getTTL() * 60,
            'citizen'      => $authorizedCitizen->fresh(['citizenType.services']),
            'services'     => $this->getCitizenServices($authorizedCitizen),
        ];
    }

    /**
     * Build the credentials array for authentication.
     */
    protected function buildIdentifier(array $args, $citizen): array
    {
        if (! empty($args['email'])) {
            return ['email' => $args['email']];
        }

        if (! empty($args['national_id'])) {
            return ['national_id' => $args['national_id']];
        }

        if (! empty($citizen->email)) {
            return ['email' => $citizen->email];
        }

        return ['national_id' => $citizen->national_id];
    }

    /**
     * Fetch services allowed for the citizen's type.
     */
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

