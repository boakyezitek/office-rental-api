<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    use HasFactory;

    /**
     * Define a polymorphic relationship with various resource types.
     *
     * This method establishes a "morphTo" relationship, indicating that
     * the current model instance can belong to various other models. It
     * returns the Eloquent relationship instance using polymorphism.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function resource(): MorphTo
    {
        return $this->morphTo();
    }
}
