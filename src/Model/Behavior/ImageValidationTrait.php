<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\Validation\Validator;

/**
 * ImageValidationTrait
 *
 * Provides reusable image validation rules for models that handle image uploads.
 * This trait consolidates common image validation logic that was previously
 * duplicated across multiple table classes (Images, Articles, Users, Tags).
 *
 * Usage:
 * ```php
 * use App\Model\Behavior\ImageValidationTrait;
 *
 * class ImagesTable extends Table
 * {
 *     use ImageValidationTrait;
 *
 *     public function validationCreate(Validator $validator): Validator
 *     {
 *         return $this->addImageValidationRules($validator, 'image', true);
 *     }
 * }
 * ```
 */
trait ImageValidationTrait
{
    /**
     * Allowed file extensions for image uploads (security measure)
     *
     * @var array<string>
     */
    private array $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Dangerous file extensions that should never be allowed
     *
     * @var array<string>
     */
    private array $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar',
        'exe', 'sh', 'bash', 'bat', 'cmd', 'com',
        'js', 'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py', 'rb',
        'htaccess', 'htpasswd',
    ];

    /**
     * Add standard image validation rules to a validator
     *
     * @param \Cake\Validation\Validator $validator The validator instance
     * @param string $field The field name to validate (default: 'image')
     * @param bool $required Whether the image field is required (default: false)
     * @param array $options Additional options for customization
     * @return \Cake\Validation\Validator
     */
    public function addImageValidationRules(
        Validator $validator,
        string $field = 'image',
        bool $required = false,
        array $options = [],
    ): Validator {
        // Default configuration
        $defaults = [
            'allowedMimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'allowedExtensions' => $this->allowedImageExtensions,
            'maxFileSize' => '10MB',
            'messages' => [
                'mimeType' => __('Please upload only images (jpeg, png, gif, webp).'),
                'fileSize' => __('Image must be less than 10MB.'),
                'required' => __('An image file is required.'),
                'extension' => __('Invalid file extension. Only jpg, jpeg, png, gif, webp allowed.'),
                'dangerous' => __('This file type is not allowed for security reasons.'),
            ],
        ];

        // Deep merge options with defaults (messages array needs special handling)
        $config = array_merge($defaults, $options);
        if (isset($options['messages'])) {
            $config['messages'] = array_merge($defaults['messages'], $options['messages']);
        }

        // Handle required validation
        if ($required) {
            $validator
                ->requirePresence($field, 'create')
                ->notEmptyFile($field, $config['messages']['required']);
        } else {
            $validator->allowEmptyFile($field);
        }

        // Add file-specific validation rules
        return $validator->add($field, [
            'mimeType' => [
                'rule' => ['mimeType', $config['allowedMimeTypes']],
                'message' => $config['messages']['mimeType'],
                'on' => function ($context) use ($field) {
                    // Only validate mime type if file was uploaded successfully
                    return !empty($context['data'][$field])
                        && is_object($context['data'][$field])
                        && method_exists($context['data'][$field], 'getError')
                        && $context['data'][$field]->getError() === UPLOAD_ERR_OK;
                },
            ],
            'fileSize' => [
                'rule' => ['fileSize', '<=', $config['maxFileSize']],
                'message' => $config['messages']['fileSize'],
                'on' => function ($context) use ($field) {
                    // Only validate file size if file was uploaded successfully
                    return !empty($context['data'][$field])
                        && is_object($context['data'][$field])
                        && method_exists($context['data'][$field], 'getError')
                        && $context['data'][$field]->getError() === UPLOAD_ERR_OK;
                },
            ],
            'safeExtension' => [
                'rule' => function ($value) use ($config) {
                    return $this->validateFileExtension($value, $config['allowedExtensions']);
                },
                'message' => $config['messages']['extension'],
                'on' => function ($context) use ($field) {
                    return !empty($context['data'][$field])
                        && is_object($context['data'][$field])
                        && method_exists($context['data'][$field], 'getError')
                        && $context['data'][$field]->getError() === UPLOAD_ERR_OK;
                },
            ],
            'notDangerous' => [
                'rule' => function ($value) {
                    return $this->validateNotDangerousExtension($value);
                },
                'message' => $config['messages']['dangerous'],
                'on' => function ($context) use ($field) {
                    return !empty($context['data'][$field])
                        && is_object($context['data'][$field])
                        && method_exists($context['data'][$field], 'getError')
                        && $context['data'][$field]->getError() === UPLOAD_ERR_OK;
                },
            ],
        ]);
    }

    /**
     * Validate that file extension is in the allowed list
     *
     * @param mixed $value The uploaded file object
     * @param array $allowedExtensions List of allowed extensions
     * @return bool
     */
    private function validateFileExtension(mixed $value, array $allowedExtensions): bool
    {
        if (!is_object($value) || !method_exists($value, 'getClientFilename')) {
            return false;
        }

        $filename = $value->getClientFilename();
        if (empty($filename)) {
            return false;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, $allowedExtensions, true);
    }

    /**
     * Validate that file does not have a dangerous extension
     * Checks all extensions in the filename (e.g., shell.php.jpg)
     *
     * @param mixed $value The uploaded file object
     * @return bool True if safe, false if dangerous
     */
    private function validateNotDangerousExtension(mixed $value): bool
    {
        if (!is_object($value) || !method_exists($value, 'getClientFilename')) {
            return false;
        }

        $filename = $value->getClientFilename();
        if (empty($filename)) {
            return false;
        }

        // Check all parts of the filename for dangerous extensions
        // This catches tricks like "shell.php.jpg" or "file.phtml.png"
        $parts = explode('.', strtolower($filename));
        foreach ($parts as $part) {
            if (in_array($part, $this->dangerousExtensions, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add image validation rules for create operations (required)
     *
     * @param \Cake\Validation\Validator $validator The validator instance
     * @param string $field The field name to validate (default: 'image')
     * @param array $options Additional options for customization
     * @return \Cake\Validation\Validator
     */
    public function addRequiredImageValidation(
        Validator $validator,
        string $field = 'image',
        array $options = [],
    ): Validator {
        return $this->addImageValidationRules($validator, $field, true, $options);
    }

    /**
     * Add image validation rules for update operations (optional)
     *
     * @param \Cake\Validation\Validator $validator The validator instance
     * @param string $field The field name to validate (default: 'image')
     * @param array $options Additional options for customization
     * @return \Cake\Validation\Validator
     */
    public function addOptionalImageValidation(
        Validator $validator,
        string $field = 'image',
        array $options = [],
    ): Validator {
        return $this->addImageValidationRules($validator, $field, false, $options);
    }
}
