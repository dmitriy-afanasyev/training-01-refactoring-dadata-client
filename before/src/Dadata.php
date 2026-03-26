<?php

class DaDataRu {

    protected $curl;
    protected $messages;
    protected $result;
    protected $temp;
    protected $http_request_result;
    protected $api_key = 'apikey';
    public $http_headers;

    public function getCompanyDataByInn($inn) {
        $url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token '.$this->api_key,
        ];
        $post_data = json_encode(['query' => $inn, 'branch_type' => 'MAIN']);
        $result = json_decode(self::requestUrl($url, false, 'POST', $post_data, $headers));
        if (isset($result->suggestions) && count($result->suggestions) > 0) {
            return $result->suggestions[0]->data;
        }
        return false;
    }

    public function getBankDataByBic($bic) {
        $url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/bank';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token '.$this->api_key,
        ];
        $post_data = json_encode(['query' => $bic]);
        $result = json_decode(self::requestUrl($url, false, 'POST', $post_data, $headers));
        if (isset($result->suggestions) && count($result->suggestions) > 0) {
            return $result->suggestions[0]->data;
        }
        return false;
    }

    public function searchCountry($country) {
        $url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/country';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token '.$this->api_key,
        ];
        $post_data = json_encode(['query' => $country]);
        $result = json_decode(self::requestUrl($url, false, 'POST', $post_data, $headers));
        if (isset($result->suggestions) && count($result->suggestions) > 0) {
            return array_map(function ($data) { return $data->value ?? null; }, $result->suggestions);
        }
        return false;
    }

    public function searchAddress($search, $locations = null) {
        $url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token '.$this->api_key,
        ];
        $post_data = json_encode(
            [
                'query' => $search,
                'locations' => $locations
            ]
        );
        $result = json_decode(self::requestUrl($url, false, 'POST', $post_data, $headers));
        if (isset($result->suggestions) && count($result->suggestions) > 0) {
            return array_map(function ($data) { return $data->value ?? null; }, $result->suggestions);
        }
        return false;
    }

    protected function curlStart() {
        //Инициализируем cURL
        $this->curl = curl_init();
        if (filter_var(config('app.external_ip'), FILTER_VALIDATE_IP)) {
            curl_setopt($this->curl, CURLOPT_INTERFACE, config('app.external_ip')); // работаем через указанный IP адрес
        }
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);    // возвращает веб-страницу
        curl_setopt($this->curl, CURLOPT_HEADER, 0);            // не возвращает заголовки
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);  // Возвращает HTTP заголовки переданные удаленному серверу
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 40);        // таймаут ответа
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 20); // таймаут соединения
        curl_setopt($this->curl, CURLOPT_ENCODING, "");       // обрабатывает все кодировки
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);    // переходить по редиректам
        curl_setopt($this->curl, CURLOPT_MAXREDIRS, 10);        // останавливаться после 10-ого редиректа
        curl_setopt($this->curl, CURLOPT_VERBOSE, 1);
        return true;
    }

    protected function requestUrl($url, $referer = false, $method = 'POST', $post_data = false, $headers = [], $proxy = false) {
        //Проверяем есть ли сессия cUrl
        if (!$this->curl) {
            $this->curlStart();
        }
        //Указываем реферера, если передан
        if ($referer) {
            curl_setopt($this->curl, CURLOPT_REFERER, $referer);
        }
        //Делаем POST запрос, если требуется
        if ($method === 'POST') {
            curl_setopt($this->curl, CURLOPT_POST, true);
            //Если данные запроса - массив, переводим его в строку
            if ($post_data) {
                if (is_array($post_data)) {
                    $post_data = http_build_query($post_data);
                }
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_data);
            }
        } else {
            curl_setopt($this->curl, CURLOPT_POST, false);
        }
        //Задаём заголовки запроса, если переданы
        $accept_language = array('Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7'); //Задаём заголовок с ожидаемым языком сайта
        if (is_array($headers)) {
            $headers_request = array_merge($accept_language, $headers);
        } else {
            $headers_request = $accept_language;
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers_request);
        //Добавляем работу прокси, если требуется
        curl_setopt($this->curl, CURLOPT_PROXY, false);
        if ($proxy) {
            $proxy_data = explode('@', $proxy);
            if (isset($proxy_data[1])) {
                curl_setopt($this->curl, CURLOPT_PROXY, $proxy_data[1]);
                curl_setopt($this->curl, CURLOPT_PROXYUSERPWD, $proxy_data[0]);
            }
        }
        //Для сохранения строк заголовков ответа вызываем callback функцию saveResponseHeaders
        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'saveResponseHeaders'));
        //Указываем ссылку для перехода
        curl_setopt($this->curl, CURLOPT_URL, $url);
        //Получаем ответ в правильной кодировке
        $html = html_entity_decode(curl_exec($this->curl), ENT_HTML401, 'UTF-8');
        //Проверяем код ответа сервера
        $this->http_request_result = curl_getinfo($this->curl);
        return $html;
    }

    protected function saveResponseHeaders($ch, $header_line){
        $this->http_headers .= $header_line;
        return strlen($header_line);
    }
}
