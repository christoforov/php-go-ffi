<?php

/**
 * Отправляет запрос к сервису и пишет ответ в stdout
 * Ожидает ответ от сервиса в формате JSON и вернет то, что пришло в поле result
 * @param mixed $url
 * @return ?string
 */
function callService(string $url): string
{
    $response = file_get_contents($url);
    $result = json_decode($response, true);
    return $result['result'] ?? null;
}

/**
 * Функция формирует структуру данных типа GoString
 * на языке C.
 * У такой структуры есть указатель на char *p
 * и количество байтов (длина строки) int n
 * 
 * @param \FFI\CData $goStr
 * @param string $string
 * @return \FFI\CData
 */
function stringToGoString($goStr, $string) {
    $strChar = str_split($string);
    $ffi = FFI::cdef();
    $c = $ffi->new('char[' . count($strChar) . ']', false);
    foreach ($strChar as $i => $char) {
        $c[$i] = $char;
    }
    $goStr->p = $ffi->cast($ffi->type('char *'), $c);
    $goStr->n = count($strChar);

    return $goStr;
}

/**
 * Выполняет вызов функции из библиотеки на Go
 * @param string $action название действия, которое поддерживается библиотекой на Go
 * @param mixed $payload параметры к действию, которые будут туда переданы (паковаться будут JSON)
 * @return array
 */
function callGo(string $action, mixed $payload = null): array
{
    // Готовим параметры перед отправкой в FFI
    $payloadJson = $payload === null ? "" : json_encode($payload);
    $phpRequest = json_encode(["action" => $action, "payload_json" => $payloadJson]);
    
    // Получаем экземпляр FFI
    $cLangHeader = "
    typedef struct { const char *p; long n; } GoString;
    GoString HandleRequestFromPhp(GoString requestStr);
    ";
    $goLibrary = "/var/app/bin/handler-for-php.so";
    $ffi = FFI::cdef($cLangHeader, $goLibrary);
    
    // Делаем Go-строку из нашего JSON
    $phpRequestGoString = stringToGoString($ffi->new("GoString"), $phpRequest);
    // Запуск функции handler-for-php.so::HandleRequestFromPhp
    $goResponse = $ffi->HandleRequestFromPhp($phpRequestGoString);
    
    // Полученный ответ будет в JSON, поэтому декодируем его
    return json_decode($goResponse->p, true);
}