<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = ['name', 'document_types_id'];

    public function document_type(): BelongsTo {
        //As Doc type is softDelete, I can might need get it as withTrashed
        return $this->belongsTo(DocumentType::class, 'document_types_id')->withTrashed();
    }

    public function resumed() {
        return ColumnDocument::select(['name', 'content', 'column_document.id as rel_id'])
            ->join('columns', 'column_document.column_id', '=', 'columns.id')
            ->where('document_id', $this->id)
            ->orderBy('columns.id')
            ->get();
    }

}
