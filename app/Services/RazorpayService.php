<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    public Api $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    public function createSubscription(Organization $organization, int $planId, string $billingCycle)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($planId);

            // Create or get Razorpay customer
            $customer = $this->getOrCreateCustomer($organization);

            // Create Razorpay plan if not exists
            $razorpayPlanId = $this->getOrCreateRazorpayPlan($plan, $billingCycle);

            // Create subscription
            return $this->razorpay->subscription->create([
                'plan_id' => $razorpayPlanId,
                'customer_id' => $customer->id,
                'total_count' => $billingCycle === 'yearly' ? 1 : 12,
                'quantity' => 1,
                'customer_notify' => 1,
            ]);
        } catch (\Razorpay\Api\Errors\Error $e) {
            Log::error('Razorpay API Error in createSubscription', [
                'organization_id' => $organization->id,
                'plan_id' => $planId,
                'billing_cycle' => $billingCycle,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error in createSubscription', [
                'organization_id' => $organization->id,
                'plan_id' => $planId,
                'billing_cycle' => $billingCycle,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function handleWebhook(array $payload): void
    {
        $event = $payload['event'] ?? null;

        if (!$event) {
            return;
        }

        switch ($event) {
            case 'subscription.activated':
                $this->handleSubscriptionActivated($payload);
                break;
            case 'subscription.charged':
                $this->handleSubscriptionCharged($payload);
                break;
            case 'subscription.cancelled':
                $this->handleSubscriptionCancelled($payload);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($payload);
                break;
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $this->razorpay->utility->verifyWebhookSignature(
                $payload,
                $signature,
                config('services.razorpay.webhook_secret')
            );
            return true;
        } catch (SignatureVerificationError $e) {
            return false;
        }
    }

    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ];

            $this->razorpay->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (SignatureVerificationError $e) {
            return false;
        }
    }

    private function getOrCreateCustomer(Organization $organization)
    {
        if ($organization->razorpay_customer_id) {
            try {
                return $this->razorpay->customer->fetch($organization->razorpay_customer_id);
            } catch (\Exception $e) {
                // Customer not found, create new one
            }
        }

        // Get email - required by Razorpay
        $email = $organization->email;
        if (!$email) {
            $firstUser = $organization->users()->first();
            $email = $firstUser?->email;
        }

        if (!$email) {
            throw new \Exception('Email is required to create a Razorpay customer. Please ensure the organization or user has an email address.');
        }

        // Build customer data
        $customerData = [
            'name' => $organization->name ?? 'Customer',
            'email' => $email,
        ];

        // Contact (phone) is optional in Razorpay, but include if available
        if ($organization->phone) {
            $customerData['contact'] = $organization->phone;
        }

        $customer = $this->razorpay->customer->create($customerData);

        $organization->update(['razorpay_customer_id' => $customer->id]);

        return $customer;
    }

    private function getOrCreateRazorpayPlan(SubscriptionPlan $plan, string $billingCycle): string
    {
        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $period = $billingCycle === 'yearly' ? 'yearly' : 'monthly';

        // Create a unique plan ID based on plan name and billing cycle
        $planId = "plan_{$plan->name}_{$billingCycle}";

        try {
            // Try to fetch existing plan
            $razorpayPlan = $this->razorpay->plan->fetch($planId);
            return $razorpayPlan->id;
        } catch (\Exception $e) {
            // Plan doesn't exist, create it
            $razorpayPlan = $this->razorpay->plan->create([
                'period' => $period,
                'interval' => 1,
                'item' => [
                    'name' => $plan->display_name . ' (' . ucfirst($billingCycle) . ')',
                    'amount' => $price,
                    'currency' => 'INR',
                ],
            ]);

            return $razorpayPlan->id;
        }
    }

    private function handleSubscriptionActivated(array $payload): void
    {
        $subscriptionData = $payload['payload']['subscription']['entity'] ?? null;
        
        if (!$subscriptionData) {
            return;
        }

        $razorpaySubscriptionId = $subscriptionData['id'] ?? null;
        $razorpayCustomerId = $subscriptionData['customer_id'] ?? null;

        if (!$razorpaySubscriptionId || !$razorpayCustomerId) {
            return;
        }

        // Find organization by razorpay_customer_id
        $organization = Organization::where('razorpay_customer_id', $razorpayCustomerId)->first();

        if (!$organization) {
            return;
        }

        // Find or create subscription
        $subscription = \App\Models\Subscription::where('razorpay_subscription_id', $razorpaySubscriptionId)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
            ]);
        }
    }

    private function handleSubscriptionCharged(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? null;
        $subscriptionData = $payload['payload']['subscription']['entity'] ?? null;

        if (!$paymentData || !$subscriptionData) {
            return;
        }

        $razorpaySubscriptionId = $subscriptionData['id'] ?? null;
        $razorpayPaymentId = $paymentData['id'] ?? null;
        $amount = $paymentData['amount'] ?? 0;

        if (!$razorpaySubscriptionId || !$razorpayPaymentId) {
            return;
        }

        // Find subscription
        $subscription = \App\Models\Subscription::where('razorpay_subscription_id', $razorpaySubscriptionId)
            ->first();

        if (!$subscription) {
            return;
        }

        // Create transaction record
        \App\Models\Transaction::create([
            'organization_id' => $subscription->organization_id,
            'subscription_id' => $subscription->id,
            'razorpay_payment_id' => $razorpayPaymentId,
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'completed',
            'type' => 'subscription',
            'description' => 'Subscription payment',
        ]);
    }

    private function handleSubscriptionCancelled(array $payload): void
    {
        $subscriptionData = $payload['payload']['subscription']['entity'] ?? null;

        if (!$subscriptionData) {
            return;
        }

        $razorpaySubscriptionId = $subscriptionData['id'] ?? null;

        if (!$razorpaySubscriptionId) {
            return;
        }

        // Find subscription
        $subscription = \App\Models\Subscription::where('razorpay_subscription_id', $razorpaySubscriptionId)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }
    }

    private function handlePaymentFailed(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? null;

        if (!$paymentData) {
            return;
        }

        $razorpayPaymentId = $paymentData['id'] ?? null;
        $amount = $paymentData['amount'] ?? 0;

        if (!$razorpayPaymentId) {
            return;
        }

        // Find subscription by payment
        $subscription = \App\Models\Subscription::whereHas('transactions', function ($query) use ($razorpayPaymentId) {
            $query->where('razorpay_payment_id', $razorpayPaymentId);
        })->first();

        if ($subscription) {
            // Update subscription status
            $subscription->update([
                'status' => 'past_due',
            ]);

            // Create failed transaction record
            \App\Models\Transaction::create([
                'organization_id' => $subscription->organization_id,
                'subscription_id' => $subscription->id,
                'razorpay_payment_id' => $razorpayPaymentId,
                'amount' => $amount,
                'currency' => 'INR',
                'status' => 'failed',
                'type' => 'subscription',
                'description' => 'Payment failed',
            ]);
        }
    }
}

