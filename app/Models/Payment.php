<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'amount',
        'payment_method',
        'reference_number',
        'status',
    ];

    /**
     * Get the queue associated with the payment.
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }
}
