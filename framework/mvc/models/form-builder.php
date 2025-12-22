<?php
/**
 * PHOENIX Form Builder
 * Drag-and-drop form creation interface
 * Template: form-builder
 */

if (!defined('micro_mvc'))
    exit();

class FormBuilder_Model
{
    public static function Get_Data()
    {
        return [
            'phoenix' => true,
            'title' => 'PHOENIX Form Builder',
            'template' => 'form-builder',
            'theme' => 'cyber',

            'config' => [
                'theme' => 'cyber',
                'auto_save' => true,
                'save_interval' => 30000,
                'preview_mode' => true,
                'show_field_ids' => false
            ],

            'field_categories' => [
                ['id' => 'basic', 'name' => 'Basic Fields', 'icon' => 'T'],
                ['id' => 'choice', 'name' => 'Choice Fields', 'icon' => 'V'],
                ['id' => 'advanced', 'name' => 'Advanced Fields', 'icon' => '+'],
                ['id' => 'layout', 'name' => 'Layout Elements', 'icon' => '#']
            ],

            'field_types' => [
                ['type' => 'text', 'label' => 'Text Input', 'icon' => 'T', 'category' => 'basic', 'description' => 'Single line text input'],
                ['type' => 'email', 'label' => 'Email', 'icon' => '@', 'category' => 'basic', 'description' => 'Email address with validation'],
                ['type' => 'password', 'label' => 'Password', 'icon' => '*', 'category' => 'basic', 'description' => 'Secure password input'],
                ['type' => 'number', 'label' => 'Number', 'icon' => '#', 'category' => 'basic', 'description' => 'Numeric input with optional range'],
                ['type' => 'textarea', 'label' => 'Text Area', 'icon' => 'P', 'category' => 'basic', 'description' => 'Multi-line text input'],
                ['type' => 'select', 'label' => 'Dropdown', 'icon' => 'V', 'category' => 'choice', 'description' => 'Single selection dropdown'],
                ['type' => 'radio', 'label' => 'Radio Buttons', 'icon' => 'O', 'category' => 'choice', 'description' => 'Single choice from options'],
                ['type' => 'checkbox', 'label' => 'Checkboxes', 'icon' => 'X', 'category' => 'choice', 'description' => 'Multiple choice selection'],
                ['type' => 'date', 'label' => 'Date Picker', 'icon' => 'D', 'category' => 'advanced', 'description' => 'Calendar date selection'],
                ['type' => 'time', 'label' => 'Time Picker', 'icon' => 'C', 'category' => 'advanced', 'description' => 'Time input with picker'],
                ['type' => 'file', 'label' => 'File Upload', 'icon' => 'F', 'category' => 'advanced', 'description' => 'File upload with validation'],
                ['type' => 'hidden', 'label' => 'Hidden Field', 'icon' => 'H', 'category' => 'advanced', 'description' => 'Invisible field for data'],
                ['type' => 'heading', 'label' => 'Section Heading', 'icon' => 'H1', 'category' => 'layout', 'description' => 'Section title or heading'],
                ['type' => 'paragraph', 'label' => 'Paragraph Text', 'icon' => 'P', 'category' => 'layout', 'description' => 'Descriptive text block'],
                ['type' => 'divider', 'label' => 'Divider', 'icon' => '-', 'category' => 'layout', 'description' => 'Visual separator line']
            ],

            'form_settings' => [
                'name' => 'New Form',
                'description' => '',
                'submit_text' => 'Submit',
                'success_message' => 'Thank you for your submission!',
                'redirect_url' => '',
                'email_notification' => true,
                'notification_email' => '',
                'store_submissions' => true
            ],

            'demo_form' => [
                'id' => 'demo-contact',
                'name' => 'Contact Form',
                'fields' => [
                    ['id' => 'field_1', 'type' => 'heading', 'content' => 'Contact Us'],
                    ['id' => 'field_2', 'type' => 'paragraph', 'content' => 'Fill out the form below and we will get back to you within 24 hours.'],
                    ['id' => 'field_3', 'type' => 'text', 'label' => 'Your Name', 'placeholder' => 'John Doe', 'required' => true],
                    ['id' => 'field_4', 'type' => 'email', 'label' => 'Email Address', 'placeholder' => 'john@example.com', 'required' => true],
                    ['id' => 'field_5', 'type' => 'select', 'label' => 'Subject', 'options' => ['General Inquiry', 'Support', 'Sales', 'Partnership'], 'required' => true],
                    ['id' => 'field_6', 'type' => 'textarea', 'label' => 'Message', 'placeholder' => 'How can we help you?', 'required' => true, 'rows' => 5]
                ]
            ]
        ];
    }
}
