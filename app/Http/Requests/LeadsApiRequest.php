<?php

namespace App\Http\Requests;

class LeadsApiRequest extends BaseFormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function store()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'referer' => 'nullable|string|max:65500',
            'status' => 'nullable|string|max:255',
        //    'requestid' => 'nullable|string|max:255',

        ];
    }

//     public function update()
//     {
//         return [
//             'name' => 'required|string|max:255',
//             'phone' => 'required|string|max:255',
//             'referer' => 'nullable|string|max:65500',
// //            'requestid' => 'nullable|integer',
//         ];
//     }


}
