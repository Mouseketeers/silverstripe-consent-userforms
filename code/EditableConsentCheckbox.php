<?php

class EditableConsentCheckbox extends EditableFormField {

	private static $singular_name = 'Consent Checkbox';
	private static $plural_name = 'Consent Checkboxes';

	static $icon = 'consent-forms/images/privacy.png';

	/**
	 * The other fields on the form, as Name => Title. Empty while the field is
	 * not yet attached to a form.
	 */
	protected function getSiblingFieldOptions() {
		$parent = $this->Parent();
		if(!$parent || !$parent->exists()) {
			return array();
		}

		$options = $parent->Fields()->map('Name', 'Title')->toArray();
		unset($options[$this->Name]);

		return $options;
	}

	public function getFieldConfiguration() {

		$fields = new FieldList();

		$consentID = $this->getSetting('ConsentID');
		$pre = "Fields[$this->ID][CustomSettings]";

		$fields->push(
			DropdownField::create("{$pre}[ConsentID]", _t(get_class($this) . '.ConsentID', 'Consent ID'), $this->getSiblingFieldOptions(), $consentID)
				->setEmptyString('(none)')
				->setRightTitle('Consent ID is typically an e-mail address')
		);
		return $fields;
	}

	public function getFormField() {

		$field = $this->createConsentField()
			->setConsentIDFieldName($this->getSetting('ConsentID'));

		$errorMessage = ($this->getErrorMessage()) ? $this->getErrorMessage() : $field->getCustomValidationMessage();
		$field->setAttribute('data-rule-required', 'true');
		$field->setAttribute('data-msg-required', $errorMessage);

		return $field;
	}

	/**
	 * Subclasses override this to use another consent field class.
	 */
	protected function createConsentField() {
		return ConsentCheckboxField::create($this->Name, $this->Title);
	}

	public function getErrorMessage() {
        return DBField::create_field('Varchar', $this->CustomErrorMessage);
	}
	public function getIcon() {
		return  self::$icon;
	}
}
