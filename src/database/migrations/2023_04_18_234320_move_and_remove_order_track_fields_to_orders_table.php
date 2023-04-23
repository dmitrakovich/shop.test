<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Orders\Order;
use App\Models\Orders\OrderTrack;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $orders = Order::select('id', 'delivery_track', 'delivery_track_link')->where(function ($query) {
            $query->whereNotNull('delivery_track')->orWhereNotNull('delivery_track_link');
        })->get();
        foreach ($orders as $order) {
            OrderTrack::create([
                'track_number' => $order->delivery_track,
                'track_link' => $order->delivery_track_link,
                'order_id' => $order->id,
            ]);
        }
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_track');
            $table->dropColumn('delivery_track_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_track', 128)->nullable();
            $table->string('delivery_track_link')->nullable();
        });
        $orderTracks = OrderTrack::whereNotNull('order_id')->get();
        foreach ($orderTracks as $orderTrack) {
            Order::where('id', $orderTrack->order_id)->update([
                'delivery_track' => $orderTrack->track_number,
                'delivery_track_link' => $orderTrack->track_link
            ]);
            $orderTrack->delete();
        }
    }
};
