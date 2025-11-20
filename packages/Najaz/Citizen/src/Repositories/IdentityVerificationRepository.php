<?php

namespace Najaz\Citizen\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Webkul\Core\Eloquent\Repository;
use Webkul\GraphQLAPI\Validators\CustomException;

class IdentityVerificationRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Citizen\Contracts\IdentityVerification';
    }

    /**
     * Update identity verification status.
     *
     * @param  \Najaz\Citizen\Models\IdentityVerification|int  $verificationOrId
     * @param  string|null  $status
     * @return void
     */
    public function updateStatus($verificationOrId, $status = null)
    {
        $verification = $this->resolveVerificationInstance($verificationOrId);

        Event::dispatch('identity.verification.update-status.before', $verification);

        if (! empty($status)) {
            $verification->status = $status;
        }

        $verification->save();

        Event::dispatch('identity.verification.update-status.after', $verification);
    }

    /**
     * Update identity verification status with full validation and business logic.
     *
     * @param  array  $data
     * @param  int  $id
     * @return \Najaz\Citizen\Models\IdentityVerification
     *
     * @throws CustomException
     */
    public function updateVerificationStatus(array $data, int $id): \Najaz\Citizen\Models\IdentityVerification
    {
        DB::beginTransaction();

        try {
            $verification = $this->findOrFail($id);

            Event::dispatch('identity.verification.update-status.before', [$id, $data]);

            // If status is being changed to approved/rejected/needs_more_info, set reviewer info
            if (isset($data['status']) && in_array($data['status'], ['approved', 'rejected', 'needs_more_info'])) {
                $data['reviewed_by'] = Auth::guard('admin')->id();
                $data['reviewed_at'] = now();
            }

            // If approved, update citizen's identity_verification_status
            if (isset($data['status']) && $data['status'] == 'approved') {
                $citizen = $verification->citizen;
                if ($citizen) {
                    $citizen->identity_verification_status = 1;
                    $citizen->save();
                }
            }

            // If status changed from approved to something else, reset citizen's identity_verification_status
            if (isset($data['status']) && $verification->status == 'approved' && $data['status'] != 'approved') {
                $citizen = $verification->citizen;
                if ($citizen) {
                    $citizen->identity_verification_status = 0;
                    $citizen->save();
                }
            }

            $verification = $this->update($data, $id);

            Event::dispatch('identity.verification.update-status.after', $verification);
        } catch (\Exception $e) {
            /* rolling back first */
            DB::rollBack();

            /* storing log for errors */
            Log::error(
                'IdentityVerificationRepository:updateVerificationStatus: '.$e->getMessage(),
                ['id' => $id, 'data' => $data]
            );

            /* throwing custom exception */
            throw new CustomException(
                trans('najaz_graphql::app.citizens.identity_verification.update_error', [
                    'message' => $e->getMessage(),
                ])
            );
        } finally {
            /* commit in each case */
            DB::commit();
        }

        return $verification->fresh(['citizen', 'reviewer']);
    }

    /**
     * Resolve verification instance.
     *
     * @param  \Najaz\Citizen\Models\IdentityVerification|int  $verificationOrId
     * @return \Najaz\Citizen\Models\IdentityVerification
     */
    protected function resolveVerificationInstance($verificationOrId)
    {
        if ($verificationOrId instanceof \Najaz\Citizen\Models\IdentityVerification) {
            return $verificationOrId;
        }

        return $this->findOrFail($verificationOrId);
    }
}




















