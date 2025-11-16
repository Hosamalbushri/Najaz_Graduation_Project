<?php

namespace Najaz\GraphQLAPI;

use Illuminate\Support\Facades\Validator;
use Webkul\GraphQLAPI\Validators\CustomException;

class NajazGraphql
{
    /**
     * Validate the incoming GraphQL arguments.
     */
    public function validate(array $args, array $rules): void
    {
        $validator = Validator::make($args, $rules);

        $this->checkValidatorFails($validator);
    }

    /**
     * Check if validator fails and throw formatted exception.
     */
    public function checkValidatorFails($validator): void
    {
        if (! $validator->fails()) {
            return;
        }

        $messages = [];

        foreach ($validator->messages()->toArray() as $field => $message) {
            $messages[] = is_array($message)
                ? "{$field}: ".current($message)
                : "{$field}: $message";
        }

        throw new CustomException(implode(', ', $messages));
    }

    /**
     * Authorize the authenticated citizen via the given guard.
     *
     * @throws CustomException
     */
    public function authorize(string $guard = 'citizen-api', ?string $token = null)
    {
        $authGuard = auth()->guard($guard);

        if ($token) {
            request()->headers->set('Authorization', 'Bearer '.$token);
            request()->merge(['token' => $token]);
        }

        if (! $authGuard->check()) {
            throw new CustomException(trans('najaz_graphql::app.citizens.auth.unauthenticated'));
        }

        $citizen = $authGuard->user();

        if (isset($citizen->status) && (int) $citizen->status !== 1) {
            $message = trans('najaz_graphql::app.citizens.auth.not-activated');
        }

        if (
            isset($citizen->is_verified)
            && (int) $citizen->is_verified !== 1
        ) {
            $message = trans('najaz_graphql::app.citizens.auth.verify-first');
        }

        if (isset($message)) {
            $authGuard->logout();

            throw new CustomException($message);
        }

        return $citizen;
    }
}

