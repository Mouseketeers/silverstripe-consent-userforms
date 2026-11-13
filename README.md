# silverstripe-consent-userforms

SilverStripe UserForms integration for
[mouseketeers/silverstripe-consent-forms](https://github.com/Mouseketeers/silverstripe-consent-forms).

This add-on provides the CMS-editable consent fields and the automatic consent
recording for [silverstripe/userforms](https://github.com/silverstripe/silverstripe-userforms).

## Supported SilverStripe versions

| Branch        | SilverStripe | Install                                                           |
|---------------|--------------|-------------------------------------------------------------------|
| `master`      | 6            | `composer require mouseketeers/silverstripe-consent-userforms:^4` |
| `support/3.x` | 5.4          | `composer require mouseketeers/silverstripe-consent-userforms:^3` |
| `support/2.x` | 4            | `composer require mouseketeers/silverstripe-consent-userforms:^2` |
| `support/1.x` | 3            | `composer require mouseketeers/silverstripe-consent-userforms:^1` |

## Requirements

| Version | SilverStripe | silverstripe/userforms | mouseketeers/silverstripe-consent-forms | PHP   |
|---------|--------------|------------------------|-----------------------------------------|-------|
| 4.x     | ^6           | ^7                     | ^5                                      | ^8.3  |
| 3.x     | ^5.4         | ^6                     | ^4                                      | ^8.1  |
| 2.x     | ^4           | ^5                     | ^3                                      |       |
| 1.x     | ^3           | >=2 <5                 | ^1.1                                    |       |

## What it provides

- `EditableConsentCheckbox` — a checkbox consent field for UserDefinedForms
- `EditableTermsAndPrivacyConsentCheckbox` — terms & privacy consent field
- `EditablePrivacyNoticeField` — informational privacy notice
- `ConsentFormExtension` — records a `ConsentRecord` for every checked consent
  field after a UserDefinedForm is submitted

The core `mouseketeers/silverstripe-consent-forms` package provides the
reusable form fields (`ConsentCheckboxField`, `TermsAndPrivacyConsentCheckboxField`,
`PrivacyNoticeField`) and `ConsentRecorder`, which can be used from any
SilverStripe form without UserForms.

## Upgrading to 4.x

The classes moved from `Mouseketeers\ConsentForms` to `Mouseketeers\ConsentUserForms`, so this package no
longer shares a namespace with consent-forms. Run `dev/build`: the old class names stored on existing form
fields are remapped automatically. Update any of your own code, config or templates that name these classes.

## Retention

When consent-records purges records (`sake tasks:consent-retention`, or its scheduled job), the UserForms
submission each record points to is deleted too, with its field values. That also makes
`--consent-id=jo@example.com` remove Jo's submission, not just the consent record.

- Uploaded files are deleted with their submission, as UserForms does itself. To keep them, set
  `Mouseketeers\ConsentUserForms\ConsentRetentionExtension: delete_uploaded_files: false`; they then stay in
  assets, linked to nothing, and you remove them yourself.
- Only submissions linked to a consent record are reached. A form with no consent field leaves no record,
  so its submissions need their own cleanup.
- A submission shared by several consent boxes is deleted once; one that is already gone is skipped.

## Consent field settings

- **Consent ID Field** (required): the form field, usually the email, that identifies who gave the consent.
- **Consent type**: stored with each record. Defaults to a slug of the field title.
- **Optional**: lets the visitor leave the box unticked. Fields are mandatory by default.

Only the consent itself is stored in the record. To also copy the whole submission into every record, set
`Mouseketeers\ConsentForms\ConsentRecorder.store_form_data: true`.
