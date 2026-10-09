# API integration configuration

## Password reset SMS gateway

The password-reset API uses the generic JSON SMS endpoint configured in config/services.php.

Set these environment variables in the deployment environment:

- SMS_GATEWAY_URL: HTTPS endpoint that accepts an SMS send request.
- SMS_GATEWAY_TOKEN: bearer token used in the Authorization header.
- SMS_GATEWAY_SENDER_ID: optional sender ID supplied to the provider.

The application sends a JSON POST body with these fields:

```json
{
  "to": "recipient phone number",
  "message": "Your password reset code is 123456. It expires in 10 minutes.",
  "sender": "GAMEAPP"
}
```

The provider must return a successful HTTP status. If it returns JSON with success=false or status=false, delivery is treated as failed. The application stores only a password hash of the OTP, expires it after 10 minutes, rate-limits requests per phone number, and removes the OTP record if delivery fails.

This is a generic adapter, not a provider-specific integration. Confirm that the selected SMS provider accepts this request shape before enabling the password-reset endpoint in production. If no endpoint/token is configured, the API returns HTTP 503 instead of pretending an OTP was sent.

## Wallet payment gateway return

Configure the payment provider's server/browser return endpoint with `UPI_GATEWAY_RETURN_URL`, pointing to the public `/api/v1/wallet/gateway/return` route. The endpoint verifies the transaction with the provider before trusting its status.

Set `UPI_GATEWAY_FRONTEND_RETURN_URL` to the deployed wallet-add page if it differs from `APP_URL/wallet/add`. Browser redirects return to that page with `gateway_return=1`, `status`, and `request_id` query parameters; API clients that request JSON continue to receive the JSON response. The app then refreshes the request status and wallet balance.

