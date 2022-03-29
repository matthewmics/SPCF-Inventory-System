<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Room;
use App\Models\Building;
use App\Models\DepartmentBuilding;
use App\Models\InventoryParentItem;
use App\Models\InventoryItem;
use Carbon\Carbon;

class SeedController extends Controller
{
    public function seed()
    {
        User::insert([
            [
                'name' => 'Phillip Rose',
                'email' => 'department@localhost.com',
                'role' => 'department',
                'password' => bcrypt('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Wesley Anderson',
                'email' => 'department2@localhost.com',
                'role' => 'department',
                'password' => bcrypt('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Richard Smith',
                'email' => 'its@localhost.com',
                'role' => 'its',
                'password' => bcrypt('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Lucas Thomas',
                'email' => 'ppfo@localhost.com',
                'role' => 'ppfo',
                'password' => bcrypt('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        Building::insert([
            [
                'name' => 'Building 001',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Building 002',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Building 003',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        DepartmentBuilding::insert([
            [
                'user_id' => 2,
                'building_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'user_id' => 2,
                'building_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'user_id' => 3,
                'building_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        Room::insert([
            [
                'name' => 'Room-A',
                'room_type' => 'room',
                'building_id' => 1, 
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Room-B',
                'room_type' => 'room',
                'building_id' => 2, 
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Lab-A',
                'room_type' => 'lab',
                'building_id' => 3, 
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        InventoryParentItem::insert([
            [
                'name' => 'Monitor',
                'item_type' => 'PC',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Table',
                'item_type' => 'Fixture',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Keyboard',
                'item_type' => 'PC',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);

        // monitor items
        InventoryItem::insert([
            [
                'serial_number' => '6988606763',
                'inventory_parent_item_id' => 1,
                'brand' => 'Brand-A',
                'room_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'serial_number' => '9671308195',
                'inventory_parent_item_id' => 1,
                'brand' => 'Brand-A',
                'room_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'serial_number' => '8526093502',
                'inventory_parent_item_id' => 1,
                'brand' => 'Brand-A',
                'room_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
        // keyboard items
        InventoryItem::insert([
            [
                'serial_number' => '4725141495',
                'inventory_parent_item_id' => 2,
                'brand' => 'Brand-A',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'serial_number' => '7823226948',
                'inventory_parent_item_id' => 2,
                'brand' => 'Brand-A',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'serial_number' => '4045924363',
                'inventory_parent_item_id' => 2,
                'brand' => 'Brand-A',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
        // fixture items
        InventoryItem::insert([
            [
                'serial_number' => '9014579132',
                'inventory_parent_item_id' => 3,
                'room_id' => 1,
                'brand' => 'Brand-B',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'serial_number' => '2374982187',
                'inventory_parent_item_id' => 3,
                'room_id' => 1,
                'brand' => 'Brand-B',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'serial_number' => '2823771691',
                'inventory_parent_item_id' => 3,
                'room_id' => 2,
                'brand' => 'Brand-B',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]

        ]);

        return "Seed data applied!";
    }
}
