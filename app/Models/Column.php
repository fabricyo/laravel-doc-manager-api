<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Column extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'columns';

    protected $dates = ['deleted_at'];

    protected $fillable = ['name', 'document_types_id'];

    public function document_type(): BelongsTo {
        return $this->belongsTo(DocumentType::class, 'document_types_id');
    }
}
