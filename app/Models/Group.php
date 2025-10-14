<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * The table associated with the model.
     * Memberi tahu Laravel untuk menggunakan tabel 'sis_group'.
     *
     * @var string
     */
    protected $table = 'sis_group';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}

