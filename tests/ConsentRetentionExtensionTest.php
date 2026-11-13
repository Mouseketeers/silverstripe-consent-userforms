<?php

namespace Mouseketeers\ConsentUserForms\Tests;

use Mouseketeers\ConsentRecords\ConsentRecord;
use Mouseketeers\ConsentRecords\ConsentRetention;
use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;

class ConsentRetentionExtensionTest extends SapphireTest
{
    protected $usesDatabase = true;

    private function submission(string $email): SubmittedForm
    {
        $form = UserDefinedForm::get()->first() ?: UserDefinedForm::create(['Title' => 'Contact']);
        $form->write();

        $submission = SubmittedForm::create(['ParentClass' => UserDefinedForm::class, 'ParentID' => $form->ID]);
        $submission->write();
        SubmittedFormField::create(['Name' => 'Email', 'Value' => $email, 'ParentID' => $submission->ID])->write();

        return $submission;
    }

    private function record(string $email, string $source, int $submissionID): void
    {
        ConsentRecord::create([
            'ConsentID' => $email, 'ConsentType' => 'Newsletter', 'URL' => 'https://example.com/',
            'Source' => $source, 'SubmissionID' => $submissionID,
        ])->write();
    }

    public function testErasureByConsentIDDeletesTheSubmissionAndItsValues()
    {
        $jo = $this->submission('jo@example.com');
        $kim = $this->submission('kim@example.com');
        $this->record('jo@example.com', 'userform', $jo->ID);
        $this->record('jo@example.com', 'userform', $jo->ID); // two consent boxes, one submission
        $this->record('kim@example.com', 'userform', $kim->ID);

        ConsentRetention::purge(0, 'jo@example.com');

        $this->assertNull(SubmittedForm::get()->byID($jo->ID));
        $this->assertSame(0, SubmittedFormField::get()->filter('Value', 'jo@example.com')->count());
        $this->assertNotNull(SubmittedForm::get()->byID($kim->ID), 'other submissions are kept');
        $this->assertSame(1, ConsentRecord::get()->count());
    }

    public function testOnlyUserformRecordsPointAtSubmissions()
    {
        $submission = $this->submission('jo@example.com');
        $this->record('jo@example.com', 'form', $submission->ID);
        $this->record('jo@example.com', 'cookie-banner', 0);

        ConsentRetention::purge(0, 'jo@example.com');

        $this->assertNotNull(SubmittedForm::get()->byID($submission->ID));
    }

    public function testUploadedFilesAreKept()
    {
        $submission = $this->submission('jo@example.com');
        $file = File::create(['Name' => 'cv.txt']);
        $file->write();
        SubmittedFileField::create(['Name' => 'Upload', 'ParentID' => $submission->ID, 'UploadedFileID' => $file->ID])->write();
        $this->record('jo@example.com', 'userform', $submission->ID);

        ConsentRetention::purge(0, 'jo@example.com');

        $this->assertNull(SubmittedForm::get()->byID($submission->ID));
        $this->assertNotNull(File::get()->byID($file->ID), 'the uploaded file is not deleted');
    }

    public function testMissingSubmissionIsNotAnError()
    {
        $this->record('jo@example.com', 'userform', 99999);

        $this->assertSame(1, ConsentRetention::purge(0, 'jo@example.com'));
    }
}
