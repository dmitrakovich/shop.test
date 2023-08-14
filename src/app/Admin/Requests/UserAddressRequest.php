<?php

namespace App\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'region' => $this->addressRegion,
            'city' => $this->addressCity,
            'zip' => $this->addressZip,
            'street' => $this->addressStreet,
            'house' => $this->addressHouse,
            'corpus' => $this->addressCorpus,
            'room' => $this->addressRoom,
            'approve' => ($this->addressApprove || $this->addressApprove == 'on') ? true : false,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'country_id' => ['nullable', 'integer'],
            'region' => ['nullable', 'max:120'],
            'city' => ['nullable', 'max:50'],
            'address' => ['nullable', 'max:191'],
            'zip' => ['nullable', 'max:10'],
            'street' => ['nullable', 'max:96'],
            'house' => ['nullable', 'max:32'],
            'corpus' => ['nullable', 'max:32'],
            'room' => ['nullable', 'max:32'],
            'approve' => ['required'],
        ];
    }
}
