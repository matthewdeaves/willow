<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Log\LogTrait;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

/**
 * SendEmailJob Class
 *
 * This class is responsible for processing and sending emails as a background job.
 * It fetches email templates from the database, replaces placeholders with provided data,
 * and sends the email using CakePHP's Mailer class.
 */
class SendEmailJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts to process the job.
     *
     * This property defines how many times the job should be retried if it fails.
     *
     * @var int|null
     */
    public static ?int $maxAttempts = 3;

    /**
     * Indicates if there should be only one instance of this job on the queue at a time.
     *
     * When set to true, it ensures that only one instance of this job is queued,
     * preventing duplicate processing.
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * Executes the email sending process using the provided message data.
     *
     * This method performs the following steps:
     * 1. Retrieves email details from the Message object.
     * 2. Logs the email job details for tracking.
     * 3. Fetches the email template from the database.
     * 4. Replaces placeholders in the email body with provided view variables.
     * 5. Configures and sends the email using CakePHP's Mailer class.
     * 6. Logs the result of the email sending process.
     *
     * @param \Cake\Queue\Job\Message $message The message object containing email details such as
     *                                         template identifier, sender, recipient, and view variables.
     * @return string|null Returns Processor::ACK if the email is sent successfully,
     *                     Processor::REJECT if the email sending fails or an error occurs.
     * @throws \Exception If the email template is not found in the database or any other error
     *                    occurs during the process.
     */
    public function execute(Message $message): ?string
    {
        // Get the data we need
        $templateIdentifier = $message->getArgument('template_identifier');
        $from = $message->getArgument('from');
        $to = $message->getArgument('to');
        $viewVars = $message->getArgument('viewVars');

        $this->log(
            sprintf(
                'Processing email job: Template: %s From: %s To: %s viewVars: %s',
                $templateIdentifier,
                $from,
                $to,
                json_encode($viewVars)
            ),
            'info',
            ['group_name' => 'email_sending']
        );

        // Fetch the email template from the database
        $emailTemplatesTable = TableRegistry::getTableLocator()->get('EmailTemplates');
        $emailTemplate = $emailTemplatesTable->find()
            ->where(['template_identifier' => $templateIdentifier])
            ->first();

        if (!$emailTemplate) {
            throw new Exception(__('Email template not found: {0}', $templateIdentifier));
        }

        // Replace placeholders in the email body
        foreach ($viewVars as $key => $value) {
            $emailTemplate->body_html = str_replace('{' . $key . '}', $value, $emailTemplate->body_html);
            $emailTemplate->body_plain = str_replace('{' . $key . '}', $value, $emailTemplate->body_plain);
        }

        $mailer = new Mailer('default');
        $mailer->setTo($to)
            ->setFrom($from)
            ->setSubject($emailTemplate->subject)
            ->setEmailFormat('both')
            ->setViewVars([
                'bodyHtml' => $emailTemplate->body_html,
                'bodyPlain' => $emailTemplate->body_plain,
            ])
            ->viewBuilder()
                ->setTemplate('default')
                ->setLayout('default')
                ->setPlugin('AdminTheme');

        $result = $mailer->deliver();

        if ($result) {
            $this->log(
                sprintf(
                    'Email sent successfully: %s to %s',
                    $emailTemplate->subject,
                    $to
                ),
                'info',
                ['group_name' => 'email_sending']
            );

            return Processor::ACK;
        } else {
            $this->log(
                sprintf(
                    'Email sending failed: %s to %s',
                    $emailTemplate->subject,
                    $to
                ),
                'error',
                ['group_name' => 'email_sending']
            );
        }

        return Processor::REJECT;
    }
}
