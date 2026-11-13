<?php

namespace Mouseketeers\ConsentUserForms;

use Mouseketeers\ConsentForms\ConsentCheckboxField;
use Mouseketeers\ConsentForms\TermsAndPrivacyConsentCheckboxField;

class EditableTermsAndPrivacyConsentCheckbox extends EditableConsentCheckbox {

    private static $table_name = 'EditableTermsAndPrivacyConsentCheckbox';

    private static $singular_name = 'Terms and Privacy Consent Checkbox';

    public function populateDefaults() {
        parent::populateDefaults();
        $this->Title = self::$singular_name;
        return $this;
    }

    protected function getDefaultConsentType(): ?string
    {
        return null; // TermsAndPrivacyConsentCheckboxField has its own fixed type
    }

    protected function createConsentField(): ConsentCheckboxField
    {
        return TermsAndPrivacyConsentCheckboxField::create($this->Name);
    }
}
