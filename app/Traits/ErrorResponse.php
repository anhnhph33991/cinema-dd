<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

trait ErrorResponse
{
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $errorMessages = [];

        foreach ($errors->messages() as $field => $message) {
            $errorMessages[$field] = array_shift($message);
        }

        $response = response()->json([
            'status' => false,
            'errors' => $errorMessages
        ], Response::HTTP_BAD_REQUEST);

        throw  new HttpResponseException($response);
    }
}
