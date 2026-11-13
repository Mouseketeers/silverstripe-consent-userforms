<?php

namespace Mouseketeers\ConsentForms;

class EditableTermsAndPrivacyConsentCheckbox extends EditableConsentCheckbox {

    private static $table_name = 'EditableTermsAndPrivacyConsentCheckbox';

    private static $singular_name = 'Terms and Privacy Consent Checkbox';

    public function populateDefaults() {
        parent::populateDefaults();
        $this->Title = self::$singular_name;
        return $this;
    }

    protected function createConsentField(): ConsentCheckboxField
    {
        return TermsAndPrivacyConsentCheckboxField::create($this->Name);
    }
}
