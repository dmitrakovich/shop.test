<?php

namespace Database\Seeders;

use App\Models\Feedback;
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
        DB::table('media')->where('model_type', 'App\Models\Feedback')->delete();

        $reviews = DB::connection('old_mysql')
            ->table($this->oldTableName)
            ->where('publish', true)
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
            // 10 -> 5
            $review->rating = ceil($review->rating / 2);

            // фикс для кривых отзывов
            if ($review->user_email == ' Otziv@mail.ru') {
                $review->user_name = 'barocco.by';
                $review->text = 'Отзывы наших благодарных клиентов';
            }
            $review->user_email = trim($review->user_email);

            $feedback = Feedback::create((array) $review);

            if (($imgStartPos = mb_strpos($review->text, '{img}')) !== false) {
                $images = mb_substr($review->text, $imgStartPos + 5); // - {img} (5)
                $images = explode(',', $images);

                $feedback->text = trim(mb_substr($review->text, 0, $imgStartPos));
                $feedback->save();

                foreach ($images as $image) {
                    if ($image == 'undefined') {
                        continue;
                    }

                    $urlToFile = 'https://modny.by/images/comments/' . $image;
                    try {
                        $feedback->addMediaFromUrl($urlToFile)
                            ->preservingOriginal()
                            ->toMediaCollection();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
        }
    }
}
