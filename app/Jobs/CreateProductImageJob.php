<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Product object
     *
     * @var Product
     */
    private Product $product;
    /**
     * Путь к картинке
     *
     * @var string
     */
    private string $path;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product, string $path)
    {
        $this->product = $product;
        $this->path = $path;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // logs()->info($this->product);
        // logs()->info($this->path);
        if (\Str::startsWith($this->path, ['http://', 'https://'])) {
            $this->product
                ->addMediaFromUrl($this->path)
                ->preservingOriginal()
                ->toMediaCollection();
        } elseif (file_exists($this->path)) {
            $this->product
                ->addMedia($this->path)
                ->preservingOriginal()
                ->toMediaCollection();
        }
    }
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    // public function failed(Throwable $exception)
    // {
    //     // Send user notification of failure, etc...
    // }
}
