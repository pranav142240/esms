<?php

namespace App\Interfaces\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolSetupRequest;
use App\Application\Services\Admin\AdminTenantConversionService;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class SchoolSetupController extends Controller
{
    protected AdminTenantConversionService $conversionService;

    public function __construct(AdminTenantConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Get school setup form structure
     */
    public function getSetupForm(): JsonResponse
    {
        try {
            $admin = Auth::guard('admin')->user();

            // Check if admin can create school
            if (!in_array($admin->status, ['active', 'pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not eligible to create a school at this time.',
                    'error' => 'INVALID_ADMIN_STATUS'
                ], 403);
            }

            if ($admin->status === 'converted') {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already created a school. Please login to your school portal.',
                    'error' => 'ADMIN_ALREADY_CONVERTED'
                ], 403);
            }

            $formStructure = [
                'school_information' => [
                    'title' => 'School Information',
                    'fields' => [
                        [
                            'name' => 'school_name',
                            'label' => 'School Name',
                            'type' => 'text',
                            'required' => true,
                            'placeholder' => 'Enter your school name'
                        ],
                        [
                            'name' => 'school_email',
                            'label' => 'School Email',
                            'type' => 'email',
                            'required' => true,
                            'placeholder' => 'school@example.com'
                        ],
                        [
                            'name' => 'school_phone',
                            'label' => 'School Phone',
                            'type' => 'tel',
                            'required' => false,
                            'placeholder' => '+1234567890'
                        ],
                        [
                            'name' => 'school_address',
                            'label' => 'School Address',
                            'type' => 'textarea',
                            'required' => false,
                            'placeholder' => 'Enter full school address'
                        ]
                    ]
                ],
                'domain_settings' => [
                    'title' => 'Domain Configuration',
                    'fields' => [
                        [
                            'name' => 'preferred_domain',
                            'label' => 'Preferred Domain Name',
                            'type' => 'text',
                            'required' => false,
                            'placeholder' => 'my-school (will become my-school.localhost)',
                            'help' => 'Leave blank to auto-generate from school name'
                        ]
                    ]
                ],
                'subscription_settings' => [
                    'title' => 'Subscription Plan',
                    'fields' => [
                        [
                            'name' => 'subscription_plan',
                            'label' => 'Plan',
                            'type' => 'select',
                            'required' => true,
                            'options' => [
                                ['value' => 'basic', 'label' => 'Basic Plan (Free Trial)'],
                                ['value' => 'standard', 'label' => 'Standard Plan'],
                                ['value' => 'premium', 'label' => 'Premium Plan']
                            ],
                            'default' => 'basic'
                        ]
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'form_structure' => $formStructure,
                    'admin' => [
                        'name' => $admin->name,
                        'email' => $admin->email,
                        'status' => $admin->status
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load setup form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit school creation request
     */
    public function createSchool(SchoolSetupRequest $request): JsonResponse
    {
        try {
            $admin = Auth::guard('admin')->user();

            // Validate admin status
            if (!$this->conversionService->validateConversionRequirements($admin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin does not meet requirements for school creation',
                    'error' => 'CONVERSION_REQUIREMENTS_NOT_MET'
                ], 400);
            }

            $schoolData = $request->validated();

            // Convert admin to tenant
            $result = $this->conversionService->convertAdminToTenant($admin, $schoolData);

            return response()->json([
                'success' => true,
                'message' => 'School created successfully! You are now being redirected to your school portal.',
                'data' => [
                    'tenant_domain' => $result['tenant']['domain'],
                    'school_url' => "http://{$result['tenant']['domain']}.localhost",
                    'conversion_id' => $result['conversion_id'],
                    'tenant_user_id' => $result['tenant_user']->id,
                    'redirect_url' => "http://{$result['tenant']['domain']}.localhost/api/v1/auth/auto-login?token=" . $result['tenant_user']->createToken('school-owner')->plainTextToken
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'School creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check school setup progress
     */
    public function getSetupStatus(): JsonResponse
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $status = $this->conversionService->getConversionStatus($admin);

            return response()->json([
                'success' => true,
                'data' => [
                    'admin_status' => $admin->status,
                    'conversion_status' => $status,
                    'can_create_school' => in_array($admin->status, ['active', 'pending']),
                    'is_converted' => $admin->status === 'converted',
                    'tenant_domain' => $admin->status === 'converted' ? $status['tenant_domain'] : null
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get setup status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel school setup (if in progress)
     */
    public function cancelSetup(): JsonResponse
    {
        try {
            $admin = Auth::guard('admin')->user();

            if ($admin->status !== 'setting_up') {
                return response()->json([
                    'success' => false,
                    'message' => 'No setup process to cancel',
                    'error' => 'NO_SETUP_IN_PROGRESS'
                ], 400);
            }

            // Find the latest conversion attempt
            $conversion = \App\Models\AdminTenantConversion::where('admin_id', $admin->id)
                ->where('conversion_status', 'initiated')
                ->latest()
                ->first();

            if ($conversion) {
                // Rollback the conversion
                $rollbackSuccess = $this->conversionService->rollbackConversion($conversion->id);
                
                if ($rollbackSuccess) {
                    return response()->json([
                        'success' => true,
                        'message' => 'School setup cancelled successfully'
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel school setup',
                'error' => 'ROLLBACK_FAILED'
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel setup',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry failed school setup
     */
    public function retrySetup(SchoolSetupRequest $request): JsonResponse
    {
        try {
            $admin = Auth::guard('admin')->user();

            // Check if there's a failed conversion to retry
            $failedConversion = \App\Models\AdminTenantConversion::where('admin_id', $admin->id)
                ->where('conversion_status', 'failed')
                ->latest()
                ->first();

            if (!$failedConversion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No failed setup to retry',
                    'error' => 'NO_FAILED_SETUP'
                ], 400);
            }

            // Reset admin status for retry
            $admin->update(['status' => 'active']);

            // Attempt conversion again
            $schoolData = $request->validated();
            $result = $this->conversionService->convertAdminToTenant($admin, $schoolData);

            return response()->json([
                'success' => true,
                'message' => 'School setup retry successful!',
                'data' => [
                    'tenant_domain' => $result['tenant']['domain'],
                    'school_url' => "http://{$result['tenant']['domain']}.localhost",
                    'conversion_id' => $result['conversion_id']
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Setup retry failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
