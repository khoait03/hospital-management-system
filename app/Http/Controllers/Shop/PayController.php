<?php

namespace App\Http\Controllers\shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\PaymentRequest;
use App\Models\Order;
use App\Models\Products\CartDetail;
use App\Models\Products\CartProduct;
use App\Models\Products\Coupon;
use App\Models\Products\OrderProduct;
use App\Models\Products\PaymentProduct;
use App\Models\Products\Product;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayController extends Controller
{

    public function bill()
    {
        $user = Auth::user();
        $user_id = $user->user_id;
        $order = OrderProduct::join('payment_products', 'payment_products.order_id', '=', 'order_products.order_id')
            ->join('users', 'users.user_id', '=', 'order_products.user_id')
            ->where('users.user_id', $user_id)
            ->whereNull('order_products.deleted_at')
            ->orderBy('order_products.created_at', 'desc')
            ->first();
        if ($order) {
            $cart_id = $order->cart_id;
            $product = Product::join('cart_details', 'cart_details.product_id', '=', 'products.product_id')
                ->leftJoin('product_sale', 'product_sale.product_id', '=', 'products.product_id')
                ->leftJoin('coupons', 'coupons.coupon_id', '=', 'product_sale.coupon_id')
                ->join('cart_products', 'cart_products.cart_id', 'cart_details.cart_id')
                ->join('img_products', 'img_products.product_id', '=', 'products.product_id')
                ->where('cart_products.cart_id', $cart_id)
                ->select(
                    'cart_products.*',
                    'cart_details.*',
                    'cart_details.quantity as quantitycart',
                    'products.*',
                    'coupons.discount_code',
                    'coupons.percent',
                    'coupons.time_start as dateStartSale',
                    'coupons.time_end as dateEndSale',
                    DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(img_products.img ORDER BY img_products.img SEPARATOR ","), ",", 1) as img_first')
                )
                ->groupBy(
                    'cart_products.cart_id',
                    'cart_products.user_id',
                    'cart_products.deleted_at',
                    'cart_products.created_at',
                    'cart_products.updated_at',
                    'products.product_id',
                    'products.name',
                    'products.code_product',
                    'products.unit_of_measurement',
                    'products.active_ingredient',
                    'products.used',
                    'products.description',
                    'products.price',
                    'products.quantity',
                    'products.category_id',
                    'products.brand',
                    'products.manufacture',
                    'products.registration_number',
                    'products.status',
                    'products.deleted_at',
                    'products.created_at',
                    'products.updated_at',
                    'cart_details.cart_detail_id',
                    'cart_details.product_id',
                    'cart_details.quantity',
                    'cart_details.cart_id',
                    'cart_details.deleted_at',
                    'cart_details.created_at',
                    'cart_details.updated_at',
                    'coupons.discount_code',
                    'coupons.percent',
                    'coupons.time_start',
                    'coupons.time_end'
                )
                ->get();

            $order_user = OrderProduct::join('payment_products', 'payment_products.order_id', '=', 'order_products.order_id')
                ->join('cart_products', 'cart_products.cart_id', '=', 'order_products.cart_id')
                ->join('cart_details', 'cart_details.cart_id', '=', 'cart_products.cart_id')
                ->join('products', 'products.product_id', '=', 'cart_details.product_id')
                ->join('img_products', 'img_products.product_id', '=', 'products.product_id')
                ->where('order_products.user_id', $user_id)
                ->whereNull('order_products.deleted_at')
                ->select(
                    'order_products.*',
                    'payment_products.payment_method',
                    'payment_products.payment_status',
                    'payment_products.payment_id',
                    'cart_products.cart_id',
                    
                )
                ->groupBy('order_products.order_id','order_products.quantity','order_products.price_old','order_products.price_sale','order_products.order_status','order_products.order_username','order_products.order_phone','order_products.order_address','order_products.note', 'cart_products.cart_id','order_products.cart_id','order_products.product_id','order_products.coupon_id','order_products.user_id','order_products.deleted_at','order_products.created_at','order_products.updated_at', 'payment_products.payment_id', 'payment_products.payment_method', 'payment_products.payment_status',)
                ->get();

                

                
        } else {
            $product = [];
            $order_user = [];
        }

        return view('shop.order', ['order' => $order, 'product' => $product, 'order_user' => $order_user]);
    }



    function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }

    public function handleMomoPaymentResponse(Request $request)
    {
        // Lấy thông tin phản hồi từ MoMo
        $resultCode = $request->query('resultCode'); // Mã kết quả (0 = thành công)

        $orderId = OrderProduct::latest()->pluck('order_id')->first();
        if ($resultCode == 0) {

            $payment = new PaymentProduct();
            $payment->payment_method = 2;
            $payment->payment_status = 1;
            $payment->order_id = $orderId;
            $payment->save();


            return redirect()->route('shop.bill')->with([
                'success' => 'Thanh toán thành công!',
                'active_tab' => 'order',
            ]);
        } else {
            $payment = new PaymentProduct();
            $payment->payment_method = 2;
            $payment->payment_status = 2;
            $payment->order_id = $orderId;
            $payment->save();


            return redirect()->route('shop.bill')->with([
                'error' => 'Thanh toán không thành công!',
                'active_tab' => 'order',
            ]);
        }
    }
    public function handleZaloPaymentResponse(Request $request)
    {
        $status = $request->query('status');
        $orderId = OrderProduct::latest()->pluck('order_id')->first();

        if ($status == 1) {

            $payment = new PaymentProduct();
            $payment->payment_method = 4;
            $payment->payment_status = 1;
            $payment->order_id = $orderId;
            $payment->save();

            return redirect()->route('shop.bill')->with([
                'success' => 'Thanh toán thành công!',
                'active_tab' => 'order',
            ]);
        } else {
            $payment = new PaymentProduct();
            $payment->payment_method = 4;
            $payment->payment_status = 2;
            $payment->order_id = $orderId;
            $payment->save();
            // Thanh toán thất bại

            return redirect()->route('shop.bill')->with([
                'error' => 'Thanh toán không thành công!',
                'active_tab' => 'order',
            ]);
        }
    }



    public function handleVNPaymentResponse(Request $request)
    {

        $vnp_TransactionStatus = $request->query('vnp_TransactionStatus');
        $vnp_TxnRef = $request->query('vnp_TxnRef'); // Mã giao dịch


        $paymentExists = PaymentProduct::where('order_id', $vnp_TxnRef)->exists();
        if ($paymentExists) {
            return redirect()->route('shop.bill')->with(['error' => 'Giao dịch này đã được xử lý trước đó.']);
        }


        $order_id = OrderProduct::latest()->pluck('order_id')->first();

        // Kiểm tra trạng thái giao dịch
        if ($vnp_TransactionStatus == '00') { // Thành công
            $payment = new PaymentProduct();
            $payment->order_id = $order_id;
            $payment->payment_method = 1; // Ví dụ: 1 = VNPAY
            $payment->payment_status = 1; // Thành công
            $payment->save();


            return redirect()->route('shop.bill')->with([
                'success' => 'Thanh toán thành công!',
                'active_tab' => 'order',
            ]);
        } else { // Không thành công
            $payment = new PaymentProduct();
            $payment->order_id = $order_id;
            $payment->payment_method = 1; // Ví dụ: 1 = VNPAY
            $payment->payment_status = 2; // Không thành công
            $payment->save();


            return redirect()->route('shop.bill')->with([
                'error' => 'Thanh toán không thành công!',
                'active_tab' => 'order',
            ]);
        }
    }




    public function order(Request $request)
    {

        // Nếu có lỗi xác thực, trả về lỗi dưới dạng JSON

        $username = $request->input('first_name') . ' ' . $request->input('last_name');
        $address = $request->input('address') . ', ' . $request->input('ward_name') . ', ' . $request->input('district_name') . ', ' . $request->input('province_name');
        $user = Auth::user();
        $user_id = $user->user_id;
        $order = new OrderProduct();
        $order->quantity = $request->input('quantity');
        $order->price_old = $request->input('total');
        $order->price_sale = $request->input('total_final');
        $order->order_status = $request->input('coupon_id');
        $order->order_status = 0;
        $order->order_username = $username;
        $order->order_phone = $request->input('phone');
        $order->order_address = $address;
        $order->note = $request->input('note');
        $order->cart_id  = $request->input('cart_id');
        $order->user_id   = $user_id;
        $order->save();

        if ($request->input('coupon_id')) {
            $coupon = Coupon::where('discount_code', $request->input('coupon_id'))->first();
            $quantity = $coupon->use_limit;
            $coupon->use_limit = $quantity - 1;
            $coupon->update();
        }

        $cart_id = $order->cart_id;
        CartDetail::where('cart_id', $cart_id)->delete();
        CartProduct::where('cart_id', $cart_id)->delete();

        $price = $request->input("total_final");
        $code = rand(00, 99999);
        if ($request->input('payment_option') == 'vnpay') {
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = env('APP_URL').  "/cua-hang/payment/vnpay/return";
            $vnp_TmnCode = "ZAZD6H5N"; //Mã website tại VNPAY 
            $vnp_HashSecret = "VG6VICU7L6V62EHHPKMPR7UG45FBK918"; //Chuỗi bí mật

            $vnp_TxnRef = $code;
            $vnp_OrderInfo = 'Thanh toán hóa đơn';
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $price * 100;
            $vnp_Locale = 'vn';
            $vnp_BankCode = 'NCB';
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef

            );

            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }
            if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
                $inputData['vnp_Bill_State'] = $vnp_Bill_State;
            }

            //var_dump($inputData);
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            $returnData = array(
                'code' => '00',
                'message' => 'success',
                'data' => $vnp_Url
            );

            if (isset($_POST['redirect'])) {
                if ($order) {
                } else {
                    dd('Lưu đơn hàng không thành công');
                }
                // Redirect tới VNPAY
                header('Location: ' . $vnp_Url);
                die();
            } else {
                echo json_encode($returnData);
            }
        } elseif ($request->input('payment_option') === 'momo') {

            // Cấu hình thông tin gửi yêu cầu thanh toán
            $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
            $partnerCode = 'MOMOBKUN20180529';
            $accessKey = 'klm05TvNBzhg7h7j';
            $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
            $orderInfo = "Thanh toán qua MoMo";
            $amount = $price;
            $orderId = time() . "";
            $redirectUrl = env('APP_URL'). "/cua-hang/payment/momo/return";
            $ipnUrl = env('APP_URL').  "/cua-hang/hoa-don";
            $extraData = "";
            $requestId = time() . "";
            $requestType = "payWithATM";

            // Tạo rawHash và signature
            $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => "Test",
                'storeId' => "MomoTestStore",
                'requestType' => $requestType,
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'signature' => $signature
            ];

            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);
            return redirect()->to($jsonResult['payUrl']);
        } elseif ($request->input('payment_option') === 'zalopay') {
            $config = [
                "app_id" => 2554,
                "key1" => "sdngKKJmqEMzvh5QQcdD2A9XBSKUNaYn",
                "key2" => "trMrHtvjo6myautxDUiAcYsVtaeQ8nhf",
                "endpoint" => "https://sb-openapi.zalopay.vn/v2/create"
            ];

            // Đảm bảo URL của bạn được cấu hình đúng để nhận phản hồi từ ZaloPay
            $redirectUrl = env('APP_URL').  '/cua-hang/payment/zalopay/return'; // Thay bằng tên miền của bạn

            $embeddata = json_encode([
                "redirecturl" => $redirectUrl // Cấu hình đúng URL sau khi thanh toán
            ]);
            $price = (int) $price;
            $items = '[]';
            $transID = rand(0, 1000000);

            $order = [
                "app_id" => $config["app_id"],
                "app_time" => round(microtime(true) * 1000),
                "app_trans_id" => date("ymd") . "_" . $transID,
                "app_user" => $user->$user_id,
                "item" => $items,
                "embed_data" => $embeddata,
                "amount" => $price,
                "description" => "Payment for order #$transID",
                "bank_code" => "",
            ];


            $data = implode("|", [
                $order["app_id"],
                $order["app_trans_id"],
                $order["app_user"],
                $order["amount"],
                $order["app_time"],
                $order["embed_data"],
                $order["item"]
            ]);

            $order["mac"] = hash_hmac("sha256", $data, $config["key1"]);

            // Gửi yêu cầu tới ZaloPay endpoint
            $result = $this->execPostRequest($config["endpoint"], json_encode($order));
            $jsonResult = json_decode($result, true);

            // Kiểm tra kết quả và chuyển hướng
            if (isset($jsonResult['order_url'])) {
                return redirect()->to($jsonResult['order_url']);
            } else {
                return back()->withErrors(['error' => 'Không thể tạo thanh toán ZaloPay: ' . json_encode($jsonResult)]);
            }
        } elseif ($request->input('payment_option') === 'cash') {
            $order_id = OrderProduct::latest()->pluck('order_id')->first();
            $payment = new PaymentProduct();
            $payment->order_id = $order_id;
            $payment->payment_method = 0;
            $payment->payment_status = 0;
            $payment->save();



            return redirect()->route('shop.bill')->with([
                'success' => 'Đặt hàng thành công!',
                'active_tab' => 'order',
            ]);
        }
    }
}