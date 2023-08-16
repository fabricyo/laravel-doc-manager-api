<?php

namespace App\Http\Controllers;

use App\Models\ColumnDocument;
use App\Models\Document;
use App\Rules\RightTypeOfColums;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;


/**
 * @group Documents
 *
 * APIs for managing Documents
 */
class DocumentController extends Controller
{
    /**
     * Index
     *
     * This endpoint is used to see all documents in the system.
     *
     * @response scenario="JsonResponse" {
     * "data": ['all the documents in the system'],
     * }
     */

    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Document::with(['document_type'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|unique:documents|min:3',
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
        try{
            $model = Document::create($request->only(['name', 'document_types_id']));
            foreach ($request->input('column') as $column){
                $col_doc = ColumnDocument::create([
                    'column_id' => $column['id'],
                    'document_id' => $column->id,
                    'content' => $column['content'],
                    ]);
            }

            return response()->json([
                'message' => 'Document created successfully!!',
                'data' => $model,
            ]);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $model = Document::with('document_type')->findOrFail($id);
            $model->data = $model->resumed();
            return response()->json($model);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Document not found'], 400);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Download one document
     *
     * This endpont will download an document with all it's values as a pdf
     *
     * @bodyParam id The Document id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function download(Request $request, $id)
    {
        try {
            $doc = Document::with('document_type')->findOrFail($id);
            $doc->data = $doc->resumed();
            $pdf = Pdf::loadView('document_as_pdf', ['doc' => $doc]);
            return $pdf->download('invoice.pdf');
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Document not found'], 400);
        } catch (\Exception $e){
            echo $e;
            return response()->json(['error' => 'server error'], 500);
        }

    }

    //URL: put

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $model = Document::findOrFail($id);
        //As this is a complex many-to-many relationship, the validation must be as well
        $this->validate($request, [
            'name' => 'min:3|unique:documents,name,'. $id,
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
                Rule::requiredIf(function() use ($request) {
                    return ($request->input('column.*.rel_id') || $request->input('column.*.id'));
                })
            ]
        ]);
        try{
            $model->fill($request->only(['name']))->update();
            if($request->input('column')){
                foreach ($request->input('column') as $column){
                    //Validate if is update of a existing relationship, or adding a new one
                    // If the request is bad formatted, it does nothing
                    if(isset($column['rel_id'])&& isset($column['content'])){
                        $col = ColumnDocument::find($column['rel_id']);
                        $col->content = $column['content'];
                        $col->update();
                    }
                    else if (isset($column['id']) && isset($column['content'])){
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
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Document not found'], 400);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try{
            $model = Document::findOrFail($id);
            //You only wants to delete a column_document relationship
            if ($request->input('rel_id')){
                ColumnDocument::where('id', $request->input('rel_id'))->delete();
                return response()->json([
                    'message' => 'Column Document relationship deleted successfully!!',
                ]);
            }else {
                ColumnDocument::where('document_id', $id)->delete();
                $model->delete();
                return response()->json([
                    'message' => 'Document and columns deleted successfully!!',
                ]);
            }
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Document not found'], 400);
        } catch (QueryException $e){
            Log::error('Document'. $e);
            return response()->json(['error' => "Couldn't delete the Document or Column"], 400);
        } catch (\Exception $e){
            Log::error('Document - '. $e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }


}
