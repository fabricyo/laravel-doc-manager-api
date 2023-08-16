<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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

    //URL: put

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
