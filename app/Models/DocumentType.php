<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'document_types';

    protected $fillable = ['name', 'active'];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'document_types_id');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class, 'document_types_id');
    }
}
