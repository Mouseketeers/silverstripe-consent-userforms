<?php

namespace Mouseketeers\ConsentUserForms\Tests;

use Mouseketeers\ConsentForms\ConsentCheckboxField;
use Mouseketeers\ConsentUserForms\ConsentFormExtension;
use Mouseketeers\ConsentRecords\ConsentRecord;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TextField;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

class ConsentFormExtensionTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testHiddenConsentFieldIsNotRecorded()
    {
        $count = $this->submit([
            ['Name' => 'EditableEmailField_ab12', 'Value' => 'a@example.com', 'Displayed' => true],
            ['Name' => 'Newsletter', 'Value' => 1, 'Displayed' => false],
        ]);

        $this->assertSame(0, $count);
    }

    public function testDisplayedConsentFieldIsRecorded()
    {
        $count = $this->submit([
            ['Name' => 'EditableEmailField_ab12', 'Value' => 'a@example.com', 'Displayed' => true],
            ['Name' => 'Newsletter', 'Value' => 1, 'Displayed' => true],
        ]);

        $this->assertSame(1, $count);
    }

    public function testRecordCarriesSourceAndSubmissionID()
    {
        $this->submit([
            ['Name' => 'EditableEmailField_ab12', 'Value' => 'a@example.com', 'Displayed' => true],
            ['Name' => 'Newsletter', 'Value' => 1, 'Displayed' => true],
        ]);

        $record = ConsentRecord::get()->first();
        $this->assertSame('userform', $record->Source);
        $this->assertSame(7, (int) $record->SubmissionID);
    }

    /**
     * Runs the extension against a stubbed controller and returns how many
     * ConsentRecords were created.
     */
    private function submit(array $submittedFields): int
    {
        $form = Form::create(null, 'Form', FieldList::create(
            TextField::create('EditableEmailField_ab12'),
            ConsentCheckboxField::create('Newsletter', 'Send me news')->setConsentIDFieldName('EditableEmailField_ab12')
        ));

        $controller = $this->getMockBuilder(UserDefinedFormController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Form'])
            ->getMock();
        $controller->method('Form')->willReturn($form);

        $request = new HTTPRequest('GET', '/contact');
        $request->setSession(new Session([]));
        $controller->setRequest($request);
        $controller->pushCurrent();

        $extension = new ConsentFormExtension();
        $extension->setOwner($controller);
        $extension->updateEmailData(
            [
                'SubmittedForm' => SubmittedForm::create(['ID' => 7]),
                'Fields' => array_map(fn (array $row) => SubmittedFormField::create($row), $submittedFields),
            ],
            []
        );

        $controller->popCurrent();

        return ConsentRecord::get()->count();
    }
}
