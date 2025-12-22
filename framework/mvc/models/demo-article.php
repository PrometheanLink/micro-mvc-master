<?php
/**
 * Demo Article
 * Blog post showcase
 * Template: article
 */

if (!defined('micro_mvc'))
    exit();

class DemoArticle_Model
{
    public static function Get_Data()
    {
        return [
            'phoenix' => true,
            'title' => 'Demo Article',
            'template' => 'article',
            'theme' => 'cyber',

            'config' => [
                'theme' => 'cyber',
                'show_hero' => true,
                'show_author' => true,
                'show_date' => true,
                'show_toc' => true,
                'show_share' => true,
                'show_comments' => true,
                'show_related' => true
            ],

            'article' => [
                'title' => 'Building the Future with AI-Powered Development',
                'excerpt' => 'Discover how artificial intelligence is revolutionizing the way we build software, from code generation to intelligent debugging.',
                'hero_image' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=1200',
                'category' => 'Technology',
                'date' => '2024-12-21',
                'read_time' => 8,
                'tags' => ['AI', 'Development', 'Future Tech', 'Automation', 'PHOENIX'],
                'content' => '
                    <h2>The Dawn of AI-Assisted Development</h2>
                    <p>The landscape of software development is undergoing a profound transformation. With tools like PHOENIX, developers can now describe what they want in plain English and watch as entire applications materialize before their eyes.</p>

                    <p>This isn\'t science fiction—it\'s happening right now. The combination of large language models, sophisticated code generation, and intuitive interfaces has created a new paradigm where the barrier between idea and implementation is thinner than ever.</p>

                    <blockquote>
                        "The best code is the code you don\'t have to write." — Every Developer Ever
                    </blockquote>

                    <h2>What Makes PHOENIX Different</h2>
                    <p>Unlike traditional page builders that limit you to predefined blocks and layouts, PHOENIX understands <strong>intent</strong>. Tell it you need a dashboard for tracking fitness goals, and it doesn\'t just give you empty widgets—it creates a complete, functional solution with appropriate charts, metrics, and data structures.</p>

                    <h3>Key Features</h3>
                    <ul>
                        <li>Natural language page creation</li>
                        <li>Intelligent template selection</li>
                        <li>Automatic data structure generation</li>
                        <li>Real-time preview and editing</li>
                        <li>WordPress integration ready</li>
                    </ul>

                    <h2>The Technical Architecture</h2>
                    <p>At its core, PHOENIX uses the Model Context Protocol (MCP) to communicate with AI assistants like Claude. This enables a seamless conversation between you and the platform, where complex operations are reduced to simple requests.</p>

                    <pre><code>// Creating a page is as simple as:
phoenix_create_page({
    name: "Sales Dashboard",
    template: "dashboard",
    widgets: ["stat-card", "chart-bar", "activity-feed"]
});</code></pre>

                    <h2>Looking Ahead</h2>
                    <p>The future of PHOENIX includes even deeper AI integration, allowing the system to not just build what you ask for, but to suggest improvements, optimize performance, and even predict what you might need next.</p>

                    <p>We\'re not just building a tool—we\'re building a partner in creation.</p>
                '
            ],

            'author' => [
                'name' => 'PrometheanLink Team',
                'title' => 'Industrial Strength Digital Operations',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100',
                'bio' => 'Building the future of AI-powered development tools. We believe technology should amplify human creativity, not replace it.',
                'social' => [
                    'twitter' => 'https://twitter.com/prometheanlink',
                    'github' => 'https://github.com/prometheanlink'
                ]
            ],

            'related' => [
                ['title' => 'Getting Started with PHOENIX Templates', 'excerpt' => 'A comprehensive guide to the template system.', 'url' => '#', 'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=400'],
                ['title' => 'Building Custom Widgets', 'excerpt' => 'Extend PHOENIX with your own widget library.', 'url' => '#', 'image' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=400'],
                ['title' => 'MCP Integration Deep Dive', 'excerpt' => 'Understanding the protocol behind the magic.', 'url' => '#', 'image' => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=400']
            ]
        ];
    }
}
?>
