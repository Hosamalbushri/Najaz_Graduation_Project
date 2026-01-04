<?php

return [
    'notifications' => [
        'service_request_created' => [
            'title' => 'New Request Created',
            'message' => 'A new request :increment_id has been created for service :service_name',
            'beneficiary_title' => 'You have been linked to a new request',
            'beneficiary_message' => 'You have been linked as a beneficiary to request :increment_id for service :service_name',
        ],
        'service_request_status_changed' => [
            'title' => 'Request Status Changed',
            'message' => 'Request :increment_id for service :service_name status changed to :status',
            'beneficiary_title' => 'Linked request status updated',
            'beneficiary_message' => 'The status of request :increment_id for service :service_name that you are linked to has changed to :status',
        ],
        'identity_verification_submitted' => [
            'title' => 'Identity Verification Submitted',
            'message' => 'Your identity verification request has been submitted and is under review',
        ],
        'identity_verification_status_changed' => [
            'title' => 'Identity Verification Status Changed',
            'message' => 'Your identity verification request status changed to :status',
        ],
    ],
    'statuses' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'rejected' => 'Rejected',
        'canceled' => 'Canceled',
        'cancelled' => 'Canceled',
        'approved' => 'Approved',
        'needs_more_info' => 'Needs More Info',
    ],
];

