<?php

namespace App\Models;

use App\Traits\HasMasterActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Roles extends SpatieRole
{
    use HasFactory, HasMasterActivityLog;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function masterMenu(): BelongsTo
    {
        return $this->belongsTo(MasterMenus::class, 'master_menu_id');
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
