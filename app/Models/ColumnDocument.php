<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ColumnDocument extends Model
{
    use HasFactory;

    protected $table = 'column_document';

    protected $fillable = ['content', 'column_id', 'document_id'];

    public function document_type(): BelongsTo {
        return $this->belongsTo(DocumentType::class, 'document_types_id');
    }
}
