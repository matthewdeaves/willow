<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Quiz Controller
 * 
 * Interactive quiz to help users find suitable adapters and chargers
 *
 * @property \App\Model\Table\ProductsTable $Products
 */
class QuizController extends AppController
{
    /**
     * beforeFilter callback.
     *
     * Allow unauthenticated access to all quiz actions
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to all quiz actions
        $this->Authentication->addUnauthenticatedActions(['take', 'preview', 'submit']);
    }

    /**
     * Take method - Interactive quiz form
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function take(): ?Response
    {
        $config = $this->getQuizConfig();
        
        if ($this->request->is('post')) {
            return $this->handleQuizSubmission($config);
        }

        // Prepare quiz data for the view
        $quizInfo = $config['quiz_info'] ?? [
            'title' => 'Adapter Finder Quiz',
            'description' => 'Find the perfect adapter for your needs',
            'estimated_time' => '2-3 minutes'
        ];
        
        $questions = $config['questions'] ?? [];
        $display = $config['display'] ?? [];

        $this->set(compact('config', 'quizInfo', 'questions', 'display'));
        $this->set('_serialize', ['config', 'quizInfo', 'questions', 'display']);

        return null;
    }

    /**
     * Preview method - Read-only quiz preview
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function preview(): ?Response
    {
        $config = $this->getQuizConfig();
        
        // Prepare quiz data for preview (read-only)
        $quizInfo = $config['quiz_info'] ?? [
            'title' => 'Adapter Finder Quiz (Preview)',
            'description' => 'Preview of the quiz questions',
            'estimated_time' => '2-3 minutes'
        ];
        
        $questions = $config['questions'] ?? [];
        $display = $config['display'] ?? [];
        $isPreview = true;

        $this->set(compact('config', 'quizInfo', 'questions', 'display', 'isPreview'));
        $this->set('_serialize', ['config', 'quizInfo', 'questions', 'display', 'isPreview']);

        return null;
    }

    /**
     * Submit method - Process quiz answers (AJAX endpoint)
     *
     * @return \Cake\Http\Response|null|void JSON response
     */
    public function submit(): ?Response
    {
        if (!$this->request->is('post')) {
            throw new \Cake\Http\Exception\MethodNotAllowedException('Only POST requests are allowed.');
        }

        $config = $this->getQuizConfig();
        return $this->handleQuizSubmission($config);
    }

    /**
     * Get quiz configuration from database settings or fallback to default
     *
     * @return array Quiz configuration
     */
    private function getQuizConfig(): array
    {
        try {
            $settingsTable = TableRegistry::getTableLocator()->get('Settings');
            
            // Try to get quiz config from database
            $quizConfigSetting = $settingsTable->find()
                ->where(['category' => 'Products', 'key_name' => 'quiz_config_json'])
                ->first();

            if ($quizConfigSetting && !empty($quizConfigSetting->value)) {
                $dbConfig = json_decode($quizConfigSetting->value, true);
                if ($dbConfig && is_array($dbConfig)) {
                    return $this->normalizeDbConfig($dbConfig);
                }
            }
        } catch (Exception $e) {
            // Log error but continue with fallback
            $this->log('Error loading quiz config from database: ' . $e->getMessage(), 'warning');
        }

        // Fallback to default configuration
        $defaultConfig = Configure::read('Quiz.default');
        if (!$defaultConfig) {
            // Ultimate fallback - minimal quiz
            return $this->getMinimalFallbackConfig();
        }

        return $defaultConfig;
    }

    /**
     * Normalize database configuration to match expected format
     *
     * @param array $dbConfig Database configuration
     * @return array Normalized configuration
     */
    private function normalizeDbConfig(array $dbConfig): array
    {
        $questions = [];
        
        if (isset($dbConfig['questions']) && is_array($dbConfig['questions'])) {
            foreach ($dbConfig['questions'] as $question) {
                $normalizedQuestion = [
                    'id' => $question['id'] ?? uniqid(),
                    'type' => 'multiple_choice', // Force multiple choice
                    'text' => $question['question'] ?? 'Question',
                    'required' => $question['required'] ?? true,
                    'weight' => $question['weight'] ?? 1,
                    'help_text' => $question['help_text'] ?? null,
                    'multiple' => $question['type'] === 'multiple_choice' && isset($question['multiple']) ? $question['multiple'] : false,
                    'options' => []
                ];

                // Convert options to expected format
                if (isset($question['options']) && is_array($question['options'])) {
                    foreach ($question['options'] as $option) {
                        $normalizedQuestion['options'][] = [
                            'key' => $option['id'] ?? $option['value'] ?? $option['key'] ?? uniqid(),
                            'label' => $option['label'] ?? $option['text'] ?? 'Option'
                        ];
                    }
                }

                $questions[] = $normalizedQuestion;
            }
        }

        return [
            'version' => 2,
            'quiz_info' => $dbConfig['quiz_info'] ?? [
                'title' => 'Smart Adapter & Charger Finder Quiz',
                'description' => 'Find the perfect adapter for your device',
                'estimated_time' => '2-3 minutes'
            ],
            'questions' => $questions,
            'display' => [
                'shuffle_questions' => false,
                'shuffle_options' => false,
                'show_progress' => true,
                'allow_back' => true
            ],
            'scoring' => $dbConfig['ai_matching_algorithm'] ?? [
                'method' => 'weighted_confidence',
                'minimum_match_score' => 0.6,
                'max_results' => 5
            ]
        ];
    }

    /**
     * Get minimal fallback configuration
     *
     * @return array Minimal configuration
     */
    private function getMinimalFallbackConfig(): array
    {
        return [
            'version' => 2,
            'quiz_info' => [
                'title' => 'Adapter Finder Quiz',
                'description' => 'Find the perfect adapter for your device',
                'estimated_time' => '1 minute'
            ],
            'questions' => [
                [
                    'id' => 'device_type',
                    'type' => 'multiple_choice',
                    'text' => 'What type of device do you need an adapter for?',
                    'required' => true,
                    'weight' => 10,
                    'options' => [
                        ['key' => 'laptop', 'label' => 'Laptop'],
                        ['key' => 'phone', 'label' => 'Phone'],
                        ['key' => 'tablet', 'label' => 'Tablet'],
                        ['key' => 'other', 'label' => 'Other']
                    ]
                ]
            ],
            'display' => [
                'shuffle_questions' => false,
                'shuffle_options' => false,
                'show_progress' => true,
                'allow_back' => true
            ],
            'scoring' => [
                'method' => 'simple',
                'max_results' => 5
            ]
        ];
    }

    /**
     * Handle quiz submission
     *
     * @param array $config Quiz configuration
     * @return \Cake\Http\Response|null
     */
    private function handleQuizSubmission(array $config): ?Response
    {
        $answers = $this->request->getData('answers', []);
        
        // Validate required questions
        $validationErrors = $this->validateQuizAnswers($answers, $config);
        
        if (!empty($validationErrors)) {
            if ($this->request->is('ajax')) {
                $response = [
                    'success' => false,
                    'errors' => $validationErrors,
                    'message' => __('Please answer all required questions.')
                ];

                $this->response = $this->response->withType('application/json');
                $this->set('response', $response);
                $this->set('_serialize', 'response');
                $this->viewBuilder()->setClassName('Json');

                return null;
            } else {
                foreach ($validationErrors as $error) {
                    $this->Flash->error($error);
                }
                return $this->redirect(['action' => 'take']);
            }
        }

        // Process quiz and get recommendations
        try {
            $recommendations = $this->processQuizResults($answers, $config);
            
            if ($this->request->is('ajax')) {
                $response = [
                    'success' => true,
                    'recommendations' => $recommendations,
                    'resultsHtml' => $this->renderQuizResults($recommendations)
                ];

                $this->response = $this->response->withType('application/json');
                $this->set('response', $response);
                $this->set('_serialize', 'response');
                $this->viewBuilder()->setClassName('Json');

                return null;
            } else {
                // Non-AJAX submission - redirect to results or set variables for results view
                $this->set(compact('recommendations', 'answers'));
                return $this->render('results');
            }
            
        } catch (Exception $e) {
            $this->log('Error processing quiz results: ' . $e->getMessage(), 'error');
            
            $errorMessage = __('Sorry, there was an error processing your quiz. Please try again.');
            
            if ($this->request->is('ajax')) {
                $response = [
                    'success' => false,
                    'message' => $errorMessage
                ];

                $this->response = $this->response->withType('application/json');
                $this->set('response', $response);
                $this->set('_serialize', 'response');
                $this->viewBuilder()->setClassName('Json');

                return null;
            } else {
                $this->Flash->error($errorMessage);
                return $this->redirect(['action' => 'take']);
            }
        }
    }

    /**
     * Validate quiz answers
     *
     * @param array $answers User answers
     * @param array $config Quiz configuration
     * @return array Validation errors
     */
    private function validateQuizAnswers(array $answers, array $config): array
    {
        $errors = [];
        $questions = $config['questions'] ?? [];

        foreach ($questions as $question) {
            if ($question['required'] ?? false) {
                $questionId = $question['id'];
                
                if (!isset($answers[$questionId]) || empty($answers[$questionId])) {
                    $errors[] = __('Please answer: {0}', $question['text']);
                }
            }
        }

        return $errors;
    }

    /**
     * Process quiz results and find matching products
     *
     * @param array $answers User answers
     * @param array $config Quiz configuration
     * @return array Recommendations
     */
    private function processQuizResults(array $answers, array $config): array
    {
        // Load Products table
        $this->loadModel('Products');
        
        // Start with published products
        $query = $this->Products->getPublishedProducts();
        
        // Apply filters based on answers
        $filters = $this->buildProductFilters($answers, $config);
        
        if (!empty($filters['manufacturers'])) {
            $manufacturerConditions = [];
            foreach ($filters['manufacturers'] as $manufacturer) {
                $manufacturerConditions[] = ['Products.manufacturer LIKE' => '%' . $manufacturer . '%'];
            }
            $query->where(['OR' => $manufacturerConditions]);
        }

        if (!empty($filters['tags'])) {
            $query->matching('Tags', function ($q) use ($filters) {
                return $q->where(['Tags.slug IN' => $filters['tags']]);
            });
        }

        if (!empty($filters['price_range'])) {
            $priceRange = $filters['price_range'];
            if (isset($priceRange['min'])) {
                $query->where(['Products.price >=' => $priceRange['min']]);
            }
            if (isset($priceRange['max'])) {
                $query->where(['Products.price <=' => $priceRange['max']]);
            }
        }

        // Get results
        $products = $query->limit(10)->toArray();

        return [
            'products' => $products,
            'filters_applied' => $filters,
            'total_found' => count($products),
            'confidence_score' => $this->calculateConfidenceScore($answers, $products),
            'reasoning' => $this->generateRecommendationReasoning($answers, $filters)
        ];
    }

    /**
     * Build product filters from quiz answers
     *
     * @param array $answers User answers
     * @param array $config Quiz configuration
     * @return array Product filters
     */
    private function buildProductFilters(array $answers, array $config): array
    {
        $filters = [
            'manufacturers' => [],
            'tags' => [],
            'price_range' => []
        ];

        // Map answers to product filters
        foreach ($answers as $questionId => $answer) {
            switch ($questionId) {
                case 'device_type':
                    $filters['tags'][] = $answer;
                    break;
                    
                case 'manufacturer':
                    $filters['manufacturers'][] = $answer;
                    break;
                    
                case 'budget':
                    $filters['price_range'] = $this->parseBudgetRange($answer);
                    break;
                    
                case 'port_type':
                    $filters['tags'][] = str_replace('-', '_', $answer);
                    break;
                    
                case 'features':
                    if (is_array($answer)) {
                        $filters['tags'] = array_merge($filters['tags'], $answer);
                    } else {
                        $filters['tags'][] = $answer;
                    }
                    break;
            }
        }

        return $filters;
    }

    /**
     * Parse budget range from answer
     *
     * @param string $budgetAnswer Budget answer
     * @return array Price range
     */
    private function parseBudgetRange(string $budgetAnswer): array
    {
        $ranges = [
            '5-20' => ['min' => 5, 'max' => 20],
            '20-50' => ['min' => 20, 'max' => 50],
            '50-100' => ['min' => 50, 'max' => 100],
            '100+' => ['min' => 100, 'max' => 999]
        ];

        return $ranges[$budgetAnswer] ?? [];
    }

    /**
     * Calculate confidence score for recommendations
     *
     * @param array $answers User answers
     * @param array $products Found products
     * @return float Confidence score
     */
    private function calculateConfidenceScore(array $answers, array $products): float
    {
        $totalQuestions = count($answers);
        $productsFound = count($products);
        
        if ($totalQuestions === 0) {
            return 0.0;
        }
        
        // Base confidence on completeness and results
        $completeness = min($totalQuestions / 8, 1.0); // Expecting up to 8 questions
        $availability = min($productsFound / 5, 1.0);  // Expecting up to 5 good results
        
        return round(($completeness * 0.6 + $availability * 0.4) * 100, 1);
    }

    /**
     * Generate recommendation reasoning
     *
     * @param array $answers User answers
     * @param array $filters Applied filters
     * @return array Reasoning text
     */
    private function generateRecommendationReasoning(array $answers, array $filters): array
    {
        $reasoning = [];

        if (!empty($filters['manufacturers'])) {
            $reasoning[] = __('Filtered by manufacturer: {0}', implode(', ', $filters['manufacturers']));
        }

        if (!empty($filters['tags'])) {
            $reasoning[] = __('Matched device compatibility: {0}', implode(', ', $filters['tags']));
        }

        if (!empty($filters['price_range'])) {
            $range = $filters['price_range'];
            $min = $range['min'] ?? 0;
            $max = $range['max'] ?? 999;
            $reasoning[] = __('Within your budget range: ${0} - ${1}', $min, $max);
        }

        if (empty($reasoning)) {
            $reasoning[] = __('Based on your responses, here are some general recommendations.');
        }

        return $reasoning;
    }

    /**
     * Render quiz results as HTML
     *
     * @param array $recommendations Quiz recommendations
     * @return string HTML content
     */
    private function renderQuizResults(array $recommendations): string
    {
        $products = $recommendations['products'] ?? [];
        $confidence = $recommendations['confidence_score'] ?? 0;
        $reasoning = $recommendations['reasoning'] ?? [];

        $html = '<div class="quiz-results-content">';
        
        // Confidence and summary
        $html .= '<div class="results-summary mb-4">';
        $html .= '<div class="confidence-score text-center mb-3">';
        $html .= '<div class="h2 text-primary">' . $confidence . '%</div>';
        $html .= '<p class="text-muted">Match Confidence</p>';
        $html .= '</div>';
        
        if (!empty($reasoning)) {
            $html .= '<div class="reasoning">';
            $html .= '<h6><i class="fas fa-lightbulb"></i> ' . __('Why these recommendations:') . '</h6>';
            $html .= '<ul>';
            foreach ($reasoning as $reason) {
                $html .= '<li>' . h($reason) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        $html .= '</div>';

        // Product recommendations
        if (!empty($products)) {
            $html .= '<div class="recommended-products">';
            $html .= '<h4 class="mb-3"><i class="fas fa-star"></i> ' . __('Recommended Products') . '</h4>';
            $html .= '<div class="row">';

            foreach (array_slice($products, 0, 6) as $product) {
                $html .= '<div class="col-md-6 col-lg-4 mb-3">';
                $html .= '<div class="card h-100">';
                if (!empty($product->image)) {
                    $html .= '<img src="' . h($product->image) . '" class="card-img-top" alt="' . h($product->alt_text ?: $product->title) . '" style="height: 150px; object-fit: cover;">';
                }
                $html .= '<div class="card-body">';
                $html .= '<h6 class="card-title">' . h($product->title) . '</h6>';
                $html .= '<p class="card-text text-muted small">' . $this->truncateText(h($product->description ?? ''), 80) . '</p>';
                if (!empty($product->manufacturer)) {
                    $html .= '<p class="mb-1 small"><strong>Manufacturer:</strong> ' . h($product->manufacturer) . '</p>';
                }
                if (!empty($product->price)) {
                    $html .= '<p class="mb-2"><strong class="text-success">$' . number_format($product->price, 2) . '</strong></p>';
                }
                $html .= '</div>';
                $html .= '<div class="card-footer">';
                $html .= '<a href="/products/view/' . $product->id . '" class="btn btn-primary btn-sm">View Details</a>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }

            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="no-results text-center py-4">';
            $html .= '<i class="fas fa-search fa-3x text-muted mb-3"></i>';
            $html .= '<h5>' . __('No matches found') . '</h5>';
            $html .= '<p class="text-muted">' . __('We couldn\'t find products matching your criteria, but you can browse our full catalog.') . '</p>';
            $html .= '<a href="/products" class="btn btn-primary">' . __('Browse All Products') . '</a>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Truncate text to specified length
     *
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @return string Truncated text
     */
    private function truncateText(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }
}
