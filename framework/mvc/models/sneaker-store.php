<?php
/**
 * Sneaker Store
 * Premium sneaker collection catalog
 * Template: catalog
 */

if (!defined('micro_mvc'))
    exit();

class SneakerStore_Model
{
    public static function Get_Data()
    {
        return [
            'phoenix' => true,
            'title' => 'Sneaker Store',
            'template' => 'catalog',
            'theme' => 'cyber',

            // Catalog configuration
            'config' => [
                'theme' => 'cyber',
                'columns' => 3,
                'items_per_page' => 12,
                'show_filters' => true,
                'currency' => '$'
            ],

            // Filter options
            'filters' => [
                [
                    'label' => 'Brand',
                    'options' => [
                        ['value' => 'nike', 'label' => 'Nike', 'count' => 4],
                        ['value' => 'adidas', 'label' => 'Adidas', 'count' => 3],
                        ['value' => 'jordan', 'label' => 'Jordan', 'count' => 2],
                        ['value' => 'yeezy', 'label' => 'Yeezy', 'count' => 2]
                    ]
                ],
                [
                    'label' => 'Size',
                    'options' => [
                        ['value' => '8', 'label' => 'US 8'],
                        ['value' => '9', 'label' => 'US 9'],
                        ['value' => '10', 'label' => 'US 10'],
                        ['value' => '11', 'label' => 'US 11'],
                        ['value' => '12', 'label' => 'US 12']
                    ]
                ]
            ],

            // Products
            'products' => [
                [
                    'id' => 'nk-001',
                    'name' => 'Nike Air Max 90',
                    'price' => 129.99,
                    'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400',
                    'rating' => 5,
                    'review_count' => 128,
                    'badge' => ['type' => 'hot', 'text' => 'Hot']
                ],
                [
                    'id' => 'jd-001',
                    'name' => 'Air Jordan 1 Retro High',
                    'price' => 189.99,
                    'sale_price' => 159.99,
                    'image' => 'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=400',
                    'rating' => 5,
                    'review_count' => 256,
                    'badge' => ['type' => 'sale', 'text' => 'Sale']
                ],
                [
                    'id' => 'ad-001',
                    'name' => 'Adidas Ultraboost 22',
                    'price' => 189.99,
                    'image' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400',
                    'rating' => 4,
                    'review_count' => 89
                ],
                [
                    'id' => 'yz-001',
                    'name' => 'Yeezy Boost 350 V2',
                    'price' => 299.99,
                    'image' => 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?w=400',
                    'rating' => 5,
                    'review_count' => 412,
                    'badge' => ['type' => 'new', 'text' => 'New']
                ],
                [
                    'id' => 'nk-002',
                    'name' => 'Nike Dunk Low',
                    'price' => 109.99,
                    'image' => 'https://images.unsplash.com/photo-1597045566677-8cf032ed6634?w=400',
                    'rating' => 4,
                    'review_count' => 67
                ],
                [
                    'id' => 'ad-002',
                    'name' => 'Adidas Forum Low',
                    'price' => 99.99,
                    'sale_price' => 79.99,
                    'image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400',
                    'rating' => 4,
                    'review_count' => 45,
                    'badge' => ['type' => 'sale', 'text' => '20% Off']
                ],
                [
                    'id' => 'nk-003',
                    'name' => 'Nike Air Force 1 Low',
                    'price' => 119.99,
                    'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400',
                    'rating' => 5,
                    'review_count' => 892
                ],
                [
                    'id' => 'jd-002',
                    'name' => 'Air Jordan 4 Retro',
                    'price' => 219.99,
                    'image' => 'https://images.unsplash.com/photo-1603787081207-362bcef7c144?w=400',
                    'rating' => 5,
                    'review_count' => 334,
                    'badge' => ['type' => 'hot', 'text' => 'Trending']
                ],
                [
                    'id' => 'yz-002',
                    'name' => 'Yeezy 500',
                    'price' => 249.99,
                    'image' => 'https://images.unsplash.com/photo-1584735175315-9d5df23860e6?w=400',
                    'rating' => 4,
                    'review_count' => 156
                ]
            ],

            // Pagination
            'pagination' => [
                'page' => 1,
                'total_pages' => 3,
                'total_items' => 27
            ]
        ];
    }
}
?>
