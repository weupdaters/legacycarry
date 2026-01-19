<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\UserDocument;

class UserDocumentController extends Controller
{
    // Upload or update document based on user_id and type
    public function uploadOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'document_type' => 'required|in:id_proof,address_proof,verification_photo',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user already uploaded this type
        $document = UserDocument::where('user_id', $request->user_id)
            ->where('document_type', $request->document_type)
            ->first();

        // Replace if exists
        if ($document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
        } else {
            $document = new UserDocument();
            $document->user_id = $request->user_id;
            $document->document_type = $request->document_type;
        }

        // Store new file
        $path = $request->file('file')->store('uploads/documents', 'public');
        $document->file_path = $path;
        $document->status = 'pending'; // reset to pending after update
        $document->remarks = null;
        $document->save();

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => $document
        ], 201);
    }

    // Get documents of a specific user
    public function getByUser($user_id)
    {
        $documents = UserDocument::where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }
}
