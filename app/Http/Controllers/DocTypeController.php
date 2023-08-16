<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


/**
 * @group Document Types
 *
 * APIs for managing the Documents Types, the base of the API
 */
class DocTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(DocumentType::with('columns')->get());
    }

    /**
     * Store a new document type.
     * @bodyParam name string required An unique name of the document type. Example: Personal Info
     * @bodyParam active boolean If the document is to be created deactivated. Example: 0
     *
     * @response {
     *  "message": "Document type created successfully!!",
     *  "data": {
     *  "name": "Personal Info",
     *  "updated_at": "2023-08-16T22:03:20.000000Z",
     *  "created_at": "2023-08-16T22:03:20.000000Z",
     *  "id": 1
     * }
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:document_types|min:3',
            'active' => 'boolean'
        ]);
        //Validation has passed
        try{
            $model = DocumentType::create($request->only(['name', 'active']));
            return response()->json([
                'message' => 'Document type created successfully!!',
                'data' => $model,
            ]);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Display the specified document type.
     * @urlParam id int required The document type id
     *
     * @response {
     * "id": 1,
     * "created_at": "2023-08-16T22:03:20.000000Z",
     * "updated_at": "2023-08-16T22:03:20.000000Z",
     * "deleted_at": null,
     * "name": "Personal Info",
     * "active": 1
     * }
     */
    public function show(string $id)
    {
        try {
            $model = DocumentType::findOrFail($id);
            return response()->json($model);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'document type not found'], 400);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Update the specified document type.
     * @bodyParam name string An Unique name of the document type. Example: Personal Info
     * @bodyParam active boolean Toggle documentation type. Example: 0
     * @response {
     * "id": 1,
     * "created_at": "2023-08-16T22:03:20.000000Z",
     * "updated_at": "2023-08-16T22:09:28.000000Z",
     * "deleted_at": null,
     * "name": "Personal Info",
     * "active": "0"
     * }
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'name' => 'min:3|unique:document_types,name,'. $id,
            'active' => 'bool'
        ]);
        try{
            $model = DocumentType::findOrFail($id);
            $model->fill($request->only(['name', 'active']))->update();
            return response()->json($model);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'document type not found'], 400);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Remove the specified document type from storage.
     *
     * <aside>Document Type is softdeleted. ❗️</aside>
     *
     * @response {
     * "message": "Document type deleted successfully!!"
     * }
     */
    public function destroy(string $id)
    {
        try{
            $model = DocumentType::findOrFail($id);
            $model->delete();
            return response()->json([
                'message' => 'Document type deleted successfully!!',
            ]);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'document type not found'], 400);
        } catch (QueryException $e){
            Log::error('Delete Document Type '. $e);
            return response()->json(['error' => "Couldn't delete the document type with id {$model->id}"], 400);
        } catch (\Exception $e){
            Log::error('Delete Document Type - '. $e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }
}
