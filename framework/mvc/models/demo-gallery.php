<?php
/**
 * Demo Gallery
 * Photo gallery showcase
 * Template: gallery
 */

if (!defined('micro_mvc'))
    exit();

class DemoGallery_Model
{
    public static function Get_Data()
    {
        return [
            'phoenix' => true,
            'title' => 'Demo Gallery',
            'template' => 'gallery',
            'theme' => 'cyber',

            'config' => [
                'theme' => 'cyber',
                'layout' => 'masonry',
                'columns' => 4,
                'show_captions' => true,
                'enable_download' => true,
                'enable_share' => true
            ],

            'albums' => [
                ['id' => 'nature', 'name' => 'Nature'],
                ['id' => 'architecture', 'name' => 'Architecture'],
                ['id' => 'technology', 'name' => 'Technology']
            ],

            'items' => [
                ['id' => 'img-001', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400', 'caption' => 'Mountain Peaks at Dawn'],
                ['id' => 'img-002', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=400', 'caption' => 'Modern Skyscraper'],
                ['id' => 'img-003', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=400', 'caption' => 'Circuit Board Macro'],
                ['id' => 'img-004', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=400', 'caption' => 'Foggy Forest Morning'],
                ['id' => 'img-005', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?w=400', 'caption' => 'Urban Glass Facade'],
                ['id' => 'img-006', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=400', 'caption' => 'Server Room'],
                ['id' => 'img-007', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=400', 'caption' => 'Waterfall Paradise'],
                ['id' => 'img-008', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1545558014-8692077e9b5c?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1545558014-8692077e9b5c?w=400', 'caption' => 'Spiral Staircase'],
                ['id' => 'img-009', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=400', 'caption' => 'Robot Assistant'],
                ['id' => 'img-010', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400', 'caption' => 'Tropical Beach'],
                ['id' => 'img-011', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1511818966892-d7d671e672a2?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1511818966892-d7d671e672a2?w=400', 'caption' => 'Concrete Brutalism'],
                ['id' => 'img-012', 'type' => 'image', 'src' => 'https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=800', 'thumbnail' => 'https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=400', 'caption' => 'Laptop Workspace']
            ]
        ];
    }
}
?>
