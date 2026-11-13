<?php

namespace Mouseketeers\ConsentUserForms;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

/**
 * When consent records are purged (by age, or by ConsentID for an erasure request),
 * also deletes the UserForms submissions they point to, with their field values.
 *
 * Uploaded files are kept by default. UserForms deletes a submission's files together
 * with the submission (SubmittedFileField cascades the delete), so this detaches them
 * first. They stay in assets, no longer linked to anything: remove them yourself, or set
 * delete_uploaded_files to true to delete them with the submission.
 *
 * Only submissions of records written by this module (Source 'userform') are
 * touched. A submission whose form has no consent field has no record, so it is
 * never reached from here.
 */
class ConsentRetentionExtension extends Extension
{
    /**
     * Delete uploaded files together with their submission (UserForms' own behaviour).
     *
     * @config
     */
    private static $delete_uploaded_files = false;

    /**
     * @param \Mouseketeers\ConsentRecords\ConsentRecord[] $records
     */
    public function onBeforePurge(array $records): void
    {
        $submissionIDs = [];
        foreach ($records as $record) {
            if ($record->Source === 'userform' && (int) $record->SubmissionID > 0) {
                $submissionIDs[(int) $record->SubmissionID] = true;
            }
        }

        if (!$submissionIDs) {
            return;
        }

        $keepFiles = !Config::inst()->get(self::class, 'delete_uploaded_files');

        // A submission can have several consent records and may already be gone: that is fine.
        foreach (SubmittedForm::get()->filter('ID', array_keys($submissionIDs)) as $submission) {
            if ($keepFiles) {
                foreach (SubmittedFileField::get()->filter('ParentID', $submission->ID) as $value) {
                    $value->UploadedFileID = 0;
                    $value->write();
                }
            }
            $submission->delete();
        }
    }
}
