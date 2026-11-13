# silverstripe-consent-userforms

SilverStripe UserForms integration for
[mouseketeers/silverstripe-consent-forms](https://github.com/Mouseketeers/silverstripe-consent-forms).

This add-on provides the CMS-editable consent fields and the automatic consent
recording for [silverstripe/userforms](https://github.com/silverstripe/silverstripe-userforms).

## Supported SilverStripe versions

| Branch        | SilverStripe | Install                                                           |
|---------------|--------------|-------------------------------------------------------------------|
| `master`      | 5.4 and 6    | `composer require mouseketeers/silverstripe-consent-userforms:^3` |
| `support/2.x` | 4            | `composer require mouseketeers/silverstripe-consent-userforms:^2` |
| `support/1.x` | 3            | `composer require mouseketeers/silverstripe-consent-userforms:^1` |

## Requirements

| Version | SilverStripe | silverstripe/userforms | mouseketeers/silverstripe-consent-forms | PHP   |
|---------|--------------|------------------------|-----------------------------------------|-------|
| 3.x     | ^5.4 or ^6   | ^6 or ^7               | ^4 or ^5                                | ^8.1  |
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
