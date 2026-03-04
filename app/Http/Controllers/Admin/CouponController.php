<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Models\Coupon;
use App\Models\Cuisine;
use App\Models\Discount;
use App\Enums\CouponType;
use App\Models\Restaurant;
use App\Enums\CouponStatus;
use App\Enums\DiscountType;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Requests\CouponRequest;
use App\Http\Services\CouponService;
use App\Http\Controllers\BackendController;

class CouponController extends BackendController
{

    /**
     * Category Controller constructor.
     */
    protected $couponService;
    public function __construct(CouponService $couponService)
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Coupons';
        $this->couponService = $couponService;
        $this->middleware(['permission:coupon'])->only('index');
        $this->middleware(['permission:coupon_create'])->only('create', 'store');
        $this->middleware(['permission:coupon_edit'])->only('edit', 'update');
        $this->middleware(['permission:coupon_delete'])->only('destroy');
        $this->middleware(['permission:coupon_show'])->only('show');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        return $this->getCoupon($request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['restaurants'] = Restaurant::select('id', 'name')->get();
        $this->data['users'] = User::orderBy('id','asc')->where('status',UserStatus::ACTIVE)->get();
        return view('admin.coupon.create', $this->data);
    }


    public function store(CouponRequest $request)
    {
        $coupon = $this->couponService->store($request);
        return redirect(route('admin.coupon.index'))->withSuccess('Kupon Başarıyla Eklendi.');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->data['coupon'] = Coupon::findOrFail($id);
        $this->data['restaurants'] = Restaurant::select('id', 'name')->get();
        $this->data['users'] = User::orderBy('id','asc')->where('status',UserStatus::ACTIVE)->get();
        return view('admin.coupon.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CouponRequest $request, $id)
    {
        $coupon = $this->couponService->update($id, $request);
        return redirect(route('admin.coupon.index'))->withSuccess('Bilgiler başarıyla güncellendi..');
    }



    public function show($id)
    {
        $this->data['coupon'] = $this->couponService->show($id);
        return view('admin.coupon.show', $this->data);
    }




    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();
        return redirect(route('admin.coupon.index'))->withSuccess('Kupon Başarışla Silindi');
    }

    private function getCoupon($request)
    {
        if (request()->ajax()) {
            $queryArray = [];

            if (auth()->user()->myrole != 1 && !blank(auth()->user()->restaurant)) {
                $queryArray['restaurant_id'] = auth()->user()->restaurant->id;
            }

            if ($request->discount_type) {
                $queryArray['discount_type'] = $request->discount_type;
            }
            if ($request->coupon_type) {
                $queryArray['coupon_type'] = $request->coupon_type;
            }

            if (!blank($queryArray)) {
                $coupons = Coupon::where($queryArray)->descending()->get();
            } else {
                $coupons = Coupon::descending()->get();
            }

            $i = 0;
           return Datatables::of($coupons)
                ->addColumn('action', function ($coupon) {

                    return action_button([
                        'view'   => ['route' => route('admin.coupon.show', $coupon),'permission' => 'coupon_show'],
                        'edit'   => ['route' => route('admin.coupon.edit', $coupon),'permission' => 'coupon_edit'],
                        'delete' => ['route' => route('admin.coupon.destroy', $coupon),'permission' => 'coupon_delete'],
                    ]);
                })
                ->editColumn('id', function ($coupon) use (&$i) {
                    return ++$i;
                })
                ->editColumn('name', function ($coupon) {
                    return $coupon->name;
                })
                ->editColumn('slug', function ($coupon) {
                    return $coupon->slug;
                })
                ->editColumn('coupon_type', function ($coupon) {
                    return trans('coupon_types.' . $coupon->coupon_type);
                })
                ->editColumn('limit', function ($coupon) {
                    return $coupon->limit;
                })
                ->editColumn('status', function ($coupon) {
                    return $coupon->statusName;
                })
                ->rawColumns(['action'])
                ->escapeColumns([])
                ->make(true);
        }
        return view('admin.coupon.index');
    }

    public function allCoupons()
    {
        $coupons = $this->couponService->allCoupons();
        return $coupons;
    }

    public function singleRestaurantCoupon(Request $request)
    {
        $coupons = $this->couponService->singleRestaurantCoupon($request);
        return $coupons;
    }
}
