# Features — EXT:mai_newsletter

`mai_newsletter` is the **sole owner of the subscriber record** for the `www.bgm-pulheim.org`
TYPO3 project. All opt-in, confirmation, and unsubscribe operations across every extension must
delegate to `SubscriberService` here. Campaign dispatch and the confirmation email both use
`mai_mail::MailService` for transport.

---

## 1 · Subscriber Management

The subscriber table (`tx_mainewsletter_subscriber`) is the single source of truth for all
newsletter subscriptions. No other extension may write to this table directly — all mutations
go through `SubscriberService`.

### Subscriber Status Lifecycle

```
                     ┌─────────────────────────────┐
                     │           PENDING            │◄──────────────────────────┐
                     │  status = 'pending'          │                           │
                     │  token = <random 64-hex>     │                           │
                     └──────────────┬──────────────┘                           │
                                    │  SubscriberService::confirm($token)       │
                                    ▼                                           │
                     ┌─────────────────────────────┐                           │
                     │         SUBSCRIBED           │   SubscriberService::     │
                     │  status = 'subscribed'       │   optIn() called again    │
                     │  confirmedAt = now()         │──►on already-pending or   │
                     │  token = <rotated>           │   unsubscribed address    │
                     └──────────────┬──────────────┘                           │
                                    │  SubscriberService::unsubscribe($token)   │
                                    ▼                                           │
                     ┌─────────────────────────────┐                           │
                     │        UNSUBSCRIBED          │                           │
                     │  status = 'unsubscribed'     │                           │
                     │  unsubscribedAt = now()      │                           │
                     │  token = ''  (cleared)       │  optIn() re-opens the     │
                     └─────────────────────────────┘──►pending flow ────────────┘
```

| Status | Meaning |
|---|---|
| `pending` | Opt-in received; confirmation email sent; not yet confirmed |
| `subscribed` | Double opt-in confirmed; eligible for campaign dispatch |
| `unsubscribed` | Unsubscribe link clicked; no longer receives campaigns |

### Site Isolation

Each subscriber row carries a `site` column populated from the TYPO3 site identifier (e.g.
`bgm-pulheim`). The table has a unique index on `(email, site)`, so the same email address
can exist independently per site. `SubscriberService::optIn()` always passes the current
site identifier resolved via `SiteFinder`.

### FeUser Linkage

`Subscriber::$feUser` stores the `fe_users.uid` when the opt-in originates from an
authenticated frontend user (e.g. via the `mai_account` newsletter preferences page).
A value of `0` means the subscriber has no associated frontend user account.

---

## 2 · Double Opt-In Confirmation Flow

```
1. Visitor fills in the subscribe form
   → NewsletterController::subscribeAction()

2. SubscriberService::optIn($email, $site, $storagePid, $feUserUid = 0)
   - Normalises email: strtolower + trim
   - If subscriber exists and is already SUBSCRIBED → returns early (no-op)
   - Otherwise creates or resets a pending subscriber with a new token
   - Persists via SubscriberRepository

3. ConfirmationMailer::send($subscriber, $confirmUrl, $unsubscribeUrl)
   - Renders Email/Confirm.html with subscriber, confirmUrl, unsubscribeUrl
   - Enqueues via MailService::queue() → async dispatch by mai_mail

4. Visitor clicks confirmation link
   → NewsletterController::confirmAction($token)
   → SubscriberService::confirm($token)
      - Looks up subscriber by token (storage-page-independent query)
      - Returns null if token unknown or subscriber is not PENDING
      - Sets status = 'subscribed', confirmedAt = now(), rotates token
      - Persists

5. Visitor clicks unsubscribe link (from any email)
   → NewsletterController::unsubscribeAction($token)
   → SubscriberService::unsubscribe($token)
      - Looks up subscriber by token (storage-page-independent query)
      - Returns null if token unknown
      - Sets status = 'unsubscribed', unsubscribedAt = now(), clears token
      - Persists
```

---

## 3 · SubscriberService API

The canonical service for subscriber lifecycle management. Inject via constructor autowiring
or retrieve via `GeneralUtility::makeInstance()` when constructor injection is not available
(see integration guide in section 7).

### `optIn(string $email, string $site, int $storagePid, int $feUserUid = 0): Subscriber`

Creates or refreshes a pending subscriber record.

- Email is normalised to lowercase and trimmed before lookup.
- If a matching `(email, site)` record already exists and is `subscribed`, it is returned
  unchanged (idempotent — no duplicate confirmation email is sent).
- If the record exists but is `pending` or `unsubscribed`, it is reset to `pending` with a
  fresh token.
- Always returns the `Subscriber` entity; the caller decides whether to send a confirmation
  email by checking `$subscriber->isPending()`.

### `confirm(string $token): ?Subscriber`

Confirms a pending subscription.

- Returns `null` if the token is not found or the subscriber is not in `pending` status.
- On success: transitions to `subscribed`, sets `confirmedAt`, rotates the token.

### `unsubscribe(string $token): ?Subscriber`

Unsubscribes a confirmed subscriber.

- Returns `null` if the token is not found.
- On success: transitions to `unsubscribed`, sets `unsubscribedAt`, clears the token.

---

## 4 · SubscriberRepository Query API

| Method | Description |
|---|---|
| `findByEmailAndSite(string $email, string $site): ?Subscriber` | Unique lookup; storage-page-independent |
| `findByToken(string $token): ?Subscriber` | Token lookup; returns `null` for empty string |
| `findSubscribed(): QueryResultInterface` | All `status = 'subscribed'` rows (for campaign dispatch) |
| `findByStatus(string $status): QueryResultInterface` | Generic status filter |

All finders use `setRespectStoragePage(false)` so they work regardless of where the plugin
is placed in the page tree.

---

## 5 · Campaign Management

### Campaign Status Lifecycle

```
  DRAFT  ──► SCHEDULED ──► SENT
```

| Status | Meaning |
|---|---|
| `draft` | Being authored; not yet scheduled |
| `scheduled` | `scheduledAt` is set; ready for dispatch when the time arrives |
| `sent` | Dispatch complete; `sentAt` and `recipientCount` are populated |

### `CampaignRepository` Query API

| Method | Description |
|---|---|
| `findByStatus(string $status): QueryResultInterface` | Filter by status |
| `findDue(DateTimeImmutable $now): QueryResultInterface` | All `scheduled` campaigns with `scheduledAt ≤ now` |

`findDue()` is the entry point for the future campaign dispatcher (see `NextSteps.md`).

---

## 6 · Database Tables

### `tx_mainewsletter_subscriber`

| Column | Type | Description |
|---|---|---|
| `uid` | `int` | Auto-increment primary key |
| `pid` | `int` | Storage page UID |
| `hidden` | `tinyint` | Standard TYPO3 hidden flag |
| `deleted` | `tinyint` | Standard TYPO3 soft-delete flag |
| `tstamp` | `int` | Unix timestamp of last modification |
| `crdate` | `int` | Unix timestamp of record creation |
| `email` | `varchar(255)` | Email address (lowercase-normalised by `SubscriberService`) |
| `status` | `varchar(16)` | `pending` / `subscribed` / `unsubscribed` |
| `token` | `varchar(128)` | 64-character hex token; cleared after unsubscribe |
| `confirmed_at` | `int` | Unix timestamp of confirmation; `0` until confirmed |
| `unsubscribed_at` | `int` | Unix timestamp of unsubscribe; `0` until unsubscribed |
| `site` | `varchar(100)` | TYPO3 site identifier (e.g. `bgm-pulheim`) |
| `fe_user` | `int` | `fe_users.uid` of the linked account; `0` if none |

**Unique constraint:** `(email, site)` — one subscription per address per site.

**Indexes:** `pid`, `status`, `token`, `fe_user`.

### `tx_mainewsletter_campaign`

| Column | Type | Description |
|---|---|---|
| `uid` | `int` | Auto-increment primary key |
| `pid` | `int` | Storage page UID |
| `hidden` | `tinyint` | Standard TYPO3 hidden flag |
| `deleted` | `tinyint` | Standard TYPO3 soft-delete flag |
| `tstamp` | `int` | Unix timestamp of last modification |
| `crdate` | `int` | Unix timestamp of record creation |
| `sys_language_uid` | `int` | Language UID for localisation |
| `l10n_parent` | `int` | Translation parent UID |
| `title` | `varchar(255)` | Internal campaign name (backend only) |
| `subject` | `varchar(255)` | Email subject line sent to recipients |
| `body` | `mediumtext` | Full HTML body (or RTE-formatted source) |
| `status` | `varchar(16)` | `draft` / `scheduled` / `sent` |
| `scheduled_at` | `int` | Unix timestamp of intended dispatch; `0` for drafts |
| `sent_at` | `int` | Unix timestamp when dispatch completed; `0` until then |
| `recipient_count` | `int` | Number of recipients at send time |

---

## 7 · Integration Guide for Downstream Extensions

Extensions that need to subscribe a frontend user to the newsletter must:

1. Check whether `mai_newsletter` is installed before attempting any integration:
   ```php
   if (!ExtensionManagementUtility::isLoaded('mai_newsletter')) {
       return;
   }
   ```

2. Resolve `SubscriberService` and `ConfirmationMailer`. Two patterns are supported:

   **Constructor injection** (preferred for new extensions that declare `mai_newsletter` as a
   hard dependency in `composer.json`):
   ```php
   use Maispace\MaiNewsletter\Service\ConfirmationMailer;
   use Maispace\MaiNewsletter\Service\SubscriberService;

   final class MyService
   {
       public function __construct(
           private readonly SubscriberService $subscriberService,
           private readonly ConfirmationMailer $confirmationMailer,
       ) {}
   }
   ```

   **Late resolution via `GeneralUtility::makeInstance()`** (required for extensions that treat
   `mai_newsletter` as an optional dependency, as `mai_account` does):
   ```php
   $subscriberServiceClass = 'Maispace\\MaiNewsletter\\Service\\SubscriberService';
   $mailerClass = 'Maispace\\MaiNewsletter\\Service\\ConfirmationMailer';

   if (!class_exists($subscriberServiceClass) || !class_exists($mailerClass)) {
       return; // mai_newsletter not installed
   }

   $subscriberService = GeneralUtility::makeInstance($subscriberServiceClass);
   $mailer = GeneralUtility::makeInstance($mailerClass);
   ```
   Both classes are declared `public: true` in `Configuration/Services.yaml` so they are
   resolvable by the TYPO3 DI container even when retrieved this way.

3. Call `optIn()`, check the result, then send the confirmation email only when the subscriber
   is still pending:
   ```php
   $subscriber = $subscriberService->optIn(
       email:       $email,
       site:        $siteIdentifier,   // TYPO3 site identifier string
       storagePid:  $storagePid,       // PID for the subscriber record
       feUserUid:   $feUserUid,        // 0 if no account link
   );

   if (!$subscriber->isPending()) {
       // already subscribed — skip the confirmation email
       return;
   }

   $mailer->send($subscriber, $confirmUrl, $unsubscribeUrl);
   ```

4. The `confirmUrl` and `unsubscribeUrl` must be absolute URLs pointing to the
   `NewsletterController::confirmAction` and `NewsletterController::unsubscribeAction`
   endpoints respectively. Use `UriBuilder` with `setCreateAbsoluteUri(true)`.

---

## 8 · Architecture Constraints

- `mai_newsletter` is the **sole owner** of `tx_mainewsletter_subscriber`. No other extension
  may read from or write to this table without going through `SubscriberService`.
- `mai_newsletter` delegates all email transport to `mai_mail` — it does not declare a
  `symfony/mailer` dependency and does not send email directly.
- Marketing / bulk email is the responsibility of `mai_newsletter`. Transactional email
  (account confirmations, reminders, application notifications) belongs to `mai_mail`.
- Extensions that store a newsletter opt-in preference in their own table (e.g.
  `fe_users.tx_maiaccount_newsletter_optin`) must still delegate the actual subscription to
  `SubscriberService` — the local flag is UI state only, not the canonical subscription record.
