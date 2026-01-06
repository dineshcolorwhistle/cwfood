<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIPrompt extends Model
{
    use HasFactory;

    protected $table ="ai_prompts";

    protected $fillable = [
        'system_prompt', 'upload_user_prompt', 'text_user_prompt', 
        'audit_system_prompt', 'audit_user_prompt', 'extraction_schema', 
        'ai_extract_pdf', 'ai_extract_text', 'audit_summary', 
        'temprature', 'max_tokens', 'top_p', 
        
    ];

    protected $casts = [
        'extraction_schema' => 'array', // automatically decode to array
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
