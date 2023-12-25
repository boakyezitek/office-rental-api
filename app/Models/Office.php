<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * This property specifies the data types to which certain attributes
     * should be cast when accessed or retrieved from the database. It helps
     * in ensuring consistent data types for specific attributes.
     *
     * @var array
     */
    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'approval_status' => 'integer',
        'hidden' => 'bool',
        'price_per_day' => 'integer',
        'monthly_discount' => 'integer',
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
     * Define a one-to-many relationship with the Reservation model.
     *
     * This method establishes a "hasMany" relationship, indicating that
     * the current model instance has multiple associated Reservation models.
     * It returns the Eloquent relationship instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservation(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Define a polymorphic one-to-many relationship with Image models.
     *
     * This method establishes a "morphMany" relationship, indicating that
     * the current model instance can have multiple associated Image models.
     * It returns the Eloquent relationship instance using polymorphism,
     * associating the images with a specific resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'resource');
    }

}
