<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Utility\SettingsManager;
use Cake\Http\Response;

/**
 * Settings Controller
 *
 * This controller handles the management of application settings.
 * It provides functionality to view and save settings, which are organized
 * by category and key name.
 *
 * @property \App\Model\Table\SettingsTable $Settings The settings table instance.
 */
class SettingsController extends AppController
{
    /**
     * Index method
     *
     * This method retrieves all settings from the database, orders them by category
     * and key name, and groups them into an array structure.
     * The grouped settings are then passed to the view for rendering.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $settings = $this->Settings->find('all')
            ->orderBy(['category' => 'ASC', 'key_name' => 'ASC'])
            ->toArray();

        $groupedSettings = [];
        foreach ($settings as $setting) {
            $groupedSettings[$setting->category][$setting->key_name] = [
                'value' => $setting->value,
                'value_type' => $setting->value_type,
                'value_obscure' => $setting->value_obscure,
            ];
        }

        $this->set(compact('groupedSettings'));

        return null;
    }

    /**
     * Save Settings method
     *
     * This method processes the incoming request to update settings. It iterates over
     * the submitted data, finds the corresponding setting in the database, and updates
     * its value. If all updates are successful, a success message is displayed; otherwise,
     * an error message is shown. The user is then redirected to the index action.
     *
     * @return \Cake\Http\Response|null|void Redirects on successful save, renders view otherwise.
     */
    public function saveSettings(): ?Response
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $success = true;
            $errorMessages = [];

            foreach ($data as $category => $settings) {
                foreach ($settings as $key => $value) {
                    $setting = $this->Settings->find()
                        ->where([
                            'category' => $category,
                            'key_name' => $key,
                        ])
                        ->first();

                    if ($setting) {
                        $setting = $this->Settings->patchEntity($setting, ['value' => $value]);
                    } else {
                        $errorMessages[] = __('Setting not found: {0}.{1}', $category, $key);
                        $success = false;
                        continue;
                    }

                    if (!$this->Settings->save($setting)) {
                        $success = false;
                        $errors = $setting->getErrors();
                        foreach ($errors as $fieldErrors) {
                            foreach ($fieldErrors as $error) {
                                $errorMessages[] = __('Error saving {0}.{1}: {2}', $category, $key, $error);
                            }
                        }
                    }
                }
            }

            if ($success) {
                $this->Flash->success(__('The settings have been saved.'));
                //Clear the cache if saved OK
                SettingsManager::clearCache();
            } else {
                $this->Flash->error(__('Some settings could not be saved. Please, try again.'), [
                    'params' => ['errors' => $errorMessages],
                ]);
            }

            return $this->redirect(['action' => 'index']);
        }

        return null;
    }
}
