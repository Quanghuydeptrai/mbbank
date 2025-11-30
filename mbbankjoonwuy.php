<?php
/*
====================================================
 🔰 SePay API Viewer for MB Bank
 🔰 Tác giả: ChatGPT x Hải Hoàng
 🔰 Chức năng: Lấy lịch sử giao dịch MB Bank qua API SePay
 🔰 Phiên bản: 2025-11 (Bản tiền không dấu)
====================================================
*/

$token = 'SPSHS3ZLT6WGHPM5DOAZLYW4ZYQFSCKQEIIN8FT43Y9ZBNARORB62XGR28VUJE1C';
$account = '7705777777';
$limit = 50;

$url = "https://my.sepay.vn/userapi/transactions/list?account_number={$account}&limit={$limit}";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json; charset=utf-8');
http_response_code($http_code);

if ($http_code == 200 && !empty($response)) {
    $data = json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE && isset($data['transactions'])) {
        $filtered = [];

        foreach ($data['transactions'] as $item) {
            // 🔸 Chỉ lấy giao dịch cộng tiền (amount_in > 0)
            if (!isset($item['amount_in']) || floatval($item['amount_in']) <= 0) continue;

            // 🔹 Làm tròn và bỏ dấu
            foreach (['amount_in', 'amount_out', 'accumulated'] as $key) {
                if (isset($item[$key])) {
                    $item[$key] = (string)intval(round(floatval($item[$key])));
                }
            }

            $filtered[] = $item;
        }

        // 🔹 Gán lại danh sách giao dịch đã lọc
        $data['transactions'] = $filtered;
    }

    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'error' => 'Không thể kết nối đến API SePay',
        'status_code' => $http_code,
        'response' => $response
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/*
====================================================
 ✅ HƯỚNG DẪN:
 1. Upload file này lên host (public_html hoặc www)
 2. Mở https://api.tumbox.shop
 3. Chỉ hiển thị các giao dịch cộng tiền
 4. Số tiền gọn: 10000 thay vì 10.000 hoặc 10000.00
====================================================
*/
?>