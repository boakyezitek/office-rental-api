<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * This property specifies the data types to which certain attributes
     * should be cast when accessed or retrieved from the database. It helps
     * in ensuring consistent data types for specific attributes.
     *
     * - 'price' is cast to an integer.
     * - 'status' is cast to an integer.
     * - 'start_date' and 'end_date' are cast to immutable date instances.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'integer',
        'status' => 'integer',
        'start_date' => 'immutable_date',
        'end_date' => 'immutable_date',
    ];

    /**
     * Define a relationship with the User model.
     *
     * This method establishes a "belongsTo" relationship, indicating that
     * the current model instance belongs to a User. It returns the Eloquent
     * relationship instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define a relationship with the Office model.
     *
     * This method establishes a "belongsTo" relationship, indicating that
     * the current model instance belongs to a User. It returns the Eloquent
     * relationship instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
