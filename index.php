<?php
// โหลด autoload ของ LINE SDK
require __DIR__ . '/vendor/autoload.php';

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

// === กำหนดคีย์ต่าง ๆ ===
$channelToken = 'lmUw6lWXN27imHzBTrE/9EVl4MwOiIqmdzhz4co123oNl0T5pGkcG2nuiOdrCLxmM0dI/bRO8fObRYZNRAPmOMFkw7fmnu40Imy2A0ACsjgdizx0LFfhv5oa3JGs1k5mbU8xujJ+PUzWtWIVST8C9QdB04t89/1O/w1cDnyilFU='; // 👉 ใส่ LINE Access Token
$channelSecret = 'd439583024dd7b3fcb76f1c24d786a1d';       // 👉 ใส่ LINE Channel Secret
$geminiApiKey = 'AIzaSyDu8S2ngxRmAwGCnInPHsXp11uHBPtOaMo';             // 👉 ใส่ Google Gemini API Key

// === สร้าง LINE Bot Object ===
$httpClient = new CurlHTTPClient($channelToken);
$bot = new LINEBot($httpClient, ['channelSecret' => $channelSecret]);

// === รับข้อมูลจาก Webhook ===
$input = file_get_contents('php://input');
$events = json_decode($input, true);

// === ตรวจสอบว่ามี event หรือไม่ ===
if (!isset($events['events'])) {
    http_response_code(400);
    exit();
}

// === วนลูปตอบแต่ละ event ===
foreach ($events['events'] as $event) {
    if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
        $userText = $event['message']['text'];
        $replyToken = $event['replyToken'];

        // เรียกใช้ Gemini เพื่อหาคำตอบ
        $aiReply = askGemini($userText, $geminiApiKey);

        // ส่งข้อความกลับไปยัง LINE
        $message = new TextMessageBuilder($aiReply);
        $bot->replyMessage($replyToken, $message);
    }
}

// === ฟังก์ชันส่งข้อความไปหา Gemini AI ===
function askGemini($text, $apiKey) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$apiKey";
    $postData = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $text]
                ]
            ]
        ]
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($postData),
            'timeout' => 10
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return "ขออภัย ไม่สามารถติดต่อ AI ได้ในขณะนี้ 😢";
    }

    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "ขออภัย ไม่สามารถตอบได้";
}
