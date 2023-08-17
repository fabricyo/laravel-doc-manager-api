<?php

namespace App\Rules;

use App\Models\Column;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RightTypeOfColums implements ValidationRule
{
    protected $document_types_id;

    /**
     * @param $document_types_id
     */
    public function __construct($document_types_id)
    {
        $this->document_types_id = $document_types_id;
    }


    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //Checks if the column is of the right document type
        $col = Column::findOrFail($value);
        if ($col->document_types_id != $this->document_types_id) {
            $cols_ids = Column::where('document_types_id', $this->document_types_id)
                ->get()->toArray();
            $arr = implode(",",
                array_map(fn($c) => $c['id'], $cols_ids));
            $fail("$attribute ({$col->name}) is not a valid column for this type of document, "
                . "these are the right columns that you can you use : [$arr]");
        }
    }
}
