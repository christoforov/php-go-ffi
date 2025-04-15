<?php
require_once "helpers.php";

/**
 * Демонстрирует, что последовательные запросы через PHP
 * к каждому из сервисов, переданному в массиве
 * @param array $serviceUrls
 * @return void
 */
function makeSyncCalls(array $serviceUrls)
{
    $timeStarted = time();

    $result = [];
    foreach ($serviceUrls as $url) {
        $result[$url] = callService($url);
    }
    var_dump($result);
    echo "Total time: " . time() - $timeStarted . PHP_EOL;     
}

/**
 * Вызываем наш Hello Php из библиотеки на Go
 * @return void
 */
function makeGoSayHelloPhp()
{
    $cLangHeader = "
    typedef struct { const char *p; long n; } GoString;

    GoString HandleRequestFromPhp(GoString requestStr);
    ";
    $goLibrary = "/var/app/bin/handler-for-php.so";
    $ffi = FFI::cdef($cLangHeader, $goLibrary);
    $phpRequest = json_encode(["action" => "HelloPhp", "payload" => ""]);
    $phpRequestGoString = stringToGoString($ffi->new("GoString"), $phpRequest);
    $goResponse = $ffi->HandleRequestFromPhp($phpRequestGoString);
    var_dump($goResponse->p);
}

/**
 * Делает вызов на Go метода CallServices, который сделает асинхронные
 * вызовы ко всем переданным сервисам. 
 * @param array $serviceUrls
 * @return void
 */
function makeGoAsyncCalls(array $serviceUrls)
{
    $timeStarted = time();

    $callParams = [
        'service_urls' => $serviceUrls,
    ];
    $response = callGo("CallServices", $callParams);
    //var_dump($response);
    // Приводим ответ к тому же формату, что и в makeSyncCalls
    $payload = json_decode($response['payload_json'], true);
    $result = [];
    foreach ($payload as $url => $responseJson) {
        $urlResponse = json_decode($responseJson, true);
        $result[$url] = $urlResponse['result'];
    }
    var_dump($result);

    echo "Total time: " . time() - $timeStarted . PHP_EOL;     
}

// Ссылки на наши сервисы. Все отвечают по 3 секунды
$serviceUrls = ["http://php_service_a/", "http://php_service_b/", "http://php_service_c/"];    

// 1. Без Go
// Три последовательных вызова к нашим трем сервисам - итого 9 секунд
makeSyncCalls($serviceUrls);

// 2. Получаем "hello PHP" от Go
makeGoSayHelloPhp();

// 3. Запросы к сервисам через библиотеку Go - итого 3 секунды (запросы ушли параллельно)
makeGoAsyncCalls($serviceUrls);