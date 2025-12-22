<?php
/**
 * Sales Dashboard - A fully working PHOENIX dashboard
 *
 * This model demonstrates how to structure data for PHOENIX widgets.
 * Each widget gets its own configuration in the slots array.
 */

class SalesDashboard_Model {

    public static function Get_Data() {
        return [
            'phoenix' => true,  // Flag to bypass micro-MVC wrapper
            'title' => 'Sales Dashboard',
            'theme' => 'cyber',

            // Stats cards for the top row
            'stats' => [
                [
                    'id' => 'stat-revenue',
                    'title' => 'Total Revenue',
                    'value' => 284500,
                    'prefix' => '$',
                    'icon' => 'money',
                    'color' => 'cyan',
                    'trend' => ['direction' => 'up', 'value' => '+18.2%', 'period' => 'vs last month']
                ],
                [
                    'id' => 'stat-orders',
                    'title' => 'Orders',
                    'value' => 1847,
                    'icon' => 'cart',
                    'color' => 'purple',
                    'trend' => ['direction' => 'up', 'value' => '+12.5%', 'period' => 'vs last month']
                ],
                [
                    'id' => 'stat-customers',
                    'title' => 'Customers',
                    'value' => 3429,
                    'icon' => 'users',
                    'color' => 'green',
                    'trend' => ['direction' => 'up', 'value' => '+8.1%', 'period' => 'vs last month']
                ],
                [
                    'id' => 'stat-conversion',
                    'title' => 'Conversion',
                    'value' => 3.24,
                    'suffix' => '%',
                    'icon' => 'target',
                    'color' => 'orange',
                    'trend' => ['direction' => 'down', 'value' => '-0.4%', 'period' => 'vs last month']
                ]
            ],

            // Chart data
            'chart' => [
                'title' => 'Revenue Trend',
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [
                    ['label' => 'Revenue', 'data' => [42000, 48000, 45000, 52000, 58000, 64000], 'color' => '#00d4ff'],
                    ['label' => 'Expenses', 'data' => [28000, 32000, 30000, 35000, 38000, 42000], 'color' => '#ff006e']
                ]
            ],

            // Progress bars
            'progress' => [
                ['label' => 'Sales Target', 'value' => 78, 'color' => 'cyan', 'target' => '$300,000'],
                ['label' => 'New Customers', 'value' => 92, 'color' => 'green', 'target' => '500'],
                ['label' => 'Retention Rate', 'value' => 85, 'color' => 'purple', 'target' => '90%'],
                ['label' => 'Support Tickets', 'value' => 45, 'color' => 'orange', 'target' => '< 50']
            ],

            // Activity feed
            'activities' => [
                ['user' => 'Sarah Chen', 'action' => 'closed deal with', 'target' => 'Acme Corp ($45,000)', 'time' => '5 min ago', 'icon' => '💰'],
                ['user' => 'Mike Ross', 'action' => 'added new lead', 'target' => 'TechStart Inc', 'time' => '12 min ago', 'icon' => '🎯'],
                ['user' => 'Lisa Park', 'action' => 'sent proposal to', 'target' => 'GlobalTech', 'time' => '28 min ago', 'icon' => '📄'],
                ['user' => 'James Wilson', 'action' => 'completed demo for', 'target' => 'Innovate Labs', 'time' => '1 hour ago', 'icon' => '🎬'],
                ['user' => 'Emma Davis', 'action' => 'renewed contract with', 'target' => 'DataFlow ($28,000)', 'time' => '2 hours ago', 'icon' => '🔄']
            ],

            // Top customers table
            'customers' => [
                ['rank' => 1, 'name' => 'Acme Corporation', 'revenue' => 125000, 'orders' => 47, 'status' => 'Active'],
                ['rank' => 2, 'name' => 'TechGiant Inc', 'revenue' => 98000, 'orders' => 35, 'status' => 'Active'],
                ['rank' => 3, 'name' => 'GlobalSoft', 'revenue' => 87500, 'orders' => 28, 'status' => 'Active'],
                ['rank' => 4, 'name' => 'DataDrive LLC', 'revenue' => 76000, 'orders' => 22, 'status' => 'Pending'],
                ['rank' => 5, 'name' => 'CloudBase', 'revenue' => 65000, 'orders' => 19, 'status' => 'Active']
            ]
        ];
    }
}
?>
