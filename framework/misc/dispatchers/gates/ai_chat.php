<?php
    /*
        AI Chat Gate - Handles AI chat interactions for dashboard widgets

        File name: ai_chat.php
        Description: AJAX gate for AI chat widget integration

        This gate provides a simple AI response simulation by default.
        Configure AI_CHAT_API_URL and AI_CHAT_API_KEY in your environment
        to connect to a real AI provider (OpenAI, Anthropic, etc.)

        Coded by Claude AI for PHOENIX Dashboard Builder
        Copyright (C) 2025
        Open Software License (OSL 3.0)
    */

    // Check for direct access
    if (!defined('micro_mvc'))
        exit();

    header('Content-Type: text/plain; charset=utf-8');

    // Get POST data
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $system_prompt = isset($_POST['system_prompt']) ? trim($_POST['system_prompt']) : '';
    $action = isset($_POST['action']) ? $_POST['action'] : 'chat';

    if (empty($message) && $action === 'chat') {
        echo "I didn't receive a message. Please try again.";
        exit();
    }

    // Check for real AI configuration
    $api_url = defined('AI_CHAT_API_URL') ? AI_CHAT_API_URL : getenv('AI_CHAT_API_URL');
    $api_key = defined('AI_CHAT_API_KEY') ? AI_CHAT_API_KEY : getenv('AI_CHAT_API_KEY');

    if ($api_url && $api_key) {
        // Use real AI API
        $response = call_ai_api($api_url, $api_key, $message, $system_prompt);
        echo $response;
    } else {
        // Demo mode - intelligent simulation
        echo generate_demo_response($message, $system_prompt);
    }

    /**
     * Call external AI API (OpenAI-compatible)
     */
    function call_ai_api($url, $key, $message, $system_prompt) {
        $messages = [];

        if (!empty($system_prompt)) {
            $messages[] = ['role' => 'system', 'content' => $system_prompt];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $data = [
            'model' => defined('AI_CHAT_MODEL') ? AI_CHAT_MODEL : 'gpt-4',
            'messages' => $messages,
            'max_tokens' => 500
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "Connection error. Please try again.";
        }

        $json = json_decode($result, true);
        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        } elseif (isset($json['error']['message'])) {
            return "API Error: " . $json['error']['message'];
        }

        return "Unexpected response format.";
    }

    /**
     * Generate demo response when no AI API is configured
     */
    function generate_demo_response($message, $system_prompt) {
        $message_lower = strtolower($message);

        // Context-aware responses based on system prompt
        $context = '';
        if (stripos($system_prompt, 'support') !== false || stripos($system_prompt, 'help') !== false) {
            $context = 'support';
        } elseif (stripos($system_prompt, 'sales') !== false || stripos($system_prompt, 'product') !== false) {
            $context = 'sales';
        } elseif (stripos($system_prompt, 'technical') !== false || stripos($system_prompt, 'developer') !== false) {
            $context = 'technical';
        }

        // Greetings
        if (preg_match('/^(hi|hello|hey|greetings)/i', $message)) {
            $greetings = [
                "Hello! How can I assist you today?",
                "Hi there! I'm here to help. What can I do for you?",
                "Greetings! What would you like to know?",
                "Hello! I'm your AI assistant. How may I help?"
            ];
            return $greetings[array_rand($greetings)];
        }

        // Questions about capabilities
        if (preg_match('/(what can you|help me|what do you)/i', $message)) {
            return "I can help you with:\n- Answering questions about the system\n- Providing information and guidance\n- Assisting with tasks and workflows\n- Explaining features and functionality\n\nJust ask me anything!";
        }

        // Dashboard/system questions
        if (preg_match('/(dashboard|widget|chart|data)/i', $message)) {
            return "This dashboard is built with the PHOENIX system on micro-MVC. You can customize widgets, configure data sources, set up refresh intervals, and create interactive visualizations. Need help with a specific widget type?";
        }

        // Status inquiries
        if (preg_match('/(status|how are|working)/i', $message)) {
            return "All systems are operational. Current uptime is excellent and all services are responding normally.";
        }

        // Thanks
        if (preg_match('/(thank|thanks|appreciate)/i', $message)) {
            return "You're welcome! Let me know if you need anything else.";
        }

        // Goodbye
        if (preg_match('/(bye|goodbye|see you|later)/i', $message)) {
            return "Goodbye! Feel free to come back anytime you need assistance.";
        }

        // Context-specific defaults
        switch ($context) {
            case 'support':
                return "I understand you're looking for help. Could you provide more details about your issue? I'll do my best to assist or connect you with the right resources.";
            case 'sales':
                return "Thanks for your interest! I'd be happy to tell you more about our products and services. What specific information are you looking for?";
            case 'technical':
                return "I'm here to help with technical questions. Please describe the issue or what you're trying to accomplish, and I'll provide guidance.";
        }

        // Default responses
        $defaults = [
            "I understand. Could you tell me more about what you're looking for?",
            "That's an interesting question. Let me help you with that - could you provide a bit more context?",
            "I'm processing your request. Is there anything specific you'd like me to focus on?",
            "Thanks for reaching out. How can I best assist you with this?"
        ];

        return $defaults[array_rand($defaults)];
    }
?>
