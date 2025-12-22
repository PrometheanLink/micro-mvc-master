<?php
    /*
        Media Hub Gate - File upload and management

        File name: media_hub.php
        Description: AJAX gate for media operations

        Coded by Claude AI
        Copyright (C) 2025
        Open Software License (OSL 3.0)
    */

    // Check for direct access
    if (!defined('micro_mvc'))
        exit();

    header('Content-Type: application/json');

    $upload_dir = 'site/uploads/';
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    switch ($action)
    {
        case 'upload':
            if (!isset($_FILES['file'])) {
                echo json_encode(array('success' => false, 'error' => 'No file uploaded'));
                break;
            }

            $file = $_FILES['file'];
            $allowed_types = array(
                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
                'video/mp4', 'video/webm', 'video/ogg',
                'audio/mpeg', 'audio/wav', 'audio/ogg'
            );

            if (!in_array($file['type'], $allowed_types)) {
                echo json_encode(array('success' => false, 'error' => 'File type not allowed'));
                break;
            }

            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('media_') . '_' . time() . '.' . $ext;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $file_info = array(
                    'name' => $filename,
                    'original_name' => $file['name'],
                    'type' => $file['type'],
                    'size' => $file['size'],
                    'url' => '/' . $filepath,
                    'uploaded_at' => date('Y-m-d H:i:s')
                );

                // Determine if image or video
                $is_image = strpos($file['type'], 'image') !== false;
                $is_video = strpos($file['type'], 'video') !== false;
                $file_info['media_type'] = $is_image ? 'image' : ($is_video ? 'video' : 'audio');

                echo json_encode(array('success' => true, 'file' => $file_info));
            } else {
                echo json_encode(array('success' => false, 'error' => 'Failed to save file'));
            }
            break;

        case 'list':
            $files = array();
            if (is_dir($upload_dir)) {
                $items = scandir($upload_dir);
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;

                    $filepath = $upload_dir . $item;
                    $mime = mime_content_type($filepath);
                    $is_image = strpos($mime, 'image') !== false;
                    $is_video = strpos($mime, 'video') !== false;

                    $files[] = array(
                        'name' => $item,
                        'url' => '/' . $filepath,
                        'type' => $mime,
                        'media_type' => $is_image ? 'image' : ($is_video ? 'video' : 'audio'),
                        'size' => filesize($filepath),
                        'modified' => filemtime($filepath)
                    );
                }
                // Sort by modified time, newest first
                usort($files, function($a, $b) {
                    return $b['modified'] - $a['modified'];
                });
            }
            echo json_encode(array('success' => true, 'files' => $files));
            break;

        case 'delete':
            $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
            $filepath = $upload_dir . basename($filename);

            if (file_exists($filepath) && unlink($filepath)) {
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('success' => false, 'error' => 'Failed to delete file'));
            }
            break;

        case 'save_recording':
            // Handle base64 encoded recordings from screen/webcam
            $data = isset($_POST['data']) ? $_POST['data'] : '';
            $type = isset($_POST['type']) ? $_POST['type'] : 'webm';

            if (empty($data)) {
                echo json_encode(array('success' => false, 'error' => 'No data received'));
                break;
            }

            // Remove data URL prefix if present
            if (strpos($data, 'base64,') !== false) {
                $data = explode('base64,', $data)[1];
            }

            $decoded = base64_decode($data);
            $filename = 'recording_' . time() . '.' . $type;
            $filepath = $upload_dir . $filename;

            if (file_put_contents($filepath, $decoded)) {
                echo json_encode(array(
                    'success' => true,
                    'file' => array(
                        'name' => $filename,
                        'url' => '/' . $filepath,
                        'type' => 'video/' . $type,
                        'media_type' => 'video'
                    )
                ));
            } else {
                echo json_encode(array('success' => false, 'error' => 'Failed to save recording'));
            }
            break;

        case 'save_snapshot':
            // Handle webcam snapshots
            $data = isset($_POST['data']) ? $_POST['data'] : '';

            if (empty($data)) {
                echo json_encode(array('success' => false, 'error' => 'No data received'));
                break;
            }

            // Remove data URL prefix
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $decoded = base64_decode($data);

            $filename = 'snapshot_' . time() . '.png';
            $filepath = $upload_dir . $filename;

            if (file_put_contents($filepath, $decoded)) {
                echo json_encode(array(
                    'success' => true,
                    'file' => array(
                        'name' => $filename,
                        'url' => '/' . $filepath,
                        'type' => 'image/png',
                        'media_type' => 'image'
                    )
                ));
            } else {
                echo json_encode(array('success' => false, 'error' => 'Failed to save snapshot'));
            }
            break;

        default:
            echo json_encode(array('success' => false, 'error' => 'Invalid action'));
    }
?>
