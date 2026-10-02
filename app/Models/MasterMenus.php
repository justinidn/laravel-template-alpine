<?php

namespace App\Models;

use App\Traits\HasMasterActivityLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;

#[Fillable([
    'name',
    'display_name',
])]
class MasterMenus extends Model
{
    use HasFactory, HasMasterActivityLog;

    protected $table = 'master_menus';

    // Hapus komentar di bawah ini jika tabel menggunakan kolom 'is_active'
    /*
    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    */
    public function roles(): HasMany
    {
        return $this->hasMany(Roles::class, 'master_menu_id');
    }
    public function permissions(): HasMany
    {

        return $this->hasMany(Permission::class, 'name', 'name')
            ->where('name', 'like', $this->name . '.%');
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
