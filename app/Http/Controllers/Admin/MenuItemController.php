<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoryStatus;
use App\Enums\MenuItemStatus;
use App\Enums\Status;
use App\Http\Controllers\BackendController;
use App\Http\Requests\MenuItemRequest;
use App\Imports\ProductImport;
use App\Imports\ProductOptionVariant;
use App\Imports\ProductVariantImport;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemVariation;
use App\Models\Restaurant;
use App\Rules\IniAmount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;

class MenuItemController extends BackendController
{

    /**
     * MenuItemController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Menu ';

        $this->middleware(['permission:menu-items'])->only('index');
        $this->middleware(['permission:menu-items_create'])->only('create', 'store');
        $this->middleware(['permission:menu-items_edit'])->only('edit', 'update');
        $this->middleware(['permission:menu-items_delete'])->only('destroy');
        $this->middleware(['permission:menu-items_show'])->only('show');
    }

    public function temporaryClose(Request $request,$id)
    {
        $menuItem = MenuItem::find($id);

        $minutes = $request->input('minutes');

        if ($minutes === 'unlimited') {
            $menuItem->update([
                'status' => MenuItemStatus::INACTIVE,
                'closed_until' => null,
            ]);
        } elseif (is_numeric($minutes) && $minutes > 0) {
            $menuItem->update([
                'status' => MenuItemStatus::INACTIVE,
                'closed_until' => now()->addMinutes($minutes),
            ]);
        }

        return back()->with('success', 'Durum güncellendi.');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->getMenuItem($request);
    }

    function variantImport(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,csv',
            'menuItemId' => 'required',
        ]);

        $menuItem  = MenuItem::find($request->input('menuItemId'));

        $restaurant_id = $menuItem->restaurant_id;

        try {
            Excel::import(new ProductVariantImport($restaurant_id,$menuItem->id), $request->file('importFile'));
            return back()->with('success', 'Ürün varyantları başarıyla içe aktarıldı! 🎉');
        } catch (\Exception $e) {
            return back()->with('error', 'Bir hata oluştu: ' . $e->getMessage());
        }
    }

    function optionImport(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,csv',
            'menuItemId' => 'required',
        ]);

        $menuItem  = MenuItem::find($request->input('menuItemId'));

        $restaurant_id = $menuItem->restaurant_id;
        try {
            Excel::import(new ProductOptionVariant($restaurant_id,$menuItem->id), $request->file('importFile'));
            return back()->with('success', 'Ürün seçenekleri başarıyla içe aktarıldı! 🎉');
        } catch (\Exception $e) {
            return back()->with('error', 'Bir hata oluştu: ' . $e->getMessage());
        }
    }

    function import(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,csv',
            'restaurant_id' => 'required',
        ]);

        $restaurant_id = $request->restaurant_id;

        try {
            Excel::import(new ProductImport($restaurant_id), $request->file('importFile'));
            return back()->with('success', 'Ürünler başarıyla içe aktarıldı! 🎉');
        } catch (\Exception $e) {
            return back()->with('error', 'Bir hata oluştu: ' . $e->getMessage());
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['categories'] = Category::where(['status' => CategoryStatus::ACTIVE])->get();
        $this->data['restaurants'] = Restaurant::where(['status' => Status::ACTIVE])->get();
        return view('admin.menu-item.create', $this->data);
    }

    /**
     * @param MenuItemRequest $request
     * @return mixed
     */
    public function store(MenuItemRequest $request)
    {
        $menus = MenuItem::where('restaurant_id', $request->get('restaurant_id'))->get();
        $menu_array = [];
        if (isset($menus)) {
            foreach ($menus as $menu) {
                $menu_array[] = $menu->menu_number;
            }
        }
        $menuNumber = $this->checkMenuNumber($menu_array);
        $menuItem                 = new MenuItem;
        $menuItem->restaurant_id  = $request->get('restaurant_id');
        $menuItem->name           = $request->get('name');
        $menuItem->description    = $request->get('description');
        $menuItem->unit_price     = $request->get('unit_price');
        $menuItem->discount_price = $request->get('discount_price') ?? 0;
        $menuItem->status         = $request->get('status');
        $menuItem->menu_number         = $menuNumber;
        $menuItem->save();

        $menuItem->categories()->sync($request->get('categories'));

        //Store Image
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $menuItem->addMediaFromRequest('image')->toMediaCollection('menu-items');
        }

        return redirect()->back()->withSuccess('Veriler başarıyla eklendi!');
    }

    /**
     * Display the specified resource.
     *
     * @param MenuItem $MenuItem
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(MenuItem $menuItem)
    {
        return view('admin.menu-item.show', compact('menuItem'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MenuItem $MenuItem
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(MenuItem $menuItem)
    {
        $this->data['menuItem']            = $menuItem;
        $this->data['categories']          = Category::where(['status' => CategoryStatus::ACTIVE])->get();
        $this->data['menuItem_categories'] = $menuItem->categories()->pluck('id')->toArray();
        $this->data['restaurants'] = Restaurant::where(['status' => Status::ACTIVE])->get();

        return view('admin.menu-item.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param MenuItemRequest $request
     * @param $id
     * @return mixed
     */
    public function update(MenuItemRequest $request, $id)
    {
        $menuItem                 = MenuItem::owner()->findOrFail($id);
        $menuItem->restaurant_id  = $request->get('restaurant_id');
        $menuItem->name           = $request->get('name');
        $menuItem->description    = $request->get('description');
        $menuItem->unit_price     = $request->get('unit_price');
        $menuItem->discount_price = $request->get('discount_price') ?? 0;
        $menuItem->status         = $request->get('status');
        $menuItem->save();

        $menuItem->categories()->sync($request->get('categories'));

        //Update Image
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $menuItem->deleteMedia('menu-items', $menuItem->id);
            $menuItem->addMediaFromRequest('image')->toMediaCollection('menu-items');
        }

        return redirect()->back()->withSuccess('Bilgiler başarıyla güncellendi.!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        MenuItem::owner()->findOrFail($id)->delete();
        return redirect()->back()->withSuccess('Veri Başarıyla Silindi.');
    }

    private function getMenuItem($request)
    {
        if (request()->ajax()) {
            $queryArray = [];
            if (!empty($request->status) && (int) $request->status) {
                $queryArray['status'] = $request->status;
            }
            if (auth()->user()->myrole != 1 && auth()->user()->restaurant){
                $queryArray['restaurant_id'] =auth()->user()->restaurant->id;
            }

            if (!blank($queryArray)) {
                $menuItems = MenuItem::with('categories')->where($queryArray)->descending()->get();
            } else {
                $menuItems = MenuItem::with('categories')->descending()->get();
            }

            $i = 0;
            return Datatables::of($menuItems)
                ->addColumn('action', function ($menuItem) {
                    $button_array           = [];
                    $button_array['modify'] = ['route' => route('admin.menu-items.modify', $menuItem),'permission' => 'menu-items_edit'];
                    $button_array['view']   = ['route' => route('admin.menu-items.show', $menuItem),'permission' => 'menu-items_show'];
                    $button_array['edit']   = ['route' => route('admin.menu-items.edit', $menuItem),'permission' => 'menu-items_edit'];
                    $button_array['delete'] = ['route' => route('admin.menu-items.destroy', $menuItem),'permission' => 'menu-items_delete'];

                    return action_button($button_array);
                })
                ->editColumn('id', function ($menuItem) use (&$i) {
                    return ++$i;
                })
                ->editColumn('restaurants', function ($menuItem) {
                    $categories = implode(', ', $menuItem->restaurants()->pluck('name')->toArray());
                    return Str::limit($categories, 30);
                })
                ->editColumn('categories', function ($menuItem) {
                    $categories = implode(', ', $menuItem->categories()->pluck('name')->toArray());
                    return Str::limit($categories, 30);
                })
                ->editColumn('name', function ($menuItem) {
                    $col = '<p class="p-0 m-0">' . Str::limit($menuItem->name, 20) . '</p>';
                    $col .= '<small class="text-muted">' . Str::limit($menuItem->description, 20) . '</small>';
                    return $col;
                })
                ->editColumn('status', function ($menuItem) {
                    return $menuItem->statusName;
                })
                ->addColumn('image', function ($menuItem) {
                   return !blank($menuItem->image) ? $menuItem->image : '';
                })
                ->rawColumns(['name' ,'status', 'action'])
                ->make(true);
        }

        return view('admin.menu-item.index', $this->data);
    }

    public function getMedia(Request $request)
    {

        $menuItem       = MenuItem::owner()->where('status', MenuItemStatus::ACTIVE)->find($request->id);
        $menuItemImages = $menuItem->iamges;

        $i      = 0;
        $retArr = [];
        if (!blank($menuItemImages)) {
            foreach ($menuItemImages as $menuItemImage) {
                $i++;
                $retArr[$i]['name'] = $menuItemImage->file_name;
                $retArr[$i]['size'] = $menuItemImage->size;
                $retArr[$i]['url']  = asset($menuItemImage->getUrl());
            }
        }
        echo json_encode($retArr);
    }

    public function storeMedia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('file'),
            ]);
        }

        $path = storage_path('tmp/uploads');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file = $request->file('file');

        $name = uniqid() . '_' . trim($file->getClientOriginalName());

        $file->move($path, $name);

        return response()->json([
            'name'          => $name,
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    public function updateMedia(Request $request, $id)
    {

        $menuItem = MenuItem::owner()->find($id);
        if (!blank($menuItem)) {
            $validator = Validator::make($request->all(), [
                'file' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3096',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first('file'),
                ]);
            }

            $path = storage_path('tmp/uploads');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $file = $request->file('file');
            $name = uniqid() . '_' . trim($file->getClientOriginalName());
            $file->move($path, $name);

            $menuItem->addMedia(storage_path('tmp/uploads/' . $name))->toMediaCollection('menu-items');
            return response()->json([
                'name'          => $name,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    public function removeMedia(Request $request)
    {
        $menuItem = MenuItem::owner()->find($request->id);
        $menuItem->deleteMedia($menuItem, $request->media, $request->id);
        return $this->getMedia($request);
    }

    public function modify($id)
    {
        $menuItem = MenuItem::owner()->findOrFail($id);

        $this->data['menuItem']             = $menuItem;
        $this->data['menu_item_variations'] = $menuItem->variations;
        $this->data['menu_item_options']    = $menuItem->options;

        // YENİ: Restorandaki diğer tüm ürünleri (içecekler vb.) çekiyoruz
        $this->data['allMenuItems'] = MenuItem::where('restaurant_id', $menuItem->restaurant_id)
            ->where('id', '!=', $id)
            ->get(['id', 'name', 'unit_price as price']); // unit_price'ı price olarak gönderiyoruz

        return view('admin.menu-item.modify', $this->data);
    }

    public function modifyUpdate(Request $request, $id)
    {
        $menuItem = MenuItem::owner()->findOrFail($id);

        // 1. Mevcut grupları ve seçenekleri temizle (Overwrite mantığı)
        $menuItem->optionGroups()->each(function($group) {
            $group->options()->delete();
            $group->delete();
        });

        if ($request->has('groups')) {
            foreach ($request->groups as $groupData) {
                if (empty($groupData['name'])) continue;

                // 2. Grubu oluştur (min_count ve max_count eklendi)
                $group = $menuItem->optionGroups()->create([
                    'restaurant_id' => $menuItem->restaurant_id,
                    'name'          => $groupData['name'],
                    'type'          => $groupData['type'] ?? 'radio',
                    'is_required'   => isset($groupData['is_required']) ? true : false,
                    'min_count'     => $groupData['min_count'] ?? 0,
                    'max_count'     => $groupData['max_count'] ?? 0,
                ]);

                // 3. Gruba ait alt seçenekleri oluştur
                if (isset($groupData['items']) && is_array($groupData['items'])) {
                    foreach ($groupData['items'] as $itemData) {
                        if (empty($itemData['name']) && empty($itemData['linked_item_id'])) continue;

                        // Eğer isim boşsa ama ürün bağlandıysa, bağlı ürünün adını otomatik alabiliriz
                        $optionName = $itemData['name'];
                        if (empty($optionName) && !empty($itemData['linked_item_id'])) {
                            $linkedItem = MenuItem::find($itemData['linked_item_id']);
                            $optionName = $linkedItem ? $linkedItem->name : 'Bağlı Ürün';
                        }

                        $group->options()->create([
                            'restaurant_id'  => $menuItem->restaurant_id,
                            'name'           => $optionName,
                            'price'          => $itemData['price'] ?? 0,
                            'linked_item_id' => $itemData['linked_item_id'] ?? null, // YENİ ALAN
                        ]);
                    }
                }
            }
        }

        return redirect(route('admin.menu-items.modify', $id))->withSuccess("Menü opsiyonları ve grupları başarıyla güncellendi.");
    }

    private function priceValidationCheck($array)
    {
        if ($array['price'] < $array['discount_price']) {
            return true;
        }
        return false;
    }
    function checkMenuNumber($menu_array)
    {
        $menuNumber = rand(1000, 9999);
        $menuNumber = in_array($menuNumber, $menu_array) ? $this->checkMenuNumber($menu_array) : $menuNumber;
        return $menuNumber;
    }
}
