<?php
/**
 * Nature Watch Model
 * Provides data for the PHOENIX Wildlife Tracker
 */
class NatureWatch_Model
{
    public static function Get_Data()
    {
        return [
            'title' => 'Nature Watch',
            'subtitle' => 'Community Wildlife Tracker',
            'phoenix' => true,
            'species_categories' => self::Get_Species_Categories(),
            'recent_sightings' => self::Get_Recent_Sightings(10),
            'stats' => self::Get_Stats()
        ];
    }

    /**
     * Get species organized by category
     */
    public static function Get_Species_Categories()
    {
        return [
            'mammals' => [
                'icon' => '🦌',
                'label' => 'Mammals',
                'species' => [
                    'White-tailed Deer',
                    'Mule Deer',
                    'Eastern Cottontail Rabbit',
                    'Eastern Gray Squirrel',
                    'Fox Squirrel',
                    'Raccoon',
                    'Striped Skunk',
                    'Virginia Opossum',
                    'Red Fox',
                    'Gray Fox',
                    'Coyote',
                    'Groundhog',
                    'Eastern Chipmunk',
                    'White-footed Mouse',
                    'Beaver',
                    'Muskrat',
                    'River Otter',
                    'Bobcat',
                    'Black Bear'
                ]
            ],
            'birds' => [
                'icon' => '🦅',
                'label' => 'Birds',
                'species' => [
                    'Red-tailed Hawk',
                    'Cooper\'s Hawk',
                    'Bald Eagle',
                    'Great Horned Owl',
                    'Barred Owl',
                    'Northern Cardinal',
                    'Blue Jay',
                    'American Robin',
                    'American Crow',
                    'Common Raven',
                    'Red-bellied Woodpecker',
                    'Downy Woodpecker',
                    'Ruby-throated Hummingbird',
                    'Wild Turkey',
                    'Northern Bobwhite',
                    'Canada Goose',
                    'Mallard Duck',
                    'Great Blue Heron',
                    'Green Heron',
                    'Turkey Vulture',
                    'Mourning Dove',
                    'American Goldfinch',
                    'House Finch',
                    'Carolina Chickadee',
                    'Tufted Titmouse',
                    'White-breasted Nuthatch',
                    'Eastern Bluebird'
                ]
            ],
            'reptiles' => [
                'icon' => '🐍',
                'label' => 'Reptiles & Amphibians',
                'species' => [
                    'Eastern Garter Snake',
                    'Black Rat Snake',
                    'Copperhead',
                    'Eastern Box Turtle',
                    'Snapping Turtle',
                    'Painted Turtle',
                    'Five-lined Skink',
                    'American Toad',
                    'Green Frog',
                    'Bullfrog',
                    'Spring Peeper',
                    'Spotted Salamander',
                    'Red-backed Salamander'
                ]
            ],
            'insects' => [
                'icon' => '🦋',
                'label' => 'Insects & Arachnids',
                'species' => [
                    'Monarch Butterfly',
                    'Eastern Tiger Swallowtail',
                    'Black Swallowtail',
                    'Painted Lady',
                    'Luna Moth',
                    'Cecropia Moth',
                    'Honeybee',
                    'Bumblebee',
                    'Carpenter Bee',
                    'Dragonfly',
                    'Damselfly',
                    'Firefly/Lightning Bug',
                    'Praying Mantis',
                    'Garden Spider',
                    'Cicada'
                ]
            ],
            'other' => [
                'icon' => '🔍',
                'label' => 'Other/Unknown',
                'species' => [
                    'Unknown Animal',
                    'Other (describe in notes)'
                ]
            ]
        ];
    }

    /**
     * Get recent sightings from database
     */
    private static function Get_Recent_Sightings($limit = 10)
    {
        $db_conn = DB::Use_Connection();
        if (empty($db_conn)) {
            return [];
        }

        $query = "SELECT * FROM wildlife_sightings ORDER BY sighting_date DESC LIMIT " . intval($limit);
        $result = DB::Exec_SQL_Command($query);

        return $result ?: [];
    }

    /**
     * Get basic stats for the dashboard
     */
    private static function Get_Stats()
    {
        $db_conn = DB::Use_Connection();
        if (empty($db_conn)) {
            return ['total' => 0, 'today' => 0, 'week' => 0];
        }

        $total_query = "SELECT COUNT(*) as total FROM wildlife_sightings";
        $today_query = "SELECT COUNT(*) as today FROM wildlife_sightings WHERE DATE(sighting_date) = CURDATE()";
        $week_query = "SELECT COUNT(*) as week FROM wildlife_sightings WHERE sighting_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";

        $total = DB::Exec_SQL_Command($total_query);
        $today = DB::Exec_SQL_Command($today_query);
        $week = DB::Exec_SQL_Command($week_query);

        return [
            'total' => $total ? intval($total[0]['total']) : 0,
            'today' => $today ? intval($today[0]['today']) : 0,
            'week' => $week ? intval($week[0]['week']) : 0
        ];
    }

    /**
     * Get category icon by name
     */
    public static function Get_Category_Icon($category)
    {
        $icons = [
            'mammals' => '🦌',
            'birds' => '🦅',
            'reptiles' => '🐍',
            'insects' => '🦋',
            'other' => '🔍'
        ];
        return $icons[$category] ?? '🔍';
    }
}
?>
