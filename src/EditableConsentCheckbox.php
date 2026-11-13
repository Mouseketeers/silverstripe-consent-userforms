<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\Forms\DropdownField;


class EditableConsentCheckbox extends EditableFormField {

	private static $table_name = 'EditableConsentCheckbox';
	
	private static $singular_name = 'Consent Checkbox';

	private static $plural_name = 'Consent Checkboxes';


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
                      	$this->Parent()->Fields()->map('Name', 'Title')->toArray()
                    )
                ]
            );
        });

        return parent::getCMSFields();
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
