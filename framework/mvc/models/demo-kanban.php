<?php
/**
 * Demo Kanban
 * Project management board
 * Template: kanban
 */

if (!defined('micro_mvc'))
    exit();

class DemoKanban_Model
{
    public static function Get_Data()
    {
        return [
            'phoenix' => true,
            'title' => 'PHOENIX Development Board',
            'template' => 'kanban',
            'theme' => 'cyber',

            'config' => [
                'theme' => 'cyber',
                'show_card_count' => true,
                'card_size' => 'normal'
            ],

            'columns' => [
                [
                    'id' => 'backlog',
                    'title' => 'Backlog',
                    'cards' => [
                        [
                            'id' => 'card-001',
                            'title' => 'WordPress REST API Integration',
                            'description' => 'Connect PHOENIX to WordPress for seamless content management',
                            'labels' => [['text' => 'Feature', 'color' => 'blue']],
                            'due_date' => '2024-12-30',
                            'assignees' => [['name' => 'Alex', 'avatar' => 'https://i.pravatar.cc/100?img=1']]
                        ],
                        [
                            'id' => 'card-002',
                            'title' => 'Export to Static HTML',
                            'description' => 'Generate static sites from PHOENIX pages',
                            'labels' => [['text' => 'Feature', 'color' => 'blue']],
                            'assignees' => []
                        ]
                    ]
                ],
                [
                    'id' => 'todo',
                    'title' => 'To Do',
                    'cards' => [
                        [
                            'id' => 'card-003',
                            'title' => 'Form Builder Widget',
                            'description' => 'Drag-and-drop form creation with validation',
                            'labels' => [['text' => 'Feature', 'color' => 'blue'], ['text' => 'Priority', 'color' => 'red']],
                            'due_date' => '2024-12-25',
                            'checklist' => ['done' => 2, 'total' => 5],
                            'assignees' => [['name' => 'Sam', 'avatar' => 'https://i.pravatar.cc/100?img=2']]
                        ],
                        [
                            'id' => 'card-004',
                            'title' => 'User Authentication System',
                            'description' => 'Login, registration, and role-based access',
                            'labels' => [['text' => 'Feature', 'color' => 'blue']],
                            'assignees' => [['name' => 'Jordan', 'avatar' => 'https://i.pravatar.cc/100?img=3']]
                        ]
                    ]
                ],
                [
                    'id' => 'in-progress',
                    'title' => 'In Progress',
                    'cards' => [
                        [
                            'id' => 'card-005',
                            'title' => 'Demo Pages for All Templates',
                            'description' => 'Create showcase pages for gallery, article, kanban, landing',
                            'labels' => [['text' => 'Enhancement', 'color' => 'green']],
                            'due_date' => '2024-12-22',
                            'checklist' => ['done' => 3, 'total' => 4],
                            'comments' => 5,
                            'assignees' => [['name' => 'Claude', 'avatar' => 'https://i.pravatar.cc/100?img=4'], ['name' => 'You', 'avatar' => 'https://i.pravatar.cc/100?img=5']]
                        ]
                    ]
                ],
                [
                    'id' => 'review',
                    'title' => 'Review',
                    'cards' => [
                        [
                            'id' => 'card-006',
                            'title' => 'MCP Server Implementation',
                            'description' => '8 tools for page management via Claude Code',
                            'labels' => [['text' => 'Done', 'color' => 'green']],
                            'checklist' => ['done' => 8, 'total' => 8],
                            'comments' => 12,
                            'assignees' => [['name' => 'Claude', 'avatar' => 'https://i.pravatar.cc/100?img=4']]
                        ]
                    ]
                ],
                [
                    'id' => 'done',
                    'title' => 'Done',
                    'cards' => [
                        [
                            'id' => 'card-007',
                            'title' => 'Catalog Template',
                            'description' => 'E-commerce product grid with filters',
                            'labels' => [['text' => 'Complete', 'color' => 'green']],
                            'assignees' => [['name' => 'Claude', 'avatar' => 'https://i.pravatar.cc/100?img=4']]
                        ],
                        [
                            'id' => 'card-008',
                            'title' => 'Sneaker Store Demo',
                            'description' => 'Working product catalog with 9 sneakers',
                            'labels' => [['text' => 'Complete', 'color' => 'green']],
                            'comments' => 3,
                            'assignees' => [['name' => 'You', 'avatar' => 'https://i.pravatar.cc/100?img=5']]
                        ],
                        [
                            'id' => 'card-009',
                            'title' => 'PrometheanLink Dark Theme',
                            'description' => 'Industrial dark theme with cyber accents',
                            'labels' => [['text' => 'Complete', 'color' => 'green']],
                            'assignees' => []
                        ]
                    ]
                ]
            ]
        ];
    }
}
?>
