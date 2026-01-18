<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RazorpayService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private RazorpayService $razorpayService
    ) {}

    public function razorpay(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Razorpay-Signature');

        if (!$signature) {
            return response()->json([
                'success' => false,
                'message' => 'Missing signature.',
            ], 400);
        }

        // Verify webhook signature
        $isValid = $this->razorpayService->verifyWebhookSignature(
            $request->getContent(),
            $signature
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 400);
        }

        // Handle webhook
        $this->razorpayService->handleWebhook($payload);

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed successfully.',
        ]);
    }
}
