<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Models\User;
use App\Models\Todo;

class TodoController extends Controller
{

    use ApiResponserTrait;

    public function index(Request $request)
    {
        $userId = $this->user()->id;
        $todos  = Todo::query();
        $todos  = $todos = $todos->where('user_id', $userId);
        $sort   = 'status';
        $order  = 'asc';

        if($request->has('sort') && !empty($request->sort)){
            
            $sortKeys = ['id', 'name', 'start_date', 'end_date', 'status'];
            $orderkeys = ['asc', 'desc'];

            $value = explode('.',$request->sort);
            $sort  = $value[0];

            if(!in_array($sort, $sortKeys)){
                return $this->errorResponse('Invalid sort key', 404);
            }

            if(isset($value[1]) && in_array($value[1], $orderkeys) ){
                $order = $value[1];
            }
        }

        $todos = $todos->orderBy($sort, $order)->paginate(10);

        return $this->paginatedListResponse($todos);           
    }    

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:150',
            'description' => 'nullable|min:10',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $todo = new Todo();
        $todo->fill($request->only([
            'name',
            'description',
            'start_date',
            'end_date',
        ]));

        $todo->user_id = $this->user()->id;

        if($todo->save()){
            return $this->successResponse('Todo created successfully'); 
        }

        return $this->errorResponse('Internal Error occurred', 500);
    }

    public function show($id)
    {
        $todo = $this->todo($id);
        if(is_null($todo)){
            return $this->errorResponse('Todo not found', 404);
        }
        return $this->singleResponse($todo);      
    }

    public function update(Request $request, $id)
    {
        $todo = $this->todo($id);
        if(is_null($todo)){
            return $this->errorResponse('Todo not found', 404);
        }

        $request->validate([
            'name' => 'min:3|max:150',
            'description' => 'nullable|min:10',
            'start_date' => 'date|before_or_equal:end_date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $todo->fill($request->only([
            'name',
            'description',
            'start_date',
            'end_date',
        ]));

        if($todo->isClean()){
            return $this->errorResponse('No change', 422);
        }

        $todo->status = Todo::NOT_DONE;

        if($todo->save()){
            return $this->singleResponse($todo); 
        }

        return $this->errorResponse('Internal Error occurred', 500);
    }

    public function markAsDone($id)
    {
        $todo = $this->todo($id);
        if(is_null($todo)){
            return $this->errorResponse('Todo not found', 404);
        }

        $todo->status = Todo::DONE;

        if($todo->isClean()){
            return $this->errorResponse('No change', 422);
        }

        if($todo->save()){
            return $this->successResponse('Todo marked as done successfully'); 
        }

        return $this->errorResponse('Internal Error occurred', 500);
    }

    public function destroy($id)
    {
        $todo = $this->todo($id);
        if(is_null($todo)){
            return $this->errorResponse('Todo not found', 404);
        }

        if($todo->delete()){
            return $this->successResponse('Todo deleted successfully'); 
        }

        return $this->errorResponse('Internal Error occurred', 500);
    }

    protected function user()
    {
        return User::find(Auth::guard('api_guard')->user()->id);
    }

    protected function todo($id)
    {
        $userId = $this->user()->id;
        $todo = Todo::where('user_id', $userId)
                    ->find($id);
                    
        return $todo;
    }


}
