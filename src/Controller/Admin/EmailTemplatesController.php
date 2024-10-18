<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Exception;

/**
 * EmailTemplates Controller
 *
 * @property \App\Model\Table\EmailTemplatesTable $EmailTemplates
 */
class EmailTemplatesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): void
    {
        $query = $this->EmailTemplates->find();
        $emailTemplates = $this->paginate($query);

        $this->set(compact('emailTemplates'));
    }

    /**
     * View method
     *
     * @param string|null $id Email Template id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $emailTemplate = $this->EmailTemplates->get($id, contain: []);
        $this->set(compact('emailTemplate'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): Response
    {
        $emailTemplate = $this->EmailTemplates->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            // Generate plain text version from HTML
            $data['body_plain'] = $this->htmlToPlainText($data['body_html']);

            $emailTemplate = $this->EmailTemplates->patchEntity($emailTemplate, $data);
            if ($this->EmailTemplates->save($emailTemplate)) {
                $this->Flash->success(__('The email template has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The email template could not be saved. Please, try again.'));
        }
        $this->set(compact('emailTemplate'));

        return $this->render();
    }

    /**
     * Edit method
     *
     * @param string|null $id Email Template id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): Response
    {
        $emailTemplate = $this->EmailTemplates->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            // Generate plain text version from HTML
            $data['body_plain'] = $this->htmlToPlainText($data['body_html']);

            $emailTemplate = $this->EmailTemplates->patchEntity($emailTemplate, $data);
            if ($this->EmailTemplates->save($emailTemplate)) {
                $this->Flash->success(__('The email template has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The email template could not be saved. Please, try again.'));
        }
        $this->set(compact('emailTemplate'));

        return $this->render();
    }

    /**
     * Converts HTML content to plain text.
     *
     * This function removes all HTML tags from the provided HTML content,
     * decodes HTML entities to their corresponding characters, and removes
     * any extra whitespace. The resulting plain text is trimmed of leading
     * and trailing whitespace before being returned.
     *
     * @param string $html The HTML content to be converted to plain text.
     * @return string The plain text representation of the provided HTML content.
     */
    private function htmlToPlainText(string $html): string
    {
        // Remove HTML tags
        $text = strip_tags($html);
        // Convert HTML entities to their corresponding characters
        $text = html_entity_decode($text);
        // Remove extra whitespace
        //$text = preg_replace('/\s+/', ' ', $text);
        // Trim the result
        return trim($text);
    }

    /**
     * Delete method
     *
     * @param string|null $id Email Template id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $emailTemplate = $this->EmailTemplates->get($id);
        if ($this->EmailTemplates->delete($emailTemplate)) {
            $this->Flash->success(__('The email template has been deleted.'));
        } else {
            $this->Flash->error(__('The email template could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Sends an email to a user based on a selected email template.
     *
     * This method retrieves a list of email templates and users, and upon receiving a POST request,
     * it sends an email to a specified user using a specified email template. The email content is
     * customized by replacing placeholders in the template with actual data.
     *
     * @return \Cake\Http\Response|null Redirects to the index action after attempting to send the email,
     *                                   or renders the current view if no POST request is made.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If the email template or user cannot be found.
     * @throws \Exception If there is an error during the email sending process.
     * @uses \App\Model\Table\EmailTemplatesTable::find() To retrieve the list of email templates.
     * @uses \App\Model\Table\UsersTable::find() To retrieve the list of users.
     * @uses \App\Controller\Component\EmailComponent::prepareEmailVariables() To prepare variables for the email template.
     * @uses \Cake\Mailer\Mailer To send the email.
     * @uses \Cake\Http\ServerRequest::getData() To retrieve POST data.
     * @uses \Cake\Controller\Component\FlashComponent::success() To set success flash messages.
     * @uses \Cake\Controller\Component\FlashComponent::error() To set error flash messages.
     */
    public function sendEmail(): ?Response
    {
        $emailTemplates = $this->EmailTemplates->find(
            'list',
            keyField: 'id',
            valueField: 'name'
        );

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $users = $usersTable->find(
            'list',
            keyField: 'id',
            valueField: 'email'
        );

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $variables = $this->prepareEmailVariables($data['email_template_id'], $data['user_id']);
            $emailTemplate = $this->EmailTemplates->get($data['email_template_id']);
            $user = $usersTable->get($data['user_id']);

            $bodyHtml = $emailTemplate->body_html ?? '';
            $bodyPlain = $emailTemplate->body_plain ?? '';

            // Replace all placeholders
            foreach ($variables as $key => $value) {
                $bodyHtml = str_replace('{' . $key . '}', $value, $bodyHtml);
                $bodyPlain = str_replace('{' . $key . '}', $value, $bodyPlain);
            }

            $mailer = new Mailer('default');

            $mailer->setTo($user->email)
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

            try {
                $result = $mailer->deliver();
                if ($result) {
                    $this->Flash->success(__('Email sent successfully.'));
                    Log::info(
                        __(
                            'Email sent successfully to: {0}. Template: {1}, Subject: {2}',
                            $user->email,
                            $emailTemplate->template_identifier,
                            $emailTemplate->subject
                        ),
                        ['group_name' => 'email']
                    );
                } else {
                    $this->Flash->error(__('Failed to send email. Please check your email configuration.'));
                    Log::error(
                        __('Failed to send email to: {0}', $user->email),
                        ['group_name' => 'email']
                    );
                }
            } catch (Exception $e) {
                $this->Flash->error(__('Error sending email: {0}', $e->getMessage()));
                Log::error(
                    __('Error sending email: {0}', $e->getMessage()),
                    ['group_name' => 'email', 'exception' => $e]
                );
            }

            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('emailTemplates', 'users'));

        return $this->render();
    }

    /**
     * Prepares variables for email templates.
     *
     * This method generates a set of variables to be used in email templates.
     * It always includes basic user information and conditionally adds other
     * variables based on the content of the email template.
     *
     * @param string $templateId The UUID of the email template.
     * @param string $userId The ID of the user for whom the email is being prepared.
     * @return array An associative array of variables for use in the email template.
     * @uses \App\Model\Table\EmailTemplatesTable::get() To retrieve the email template.
     * @uses \Cake\ORM\TableRegistry::getTableLocator() To get the Users table.
     * @uses \App\Model\Table\UsersTable::get() To retrieve the user data.
     * @uses self::generateConfirmationLink() To generate a confirmation link if needed.
     */
    private function prepareEmailVariables(string $templateId, string $userId): array
    {
        $variables = [];
        $emailTemplate = $this->EmailTemplates->get($templateId, contain: []);
        $user = TableRegistry::getTableLocator()->get('Users')->get($userId, contain: []);

        // Always include basic user info
        $variables['username'] = $user->username;
        $variables['email'] = $user->email;

        // Check for specific template needs
        if (
            strpos($emailTemplate->body_html, '{confirm_email_link}') !== false ||
            strpos($emailTemplate->body_plain, '{confirm_email_link}') !== false
        ) {
            $variables['confirm_email_link'] = $this->generateConfirmationLink($user);
        }

        // Add more conditional variables here as needed
        // if (strpos($emailTemplate->body_html, '{some_other_variable}') !== false) {
        //     $variables['some_other_variable'] = $this->generateSomeOtherVariable();
        // }

        return $variables;
    }

    /**
     * Generates a confirmation link for a user.
     *
     * This method retrieves the confirmation code for a given user from the
     * UserAccountConfirmations table. If a confirmation code does not exist,
     * it generates a new UUID as the confirmation code, saves it to the table,
     * and then generates a URL for the user to confirm their account.
     *
     * @param \App\Model\Entity\User $user The user entity for whom the confirmation link is generated.
     * @return string The generated confirmation link URL.
     * @uses \Cake\ORM\TableRegistry::getTableLocator() To get the UserAccountConfirmations table.
     * @uses \Cake\Utility\Text::uuid() To generate a new UUID if no confirmation code exists.
     * @uses \Cake\Routing\Router::url() To generate the confirmation link URL.
     */
    private function generateConfirmationLink(User $user): string
    {
        // Get the UserAccountConfirmations table
        $userAccountConfirmationsTable = TableRegistry::getTableLocator()->get('UserAccountConfirmations');

        // Look up the confirmation code for the user
        $confirmation = $userAccountConfirmationsTable->find()
            ->where(['user_id' => $user->id])
            ->first();

        if ($confirmation) {
            // If a confirmation code exists, use it to generate the link
            $confirmationCode = $confirmation->confirmation_code;
        } else {
            // If no confirmation code exists, generate a new UUID
            $confirmationCode = Text::uuid(); // Generate a UUID

            // Save the new confirmation code
            $newConfirmation = $userAccountConfirmationsTable->newEntity([
                'user_id' => $user->id,
                'confirmation_code' => $confirmationCode,
            ]);
            $userAccountConfirmationsTable->save($newConfirmation);
        }

        // Generate the confirmation link
        return Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'confirm',
            $confirmationCode,
        ], true);
    }
}
