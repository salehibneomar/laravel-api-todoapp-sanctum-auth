<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponserTrait{
    
    protected function successResponse($data, $code=200){
        return response()->json(['message'=>$data], $code);
    }

    public function listResponse(Collection $data){
        return response()->json(['data'=>$data], 200); 
    }

    public function paginatedListResponse(LengthAwarePaginator $data){
        return response()->json($data, 200); 
    }

    public function singleResponse(Model $data, $code=200, $message=null){
        $responseData = ['data'=>$data];
        if(!is_null($message)){
            $responseData = [
                'data' => [
                    'message' => $message,
                    strtolower(class_basename($data)) => $data,
                ],
            ];
        }
        return response()->json($responseData, $code);
    }

    public function authSuccessResponse(Model $data, $token){
        $responseData = [
            'data' => [
                'message' => 'Authentication successful',
                'user' => $data,
                'bearer_token' => $token,
            ],
        ];
        return response()->json($responseData, 200);
    }

    protected function errorResponse($error, $code){
        return response()->json(['error'=>$error, 'code'=>$code], $code);
    }
}