<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Controller\AppController;

/**
 * Pages Controller
 * Handles admin pages including cost analysis
 */
class PagesController extends AppController
{
    /**
     * Cost Analysis page for deployment platforms
     * Route: /admin/pages/cost-analysis
     */
    public function costAnalysis(): void
    {
        $this->set('pageTitle', 'Server Deployment Cost Analysis');

        // Platform cost comparison data
        $platforms = [
            [
                'id' => 'kind-local',
                'name' => 'Kind (Local)',
                'category' => 'zero-cost',
                'monthly_cost' => 0,
                'yearly_cost' => 0,
                'ten_year_cost' => 0,
                'difficulty' => 'Very Low',
                'experience_needed' => 'Basic',
                'scalability' => 'None',
                'pros' => [
                    'True $0 cost for development',
                    'Perfect for local testing',
                    'No external dependencies'
                ],
                'cons' => [
                    'No production capabilities',
                    'No external access',
                    'Limited to development only'
                ],
                'best_for' => 'Local development & testing',
                'color_class' => 'success',
                'icon' => 'fas fa-laptop-code'
            ],
            [
                'id' => 'digital-ocean',
                'name' => 'Digital Ocean Droplet',
                'category' => 'low-cost',
                'monthly_cost' => 7,
                'yearly_cost' => 84,
                'ten_year_cost' => 840,
                'difficulty' => 'Low',
                'experience_needed' => 'Basic-Intermediate',
                'scalability' => 'Manual',
                'pros' => [
                    'Excellent value for money',
                    'Simple, predictable pricing',
                    'Good for small-medium workloads',
                    'Easy scaling options'
                ],
                'cons' => [
                    'Manual scaling required',
                    'Basic monitoring'
                ],
                'best_for' => 'Simple production deployment',
                'color_class' => 'primary',
                'recommended' => true,
                'icon' => 'fab fa-digital-ocean'
            ],
            [
                'id' => 'docker-compose',
                'name' => 'Docker Compose',
                'category' => 'low-cost',
                'monthly_cost' => 8,
                'yearly_cost' => 96,
                'ten_year_cost' => 960,
                'difficulty' => 'Low',
                'experience_needed' => 'Basic-Intermediate',
                'scalability' => 'Manual',
                'pros' => [
                    'Simple multi-container deployment',
                    'Easy dev to production transition',
                    'Perfect for queue workers'
                ],
                'cons' => [
                    'Limited scaling capabilities',
                    'No built-in orchestration'
                ],
                'best_for' => 'Multi-container applications',
                'color_class' => 'primary',
                'icon' => 'fab fa-docker'
            ],
            [
                'id' => 'portainer',
                'name' => 'Portainer (Self-hosted)',
                'category' => 'low-cost',
                'monthly_cost' => 8,
                'yearly_cost' => 96,
                'ten_year_cost' => 960,
                'difficulty' => 'Medium',
                'experience_needed' => 'Intermediate',
                'scalability' => 'Manual',
                'pros' => [
                    'Excellent Docker management UI',
                    'Community Edition is free',
                    'Good for teams managing containers'
                ],
                'cons' => [
                    'Requires underlying infrastructure',
                    'Learning curve for advanced features'
                ],
                'best_for' => 'Container management UI',
                'color_class' => 'info',
                'icon' => 'fas fa-anchor'
            ],
            [
                'id' => 'github-actions',
                'name' => 'GitHub Actions CI/CD',
                'category' => 'low-cost',
                'monthly_cost' => 7,
                'yearly_cost' => 84,
                'ten_year_cost' => 840,
                'difficulty' => 'Medium',
                'experience_needed' => 'Intermediate',
                'scalability' => 'Auto (CI/CD)',
                'pros' => [
                    'Free tier for public repositories',
                    'Integrated with GitHub workflow',
                    'Automated deployment capabilities'
                ],
                'cons' => [
                    'May require self-hosted runner',
                    'Usage limits on free tier'
                ],
                'best_for' => 'Automated CI/CD pipelines',
                'color_class' => 'info',
                'icon' => 'fab fa-github'
            ],
            [
                'id' => 'kubernetes-do',
                'name' => 'Kubernetes (DO)',
                'category' => 'moderate-cost',
                'monthly_cost' => 25,
                'yearly_cost' => 300,
                'ten_year_cost' => 3000,
                'difficulty' => 'High',
                'experience_needed' => 'Advanced',
                'scalability' => 'Auto',
                'pros' => [
                    'Production-ready orchestration',
                    'Excellent scaling capabilities',
                    'Industry standard'
                ],
                'cons' => [
                    'Complexity overhead',
                    'Requires Kubernetes expertise',
                    'Higher costs'
                ],
                'best_for' => 'High-traffic applications',
                'color_class' => 'warning',
                'icon' => 'fas fa-dharmachakra'
            ],
            [
                'id' => 'jenkins',
                'name' => 'Jenkins (Self-hosted)',
                'category' => 'low-cost',
                'monthly_cost' => 11,
                'yearly_cost' => 132,
                'ten_year_cost' => 1320,
                'difficulty' => 'High',
                'experience_needed' => 'Advanced',
                'scalability' => 'Manual',
                'pros' => [
                    'Powerful CI/CD capabilities',
                    'Extensive plugin ecosystem',
                    'Open source'
                ],
                'cons' => [
                    'High maintenance overhead',
                    'Requires DevOps expertise',
                    'Complex setup'
                ],
                'best_for' => 'Complex CI/CD workflows',
                'color_class' => 'warning',
                'icon' => 'fab fa-jenkins'
            ],
            [
                'id' => 'heroku',
                'name' => 'Heroku',
                'category' => 'expensive',
                'monthly_cost' => 51,
                'yearly_cost' => 612,
                'ten_year_cost' => 6120,
                'difficulty' => 'Low',
                'experience_needed' => 'Basic',
                'scalability' => 'Auto',
                'pros' => [
                    'Simple deployment process',
                    'Managed platform',
                    'Easy to get started'
                ],
                'cons' => [
                    'Very expensive for resources provided',
                    'Limited customization',
                    'Vendor lock-in'
                ],
                'best_for' => 'Rapid prototyping',
                'color_class' => 'error',
                'icon' => 'fas fa-cube'
            ],
            [
                'id' => 'openshift',
                'name' => 'Red Hat OpenShift',
                'category' => 'expensive',
                'monthly_cost' => 172,
                'yearly_cost' => 2064,
                'ten_year_cost' => 20640,
                'difficulty' => 'High',
                'experience_needed' => 'Expert',
                'scalability' => 'Auto',
                'pros' => [
                    'Enterprise-grade platform',
                    'Advanced security & compliance',
                    'Professional support'
                ],
                'cons' => [
                    'Extremely expensive',
                    'Enterprise-focused complexity',
                    'Overkill for small projects'
                ],
                'best_for' => 'Large enterprise deployments',
                'color_class' => 'error',
                'icon' => 'fas fa-hat-cowboy'
            ]
        ];

        // AI cost data
        $aiCosts = [
            'anthropic_claude' => 20, // $20 per 1M characters
            'estimated_monthly' => 250,
            'estimated_yearly' => 3000
        ];

        // Key insights
        $insights = [
            'Infrastructure costs are minimal compared to AI API usage',
            'Focus optimization efforts on AI prompt efficiency',
            'Even expensive platforms cost less than AI APIs',
            'Platform choice has minimal impact on 10-year TCO'
        ];

        $this->set(compact('platforms', 'aiCosts', 'insights'));
    }
}
