package main

import (
	"C"
	"encoding/json"
	"fmt"
)

// То, что мы ждем от PHP
type PhpRequest struct {
	Action      string `json:"action"`
	PayloadJson string `json:"payload_json"`
}

// То, в чем будем отвечать в PHP
type PhpResponse struct {
	Goal    string `json:"goal"` // "ok"|"fail"
	Payload string `json:"payload_json"`
}

func main() {
	// Ничего
}

// Принимает запросы от PHP в виде JSON строки, в том же формате отдает назад, выполнив обработку.
//
// Если передалать на возвращаемый тип данных string, то будут фатальные ошибки
// func HandleRequestFromPhp(requestStr string) string {
//
//export HandleRequestFromPhp
func HandleRequestFromPhp(requestStr string) *C.char {
	request := PhpRequest{}
	json.Unmarshal([]byte(requestStr), &request)

	var callResult string
	var err error
	switch request.Action {
	case "HelloPhp":
		callResult = HelloPhp()
	case "CallServices":
		callResult, err = CallServices(request.PayloadJson)
	default:
		err = fmt.Errorf("Unknown action %s", request.Action)
	}
	response := PhpResponse{}
	if err != nil {
		response.Goal = "fail"
		response.Payload = err.Error()
	} else {
		response.Goal = "ok"
		response.Payload = callResult
	}
	responseString, _ := json.Marshal(response)

	return C.CString(string(responseString))
	// Раскомментировать, если хочется посмотреть на фатальную ошибку
	// return string(responseString)
}
