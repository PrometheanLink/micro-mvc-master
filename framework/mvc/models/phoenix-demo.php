<?php
/**
 * PHOENIX Demo Page Model
 *
 * Demonstrates the PHOENIX widget and template system.
 */

class PhoenixDemo_Model {

    public static function Get_Data() {
        return [
            'title' => 'PHOENIX Demo',
            'subtitle' => 'The Mother of All Dashboard Builders',

            // Stats for stats-card widgets
            'stats' => [
                [
                    'title' => 'Total Users',
                    'value' => 12847,
                    'icon' => 'users',
                    'color' => 'cyan',
                    'trend' => ['direction' => 'up', 'value' => '+24%', 'period' => 'vs last month']
                ],
                [
                    'title' => 'Revenue',
                    'value' => 84250,
                    'prefix' => '$',
                    'icon' => 'money',
                    'color' => 'green',
                    'trend' => ['direction' => 'up', 'value' => '+18%', 'period' => 'vs last month']
                ],
                [
                    'title' => 'Orders',
                    'value' => 3429,
                    'icon' => 'cart',
                    'color' => 'purple',
                    'trend' => ['direction' => 'up', 'value' => '+12%', 'period' => 'vs last month']
                ],
                [
                    'title' => 'Conversion',
                    'value' => 4.8,
                    'suffix' => '%',
                    'icon' => 'target',
                    'color' => 'pink',
                    'trend' => ['direction' => 'down', 'value' => '-2%', 'period' => 'vs last month']
                ]
            ],

            // Chart data
            'chart' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [
                    ['label' => 'Sales', 'data' => [12, 19, 15, 25, 22, 30], 'color' => '#00d4ff'],
                    ['label' => 'Orders', 'data' => [8, 15, 12, 18, 16, 24], 'color' => '#7b2cbf']
                ]
            ],

            // Progress bars
            'progress' => [
                ['label' => 'Storage Used', 'value' => 72, 'color' => 'cyan'],
                ['label' => 'Bandwidth', 'value' => 45, 'color' => 'green'],
                ['label' => 'CPU Usage', 'value' => 88, 'color' => 'orange'],
                ['label' => 'Memory', 'value' => 61, 'color' => 'purple']
            ],

            // Activity feed
            'activities' => [
                ['user' => 'John Doe', 'action' => 'created', 'target' => 'new dashboard', 'time' => '2 min ago', 'icon' => '✨'],
                ['user' => 'Sarah Smith', 'action' => 'updated', 'target' => 'user profile', 'time' => '15 min ago', 'icon' => '📝'],
                ['user' => 'Mike Johnson', 'action' => 'uploaded', 'target' => '5 new files', 'time' => '1 hour ago', 'icon' => '📤'],
                ['user' => 'Emily Brown', 'action' => 'commented on', 'target' => 'Project Alpha', 'time' => '2 hours ago', 'icon' => '💬'],
                ['user' => 'David Wilson', 'action' => 'completed', 'target' => 'Task #1234', 'time' => '3 hours ago', 'icon' => '✅']
            ],

            // Table data
            'table' => [
                'columns' => [
                    ['key' => 'id', 'label' => 'ID', 'width' => '60px'],
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'status', 'label' => 'Status', 'format' => 'badge'],
                    ['key' => 'revenue', 'label' => 'Revenue', 'format' => 'currency', 'align' => 'right']
                ],
                'data' => [
                    ['id' => 1, 'name' => 'Acme Corp', 'email' => 'contact@acme.com', 'status' => 'Active', 'revenue' => 125000],
                    ['id' => 2, 'name' => 'Globex Inc', 'email' => 'info@globex.com', 'status' => 'Active', 'revenue' => 89000],
                    ['id' => 3, 'name' => 'Stark Industries', 'email' => 'hello@stark.com', 'status' => 'Pending', 'revenue' => 234000],
                    ['id' => 4, 'name' => 'Wayne Enterprises', 'email' => 'info@wayne.com', 'status' => 'Active', 'revenue' => 567000],
                    ['id' => 5, 'name' => 'Umbrella Corp', 'email' => 'contact@umbrella.com', 'status' => 'Inactive', 'revenue' => 45000]
                ]
            ]
        ];
    }
}
?>
