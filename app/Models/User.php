<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /**
     * The table associated with the model.
     * Memberi tahu Laravel untuk menggunakan tabel 'sis_user'.
     *
     * @var string
     */
    protected $table = 'sis_user';

    /**
     * Indicates if the model should be timestamped.
     * Memberi tahu Laravel bahwa tabel ini tidak punya kolom created_at & updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The groups that belong to the user.
     * Mendefinisikan relasi "many-to-many" ke model Group
     * melalui tabel perantara 'sis_usergroup'.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'sis_usergroup', 'user_id', 'group_id');
    }
}

