<?php
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class support_ticket_comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

   
    /**
     * Return the validation rules for the Machinery model.
     */
    public static function validationRules($id = null)
    {
        $currentYear = Carbon::now()->year;
        return [
            'description' => 'required|string',
            'ticket_id' => 'required'
        ];
    }

    /**
     * Return the validation messages for the Machinery model.
     */
    public static function validationMessages()
    {
        return [
            'description.required' => 'Description is required.',
            'description.max' => 'Description may not be greater than 100 characters.',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ticket()
    {
        return $this->belongsTo(support_ticket::class, 'ticket_id');
    }
}
