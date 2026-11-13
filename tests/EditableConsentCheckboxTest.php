<?php

namespace Mouseketeers\ConsentForms\Tests;

use Mouseketeers\ConsentForms\EditableConsentCheckbox;
use SilverStripe\Dev\SapphireTest;

class EditableConsentCheckboxTest extends SapphireTest
{
    public function testFieldIsMandatoryByDefault()
    {
        $field = EditableConsentCheckbox::create(['Name' => 'EditableConsentCheckbox_ab12', 'Title' => 'Send me news'])->getFormField();

        $this->assertTrue($field->isConsentRequired());
        $this->assertSame('true', $field->getAttribute('data-rule-required'));
    }

    public function testOptionalFieldIsNotRequired()
    {
        $field = EditableConsentCheckbox::create(['Name' => 'x', 'Title' => 'Send me news', 'ConsentOptional' => 1])->getFormField();

        $this->assertFalse($field->isConsentRequired());
        $this->assertNull($field->getAttribute('data-rule-required'));
    }

    public function testConsentTypeIsNotTheGeneratedFieldName()
    {
        $field = EditableConsentCheckbox::create(['Name' => 'EditableConsentCheckbox_ab12', 'Title' => 'Send me news'])->getFormField();

        $this->assertSame('SendMeNews', $field->getConsentType());
    }

    public function testExplicitConsentTypeAndIdFieldAreUsed()
    {
        $field = EditableConsentCheckbox::create([
            'Name' => 'x', 'Title' => 'Send me news', 'ConsentType' => 'Newsletter', 'ConsentIDField' => 'EditableEmailField_cd34',
        ])->getFormField();

        $this->assertSame('Newsletter', $field->getConsentType());
        $this->assertSame('EditableEmailField_cd34', $field->getConsentIDFieldName());
    }
}
