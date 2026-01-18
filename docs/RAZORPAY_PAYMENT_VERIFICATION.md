# Razorpay Payment Verification - Frontend Implementation Guide

## Problem
The frontend is sending empty strings for `payment_id` and `signature` when calling `/api/subscription/verify-payment`, causing validation errors.

## Root Cause
The frontend is not properly extracting payment data from Razorpay's payment success response.

## Solution

### For Flutter/Dart Applications

When Razorpay payment is successful, you need to extract the payment details from the response and send them to the backend.

#### Step 1: Handle Razorpay Payment Success

```dart
// After successful Razorpay payment
void _handlePaymentSuccess(PaymentSuccessResponse response) {
  // ✅ CORRECT - Extract values from Razorpay response
  final paymentData = {
    'payment_id': response.paymentId,           // NOT empty!
    'order_id': response.orderId ?? response.subscriptionId,  // Can be order_id or subscription_id
    'signature': response.signature,            // NOT empty!
  };
  
  // Send to backend
  _verifyPayment(paymentData);
}
```

#### Step 2: Verify Payment with Backend

```dart
Future<void> _verifyPayment(Map<String, String> paymentData) async {
  try {
    final response = await http.post(
      Uri.parse('https://yujix.com/api/subscription/verify-payment'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $yourAuthToken',
      },
      body: jsonEncode(paymentData),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['success'] == true) {
        // Payment verified successfully
        print('Payment verified: ${data['data']}');
      } else {
        // Handle error
        print('Error: ${data['message']}');
      }
    } else {
      // Handle HTTP error
      final error = jsonDecode(response.body);
      print('Error: ${error['message']}');
      if (error['errors'] != null) {
        print('Validation errors: ${error['errors']}');
      }
    }
  } catch (e) {
    print('Exception: $e');
  }
}
```

### For JavaScript/Web Applications

#### Step 1: Handle Razorpay Payment Success

```javascript
// After successful Razorpay payment
razorpay.on('payment.success', function(response) {
  // ✅ CORRECT - Extract values from Razorpay response
  const paymentData = {
    payment_id: response.razorpay_payment_id,    // NOT empty!
    order_id: response.razorpay_order_id || response.razorpay_subscription_id,
    signature: response.razorpay_signature       // NOT empty!
  };
  
  // Send to backend
  verifyPayment(paymentData);
});
```

#### Step 2: Verify Payment with Backend

```javascript
async function verifyPayment(paymentData) {
  try {
    const response = await fetch('https://yujix.com/api/subscription/verify-payment', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${yourAuthToken}`,
      },
      body: JSON.stringify(paymentData),
    });
    
    const data = await response.json();
    
    if (data.success) {
      console.log('Payment verified:', data.data);
    } else {
      console.error('Error:', data.message);
      if (data.errors) {
        console.error('Validation errors:', data.errors);
      }
    }
  } catch (error) {
    console.error('Exception:', error);
  }
}
```

## Common Mistakes to Avoid

### ❌ WRONG - Sending Empty Strings

```dart
// ❌ WRONG
final paymentData = {
  'payment_id': '',      // Empty!
  'order_id': 'sub_xxx',
  'signature': '',       // Empty!
};
```

### ❌ WRONG - Using Wrong Property Names

```dart
// ❌ WRONG
final paymentData = {
  'payment_id': response.paymentId,  // Wrong - should be response.paymentId
  'signature': response.sig,           // Wrong property name
};
```

### ❌ WRONG - Not Extracting from Response

```dart
// ❌ WRONG - Calling before payment completes
_verifyPayment({
  'payment_id': '',
  'signature': '',
});
```

## Required Fields

The backend expects these fields (all required, all must be non-empty strings):

```json
{
  "payment_id": "pay_xxxxxxxxxxxxx",  // From Razorpay response
  "order_id": "sub_xxxxxxxxxxxxx",    // From Razorpay response (or order_xxx)
  "signature": "xxxxxxxxxxxxxxxxxxxx"  // From Razorpay response
}
```

## Razorpay Response Structure

When payment is successful, Razorpay returns:

```json
{
  "razorpay_payment_id": "pay_xxxxxxxxxxxxx",
  "razorpay_order_id": "order_xxxxxxxxxxxxx",      // OR
  "razorpay_subscription_id": "sub_xxxxxxxxxxxxx", // For subscriptions
  "razorpay_signature": "xxxxxxxxxxxxxxxxxxxx"
}
```

## Backend Error Response

If validation fails, the backend now returns detailed error information:

```json
{
  "success": false,
  "message": "Invalid payment data provided",
  "errors": {
    "payment_id": ["The payment id field is required."],
    "signature": ["The signature field is required."]
  },
  "data": null
}
```

## Testing

1. Make a test payment with Razorpay
2. Check the payment success response contains all required fields
3. Extract the fields correctly
4. Send to `/api/subscription/verify-payment`
5. Verify the response is successful

## Debugging Tips

1. **Log the Razorpay response** to see what fields are available:
   ```dart
   print('Razorpay response: ${response.toString()}');
   ```

2. **Check the request payload** before sending:
   ```dart
   print('Sending payment data: $paymentData');
   ```

3. **Check backend error response** for validation details:
   ```dart
   if (error['errors'] != null) {
     print('Validation errors: ${error['errors']}');
   }
   ```

## Example: Complete Flutter Implementation

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class PaymentService {
  final String baseUrl = 'https://yujix.com/api';
  final String authToken;
  
  PaymentService(this.authToken);
  
  Future<Map<String, dynamic>> verifyPayment({
    required String paymentId,
    required String orderId,
    required String signature,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/subscription/verify-payment'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $authToken',
        },
        body: jsonEncode({
          'payment_id': paymentId,
          'order_id': orderId,
          'signature': signature,
        }),
      );
      
      final data = jsonDecode(response.body);
      
      if (response.statusCode == 200 && data['success'] == true) {
        return {'success': true, 'data': data['data']};
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Payment verification failed',
          'errors': data['errors'],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Exception: $e',
      };
    }
  }
  
  // Usage in Razorpay success handler
  void handleRazorpaySuccess(PaymentSuccessResponse response) {
    final paymentService = PaymentService(yourAuthToken);
    
    paymentService.verifyPayment(
      paymentId: response.paymentId ?? '',
      orderId: response.orderId ?? response.subscriptionId ?? '',
      signature: response.signature ?? '',
    ).then((result) {
      if (result['success']) {
        // Payment verified successfully
        print('Payment verified: ${result['data']}');
      } else {
        // Handle error
        print('Error: ${result['message']}');
        if (result['errors'] != null) {
          print('Validation errors: ${result['errors']}');
        }
      }
    });
  }
}
```

## Summary

- ✅ Extract `payment_id`, `order_id`, and `signature` from Razorpay success response
- ✅ Send all three fields as non-empty strings
- ✅ Use correct property names from Razorpay response
- ✅ Handle errors and show validation messages to users
- ❌ Don't send empty strings
- ❌ Don't use wrong property names
- ❌ Don't call verify-payment before payment completes

