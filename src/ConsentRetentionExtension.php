<?php

namespace Mouseketeers\ConsentUserForms;

use SilverStripe\Core\Extension;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

/**
 * When consent records are purged (by age, or by ConsentID for an erasure request),
 * also deletes the UserForms submissions they point to, with their field values.
 *
 * Uploaded files are NOT deleted: UserForms keeps them in assets, and a file may
 * be wanted after the submission is gone. Delete them separately if you need to.
 *
 * Only submissions of records written by this module (Source 'userform') are
 * touched. A submission whose form has no consent field has no record, so it is
 * never reached from here.
 */
class ConsentRetentionExtension extends Extension
{
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

        // A submission can have several consent records and may already be gone: that is fine.
        foreach (SubmittedForm::get()->filter('ID', array_keys($submissionIDs)) as $submission) {
            $submission->delete();
        }
    }
}
