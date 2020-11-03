<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackSeeder extends Seeder
{
    protected string $tableName = 'feedbacks';
    protected string $oldTableName = 'cyizj_jshopping_products_reviews';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table($this->tableName)->truncate();

        $reviews = DB::connection('old_mysql')
            ->table($this->oldTableName)
            ->get([
                'product_id',
                'user_id',
                'user_name',
                'user_email',
                'time as created_at',
                'review as text',
                'mark as rating',
                'publish',
                'ip',
            ]);

        foreach ($reviews as $review) {
            DB::table($this->tableName)->insert((array)$review);
        }
    }
}
