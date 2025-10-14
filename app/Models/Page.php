<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    /**
     * The table associated with the model.
     * Memberi tahu Laravel untuk menggunakan tabel 'sis_page'.
     *
     * @var string
     */
    protected $table = 'sis_page';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the child pages for the page.
     * Mendefinisikan relasi "one-to-many" ke model itu sendiri
     * untuk membuat struktur menu bertingkat (parent-child).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id', 'id')->orderBy('ordering');
    }
}

