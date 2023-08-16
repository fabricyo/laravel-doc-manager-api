<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\DocumentType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Column::with('document_type')->get());
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $model = DocumentType::findOrFail($id);
            return response()->json($model);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Column not found'], 400);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    //URL: put

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'value' => 'min:3',
            'document_types_id' => 'exists:document_types,id',
        ]);
        try{
            $model = Column::findOrFail($id);
            $model->fill($request->only(['value', 'document_types_id']))->update();
            return response()->json($model);
        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'Column not found'], 400);
        } catch (\Exception $e){
            Log::error($e);
            return response()->json(['error' => 'server error, try again'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
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
