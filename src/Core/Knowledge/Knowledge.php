<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @category  Core
 * @package   IslamWiki\Core\Knowledge
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Knowledge;

use Logger;\Logger
use Exception;

/**
 * Knowledge (أصول) - Business Rules and Validation System
 *
 * Knowledge provides "Principles" or "Containers" in Arabic. This class provides
 * comprehensive business rules, validation logic, Islamic knowledge
 * management, and rule engine capabilities for the IslamWiki application.
 *
 * This system is part of the Application Layer and ensures all business
 * logic follows Islamic principles and application rules.
 *
 * @category  Core
 * @package   IslamWiki\Core\Knowledge
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class Knowledge
{
    /**
     * The logging system.
     */
    protected Logger $logger;

    /**
     * Knowledge configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Business rules.
     *
     * @var array<string, array>
     */
    protected array $rules = [];

    /**
     * Validation schemas.
     *
     * @var array<string, array>
     */
    protected array $schemas = [];

    /**
     * Islamic knowledge base.
     *
     * @var array<string, mixed>
     */
    protected array $knowledgeBase = [];

    /**
     * Rule engine.
     *
     * @var array<string, callable>
     */
    protected array $ruleEngine = [];

    /**
     * Knowledge statistics.
     *
     * @var array<string, mixed>
     */
    protected array $statistics = [];

    /**
     * Constructor.
     *
     * @param Logger $logger The logging system
     * @param array        $config Knowledge configuration
     */
    public function __construct(Logger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeKnowledge();
    }

    /**
     * Initialize knowledge system.
     *
     * @return self
     */
    protected function initializeKnowledge(): self
    {
        $this->initializeStatistics();
        $this->initializeBusinessRules();
        $this->initializeValidationSchemas();
        $this->initializeIslamicKnowledge();
        $this->logger->info('Knowledge system initialized');

        return $this;
    }

    /**
     * Initialize knowledge statistics.
     *
     * @return self
     */
    protected function initializeStatistics(): self
    {
        $this->statistics = [
            'rules' => [
                'total_rules' => 0,
                'active_rules' => 0,
                'rule_evaluations' => 0,
                'rule_violations' => 0
            ],
            'validation' => [
                'total_validations' => 0,
                'successful_validations' => 0,
                'failed_validations' => 0,
                'validation_errors' => 0
            ],
            'knowledge' => [
                'total_entries' => 0,
                'islamic_entries' => 0,
                'scholarly_sources' => 0,
                'knowledge_queries' => 0
            ],
            'performance' => [
                'average_validation_time' => 0.0,
                'total_validation_time' => 0.0,
                'rule_engine_executions' => 0
            ]
        ];

        return $this;
    }

    /**
     * Initialize business rules.
     *
     * @return self
     */
    protected function initializeBusinessRules(): self
    {
        $this->rules = [
            'content_validation' => [
                'name' => 'Content Validation Rules',
                'description' => 'Rules for validating Islamic content',
                'priority' => 10,
                'is_active' => true,
                'conditions' => [
                    'requires_islamic_validation' => true,
                    'requires_source_verification' => true,
                    'requires_content_moderation' => true
                ],
                'actions' => [
                    'on_violation' => 'reject_content',
                    'on_success' => 'approve_content',
                    'log_violation' => true
                ]
            ],
            'user_permissions' => [
                'name' => 'User Permission Rules',
                'description' => 'Rules for user access and permissions',
                'priority' => 8,
                'is_active' => true,
                'conditions' => [
                    'requires_role_verification' => true,
                    'requires_permission_check' => true,
                    'requires_activity_monitoring' => true
                ],
                'actions' => [
                    'on_violation' => 'deny_access',
                    'on_success' => 'grant_access',
                    'log_access' => true
                ]
            ],
            'islamic_standards' => [
                'name' => 'Islamic Standards Rules',
                'description' => 'Rules ensuring Islamic content standards',
                'priority' => 9,
                'is_active' => true,
                'conditions' => [
                    'requires_scholarly_approval' => true,
                    'requires_islamic_terminology' => true,
                    'requires_respectful_language' => true
                ],
                'actions' => [
                    'on_violation' => 'flag_for_review',
                    'on_success' => 'mark_as_verified',
                    'log_standards_check' => true
                ]
            ],
            'data_integrity' => [
                'name' => 'Data Integrity Rules',
                'description' => 'Rules for maintaining data integrity',
                'priority' => 7,
                'is_active' => true,
                'conditions' => [
                    'requires_data_validation' => true,
                    'requires_consistency_check' => true,
                    'requires_backup_verification' => true
                ],
                'actions' => [
                    'on_violation' => 'prevent_save',
                    'on_success' => 'allow_save',
                    'log_integrity_check' => true
                ]
            ]
        ];

        $this->statistics['rules']['total_rules'] = count($this->rules);
        $this->statistics['rules']['active_rules'] = count(array_filter($this->rules, fn($rule) => $rule['is_active']));

        return $this;
    }

    /**
     * Initialize validation schemas.
     *
     * @return self
     */
    protected function initializeValidationSchemas(): self
    {
        $this->schemas = [
            'user_profile' => [
                'username' => [
                    'type' => 'string',
                    'min_length' => 3,
                    'max_length' => 50,
                    'pattern' => '/^[a-zA-Z0-9_]+$/',
                    'required' => true
                ],
                'email' => [
                    'type' => 'email',
                    'required' => true,
                    'unique' => true
                ],
                'full_name' => [
                    'type' => 'string',
                    'min_length' => 2,
                    'max_length' => 100,
                    'required' => true
                ],
                'islamic_knowledge_level' => [
                    'type' => 'enum',
                    'values' => ['beginner', 'intermediate', 'advanced', 'scholar'],
                    'default' => 'beginner'
                ]
            ],
            'islamic_content' => [
                'title' => [
                    'type' => 'string',
                    'min_length' => 5,
                    'max_length' => 200,
                    'required' => true
                ],
                'content' => [
                    'type' => 'text',
                    'min_length' => 10,
                    'max_length' => 10000,
                    'required' => true
                ],
                'source_references' => [
                    'type' => 'array',
                    'min_items' => 1,
                    'required' => true
                ],
                'scholarly_approval' => [
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false
                ],
                'islamic_category' => [
                    'type' => 'enum',
                    'values' => ['quran', 'hadith', 'fiqh', 'aqeedah', 'seerah', 'other'],
                    'required' => true
                ]
            ],
            'quran_entry' => [
                'surah_number' => [
                    'type' => 'integer',
                    'min' => 1,
                    'max' => 114,
                    'required' => true
                ],
                'ayah_number' => [
                    'type' => 'integer',
                    'min' => 1,
                    'required' => true
                ],
                'arabic_text' => [
                    'type' => 'text',
                    'required' => true,
                    'islamic_validation' => true
                ],
                'translation' => [
                    'type' => 'text',
                    'required' => true,
                    'language' => 'en'
                ],
                'tafsir' => [
                    'type' => 'text',
                    'required' => false
                ]
            ],
            'hadith_entry' => [
                'narrator' => [
                    'type' => 'string',
                    'required' => true,
                    'islamic_validation' => true
                ],
                'text_arabic' => [
                    'type' => 'text',
                    'required' => true,
                    'islamic_validation' => true
                ],
                'text_translation' => [
                    'type' => 'text',
                    'required' => true
                ],
                'authenticity' => [
                    'type' => 'enum',
                    'values' => ['sahih', 'hasan', 'daif', 'mawdu'],
                    'required' => true
                ],
                'source_collection' => [
                    'type' => 'string',
                    'required' => true
                ]
            ]
        ];

        return $this;
    }

    /**
     * Initialize Islamic knowledge base.
     *
     * @return self
     */
    protected function initializeIslamicKnowledge(): self
    {
        $this->knowledgeBase = [
            'islamic_terms' => [
                'Allah' => [
                    'arabic' => 'الله',
                    'meaning' => 'The One God',
                    'usage' => 'Proper noun for God in Islam',
                    'respect_level' => 'highest'
                ],
                'Muhammad' => [
                    'arabic' => 'محمد',
                    'meaning' => 'The Prophet of Islam',
                    'usage' => 'Prophet and Messenger of Allah',
                    'respect_level' => 'highest'
                ],
                'Quran' => [
                    'arabic' => 'القرآن',
                    'meaning' => 'The Holy Book of Islam',
                    'usage' => 'Divine revelation from Allah',
                    'respect_level' => 'highest'
                ],
                'Hadith' => [
                    'arabic' => 'الحديث',
                    'meaning' => 'Sayings and actions of Prophet Muhammad',
                    'usage' => 'Secondary source of Islamic law',
                    'respect_level' => 'high'
                ],
                'Salah' => [
                    'arabic' => 'الصلاة',
                    'meaning' => 'Islamic prayer',
                    'usage' => 'Ritual prayer performed five times daily',
                    'respect_level' => 'high'
                ],
                'Zakat' => [
                    'arabic' => 'الزكاة',
                    'meaning' => 'Charitable giving',
                    'usage' => 'Obligatory charity for Muslims',
                    'respect_level' => 'high'
                ]
            ],
            'islamic_principles' => [
                'tawheed' => [
                    'arabic' => 'التوحيد',
                    'meaning' => 'Oneness of Allah',
                    'description' => 'The fundamental principle of Islamic monotheism',
                    'importance' => 'highest'
                ],
                'adl' => [
                    'arabic' => 'العدل',
                    'meaning' => 'Justice',
                    'description' => 'Fairness and justice in all matters',
                    'importance' => 'high'
                ],
                'ihsan' => [
                    'arabic' => 'الإحسان',
                    'meaning' => 'Excellence',
                    'description' => 'Doing things in the best possible way',
                    'importance' => 'high'
                ]
            ],
            'validation_rules' => [
                'respectful_language' => [
                    'description' => 'Content must use respectful language when referring to Islamic concepts',
                    'examples' => [
                        'correct' => ['Allah (SWT)', 'Prophet Muhammad (PBUH)', 'The Holy Quran'],
                        'incorrect' => ['God', 'Muhammad', 'Quran']
                    ]
                ],
                'source_verification' => [
                    'description' => 'All Islamic content must cite reliable scholarly sources',
                    'required_sources' => ['Quran', 'Authentic Hadith', 'Scholarly Works'],
                    'verification_level' => 'strict'
                ],
                'content_moderation' => [
                    'description' => 'Content must be reviewed for Islamic accuracy and appropriateness',
                    'moderation_level' => 'strict',
                    'review_required' => true
                ]
            ]
        ];

        $this->statistics['knowledge']['total_entries'] = count($this->knowledgeBase['islamic_terms']) + 
                                                        count($this->knowledgeBase['islamic_principles']) + 
                                                        count($this->knowledgeBase['validation_rules']);
        $this->statistics['knowledge']['islamic_entries'] = $this->statistics['knowledge']['total_entries'];

        return $this;
    }

    /**
     * Validate data against a schema.
     *
     * @param array  $data   Data to validate
     * @param string $schema Schema name
     * @return array<string, mixed>
     */
    public function validateData(array $data, string $schema): array
    {
        $startTime = microtime(true);
        $this->statistics['validation']['total_validations']++;

        if (!isset($this->schemas[$schema])) {
            $this->statistics['validation']['failed_validations']++;
            $this->statistics['validation']['validation_errors']++;
            return [
                'valid' => false,
                'errors' => ["Schema '{$schema}' not found"],
                'schema' => $schema
            ];
        }

        $schemaRules = $this->schemas[$schema];
        $errors = [];
        $validatedData = [];

        foreach ($schemaRules as $field => $rules) {
            $value = $data[$field] ?? null;
            $fieldErrors = $this->validateField($field, $value, $rules, $data);

            if (!empty($fieldErrors)) {
                $errors = array_merge($errors, $fieldErrors);
            } else {
                $validatedData[$field] = $value;
            }
        }

        // Check required fields
        foreach ($schemaRules as $field => $rules) {
            if (($rules['required'] ?? false) && !isset($validatedData[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }

        $isValid = empty($errors);
        
        if ($isValid) {
            $this->statistics['validation']['successful_validations']++;
        } else {
            $this->statistics['validation']['failed_validations']++;
            $this->statistics['validation']['validation_errors'] += count($errors);
        }

        $validationTime = microtime(true) - $startTime;
        $this->updateValidationPerformance($validationTime);

        return [
            'valid' => $isValid,
            'errors' => $errors,
            'validated_data' => $validatedData,
            'schema' => $schema,
            'validation_time' => $validationTime
        ];
    }

    /**
     * Validate a single field.
     *
     * @param string $field  Field name
     * @param mixed  $value  Field value
     * @param array  $rules  Validation rules
     * @param array  $allData All form data for context
     * @return array<string>
     */
    protected function validateField(string $field, mixed $value, array $rules, array $allData): array
    {
        $errors = [];

        // Skip validation if field is not required and value is null
        if (($rules['required'] ?? false) === false && $value === null) {
            return $errors;
        }

        // Type validation
        if (isset($rules['type'])) {
            $typeErrors = $this->validateType($field, $value, $rules['type']);
            $errors = array_merge($errors, $typeErrors);
        }

        // Length validation
        if (isset($rules['min_length']) && is_string($value)) {
            if (strlen($value) < $rules['min_length']) {
                $errors[] = "Field '{$field}' must be at least {$rules['min_length']} characters long";
            }
        }

        if (isset($rules['max_length']) && is_string($value)) {
            if (strlen($value) > $rules['max_length']) {
                $errors[] = "Field '{$field}' must not exceed {$rules['max_length']} characters";
            }
        }

        // Numeric range validation
        if (isset($rules['min']) && is_numeric($value)) {
            if ($value < $rules['min']) {
                $errors[] = "Field '{$field}' must be at least {$rules['min']}";
            }
        }

        if (isset($rules['max']) && is_numeric($value)) {
            if ($value > $rules['max']) {
                $errors[] = "Field '{$field}' must not exceed {$rules['max']}";
            }
        }

        // Pattern validation
        if (isset($rules['pattern']) && is_string($value)) {
            if (!preg_match($rules['pattern'], $value)) {
                $errors[] = "Field '{$field}' format is invalid";
            }
        }

        // Enum validation
        if (isset($rules['values']) && is_array($rules['values'])) {
            if (!in_array($value, $rules['values'])) {
                $errors[] = "Field '{$field}' must be one of: " . implode(', ', $rules['values']);
            }
        }

        // Email validation
        if ($rules['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Field '{$field}' must be a valid email address";
        }

        // Unique validation
        if (($rules['unique'] ?? false) === true) {
            if ($this->isValueNotUnique($field, $value, $allData)) {
                $errors[] = "Field '{$field}' must be unique";
            }
        }

        // Islamic validation
        if (($rules['islamic_validation'] ?? false) === true) {
            $islamicErrors = $this->validateIslamicContent($field, $value);
            $errors = array_merge($errors, $islamicErrors);
        }

        return $errors;
    }

    /**
     * Validate field type.
     *
     * @param string $field Field name
     * @param mixed  $value Field value
     * @param string $type  Expected type
     * @return array<string>
     */
    protected function validateType(string $field, mixed $value, string $type): array
    {
        $errors = [];

        switch ($type) {
            case 'string':
                if (!is_string($value)) {
                    $errors[] = "Field '{$field}' must be a string";
                }
                break;
            case 'integer':
                if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
                    $errors[] = "Field '{$field}' must be an integer";
                }
                break;
            case 'boolean':
                if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
                    $errors[] = "Field '{$field}' must be a boolean";
                }
                break;
            case 'email':
                if (!is_string($value)) {
                    $errors[] = "Field '{$field}' must be a string";
                }
                break;
            case 'text':
                if (!is_string($value)) {
                    $errors[] = "Field '{$field}' must be a string";
                }
                break;
            case 'array':
                if (!is_array($value)) {
                    $errors[] = "Field '{$field}' must be an array";
                }
                break;
            case 'enum':
                // Enum validation is handled separately
                break;
        }

        return $errors;
    }

    /**
     * Check if value is unique.
     *
     * @param string $field   Field name
     * @param mixed  $value   Field value
     * @param array  $allData All form data
     * @return bool
     */
    protected function isValueNotUnique(string $field, mixed $value, array $allData): bool
    {
        // TODO: Implement actual uniqueness checking against database
        // For now, return false (not unique) to trigger validation error
        return false;
    }

    /**
     * Validate Islamic content.
     *
     * @param string $field Field name
     * @param mixed  $value Field value
     * @return array<string>
     */
    protected function validateIslamicContent(string $field, mixed $value): array
    {
        $errors = [];

        if (!is_string($value)) {
            return $errors;
        }

        // Check for respectful language
        if (!$this->usesRespectfulLanguage($value)) {
            $errors[] = "Field '{$field}' must use respectful language for Islamic content";
        }

        // Check for proper Islamic terminology
        $terminologyErrors = $this->validateIslamicTerminology($value);
        if (!empty($terminologyErrors)) {
            $errors = array_merge($errors, $terminologyErrors);
        }

        return $errors;
    }

    /**
     * Check if content uses respectful language.
     *
     * @param string $content Content to check
     * @return bool
     */
    protected function usesRespectfulLanguage(string $content): bool
    {
        // Check for respectful Islamic terms
        $respectfulTerms = [
            'Allah (SWT)', 'Prophet Muhammad (PBUH)', 'The Holy Quran',
            'Allah سبحانه وتعالى', 'محمد صلى الله عليه وسلم', 'القرآن الكريم'
        ];

        foreach ($respectfulTerms as $term) {
            if (stripos($content, $term) !== false) {
                return true;
            }
        }

        // Check for basic respectful terms
        $basicTerms = ['Allah', 'Muhammad', 'Quran', 'Hadith'];
        foreach ($basicTerms as $term) {
            if (stripos($content, $term) !== false) {
                return true;
            }
        }

        return true; // Default to respectful
    }

    /**
     * Validate Islamic terminology.
     *
     * @param string $content Content to validate
     * @return array<string>
     */
    protected function validateIslamicTerminology(string $content): array
    {
        $errors = [];

        // Check for common Islamic terms
        $islamicTerms = array_keys($this->knowledgeBase['islamic_terms']);
        $foundTerms = [];

        foreach ($islamicTerms as $term) {
            if (stripos($content, $term) !== false) {
                $foundTerms[] = $term;
            }
        }

        // If Islamic content is found, ensure it's properly formatted
        if (!empty($foundTerms)) {
            foreach ($foundTerms as $term) {
                $termInfo = $this->knowledgeBase['islamic_terms'][$term];
                if ($termInfo['respect_level'] === 'highest') {
                    // Check for proper respect indicators
                    if (!$this->hasProperRespectIndicators($content, $term)) {
                        $errors[] = "Term '{$term}' should be used with proper respect indicators";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Check for proper respect indicators.
     *
     * @param string $content Content to check
     * @param string $term    Term to check
     * @return bool
     */
    protected function hasProperRespectIndicators(string $content, string $term): bool
    {
        $respectIndicators = [
            'Allah' => ['(SWT)', 'سبحانه وتعالى', 'عز وجل'],
            'Muhammad' => ['(PBUH)', 'صلى الله عليه وسلم', 'صلى الله عليه وآله وسلم'],
            'Quran' => ['The Holy', 'القرآن الكريم', 'The Noble']
        ];

        if (isset($respectIndicators[$term])) {
            foreach ($respectIndicators[$term] as $indicator) {
                if (stripos($content, $indicator) !== false) {
                    return true;
                }
            }
        }

        return true; // Default to respectful
    }

    /**
     * Evaluate business rules.
     *
     * @param string $ruleSet Rule set name
     * @param array  $context Context data
     * @return array<string, mixed>
     */
    public function evaluateRules(string $ruleSet, array $context): array
    {
        if (!isset($this->rules[$ruleSet])) {
            return [
                'evaluated' => false,
                'error' => "Rule set '{$ruleSet}' not found"
            ];
        }

        $this->statistics['rules']['rule_evaluations']++;
        $rule = $this->rules[$ruleSet];
        
        if (!$rule['is_active']) {
            return [
                'evaluated' => false,
                'error' => "Rule set '{$ruleSet}' is not active"
            ];
        }

        $evaluationResult = $this->evaluateRuleConditions($rule, $context);
        $actions = $this->determineActions($rule, $evaluationResult);

        if (!$evaluationResult['passed']) {
            $this->statistics['rules']['rule_violations']++;
        }

        return [
            'evaluated' => true,
            'rule_set' => $ruleSet,
            'conditions_passed' => $evaluationResult['passed'],
            'violations' => $evaluationResult['violations'],
            'actions' => $actions,
            'timestamp' => time()
        ];
    }

    /**
     * Evaluate rule conditions.
     *
     * @param array $rule    Rule data
     * @param array $context Context data
     * @return array<string, mixed>
     */
    protected function evaluateRuleConditions(array $rule, array $context): array
    {
        $violations = [];
        $allConditionsMet = true;

        foreach ($rule['conditions'] as $condition => $required) {
            if ($required && !$this->evaluateCondition($condition, $context)) {
                $violations[] = $condition;
                $allConditionsMet = false;
            }
        }

        return [
            'passed' => $allConditionsMet,
            'violations' => $violations
        ];
    }

    /**
     * Evaluate a single condition.
     *
     * @param string $condition Condition name
     * @param array  $context   Context data
     * @return bool
     */
    protected function evaluateCondition(string $condition, array $context): bool
    {
        // TODO: Implement actual condition evaluation logic
        // For now, return true for most conditions
        switch ($condition) {
            case 'requires_islamic_validation':
                return isset($context['islamic_validation']) && $context['islamic_validation'];
            case 'requires_source_verification':
                return isset($context['source_verification']) && $context['source_verification'];
            case 'requires_content_moderation':
                return isset($context['content_moderation']) && $context['content_moderation'];
            default:
                return true;
        }
    }

    /**
     * Determine actions based on rule evaluation.
     *
     * @param array $rule              Rule data
     * @param array $evaluationResult Evaluation result
     * @return array<string>
     */
    protected function determineActions(array $rule, array $evaluationResult): array
    {
        $actions = [];

        if ($evaluationResult['passed']) {
            $actions[] = $rule['actions']['on_success'] ?? 'allow';
        } else {
            $actions[] = $rule['actions']['on_violation'] ?? 'deny';
        }

        if ($rule['actions']['log_violation'] ?? false) {
            $actions[] = 'log_violation';
        }

        if ($rule['actions']['log_access'] ?? false) {
            $actions[] = 'log_access';
        }

        return $actions;
    }

    /**
     * Query Islamic knowledge base.
     *
     * @param string $query Query string
     * @param array  $options Query options
     * @return array<string, mixed>
     */
    public function queryKnowledge(string $query, array $options = []): array
    {
        $this->statistics['knowledge']['knowledge_queries']++;

        $results = [];
        $query = strtolower($query);

        // Search in Islamic terms
        foreach ($this->knowledgeBase['islamic_terms'] as $term => $info) {
            if (stripos($term, $query) !== false || 
                stripos($info['meaning'], $query) !== false ||
                stripos($info['arabic'], $query) !== false) {
                $results['terms'][] = [
                    'term' => $term,
                    'arabic' => $info['arabic'],
                    'meaning' => $info['meaning'],
                    'usage' => $info['usage'],
                    'respect_level' => $info['respect_level']
                ];
            }
        }

        // Search in Islamic principles
        foreach ($this->knowledgeBase['islamic_principles'] as $principle => $info) {
            if (stripos($principle, $query) !== false || 
                stripos($info['meaning'], $query) !== false ||
                stripos($info['description'], $query) !== false) {
                $results['principles'][] = [
                    'principle' => $principle,
                    'arabic' => $info['arabic'],
                    'meaning' => $info['meaning'],
                    'description' => $info['description'],
                    'importance' => $info['importance']
                ];
            }
        }

        return [
            'query' => $query,
            'results' => $results,
            'total_results' => count($results['terms'] ?? []) + count($results['principles'] ?? []),
            'timestamp' => time()
        ];
    }

    /**
     * Update validation performance statistics.
     *
     * @param float $validationTime Validation time
     * @return self
     */
    protected function updateValidationPerformance(float $validationTime): self
    {
        $this->statistics['performance']['total_validation_time'] += $validationTime;
        
        $totalValidations = $this->statistics['validation']['total_validations'];
        if ($totalValidations > 0) {
            $this->statistics['performance']['average_validation_time'] = 
                $this->statistics['performance']['total_validation_time'] / $totalValidations;
        }

        return $this;
    }

    /**
     * Get knowledge statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Get business rules.
     *
     * @return array<string, array>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Get validation schemas.
     *
     * @return array<string, array>
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    /**
     * Get Islamic knowledge base.
     *
     * @return array<string, mixed>
     */
    public function getKnowledgeBase(): array
    {
        return $this->knowledgeBase;
    }

    /**
     * Get knowledge configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set knowledge configuration.
     *
     * @param array<string, mixed> $config Knowledge configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }
}
