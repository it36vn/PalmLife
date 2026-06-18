# Legal and Product Guardrails

This product is intentionally positioned as entertainment and self-reflection, not fortune-telling as a professional service.

## Vietnam-sensitive design choices

- Avoid claims that the app can predict the future with certainty.
- Avoid paid fear-based flows such as bad luck removal, rituals, offerings, curses, exorcism, health cures, or disaster warnings.
- Never present readings as medical, legal, financial, employment, credit, relationship, or safety advice.
- Require explicit acknowledgement before AI analysis.
- Store uploaded palm images transiently only; by default the API keeps a hash and generated result, not the original image.
- Provide account deletion and delete related app data.
- Keep marketing consent separate from core service consent.
- In mobile apps, sell digital subscriptions through Apple App Store In-App Purchase and Google Play Billing; do not route mobile users to off-store payment for app entitlements.

## Sources checked on 2026-06-07

- Vietnam Decree 38/2021/ND-CP includes penalties for organizing superstitious activities.
- Vietnam Penal Code Article 320 addresses practicing superstition after prior administrative sanction or conviction.
- Vietnam Decree 13/2023/ND-CP requires consent-oriented personal-data processing and recognizes data deletion rights with exceptions.

Ask Vietnamese counsel to review production copy, payment terms, privacy policy, AI provider data transfer, and app-store subscription disclosures before launch.

## Store subscription setup

Use these product identifiers in App Store Connect and Google Play Console:

- Free: `com.it36vn.xemchitay1`
- Standard: `com.it36vn.xemchitay2`
- Advanced: `com.it36vn.xemchitay3`
- VIP: `com.it36vn.xemchitay4`
- Lifetime: `com.it36vn.xemchitay5`

The lifetime product should be configured as a non-consumable entitlement. The weekly/monthly products should be subscriptions with clear renewal terms, restore support, and app review notes.
