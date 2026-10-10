<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'reservation_id',
        'amount',
        'method',
        'status',
        'payment_date',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reservation associated with this payment.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the transactions associated with this payment.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
