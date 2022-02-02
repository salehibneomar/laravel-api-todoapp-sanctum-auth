<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Todo extends Model
{
    use HasFactory;

    const DONE = 1;
    const NOT_DONE = 0;

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'user_id',
    ];

    protected $appends = [
        'remaining_days',
    ];

    public function isDone(){
        return $this->status == Todo::DONE;
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getRemainingDaysAttribute(){
        $startDate = date_create($this->attributes['start_date']);
        $endDate   = date_create($this->attributes['end_date']);
        $now       = date_create(date('Y-m-d'));
        $status    = 'Yet to start';

        if(!($now<$startDate)){
            $diff = date_diff($now, $endDate);
            $sign = $diff->format('%R');
            $days = (int) $diff->format('%a');

            if($sign=='-'){
                $status = 'Expired';
            }
            else{
                $status = $days==0 ? 'Deadline' : Str::plural($days.' Day', $days);
            }
        }

        return $status;
    }
    
}
