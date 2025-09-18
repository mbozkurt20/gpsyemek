<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Address;
use App\Http\Requests\AddressRequest;
use App\Http\Services\AddressService;
use App\Http\Controllers\FrontendController;

class AddressController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['site_title'] = 'Başlangıç';
    }

    public function store(AddressRequest $request)
    {

        $address = app(AddressService::class)->store($request);
        if ($address) {
            return redirect()->route('checkout.index')->withSuccess('Adres Başarıyla Eklendi.');
        }
        return view('errors.404');
    }

    public function edit($id)
    {

        $address = Address::findOrFail($id);
        echo json_encode($address);
    }

    public function update(AddressRequest $request, $id)
    {

        $address = Address::findOrFail($id);
        $address = app(AddressService::class)->update($address,$request);
        if ($address) {
            return redirect()->route('checkout.index')->withSuccess('Adres Başarıyla Güncellendi.');
        }
        return view('errors.404');

    }


    public function destroy($id)
    {
        Address::findOrFail($id)->delete();
    }
}
