<?php

use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['private', 'private', 'private', 'group', 'group'];
        
        for($i = 1; $i <= count($types); $i++) {
            $groupName = "用户组$i";
            DB::table('groups')->insert([
                'name' => $groupName,
                'type' => $types[$i - 1],
                'conv_id' => str_random(24),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
