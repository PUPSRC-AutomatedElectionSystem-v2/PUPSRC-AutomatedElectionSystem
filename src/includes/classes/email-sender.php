<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/../default-time-zone.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/../components/email-template.php');

class EmailSender
{
    use EmailTemplate;
    private $mail;
    const MAX_RECEPIENT = 98;

    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    public function sendApprovalEmail($recipientEmail)
    {
        $mailBody = 'Good day, Iskolar!<br><br>
            
        We’re pleased to inform you that your account has been <b>approved</b>! You can now access and log in to <a href="http://localhost/PUPSRC-AutomatedElectionSystem/src/landing-page.php"> iVOTE</a>. <br><br>
            
        If you have any questions or need assistance, please contact the support team at ivotepupsrc@gmail.com. ';

        error_log($recipientEmail);
        return $this->sendEmail($recipientEmail, 'iVOTE Account Approval', $mailBody);
    }


    public function sendForVerificationStatus($recipientEmail)
    {
        $mailBody = 'Good day, Iskolar!<br><br>
            
        We wanted to inform you that your account registration has been successfully submitted and is currently awaiting approval. We will review your information shortly and notify you via email once your account has been verified.<br><br>

        If you have any questions or need assistance, please contact the support team at ivotepupsrc@gmail.com. ';

        return $this->sendEmail($recipientEmail, 'iVOTE Account Registration', $mailBody);
    }


    public function sendRejectionEmail($recipientEmail, $reason, $otherReason = '')
    {
        $mailBody = 'Good day, Iskolar!<br><br>
            
        We regret to inform you that your recent account registration has been rejected. <br><br>
            
        <b>Reason for rejection:</b> ';

        if ($reason == 'reason1') {
            $mailBody .= 'Student is not part of the organization';
        } elseif ($reason == 'reason2') {
            $mailBody .= 'The PDF is low quality and illegible';
        } elseif ($reason == 'others') {
            $mailBody .= htmlspecialchars($otherReason);
        }

        $mailBody .= '<br><br>If you have any questions or need assistance, please contact the support team at ivotepupsrc@gmail.com. </h5>';

        return $this->sendEmail($recipientEmail, 'iVOTE Registration Rejected', $mailBody);
    }


    public function sendPasswordEmail($recipientEmail, $password)
    {
        $subject = 'iVOTE Admin Account Created and Password';
        $mailBody = "Hello, Committee Member.<br><br>";
        $mailBody .= "We're pleased to inform you that your account has been successfully created. 
        Below, you'll find your generated password. <br><br>";

        $mailBody .= "Password: $password <br><br>";
        $mailBody .= "For security purposes, we recommend that you log in to your account and change your passsword
        after your first login. If you have any questions or need assistance, contact the support team at ivotepupsrc@gmail.com.";

        return $this->sendEmail($recipientEmail, $subject, $mailBody);
    }

    public function sendPasswordResetEmail($recipientEmail, $token, $org_name)
    {
        $subject = 'iVOTE Password Reset Request';
        $resetPasswordLink = "http://localhost/PUPSRC-AutomatedElectionSystem/src/reset-password.php?token=$token&orgName=$org_name";
        $mailBody = <<<EOT
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>iVOTE Password Reset Request</title>
        </head>
        <body">
            <p>Dear Iskolar,</p>
            <p>We have received a request to reset the password associated with your account. To complete the process, 
            please follow the instructions below:</p>
            <ul style="padding-left: 20px;">
                <li>Click on the following link to reset your password: <a href="$resetPasswordLink">Reset Password Link</a></li>
                <li>The password reset link is only available for 30 minutes.</li>
                <li>If you are unable to click the link above, please copy and paste it into your browser's address bar.</li>
                <li>Once the link opens, you will be prompted to enter a new password for your account. Please choose a strong and secure password to ensure the safety of your account.</li>
            </ul>
            <p>If you did not initiate this password reset request, please disregard this email.</p>
            <p>Thank you for your attention to this matter.</p>
            <p>Best regards,<br>iVOTE Team</p>
        </body>
        </html>
        EOT;

        return $this->sendEmail($recipientEmail, $subject, $mailBody);
    }

    public function sendResetEmail($recipientEmail, $token, $orgName)
    {
        $subject = 'iVOTE Change Email Request';
        $updateEmailLink = "http://localhost/PUPSRC-AutomatedElectionSystem/src/setting-email-update.php?token=$token&orgName=$orgName";
        $mailBody = <<<EOT
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>iVOTE Password Change Email Request</title>
        </head>
        <body">
            <p>Dear Iskolar,</p>
            <p>We have received a request to change your email associated with your account. To complete the process, 
            please follow the instructions below:</p>
            <ul style="padding-left: 20px;">
                <li>Click on the following link to change your email: <a href="$updateEmailLink">Change Email Link</a></li>
                <li>The change email link is only available for 30 minutes.</li>
                <li>If you are unable to click the link above, please copy and paste it into your browser's address bar.</li>
                <li>Once the link opens, you will be prompted to enter a new email for your account.</li>
            </ul>
            <p>If you did not initiate this change email request, please disregard this email.</p>
            <p>Thank you for your attention to this matter.</p>
            <p>Best regards,<br>iVOTE Team</p>
        </body>
        </html>
        EOT;

        return $this->sendEmail($recipientEmail, $subject, $mailBody);
    }

    public function sendElectionCloseEmail($bccs)
    {
        global $org_acronym;
        global $org_personality;
        global $facebook;
        global $org_full_name;

        $org_full_name = ucwords($org_full_name);
        $org_acronym = strtoupper($org_acronym);


        $mainHeading = <<<HTML
            <span>$org_acronym</span> election is now closed.
    HTML;

        $title = "$org_acronym election is now closed.";

        $messageTemplate = <<<HTML
        <p>Heads up $org_personality,</p>
        <p>The election period for <b>$org_full_name</b> is now closed.</p>
        <p>Updates for new officers will be posted on <a href="$facebook">$org_acronym</a> facebook page.</p>
    HTML;

        $subject = $org_acronym . " election is now closed.";

        $mailBody = self::getEmailContent($messageTemplate, $title, $mainHeading);

        return $this->prepMassEmailByBcc($subject, $mailBody, $bccs);
    }


    public function sendElectionStartEmail($bccs, $start_date, $end_date)
    {
        global $org_acronym;
        global $org_personality;
        global $org_full_name;

        $org_full_name = ucwords($org_full_name);
        $org_acronym = strtoupper($org_acronym);
        $timestamp = strtotime($start_date);
        $start_formatted = date("F j, g:i A", $timestamp);
        $end_formatted = date("F j, Y , g:i A", strtotime($end_date));



        $mainHeading = <<<HTML
            <span>$org_personality</span> Get Ready to Vote!
        HTML;

        $title = $org_acronym . " election starts today";

        $messageTemplate = <<<HTML
        <p>Heads up $org_personality,</p>
        <p>It&#39;s that time of year again! <b>$org_full_name</b> elections are just around the corner, and we need YOU to cast your vote.</p>
        <p style="margin-bottom: calc(1rem + 1vh + 1vw);">
            Voting Periods is on <b>$start_formatted</b> to  <b>$end_formatted</b>.
        </p>
        <a href="https://ivote-pupsrc.com" style="padding: 0.75rem 1.5rem;  padding: clamp(0.01rem, 0.005rem + 0.5vw + 0.75vh, 2rem) clamp(1.5rem, 0.75rem + 0.5vw + 0.75vh, 3rem); text-decoration: none; background-color: #4870c4; color: whitesmoke; font-weight: bolder;">
            Cast your vote today
        </a>
        HTML;

        $subject = $org_acronym . " election starts today";

        $mailBody = self::getEmailContent($messageTemplate, $title, $mainHeading);

        return $this->prepMassEmailByBcc($subject, $mailBody, $bccs);
    }


    private function sendEmail($recipientEmail, $subject, $body)
    {
        try {
            $this->mail->setFrom('ivotepupsrc@gmail.com', 'iVOTE');
            $this->mail->addAddress($recipientEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function prepMassEmailByBcc($subject, $body, $bccs = null)
    {
        $serializedEmails = [];

        try {
            $this->mail->setFrom('ivotepupsrc@gmail.com', 'iVOTE');
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            if ($bccs) {
                $chunks = array_chunk($bccs, self::MAX_RECEPIENT);
                foreach ($chunks as $chunk) {
                    foreach ($chunk as $bcc) {
                        $this->mail->addBCC($bcc);
                    }
                    $this->mail->preSend();
                    // compress to binary
                    $serializedEmails[] = serialize($this->mail);
                    $this->mail->clearAllRecipients(); // Clear BCCs for the next chunk
                }
            } else {
                throw new Exception;
            }

            // print_r($serializedEmails);
            return $serializedEmails;
        } catch (Exception $e) {
            // return $e;
            echo $e;
        }
    }

    public function sendQueuedMail($mailContent)
    {
        // The email is in compressed to binary
        $this->mail = unserialize($mailContent);
        // print_r($this->mail);

        if ($this->mail->postSend()) {
            // If sent correctly
            return true;
        }
        return false;
    }
}
