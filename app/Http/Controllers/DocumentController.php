<?php

namespace App\Http\Controllers;

use App\Models\ColumnDocument;
use App\Models\Document;
use App\Rules\RightTypeOfColums;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


/**
 * @group Documents
 *
 * APIs for managing Documents
 */
class DocumentController extends Controller
{
    /**
     * Display a listing of the Documents.
     */

    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Document::with(['document_type'])->get());
    }

    /**
     * Store a new Document.
     * @bodyParam name string required An unique name of the document. Example: My first Document
     * @bodyParam document_types_id int required An valid document type id. Example: 1
     * @bodyParam column object[] required List of Column id and content
     * @bodyParam column[].id int required An valid column_id of the same document type. Example: 1
     * @bodyParam column[].content string required The info that will be stored. Example: Nicolas
     *
     * @response {
     * "message": "Document created successfully!!",
     * "data": {
     * "name": "My fourth document",
     * "document_types_id": "1",
     * "updated_at": "2023-08-16T22:37:26.000000Z",
     * "created_at": "2023-08-16T22:37:26.000000Z",
     * "id": 4
     * }
     * }
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|unique:documents,name|min:3',
            'document_types_id' => 'required|exists:document_types,id',
            'column' => 'required|array',
            'column.*.id' => [
                'required',
                'distinct',
                'exists:columns,id',
                new RightTypeOfColums($request->input('document_types_id'))
            ],
            'column.*.content' => 'required|string|min:3',
        ]);
        //Validation has passed
        try {
            $model = Document::create($request->only(['name', 'document_types_id']));
            foreach ($request->input('column') as $column) {
                $col_doc = ColumnDocument::create([
                    'column_id' => $column['id'],
                    'document_id' => $model->id,
                    'content' => $column['content'],
                ]);
            }

            return response()->json([
                'message' => 'Document created successfully!!',
                'data' => $model,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Display the specified document.
     * @urlParam id int required The document id
     *
     * <aside class="notice">rel_id of each data, is what you use to update the document data. 👍🏻</aside>
     *
     * @response {
     * "id": 1,
     * "created_at": "2023-08-16T22:35:09.000000Z",
     * "updated_at": "2023-08-16T22:35:09.000000Z",
     * "name": "My first document",
     * "document_types_id": 1,
     * "data": [
     * {
     * "name": "First name",
     * "content": "Jonh",
     * "rel_id": 1
     * }
     * ],
     * "document_type": {
     * "id": 1,
     * "created_at": "2023-08-16T22:03:20.000000Z",
     * "updated_at": "2023-08-16T22:12:53.000000Z",
     * "deleted_at": "2023-08-16T22:12:53.000000Z",
     * "name": "Personal Info",
     * "active": 0
     * }
     * }
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $model = Document::with('document_type')->findOrFail($id);
            $model->data = $model->resumed();
            return response()->json($model);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Document not found'], 400);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Download one document
     *
     * This endpont will download a document with all it's values as a pdf
     *
     * @response The Document formatted in a pretty pdf
     */
    public function download(Request $request, $id)
    {
        try {
            $doc = Document::with('document_type')->findOrFail($id);
            $doc->data = $doc->resumed();
            $pdf = Pdf::loadView('document_as_pdf', ['doc' => $doc]);
            return $pdf->download($doc->name . '.pdf');
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Document not found'], 400);
        } catch (\Exception $e) {
            echo $e;
            return response()->json(['error' => 'server error'], 500);
        }

    }


    /**
     * Update the specified document.
     *
     * @bodyParam name string An unique name of the document. Example: My first Document
     * @bodyParam column object[] required List of Column id and content
     * @bodyParam column[].id int An valid column_id of the same document type, required if it's a new info. Example: 1
     * @bodyParam column[].rel_id int  An valid column_document id, required if it's updating an existing info.
     * this can be retrieved using the get document/{id} endpoint can bem Example: 5
     * @bodyParam column[].content string The info that will be stored, required if it's creating/updating a document info. Example: Nicolas
     *
     * @response {
     * "id": 1,
     * "created_at": "2023-08-16T22:35:09.000000Z",
     * "updated_at": "2023-08-16T23:18:06.000000Z",
     * "name": "My first document",
     * "document_types_id": 1,
     * "data": [
     * {
     * "name": "First name",
     * "content": "Yasmin",
     * "rel_id": 1
     * }
     * ]
     *  }
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $model = Document::findOrFail($id);
            //As this is a complex many-to-many relationship, the validation must be as well
            $this->validate($request, [
                'name' => 'min:3|unique:documents,name,' . $id,
                'column' => 'array',
                'column.*.rel_id' => [
                    'required_if:column.*.id,=,null',
                    Rule::exists('column_document', 'id')->where('document_id', $id)
                ],
                'column.*.id' => [
                    'required_if:column.*.content,!=,null',
                    'distinct',
                    'exists:columns,id',
                    new RightTypeOfColums($model->document_types_id),
                    //Can't add 2 equals columns to the same document
                    Rule::unique('column_document', 'column_id')->where('document_id', $id)
                ],
                'column.*.content' => [
                    'string',
                    'min:3',
                    Rule::requiredIf(function () use ($request) {
                        return ($request->input('column.*.rel_id') || $request->input('column.*.id'));
                    })
                ]
            ]);
            $model->fill($request->only(['name']))->update();
            if ($request->input('column')) {
                foreach ($request->input('column') as $column) {
                    //Validate if is update of a existing relationship, or adding a new one
                    // If the request is bad formatted, it does nothing
                    if (isset($column['rel_id']) && isset($column['content'])) {
                        $col = ColumnDocument::find($column['rel_id']);
                        $col->content = $column['content'];
                        $col->update();
                    } else if (isset($column['id']) && isset($column['content'])) {
                        ColumnDocument::create([
                            'column_id' => $column['id'],
                            'document_id' => $model->id,
                            'content' => $column['content'],
                        ]);
                    }
                }
                //Ensures that the model will be marked as updated
                $model->updated_at = now();
                $model->update();
            }

            $model->data = $model->resumed();
            return response()->json($model);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Document not found'], 400);
        } catch (ValidationException $e) {
            return response()->json($e->errors());
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Remove the specified document.
     *
     * <aside class="warning">Document IS NOT softdeleted. ❗️</aside>
     *
     * You can delete only a column data of the document
     * using rel_id => column_document relationship id, this can be retrieved using the get document/{id} endpoint
     *
     * using rel_id will not trigger the document delete
     *
     *
     * @urlParam  id int required The id of the document. Example: 9
     * @bodyParam rel_id int The column_document relationship id
     *
     * @response {
     * "message": "Document deleted successfully!!"
     * }
     */
    public function destroy(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $model = Document::findOrFail($id);
            //You only wants to delete a column_document relationship
            if ($request->input('rel_id')) {
                ColumnDocument::where('id', $request->input('rel_id'))->delete();
                return response()->json([
                    'message' => 'Column Document relationship deleted successfully!!',
                ]);
            } else {
                ColumnDocument::where('document_id', $id)->delete();
                $model->delete();
                return response()->json([
                    'message' => 'Document and columns deleted successfully!!',
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Document not found'], 400);
        } catch (QueryException $e) {
            Log::error('Document' . $e);
            return response()->json(['error' => "Couldn't delete the Document or Column"], 400);
        } catch (\Exception $e) {
            Log::error('Document - ' . $e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }


}
