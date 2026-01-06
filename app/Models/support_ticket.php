<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class support_ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'topic',
        'category',
        'description',
        'ticket_number',
        'status',
        'priority',
        'due_date',
        'client_id',
        'workspace_id',
        'created_by',
        'updated_by',
        'requester',
        'ccs',
        'assignee',
        'time_estimated',
        'time_spent',
        'sort_order'
    ];

   
    /**
     * Return the validation rules for the Machinery model.
     */
    public static function validationRules($id = null)
    {
        $currentYear = Carbon::now()->year;
        return [
            'topic' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'email' => 'nullable',
            'ticket_number' => 'required',
            'status' => 'nullable',
            'priority' => 'nullable',
            'due_date' => 'nullable',
            'requester' =>'nullable',
            'ccs'=>'nullable', 
            'time_estimated' => 'nullable',
            'time_spent' =>'nullable',
        ];
    }

    /**
     * Return the validation messages for the Machinery model.
     */
    public static function validationMessages()
    {
        return [
            'topic.required' => 'Topic is required.',
            'topic.max' => 'Topic may not be greater than 100 characters.',
            'category.required' => 'Category is required.',
            'category.max' => 'Category may not be greater than 100 characters.',
            'description.required' => 'Description is required.',
            'description.max' => 'Description may not be greater than 100 characters.',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(support_ticket_comment::class, 'ticket_id')->orderBy('created_at','desc');
    }

    public function RequesterDetails()
    {
        return $this->belongsTo(User::class, 'requester');
    }

    public function ClientDetails()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function AssigneeDetails()
    {
        return $this->belongsTo(User::class, 'assignee');
    }

    public function WorkspaceDetails()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }


    
}
