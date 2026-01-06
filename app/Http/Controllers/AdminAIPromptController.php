<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{AIPrompt};


class AdminAIPromptController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;

    public function __construct()
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');
    }

    public function index(Request $request)
    {
        $prompts = AIPrompt::first();
        return view('backend.ai_prompt.manage', compact('prompts'));
    } 

    public function update(Request $request, AIPrompt $prompt)
    {
        $validated = $request->validate([
            'system_prompt' => 'nullable|string',
            'upload_user_prompt' => 'nullable|string',
            'text_user_prompt' => 'nullable|string',
            'audit_system_prompt' => 'nullable|string',
            'audit_user_prompt' => 'nullable|string',
            'extraction_schema' => 'nullable|string',
            'ai_extract_pdf' => 'nullable|string',
            'ai_extract_text' => 'nullable|string',
            'audit_summary' => 'nullable|string',
            'temprature' => 'nullable|string',
            'max_tokens' => 'nullable|string',
            'top_p' => 'nullable|string',
        ]);

        try {

            $extractionSchema = $validated['extraction_schema']
            ? json_decode($validated['extraction_schema'], true, 512, JSON_THROW_ON_ERROR)
            : null;


            $prompt->update([
                'system_prompt' => $validated['system_prompt'] ?? null,
                'upload_user_prompt' => $validated['upload_user_prompt'] ?? null,
                'text_user_prompt' => $validated['text_user_prompt'] ?? null,
                'audit_system_prompt' => $validated['audit_system_prompt'] ?? null,
                'audit_user_prompt' => $validated['audit_user_prompt'] ?? null,
                'extraction_schema' => $extractionSchema,
                'ai_extract_pdf' => $validated['ai_extract_pdf'] ?? null,
                'ai_extract_text' => $validated['ai_extract_text'] ?? null,
                'audit_summary' => $validated['audit_summary'] ?? null,
                'temprature' => $validated['temprature'] ?? null,
                'max_tokens' => $validated['max_tokens'] ?? null,
                'top_p' => $validated['top_p'] ?? null,

            ]);

            $response = [
                'status' => true,
                'message' => "Prompt Updated"
            ];

        } catch (\JsonException $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
        return response()->json($response);
    } 

}
