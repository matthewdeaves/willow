<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Core\Configure;
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

        if (Configure::read('debug')) {
            $this->log(
                __('Received message: {0}', [json_encode($args)]),
                'debug',
                ['group_name' => 'email_sending']
            );
        }

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log(
                __('Invalid args structure'),
                'error',
                ['group_name' => 'email_sending']
            );

            return Processor::REJECT;
        }

        $payload = $args[0];

        $template = $payload['template_identifier'] ?? null;
        $from = $payload['from'] ?? null;
        $to = $payload['to'] ?? null;
        $viewVars = $payload['viewVars'] ?? [];

        if (!$template || !$from || !$to || empty($viewVars)) {
            $this->log(
                __('Missing required fields in payload'),
                'error',
                ['group_name' => 'email_sending']
            );

            return Processor::REJECT;
        }

        $this->log(
            __(
                'Processing email job: {0} {1} {2} {3}',
                [
                $template,
                $from,
                $to,
                json_encode($viewVars),
                ]
            ),
            'info',
            ['group_name' => 'email_sending']
        );

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
                $this->log(
                    __('Email sent successfully: {0} to {1}', [$emailTemplate->subject, $to]),
                    'info',
                    ['group_name' => 'email_sending']
                );
            } else {
                throw new Exception('Failed to send email');
            }
        } catch (Exception $e) {
            $this->log(
                _(
                    'Error sending email: {0}',
                    $e->getMessage()
                ),
                'error',
                ['group_name' => 'email_sending']
            );

            return Processor::REJECT;
        }

        return Processor::ACK;
    }
}
