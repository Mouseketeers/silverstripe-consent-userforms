<?php

namespace Mouseketeers\ConsentForms;


class EditableTermsAndPrivacyConsentCheckbox extends EditableConsentCheckbox {

	private static $table_name = 'EditableTermsAndPrivacyConsentCheckbox';

	private static $singular_name = 'Terms and Privacy Consent Checkbox';

	private static $plural_name = 'Terms and Privacy Consent Checkboxes';


	public function populateDefaults() {
		parent::populateDefaults();
		$this->Title = self::$singular_name;
		return $this;
	}

	public function getFormField() {
		$consentID = $this->ConsentIDField;

		$field = TermsAndPrivacyConsentCheckboxField::create($this->Name)
			->setConsentIDFieldName($consentID);

		$errorMessage = $this->getErrorMessage() ?: $field->getCustomValidationMessage();
		$field->setAttribute('data-rule-required', 'true');
		$field->setAttribute('data-msg-required', $errorMessage);

		return $field;
	}
}
