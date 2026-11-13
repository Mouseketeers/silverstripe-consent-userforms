<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;

class EditableConsentCheckbox extends EditableFormField {

    private static $table_name = 'EditableConsentCheckbox';

    private static $singular_name = 'Consent Checkbox';

    private static $db = [
        'ConsentIDField' => 'Varchar(255)',
        'ConsentType' => 'Varchar(255)',
        // Inverted so existing fields stay mandatory after upgrading.
        'ConsentOptional' => 'Boolean'
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
                    )->setEmptyString(_t(self::class.'.ConsentIDFieldEmpty', '(select a field)'))
                        ->setDescription(_t(self::class.'.ConsentIDFieldHelp', 'The field (usually the email) that identifies who gave the consent.')),
                    TextField::create(
                        'ConsentType',
                        _t(self::class.'.ConsentType', 'Consent type')
                    )->setDescription(_t(self::class.'.ConsentTypeHelp', 'Stored with each consent record, e.g. Newsletter. Leave empty to derive it from the title.')),
                    CheckboxField::create(
                        'ConsentOptional',
                        _t(self::class.'.ConsentOptional', 'Optional (the visitor may leave it unticked)')
                    )
                ]
            );
        });

        return parent::getCMSFields();
    }

    /**
     * The consent ID field is required, but only checked once the form's other
     * fields exist (a brand-new field on an empty form has nothing to pick).
     */
    public function validate(): ValidationResult
    {
        $result = parent::validate();

        if (!$this->ConsentIDField && $this->getSiblingFieldOptions()) {
            $result->addFieldError(
                'ConsentIDField',
                _t(self::class.'.ConsentIDFieldRequired', 'Select the field that identifies who gives the consent.')
            );
        }

        return $result;
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
            ->setConsentRequired(!$this->ConsentOptional);

        if ($this->ConsentIDField) {
            $field->setConsentIDFieldName($this->ConsentIDField);
        }

        $type = $this->ConsentType ?: $this->getDefaultConsentType();
        if ($type) {
            $field->setConsentType($type);
        }

        if ($field->isConsentRequired()) {
            $errorMessage = $this->getErrorMessage() ?: $field->getCustomValidationMessage();
            $field->setAttribute('data-rule-required', 'true');
            $field->setAttribute('data-msg-required', $errorMessage);
        }

        return $field;
    }

    /**
     * Stable fallback type from the title, so it is not the generated field name.
     * Subclasses with a fixed type return null.
     */
    protected function getDefaultConsentType(): ?string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '', ucwords(strip_tags((string) $this->Title)));

        return $slug !== '' ? substr($slug, 0, 60) : null;
    }

    /**
     * Subclasses override this to use another consent field class.
     */
    protected function createConsentField(): ConsentCheckboxField
    {
        return ConsentCheckboxField::create($this->Name, $this->Title);
    }
}
