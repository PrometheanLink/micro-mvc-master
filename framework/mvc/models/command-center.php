<?php
/**
 * Command Center Model
 * Provides data for the PHOENIX Command Center demo dashboard
 */
class CommandCenter_Model
{
    public static function Get_Data()
    {
        return [
            'title' => 'PHOENIX Command Center',
            'subtitle' => 'Real-Time Operations Dashboard',
            'phoenix' => true,
            'stats' => self::Get_Stats(),
            'alerts' => self::Get_Alerts(),
            'activity' => self::Get_Activity(),
            'metrics' => self::Get_Metrics(),
            'systems' => self::Get_Systems()
        ];
    }

    private static function Get_Stats()
    {
        return [
            [
                'title' => 'Active Users',
                'value' => 2847,
                'prefix' => '',
                'suffix' => '',
                'trend' => 'up',
                'trend_value' => '+12.5%',
                'color' => 'cyan',
                'icon' => '👥'
            ],
            [
                'title' => 'API Requests',
                'value' => '1.2M',
                'prefix' => '',
                'suffix' => '/hr',
                'trend' => 'up',
                'trend_value' => '+8.3%',
                'color' => 'green',
                'icon' => '⚡'
            ],
            [
                'title' => 'Response Time',
                'value' => 42,
                'prefix' => '',
                'suffix' => 'ms',
                'trend' => 'down',
                'trend_value' => '-15%',
                'color' => 'orange',
                'icon' => '⏱️'
            ],
            [
                'title' => 'Uptime',
                'value' => '99.98',
                'prefix' => '',
                'suffix' => '%',
                'trend' => 'none',
                'trend_value' => '',
                'color' => 'purple',
                'icon' => '🛡️'
            ],
            [
                'title' => 'Revenue',
                'value' => '847K',
                'prefix' => '$',
                'suffix' => '',
                'trend' => 'up',
                'trend_value' => '+23.1%',
                'color' => 'gold',
                'icon' => '💰'
            ]
        ];
    }

    private static function Get_Alerts()
    {
        return [
            ['type' => 'success', 'message' => 'All systems operational', 'time' => 'Just now', 'icon' => '✓'],
            ['type' => 'info', 'message' => 'Scheduled maintenance in 4 hours', 'time' => '5m ago', 'icon' => 'ℹ'],
            ['type' => 'warning', 'message' => 'High memory usage on Node-7', 'time' => '12m ago', 'icon' => '⚠'],
            ['type' => 'success', 'message' => 'Backup completed successfully', 'time' => '1h ago', 'icon' => '✓'],
            ['type' => 'info', 'message' => 'New deployment pushed to staging', 'time' => '2h ago', 'icon' => '🚀']
        ];
    }

    private static function Get_Activity()
    {
        return [
            ['user' => 'System', 'action' => 'Auto-scaled cluster to 12 nodes', 'time' => '2 min ago', 'status' => 'success'],
            ['user' => 'admin@phoenix.io', 'action' => 'Updated firewall rules', 'time' => '15 min ago', 'status' => 'success'],
            ['user' => 'Monitor', 'action' => 'Health check passed (all regions)', 'time' => '30 min ago', 'status' => 'success'],
            ['user' => 'CI/CD Pipeline', 'action' => 'Deployed v2.4.1 to production', 'time' => '1 hour ago', 'status' => 'success'],
            ['user' => 'Security', 'action' => 'Blocked 847 suspicious requests', 'time' => '2 hours ago', 'status' => 'warning'],
            ['user' => 'Database', 'action' => 'Optimized query cache', 'time' => '3 hours ago', 'status' => 'success']
        ];
    }

    private static function Get_Metrics()
    {
        // Generate realistic-looking time series data
        $cpu = [];
        $memory = [];
        $network = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $labels[] = date('H:i', strtotime("-{$i} minutes"));
            $cpu[] = rand(25, 65);
            $memory[] = rand(55, 75);
            $network[] = rand(20, 80);
        }

        return [
            'labels' => $labels,
            'cpu' => $cpu,
            'memory' => $memory,
            'network' => $network
        ];
    }

    private static function Get_Systems()
    {
        return [
            ['name' => 'API Gateway', 'status' => 'operational', 'load' => 42, 'region' => 'US-East'],
            ['name' => 'Database Cluster', 'status' => 'operational', 'load' => 67, 'region' => 'US-East'],
            ['name' => 'Cache Layer', 'status' => 'operational', 'load' => 31, 'region' => 'Global'],
            ['name' => 'ML Pipeline', 'status' => 'operational', 'load' => 78, 'region' => 'US-West'],
            ['name' => 'Storage', 'status' => 'degraded', 'load' => 89, 'region' => 'EU-West'],
            ['name' => 'CDN', 'status' => 'operational', 'load' => 23, 'region' => 'Global']
        ];
    }
}
?>
