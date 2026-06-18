# Xem Chỉ Tay

Flutter mobile app plus Laravel web/API for an entertainment-only palm reading, astrology, numerology, and subscription product for Vietnam.

## Structure

- `backend`: Laravel website and JSON API.
- `mobile`: Flutter iOS/Android app using a clean `core` and `features/app/{data,domain,presentation}` structure.

## Run Backend

```bash
cd backend
php artisan migrate --force
php artisan db:seed --force
php artisan serve
```

Web: `http://127.0.0.1:8000`

API base: `http://127.0.0.1:8000/api`

## Run Mobile

```bash
cd mobile
flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api
```

For Android emulator, use:

```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
```

## Subscription Plans

- Free: 0 VND, 5 lifetime readings, default.
- Standard: 29,000 VND, 5 readings per month.
- Advanced: 59,000 VND, 5 readings per week.
- VIP: 990,000 VND, 15 readings per week.
- Lifetime: 199,000 VND, unlimited readings.

Mobile subscriptions are purchased through the platform stores:

- iOS: App Store In-App Purchase / StoreKit.
- Android: Google Play Billing.

Create these store products, mapped to the defined plans:

- Free: `com.it36vn.xemchitay1`
- Standard: `com.it36vn.xemchitay2`
- Advanced: `com.it36vn.xemchitay3`
- VIP: `com.it36vn.xemchitay4`
- Lifetime: `com.it36vn.xemchitay5`

The Laravel endpoint `POST /api/subscriptions/store/verify` receives the App Store receipt / Google Play purchase token from Flutter and activates the entitlement only after server-side verification. Local demo activation is disabled unless you explicitly set `STORE_VERIFICATION_MODE=local_accept` in `backend/.env`.

Store server notification endpoints:

- Apple App Store Server Notifications V2: `POST /api/store/webhooks/apple`
- Google Play RTDN Pub/Sub push: `POST /api/store/webhooks/google`

When a store notification reports a successful purchase/renewal, Laravel maps the transaction or purchase token to `store_purchases`, activates the plan, creates a user notification, and `/api/me` returns the refreshed quota plus unread notifications. Cancellation, revoke, expired, and account-hold events deactivate the store plan and fall back to the free plan.

Before production, configure:

```env
STORE_VERIFICATION_MODE=strict
APPLE_IAP_ISSUER_ID=
APPLE_IAP_KEY_ID=
APPLE_IAP_PRIVATE_KEY_PATH=
GOOGLE_PLAY_PACKAGE_NAME=com.xemchitay.mobile
GOOGLE_PLAY_SERVICE_ACCOUNT_JSON=
```

iOS also needs the In-App Purchase capability enabled in Xcode. Google Play needs the products/base plans configured and the app signed/uploaded to an internal testing track before Billing returns real products.
