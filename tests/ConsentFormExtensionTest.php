<?php

namespace Mouseketeers\ConsentForms\Tests;

use Mouseketeers\ConsentForms\ConsentCheckboxField;
use Mouseketeers\ConsentForms\ConsentFormExtension;
use Mouseketeers\ConsentRecords\ConsentRecord;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TextField;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

class ConsentFormExtensionTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testHiddenConsentFieldIsNotRecorded()
    {
        $count = $this->submit([
            ['Name' => 'Email', 'Value' => 'a@example.com', 'Displayed' => true],
            ['Name' => 'Newsletter', 'Value' => 1, 'Displayed' => false],
        ]);

        $this->assertSame(0, $count);
    }

    public function testDisplayedConsentFieldIsRecorded()
    {
        $count = $this->submit([
            ['Name' => 'Email', 'Value' => 'a@example.com', 'Displayed' => true],
            ['Name' => 'Newsletter', 'Value' => 1, 'Displayed' => true],
        ]);

        $this->assertSame(1, $count);
    }

    /**
     * Runs the extension against a stubbed controller and returns how many
     * ConsentRecords were created.
     */
    private function submit(array $submittedFields): int
    {
        $form = Form::create(null, 'Form', FieldList::create(
            TextField::create('Email'),
            ConsentCheckboxField::create('Newsletter', 'Send me news')->setConsentIDFieldName('Email')
        ));

        $controller = new class extends UserDefinedFormController {
            public $stubForm;

            public function Form()
            {
                return $this->stubForm;
            }
        };
        $controller->stubForm = $form;

        $request = new HTTPRequest('GET', '/contact');
        $request->setSession(new Session([]));
        $controller->setRequest($request);
        $controller->pushCurrent();

        $extension = new ConsentFormExtension();
        $extension->setOwner($controller);
        $extension->updateEmailData(
            ['Fields' => array_map(function (array $row) {
                return SubmittedFormField::create($row);
            }, $submittedFields)],
            []
        );

        $controller->popCurrent();

        return ConsentRecord::get()->count();
    }
}
