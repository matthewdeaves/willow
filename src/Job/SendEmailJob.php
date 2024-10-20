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
     * Maximum number of attempts to process the job
     *
     * @var int|null
     */
    public static ?int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time. (optional property)
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * Executes the email sending job
     *
     * This method processes the job message, validates the input, fetches the email template,
     * replaces placeholders, and sends the email. It logs various stages of the process
     * and handles exceptions.
     *
     * @param \Cake\Queue\Job\Message $message The job message containing email sending details
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $args = $message->getArgument('args');
        $this->log('Received message: ' . json_encode($args), 'debug');

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log('Invalid args structure', 'error');

            return Processor::REJECT;
        }

        $payload = $args[0];

        $template = $payload['template_identifier'] ?? null;
        $from = $payload['from'] ?? null;
        $to = $payload['to'] ?? null;
        $viewVars = $payload['viewVars'] ?? [];

        if (!$template || !$from || !$to || empty($viewVars)) {
            $this->log('Missing required fields in payload', 'error');

            return Processor::REJECT;
        }

        $this->log(sprintf(
            'Processing email job: template=%s, from=%s, to=%s, viewVars=%s',
            $template,
            $from,
            $to,
            json_encode($viewVars)
        ), 'info');

        try {
            // Fetch the email template from the database
            $emailTemplatesTable = TableRegistry::getTableLocator()->get('EmailTemplates');
            $emailTemplate = $emailTemplatesTable->find()
                ->where(['template_identifier' => $template])
                ->first();

            if (!$emailTemplate) {
                throw new Exception("Email template not found: {$template}");
            }

            $bodyHtml = $emailTemplate->body_html ?? '';
            $bodyPlain = $emailTemplate->body_plain ?? '';

            // Replace placeholders in the email body
            foreach ($viewVars as $key => $value) {
                $bodyHtml = str_replace('{' . $key . '}', $value, $bodyHtml);
                $bodyPlain = str_replace('{' . $key . '}', $value, $bodyPlain);
            }

            $mailer = new Mailer('default');
            $mailer->setTo($to)
                ->setFrom($from)
                ->setSubject($emailTemplate->subject)
                ->setEmailFormat('both')
                ->setViewVars([
                    'bodyHtml' => $bodyHtml,
                    'bodyPlain' => $bodyPlain,
                ])
                ->viewBuilder()
                    ->setTemplate('default')
                    ->setLayout('default')
                    ->setPlugin('AdminTheme');

            $result = $mailer->deliver();

            if ($result) {
                $this->log('Email sent successfully', 'info');
            } else {
                throw new Exception('Failed to send email');
            }
        } catch (Exception $e) {
            $this->log('Error sending email: ' . $e->getMessage(), 'error');

            return Processor::REJECT;
        }

        return Processor::ACK;
    }
}
