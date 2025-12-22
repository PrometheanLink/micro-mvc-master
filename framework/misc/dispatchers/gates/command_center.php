<?php
    /*
        Command Center Gate - Live data feed for the dashboard

        File name: command_center.php
        Description: AJAX gate for real-time dashboard data

        Coded by Claude AI
        Copyright (C) 2025
        Open Software License (OSL 3.0)
    */

    // Check for direct access
    if (!defined('micro_mvc'))
        exit();

    header('Content-Type: application/json');

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    switch ($action)
    {
        case 'get_stats':
            // System statistics
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'cpu' => rand(15, 85),
                    'memory' => rand(30, 70),
                    'network' => rand(10, 100),
                    'storage' => 65,
                    'requests' => rand(1000, 50000),
                    'active_users' => rand(50, 500),
                    'server_load' => number_format(rand(10, 40) / 10, 2),
                    'timestamp' => date('Y-m-d H:i:s')
                )
            ));
            break;

        case 'get_alerts':
            // Simulated alerts
            $alerts = array(
                array('type' => 'success', 'message' => 'All systems operational', 'time' => date('H:i')),
                array('type' => 'info', 'message' => 'Backup completed successfully', 'time' => date('H:i', strtotime('-15 minutes'))),
                array('type' => 'warning', 'message' => 'High memory usage detected', 'time' => date('H:i', strtotime('-1 hour')))
            );
            echo json_encode(array('success' => true, 'data' => $alerts));
            break;

        case 'get_activity':
            // Simulated activity feed
            $activities = array(
                array('user' => 'System', 'action' => 'Automated backup initiated', 'time' => '2 min ago'),
                array('user' => 'Admin', 'action' => 'Configuration updated', 'time' => '15 min ago'),
                array('user' => 'Monitor', 'action' => 'Health check passed', 'time' => '30 min ago'),
                array('user' => 'System', 'action' => 'Cache cleared', 'time' => '1 hour ago'),
                array('user' => 'Security', 'action' => 'Threat scan completed', 'time' => '2 hours ago')
            );
            echo json_encode(array('success' => true, 'data' => $activities));
            break;

        case 'get_metrics':
            // Historical metrics for charts
            $metrics = array();
            for ($i = 11; $i >= 0; $i--) {
                $metrics[] = array(
                    'time' => date('H:i', strtotime("-{$i} minutes")),
                    'cpu' => rand(20, 80),
                    'memory' => rand(40, 70),
                    'network' => rand(10, 90)
                );
            }
            echo json_encode(array('success' => true, 'data' => $metrics));
            break;

        default:
            echo json_encode(array('success' => false, 'error' => 'Invalid action'));
    }
?>
