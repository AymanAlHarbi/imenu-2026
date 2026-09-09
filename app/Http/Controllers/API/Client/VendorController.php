<?php

namespace App\Http\Controllers\API\Client;

use App\Address;
use App\City;
use App\Http\Controllers\Controller;
use App\Restorant as Restaurant;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    public function getCities(): JsonResponse
    {
        $cities = City::where('id', '>', 0)->get()->toArray();
        foreach ($cities as $key => &$city) {

            if (! (strpos($city['logo'], 'http') !== false)) {
                $city['logo'] = config('app.url').$city['logo'];
            }
        }

        if ($cities) {
            return response()->json([
                'data' => $cities,
                'status' => true,
                'errMsg' => '',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errMsg' => 'Cities not found!',
            ]);
        }
    }

    public function getVendors($city_id = 'none'): JsonResponse
    {
        /**
         * iMenu 2026 — الظهور في التطبيق هو الرافعة، لا تسعير المنيو.
         * لا يُعرض إلا مقهى خطته تسمح بالطلب ولم يستنفد حدّه، وإلا لمس العميل
         * مقهى ثم فشل طلبه. والمنيو يظل معروضًا وطنيًا على رابط المقهى نفسه.
         */
        $listedIds = \App\Services\AppListing::vendorIds($city_id);

        if (empty($listedIds)) {
            return response()->json([
                'data' => [],
                'status' => true,
                'errMsg' => '',
            ]);
        }

        $restaurants = Restaurant::where(['active' => 1])->whereIn('id', $listedIds)->get();

        if ($restaurants) {
            return response()->json([
                'data' => $restaurants,
                'status' => true,
                'errMsg' => '',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errMsg' => 'Vendor not found!',
            ]);
        }
    }

    public function getVendorItems($id): JsonResponse
    {
        $restorant = Restaurant::where(['id' => $id, 'active' => 1])->with(['categories.items.variants.extras'])->first();
        $items = [];
        if ($restorant) {
            if ($restorant->categories) {
                foreach ($restorant->categories as $key => $category) {
                    $theItemsInCategory = $category->items;
                    $catBox = [];
                    foreach ($theItemsInCategory as $key => $item) {
                        $itemObj = $item->toArray();
                        $itemObj['category_name'] = $category->name;
                        $itemObj['extras'] = $item->extras->toArray();
                        $itemObj['options'] = $item->options->toArray();
                        $itemObj['variants'] = $item->variants->toArray();
                        array_push($catBox, $itemObj);
                    }
                    array_push($items, $catBox);
                }

                return response()->json([
                    'data' => $items,
                    'status' => true,
                    'errMsg' => '',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'errMsg' => 'Vendor categories not found!',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'errMsg' => 'Vendor not found!',
            ]);
        }
    }

    public function getVendorHours($restorantID): JsonResponse
    {

        //Create all the time slots
        //The restaurant
        $restaurant = Restaurant::findOrFail($restorantID);

        $timeSlots = $this->getTimieSlots($restaurant);

        //Modified time slots for app
        $timeSlotsForApp = [];
        foreach ($timeSlots as $key => $timeSlotsTitle) {
            array_push($timeSlotsForApp, ['id' => $key, 'title' => $timeSlotsTitle]);
        }

        //Working hours
        $ourDateOfWeek = date('N') - 1;

        $format = 'G:i';
        if (config('settings.time_format') == 'AM/PM') {
            $format = 'g:i A';
        }

        $businessHours = $restaurant->getBusinessHours();
        $now = new \DateTime('now');

        $formatter = new \IntlDateFormatter(config('app.locale'), \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
        $formatter->setPattern(config('settings.datetime_workinghours_display_format_new'));

        $params = [
            'restorant' => $restaurant,
            'timeSlots' => $timeSlotsForApp,
            'openingTime' => $businessHours->isClosed() ? $formatter->format($businessHours->nextOpen($now)) : null,
            'closingTime' => $businessHours->isOpen() ? $formatter->format($businessHours->nextClose($now)) : null,
        ];

        if ($restaurant) {
            return response()->json([
                'data' => $params,
                'status' => true,
                'errMsg' => '',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errMsg' => 'Restaurant not found!',
            ]);
        }
    }

    public function getDeliveryFee($restaurant_id, $address_id): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurant_id);
        $addresss = Address::findOrFail($address_id);
        $addresses = $this->getAccessibleAddresses($restaurant, [$addresss]);

        return response()->json([
            'fee' => $addresses[$address_id]->cost_total,
            'inRadius' => $addresses[$address_id]->inRadius,
            'address' => $addresses[$address_id],
            'status' => true,
            'errMsg' => '',
        ]);
    }
}
