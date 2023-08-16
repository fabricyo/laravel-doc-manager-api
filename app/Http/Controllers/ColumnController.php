<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\DocumentType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


/**
 * @group Columns
 *
 * APIs for managing the Columns, necessary for creating a document
 */

class ColumnController extends Controller
{
    /**
     * Display a listing of the Columns.
     */
    public function index()
    {
        return response()->json(Column::with('document_type')->get());
    }

    /**
     * Store a new Column.
     * @bodyParam name string required An unique name of the document type. Example: First name
     * @bodyParam document_types_id int required An valid document type id. Example: 3
     *
     * @response {
     * "message": "Column created successfully!!",
     * "data": {
     * "name": "First name",
     * "document_types_id": "1",
     * "updated_at": "2023-08-16T22:21:59.000000Z",
     * "created_at": "2023-08-16T22:21:59.000000Z",
     * "id": 1
     * }
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'document_types_id' => 'required|exists:document_types,id',
        ]);
        //Validation has passed
        try{
            $model = Column::create($request->only(['name', 'document_types_id']));
            return response()->json([
                'message' => 'Column created successfully!!',
                'data' => $model,
            ]);
        } catch (\Exception $e){
            echo $e;
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Display the specified column.
     * @urlParam id int required The column id
     *
     * @response {
     * "id": 1,
     * "created_at": "2023-08-16T22:21:59.000000Z",
     * "updated_at": "2023-08-16T22:21:59.000000Z",
     * "deleted_at": null,
     * "name": "First name",
     * "document_types_id": 1
     * }
     */
    public function show(string $id)
    {
        try {
            $model = Column::findOrFail($id);
            return response()->json($model);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Column not found'], 400);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Update the specified column.
     * @bodyParam name string An Unique name of the column. Example: Full name
     * @bodyParam active boolean Toggle column. Example: 0
     * @response {
     * "id": 1,
     * "created_at": "2023-08-16T22:21:59.000000Z",
     * "updated_at": "2023-08-16T22:25:36.000000Z",
     * "deleted_at": null,
     * "name": "Full name",
     * "document_types_id": 1
     * }
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'name' => 'min:3',
            'document_types_id' => 'exists:document_types,id',
        ]);
        try{
            $model = Column::findOrFail($id);
            $model->fill($request->only(['name', 'document_types_id']))->update();
            return response()->json($model);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Column not found'], 400);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Remove the specified column from storage.
     *
     * <aside>Column is softdeleted. ❗️</aside>
     *
     * @response {
     * "message": "Column deleted successfully!!"
     * }
     */
    public function destroy(string $id)
    {
        try{
            $model = Column::findOrFail($id);
            $model->delete();
            return response()->json([
                'message' => 'Column deleted successfully!!',
            ]);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Column not found'], 400);
        } catch (QueryException $e){
            Log::error('Column'. $e);
            return response()->json(['error' => "Couldn't delete the Column with id {$model->id}"], 400);
        } catch (\Exception $e){
            Log::error('Column - '. $e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }
}
