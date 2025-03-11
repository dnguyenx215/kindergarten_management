<?php

namespace App\Http\Controllers;

use App\Models\TuitionFee;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TuitionFeeController extends Controller
{
    // Lấy danh sách học phí của từng học sinh (có thể lọc theo student_id nếu cần)
    public function index(Request $request)
    {
        if ($request->has('student_id')) {
            $fees = TuitionFee::where('student_id', $request->input('student_id'))
                ->orderBy('due_date')
                ->get();
        } else {
            $fees = TuitionFee::orderBy('due_date')->get();
        }
        return response()->json(['data' => $fees], 200);
    }

    // Tạo mới học phí
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount'     => 'required|numeric|min:0',
            'due_date'   => 'required|date',
            'paid'       => 'sometimes|boolean',
        ]);

        $fee = TuitionFee::create($validated);
        return response()->json(['data' => $fee], 201);
    }

    // Hiển thị chi tiết học phí
    public function show(TuitionFee $tuitionFee)
    {
        return response()->json(['data' => $tuitionFee], 200);
    }

    // Cập nhật thông tin học phí
    public function update(Request $request, TuitionFee $tuitionFee)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes|required|exists:students,id',
            'amount'     => 'sometimes|required|numeric|min:0',
            'due_date'   => 'sometimes|required|date',
            'paid'       => 'sometimes|required|boolean',
        ]);

        $tuitionFee->update($validated);
        return response()->json(['data' => $tuitionFee], 200);
    }

    // Xóa học phí
    public function destroy(TuitionFee $tuitionFee)
    {
        $tuitionFee->delete();
        return response()->json(['message' => 'Học phí đã được xoá thành công'], 200);
    }
    
    /**
     * Tích hợp thanh toán online qua VNPAY.
     *
     * Phương thức này tạo URL thanh toán VNPAY dựa trên thông tin học phí.
     *
     * Yêu cầu truyền các dữ liệu sau (nếu cần bổ sung):\n
     * - order_desc: Mô tả đơn hàng (mặc định: \"Thanh toán học phí\")\n
     * - order_type: Loại đơn hàng (mặc định: \"billpayment\")\n
     * - language: Ngôn ngữ (mặc định: \"vn\")\n
     * - bank_code: Mã ngân hàng (nếu có)\n
     * - txtexpire: Thời gian hết hạn thanh toán (nếu có)\n
     *
     * Thông tin cấu hình VNPAY được lấy từ file .env
     *
     * @param Request $request\n     * @param TuitionFee $tuitionFee\n     * @return \Illuminate\Http\JsonResponse\n     */
    public function pay(Request $request, TuitionFee $tuitionFee)
    {
        // Thiết lập múi giờ
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        // Lấy cấu hình VNPAY từ .env
        $vnp_Url = env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $vnp_ReturnUrl = env('VNP_RETURN_URL', 'http://localhost/vnpay_return.php');
        $vnp_TmnCode = env('VNP_TMNCODE', '');        // Mã website tại VNPAY
        $vnp_HashSecret = env('VNP_HASHSECRET', '');    // Chuỗi bí mật

        // Tạo mã giao dịch từ ID của học phí
        $vnp_TxnRef = $tuitionFee->id;
        $vnp_OrderInfo = $request->input('order_desc', 'Thanh toán học phí');
        $vnp_OrderType = $request->input('order_type', 'billpayment');
        $vnp_Amount = $tuitionFee->amount * 100; // VNPAY yêu cầu số tiền nhân 100
        $vnp_Locale = $request->input('language', 'vn');
        $vnp_BankCode = $request->input('bank_code', '');
        $vnp_IpAddr = $request->ip();
        $vnp_ExpireDate = $request->input('txtexpire', '');

        // Tạo mảng dữ liệu để gửi sang VNPAY
        $inputData = [
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
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];
        if (!empty($vnp_ExpireDate)) {
            $inputData['vnp_ExpireDate'] = $vnp_ExpireDate;
        }
        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        
        // Sắp xếp theo key
        ksort($inputData);
        $hashdata = "";
        $query = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . "&";
        }
        
        // Tạo mã bảo mật (hash) nếu có secret key
        if ($vnp_HashSecret) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $query .= "vnp_SecureHash=" . $vnpSecureHash;
        }
        $vnp_Url = $vnp_Url . "?" . $query;
        
        // Trả về URL thanh toán VNPAY dưới dạng JSON
        return response()->json([
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        ], 200);
    }

       /**
     * Xử lý kết quả trả về từ VNPAY sau khi thanh toán thành công.
     *
     * URL này được cấu hình trong VNP_RETURN_URL và VNPAY sẽ redirect về đây sau khi thanh toán.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function vnpayReturn(Request $request)
    {
        // Lấy tất cả tham số từ VNPAY
        $vnpData = $request->all();

        // Lấy SecureHash do VNPAY gửi về
        $vnp_SecureHash = $request->get('vnp_SecureHash');

        // Loại bỏ các tham số không cần dùng để tính hash
        unset($vnpData['vnp_SecureHash']);
        unset($vnpData['vnp_SecureHashType']);

        // Sắp xếp theo key
        ksort($vnpData);

        // Xây dựng chuỗi hash data
        $hashData = "";
        $i = 0;
        foreach ($vnpData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }

        // Lấy HashSecret từ file .env
        $vnp_HashSecret = env('VNP_HASHSECRET');
        $calculatedHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Kiểm tra hash hợp lệ hay không
        if ($calculatedHash === $vnp_SecureHash) {
            // Kiểm tra ResponseCode: '00' nghĩa là thanh toán thành công
            if ($request->get('vnp_ResponseCode') == '00') {
                // vnp_TxnRef chứa ID của học phí (do chúng ta sử dụng ID của TuitionFee làm TxnRef khi tạo URL thanh toán)
                $txnRef = $request->get('vnp_TxnRef');
                $tuitionFee = TuitionFee::find($txnRef);

                if ($tuitionFee) {
                    $tuitionFee->update([
                        'paid' => true,
                        'paid_at' => Carbon::now(),
                    ]);

                    return response()->json([
                        'code' => '00',
                        'message' => 'Thanh toán thành công, học phí đã được cập nhật.',
                        'data' => $tuitionFee
                    ], 200);
                } else {
                    return response()->json([
                        'code' => '01',
                        'message' => 'Không tìm thấy thông tin học phí tương ứng.'
                    ], 404);
                }
            } else {
                return response()->json([
                    'code' => $request->get('vnp_ResponseCode'),
                    'message' => 'Thanh toán không thành công, vui lòng thử lại.'
                ], 400);
            }
        } else {
            return response()->json([
                'code' => '97',
                'message' => 'Mã bảo mật không hợp lệ.'
            ], 400);
        }
    }
}
