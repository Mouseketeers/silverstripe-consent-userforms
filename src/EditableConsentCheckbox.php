<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\Forms\DropdownField;


class EditableConsentCheckbox extends EditableFormField {

	private static $table_name = 'EditableConsentCheckbox';
	
	private static $singular_name = 'Consent Checkbox';


    private static $db = [
    	'ConsentIDField' => 'Varchar(255)'
    ];	

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields): void {
            $fields->addFieldsToTab(
                'Root.Main',
                [
                    DropdownField::create(
                        'ConsentIDField',
                        _t(self::class.'.ConsentIDField', 'Consent ID Field'),
                        $this->getSiblingFieldOptions()
                    )->setEmptyString('(none)')
                ]
            );
        });

        return parent::getCMSFields();
    }

	/**
	 * The other fields on the form, as Name => Title. Empty while the field is
	 * not yet attached to a form.
	 */
	protected function getSiblingFieldOptions(): array
	{
		$parent = $this->Parent();
		if (!$parent || !$parent->exists()) {
			return [];
		}

		$options = $parent->Fields()->map('Name', 'Title')->toArray();
		unset($options[$this->Name]);

		return $options;
	}

	public function getFormField()
	{
		$field = $this->createConsentField()
			->setConsentIDFieldName($this->ConsentIDField);

		$errorMessage = $this->getErrorMessage() ?: $field->getCustomValidationMessage();
		$field->setAttribute('data-rule-required', 'true');
		$field->setAttribute('data-msg-required', $errorMessage);

		return $field;
	}

	/**
	 * Subclasses override this to use another consent field class.
	 */
	protected function createConsentField(): ConsentCheckboxField
	{
		return ConsentCheckboxField::create($this->Name, $this->Title);
	}
}
