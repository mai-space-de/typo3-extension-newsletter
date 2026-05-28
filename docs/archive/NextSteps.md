# Next Steps — EXT:mai_newsletter

Initial PHP skeleton landed on 2026-04-19.

---

## Scope

`mai_newsletter` is the **canonical subscriber store** for the whole project. Any extension that wants to collect an opt-in (`mai_account`, content elements, ad-hoc forms) delegates to `SubscriberService` here. Transport runs through `mai_mail::MailService`.

---

## Done

- `composer.json` `require` block (php, mai-base, mai-mail, cms-* packages).
- `ext_tables.sql` with `tx_mainewsletter_subscriber` (+unique `email_site` key) and `tx_mainewsletter_campaign`.
- `ext_localconf.php` registers the `Newsletter` plugin (subscribeForm, subscribe, confirm, unsubscribe).
- Site Set at `Configuration/Sets/MaiNewsletter/`.
- Domain: `Subscriber` (pending / subscribed / unsubscribed, token, confirmedAt, unsubscribedAt, site, feUser) and `Campaign` (draft / scheduled / sent, subject, body, scheduledAt, sentAt, recipientCount).
- Repositories: `SubscriberRepository::findByEmailAndSite()`, `findByToken()`, `findByStatus()`, `findSubscribed()`; `CampaignRepository::findDue()`.
- Services:
  - `SubscriberService::optIn()` — idempotent; refreshes token if subscriber exists but is not yet subscribed.
  - `SubscriberService::confirm(token)` / `unsubscribe(token)` — token rotates on confirm, cleared on unsubscribe.
  - `ConfirmationMailer::send(subscriber, confirmUrl, unsubscribeUrl)` — renders `Email/Confirm.html` via `StandaloneView` and enqueues via `MailService`.
- `NewsletterController` with `subscribeForm`, `subscribe`, `confirm`, `unsubscribe` actions (builds absolute URLs via `UriBuilder`, dispatches through `ConfirmationMailer`).
- Fluid templates for the plugin (subscribe form, confirm + unsubscribe result pages) and the confirmation email.
- Language files at `Resources/Private/Language/Default/locallang.xlf` + `locallang_tca.xlf`.
- Services are `public: true` so `mai_account` can pick up `SubscriberService` / `ConfirmationMailer` via `GeneralUtility::makeInstance()`.

---

## 1. Campaign dispatch

The backbone for campaign sending is not built yet. Need:

1. A `CampaignDispatcher` service that loads a `Campaign`, iterates subscribed subscribers, enqueues one mail per recipient via `MailService::queue()`, and sets `recipientCount` + `sentAt` + `status = sent`.
2. A scheduler task (or CLI command — `newsletter:dispatch`) wrapping the dispatcher.
3. TypoScript settings for the per-campaign "from" address (or fall back to `mai_mail`'s site defaults once those land).
4. Campaign Fluid template rendering so RTE `body` is wrapped in a proper email layout (header/footer). Share the email layout with `mai_account` / `mai_member` by extracting a partial into `mai_mail` once that layer exposes its theming hooks.

---

## 2. Backend module

No backend module yet. Add:

1. `Configuration/Backend/Modules.php` pointing at a `NewsletterBackendController` (extend `Maispace\MaiBase\Controller\Backend\AbstractBackendController`).
2. Subscriber list with CSV export via `BackendCsvExportTrait` (see `mai_team` for the pattern).
3. Campaign list with "Send now" and "Schedule" actions.
4. Pending-subscriber review (in case moderation is wanted for manual opt-ins).

---

## 3. One-click unsubscribe list-header

RFC 8058 `List-Unsubscribe` + `List-Unsubscribe-Post` headers. Currently only the in-body unsubscribe URL is used. `mai_mail::MailService::queue()` would need to accept header overrides; plan that extension once the mail service supports per-message headers.

---

## 4. `fe_user` linking

`Subscriber::$feUser` is stored but unused at query time. Consider:

- `SubscriberRepository::findByFeUser(int)` so `mai_account` can check subscription status without knowing the email.
- Auto-unsubscribe on fe_user deletion (hook into `DataHandler` or a dedicated command).

---

## 5. QA

```bash
composer lint:check
composer check:phpstan
composer test:unit
```

Priority targets for tests:

- `SubscriberService::optIn()` — new subscriber / pending re-issue / already-subscribed no-op.
- `SubscriberService::confirm()` — valid token, invalid token, already-confirmed token.
- `SubscriberService::unsubscribe()` — valid token, invalid token, token is cleared after.
