<?php

class EditableTermsAndPrivacyConsentCheckbox extends EditableConsentCheckbox {

	private static $singular_name = 'Terms and Privacy Consent Checkbox';
	private static $plural_name = 'Terms and Privacy Consent Checkboxes';

	static $icon = 'consent-forms/images/privacy.png';

    public function populateDefaults() {
        parent::populateDefaults();
        $this->Title = self::$singular_name;
    }

	protected function createConsentField() {
		return TermsAndPrivacyConsentCheckboxField::create($this->Name);
	}
}
