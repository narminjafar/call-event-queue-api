<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCallEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'call_id' => ['required', 'string', 'max:255'],
            'caller_number' => ['required', 'string', 'max:50'],
            'called_number' => ['required', 'string', 'max:50'],
            'event_type' => [
                'required',
                'string',
                'in:call_started,call_ended,call_held,call_transferred,call_missed'
            ],
            'timestamp' => ['required', 'date'],
            'duration' => ['required_if:event_type,call_ended', 'nullable', 'integer', 'min:0'],
        ];
    }

     public function messages(): array
    {
        return [
            'call_id.required' => 'Zəng ID-si tələb olunur',
            'caller_number.required' => 'Zəng edənin nömrəsi tələb olunur',
            'called_number.required' => 'Zəng olunan nömrə tələb olunur',
            'event_type.required' => 'Event tipi tələb olunur',
            'event_type.in' => 'Yalnız icazə verilmiş event tipləri qəbul edilir',
            'timestamp.required' => 'Zaman damğası tələb olunur',
            'timestamp.date' => 'Düzgün tarix formatı daxil edin',
            'duration.required_if' => 'Zəng sonlandıqda müddət göstərilməlidir',
            'duration.min' => 'Müddət 0-dan böyük və ya bərabər olmalıdır',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validasiya xətası',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
