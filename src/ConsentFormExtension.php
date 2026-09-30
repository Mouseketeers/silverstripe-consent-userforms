<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\Core\Extension;
use SilverStripe\UserForms\Control\UserDefinedFormController;

/**
 * Feeds submitted UserForms data into {@link ConsentRecorder} after a form is
 * successfully submitted.
 *
 * Registered on {@link UserDefinedFormController} (not RequestHandler) so it
 * never runs for ordinary pages, login or error controllers that also handle a
 * `Form` action. The hook is fired once per successfully validated submission
 * with the rendered form fields rather than the editable CMS field models.
 */
class ConsentFormExtension extends Extension
{
    /**
     * @param array $emailData
     * @param array $attachments
     */
    public function updateEmailData($emailData, $attachments)
    {
        $controller = $this->owner;
        if (!$controller instanceof UserDefinedFormController) {
            return;
        }

        $form = $controller->Form();
        if (!$form) {
            return;
        }

        // Build a name => value map from the submitted fields.
        $submitted = [];
        foreach ($emailData['Fields'] ?? [] as $submittedField) {
            // Older UserForms versions do not have a Displayed flag.
            // Where available, exclude fields hidden by conditional rules.
            if ($submittedField->hasField('Displayed') && !$submittedField->Displayed) {
                continue;
            }

            if ($submittedField->Name) {
                $submitted[$submittedField->Name] = $submittedField->Value;
            }
        }

        ConsentRecorder::record($form->Fields(), $submitted);
    }
}
