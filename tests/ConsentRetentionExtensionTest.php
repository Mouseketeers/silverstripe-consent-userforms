<?php

namespace Mouseketeers\ConsentUserForms\Tests;

use Mouseketeers\ConsentRecords\ConsentRecord;
use Mouseketeers\ConsentRecords\ConsentRetention;
use Mouseketeers\ConsentUserForms\ConsentRetentionExtension;
use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Versioned\Versioned;

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

    private function purgeSubmissionWithUpload(): array
    {
        $submission = $this->submission('jo@example.com');
        $file = File::create(['Name' => 'cv.txt']);
        $file->write();
        SubmittedFileField::create(['Name' => 'Upload', 'ParentID' => $submission->ID, 'UploadedFileID' => $file->ID])->write();
        $this->record('jo@example.com', 'userform', $submission->ID);

        // Uploads are draft-only, so look at the draft stage.
        $draftFile = fn () => Versioned::get_by_stage(File::class, Versioned::DRAFT)->byID($file->ID);
        $this->assertNotNull($draftFile(), 'control: the file exists before the purge');

        ConsentRetention::purge(0, 'jo@example.com');

        $this->assertNull(SubmittedForm::get()->byID($submission->ID));

        return [$draftFile(), $submission];
    }

    public function testUploadedFilesAreKeptByDefault()
    {
        [$file, $submission] = $this->purgeSubmissionWithUpload();

        $this->assertNotNull($file, 'the uploaded file is not deleted');
        $this->assertSame(0, SubmittedFileField::get()->filter('ParentID', $submission->ID)->count());
    }

    public function testUploadedFilesCanBeDeletedWithTheSubmission()
    {
        Config::modify()->set(ConsentRetentionExtension::class, 'delete_uploaded_files', true);

        [$file] = $this->purgeSubmissionWithUpload();

        $this->assertNull($file);
    }

    public function testMissingSubmissionIsNotAnError()
    {
        $this->record('jo@example.com', 'userform', 99999);

        $this->assertSame(1, ConsentRetention::purge(0, 'jo@example.com'));
    }
}
