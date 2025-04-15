package main

import (
	"bufio"
	"bytes"
	"encoding/json"
	"fmt"
	"net/http"
	"sync"
)

type CallServicesParams struct {
	ServiceUrls []string `json:"service_urls"`
}

type ResultState struct {
	mutex    sync.Mutex
	services map[string]string
}

// Вызывает сервисы, переданные в виде списка url, которые находятся в JSON объекте
// Запросы выполняются параллельно
// Ответ формируется в map<url, url_response> и оборачивается в JSON строку
func CallServices(payloadJson string) (string, error) {
	var params CallServicesParams
	err := json.Unmarshal([]byte(payloadJson), &params)
	if err != nil {
		return "", nil
	}

	var waitGroup sync.WaitGroup

	var result ResultState
	result.services = make(map[string]string)

	for _, serviceUrl := range params.ServiceUrls {
		waitGroup.Add(1)
		go func() {
			defer waitGroup.Done()

			callResult := callService(serviceUrl)

			result.mutex.Lock()
			defer result.mutex.Unlock()
			result.services[serviceUrl] = callResult
		}()
	}

	waitGroup.Wait()

	marshalledResult, _ := json.Marshal(result.services)
	return string(marshalledResult), nil
}

// Делает HTTP GET-запрос к сервису и возвращает тело ответа
func callService(url string) string {
	var result bytes.Buffer

	response, err := http.Get(url)
	if err != nil {
		return fmt.Sprintf("error in %s", url)
	}
	defer response.Body.Close()
	// Читаем HTTP ответ в одну длинную строчку
	scanner := bufio.NewScanner(response.Body)
	for scanner.Scan() {
		result.WriteString(scanner.Text())
	}
	return string(result.String())
}
