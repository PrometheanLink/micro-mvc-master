<?php
/**
 * Demo Landing
 * Marketing landing page
 * Template: landing
 */

if (!defined('micro_mvc'))
    exit();

class DemoLanding_Model
{
    public static function Get_Data()
    {
        return [
            'phoenix' => true,
            'title' => 'PHOENIX Platform',
            'template' => 'landing',
            'theme' => 'cyber',

            'brand' => 'PHOENIX',

            'config' => [
                'theme' => 'cyber',
                'show_nav' => true,
                'nav_style' => 'transparent',
                'show_particles' => true,
                'enable_animations' => true
            ],

            'nav_items' => [
                ['label' => 'Features', 'url' => '#features'],
                ['label' => 'Templates', 'url' => '#templates'],
                ['label' => 'Pricing', 'url' => '#pricing'],
                ['label' => 'FAQ', 'url' => '#faq']
            ],

            'hero' => [
                'title' => 'Build Anything with <span class="gradient-text">AI Power</span>',
                'subtitle' => 'PHOENIX transforms natural language into fully functional web applications. Describe what you want, and watch it come to life.',
                'cta_text' => 'Start Building Free',
                'cta_url' => '/en/admin/',
                'secondary_text' => 'View Demo Pages',
                'secondary_url' => '#templates',
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200'
            ],

            'features' => [
                'title' => 'Everything You Need',
                'subtitle' => 'A complete platform for building modern web applications',
                'items' => [
                    ['icon' => '🎨', 'title' => 'Beautiful Templates', 'description' => 'Dashboard, Catalog, Article, Gallery, Kanban, Landing - all ready to use.'],
                    ['icon' => '🧩', 'title' => 'Modular Widgets', 'description' => 'Stats, charts, tables, forms - mix and match to build any interface.'],
                    ['icon' => '🤖', 'title' => 'AI-Powered', 'description' => 'Describe what you want in plain English. Let AI handle the rest.'],
                    ['icon' => '⚡', 'title' => 'Instant Deploy', 'description' => 'Docker-based architecture means one command to deploy anywhere.'],
                    ['icon' => '🔌', 'title' => 'WordPress Ready', 'description' => 'Extend any WordPress site with powerful admin interfaces.'],
                    ['icon' => '🎯', 'title' => 'MCP Integration', 'description' => 'Native Claude Code support for conversational development.']
                ]
            ],

            'stats' => [
                ['value' => 9, 'suffix' => '', 'label' => 'Templates'],
                ['value' => 11, 'suffix' => '', 'label' => 'Widgets'],
                ['value' => 8, 'suffix' => '', 'label' => 'MCP Tools'],
                ['value' => 100, 'suffix' => '%', 'label' => 'Open Source']
            ],

            'showcase' => [
                'title' => 'See It In Action',
                'description' => 'PHOENIX generates complete, production-ready pages from simple descriptions. The sneaker store demo was built with just one command.',
                'features' => [
                    'Full catalog with 9 products',
                    'Filter sidebar with brands and sizes',
                    'Star ratings and sale badges',
                    'Add to cart functionality',
                    'Responsive grid layout'
                ],
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800'
            ],

            'testimonials' => [
                'title' => 'What Builders Say',
                'items' => [
                    ['quote' => 'This is the most incredible thing I\'ve ever seen! So many possibilities!', 'name' => 'Happy Developer', 'title' => 'PrometheanLink LLC', 'avatar' => 'https://i.pravatar.cc/100?img=1'],
                    ['quote' => 'Finally, a tool that understands what I want to build, not just how to build it.', 'name' => 'Tech Enthusiast', 'title' => 'Startup Founder', 'avatar' => 'https://i.pravatar.cc/100?img=2'],
                    ['quote' => 'The WordPress integration potential is game-changing for agency work.', 'name' => 'Agency Owner', 'title' => 'Web Studio', 'avatar' => 'https://i.pravatar.cc/100?img=3']
                ]
            ],

            'pricing' => [
                'title' => 'Simple Pricing',
                'subtitle' => 'Start free, scale as you grow',
                'plans' => [
                    [
                        'name' => 'Open Source',
                        'price' => '0',
                        'currency' => '$',
                        'period' => 'forever',
                        'description' => 'Full access to the platform',
                        'features' => ['All templates', 'All widgets', 'MCP integration', 'Docker deployment', 'Community support'],
                        'cta_text' => 'Get Started',
                        'cta_url' => '#',
                        'featured' => false
                    ],
                    [
                        'name' => 'Pro',
                        'price' => '49',
                        'currency' => '$',
                        'period' => 'month',
                        'description' => 'For serious builders',
                        'features' => ['Everything in Free', 'Priority support', 'Custom templates', 'White-label option', 'API access'],
                        'cta_text' => 'Coming Soon',
                        'cta_url' => '#',
                        'featured' => true
                    ],
                    [
                        'name' => 'Enterprise',
                        'price' => 'Custom',
                        'currency' => '',
                        'period' => '',
                        'description' => 'For large organizations',
                        'features' => ['Everything in Pro', 'Dedicated support', 'Custom development', 'SLA guarantee', 'On-premise option'],
                        'cta_text' => 'Contact Sales',
                        'cta_url' => '#',
                        'featured' => false
                    ]
                ]
            ],

            'faq' => [
                'title' => 'Frequently Asked Questions',
                'items' => [
                    ['question' => 'What is PHOENIX?', 'answer' => 'PHOENIX is an AI-powered platform builder that transforms natural language descriptions into fully functional web applications. It includes templates, widgets, and MCP integration for conversational development.'],
                    ['question' => 'How does the AI integration work?', 'answer' => 'PHOENIX uses the Model Context Protocol (MCP) to communicate with AI assistants like Claude. You describe what you want, and the AI uses PHOENIX tools to create pages, add widgets, and configure your application.'],
                    ['question' => 'Can I use this with WordPress?', 'answer' => 'Yes! PHOENIX is designed to work as a UI extender for WordPress, allowing admins and users to interact with WordPress data through custom interfaces without using the default admin.'],
                    ['question' => 'Is this production-ready?', 'answer' => 'The core platform is functional and Docker-based for easy deployment. We are actively building additional features like the form builder, installer scripts, and deployment documentation.'],
                    ['question' => 'How do I get started?', 'answer' => 'Clone the repository, run docker-compose up, and visit localhost:8888. Then use Claude Code with the MCP configuration to start building pages conversationally.']
                ]
            ],

            'cta' => [
                'title' => 'Ready to Build Something Incredible?',
                'subtitle' => 'Join the future of AI-powered development',
                'primary_text' => 'Start Building Now',
                'primary_url' => '/en/admin/',
                'secondary_text' => 'View on GitHub',
                'secondary_url' => 'https://github.com/prometheanlink'
            ],

            'footer' => [
                'tagline' => 'Industrial Strength Digital Operations',
                'columns' => [
                    [
                        'title' => 'Product',
                        'links' => [
                            ['label' => 'Features', 'url' => '#features'],
                            ['label' => 'Templates', 'url' => '#templates'],
                            ['label' => 'Pricing', 'url' => '#pricing'],
                            ['label' => 'Roadmap', 'url' => '#']
                        ]
                    ],
                    [
                        'title' => 'Resources',
                        'links' => [
                            ['label' => 'Documentation', 'url' => '#'],
                            ['label' => 'API Reference', 'url' => '#'],
                            ['label' => 'Examples', 'url' => '#'],
                            ['label' => 'Blog', 'url' => '#']
                        ]
                    ],
                    [
                        'title' => 'Company',
                        'links' => [
                            ['label' => 'About', 'url' => '#'],
                            ['label' => 'Contact', 'url' => '#'],
                            ['label' => 'GitHub', 'url' => 'https://github.com/prometheanlink'],
                            ['label' => 'Twitter', 'url' => '#']
                        ]
                    ]
                ]
            ]
        ];
    }
}
?>
