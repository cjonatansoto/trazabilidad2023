<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
     public function json(Request $request, $data, $code)
     {
        $message = '';
        $success = true;

        switch($code){
            case 200:
                $message = 'All is ok :)';
                break;
            case 201:
                $message = 'Resource was created';
                break;
            case 204:
                $message = 'Resource was deleted';
                break;
            case 400:
                $success  = false;
                $message = 'Bad request';
            case 403:
                $success  = false;
                $message = 'Unauthorized';
            break;
            case 404:
                $success = false;
                $message = 'Resource not found';
                break;
            case 422:
                $success = false;
                $message = 'Unprocessable entity';
                break;
            default:
                $success = false;
                $message = 'Sorry something went wrong :(';
        }

        return response()->json([
            'success' => $success,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code)->setCallback($request->input('callback'));
    }

    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return response($errors, 400);
    }
}
