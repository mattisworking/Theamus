# Theamus API JavaScript

```js
Theamus.Ajax.api(options);
```

## Options
|Key|Value Type|Required|Description|
|---|---|:---:|---|
|type|string|yes|The type of call to be made. Either "POST" or "GET"|
|url|string|yes|The URL to make the call to|
|method|array|yes|The backend method to run when making the call. `[0]` = Class, `[1]` = function|
|data|object|no|The data to send along with the call|
|data.form|object|no|A form element that Theamus will find data in and send that data along with the request|
|data.custom|object|no|Custom object of information to send along with the request|
|upload|object|no|Object of information for when uploading files|
|upload.during|function|yes if upload is set|Function to run anytime there's an update in the upload process. Accepts a data argument|
|success|function|yes|The function to run after the API has successfully finished (error or not, "success as in there was no JS errors")|

## Example
```js
Theamus.Ajax.api({
  type: "post",
  url: Theamus.base_url + "/blog",
  method: ["Blog", "create_post"],
  data: {
    form: document.getElementById("form"),
    custom: {
      "tries": 1
    }
  }
  success: function(r) {
    console.log(r);
  }
})
```

### Explanation
- You're going to send a POST request to the backend
- You want to call the "blog" feature (you do this in the URL)
- On the backend, you want to run the function `Blog->create_post()`
- You want to send form information along with a try count to the `create_post()` function.
- Once there is a response from the server, you will run the success function which will log the response information in the console.

#### Response Information
```json
{
  "data": {
    "error": {
      "status": 1,
      "message": "There was an error",
      "code": 10000
    },
    "response": {
      "data": true,
      "headers": "...",
      "status": 200,
      "text": "success"
    }
  }
}
```

##### Response Information Explantion
|Key|Description|
|---|---|
|data|All of the data returned by the request|
|data.error|An object of error information from the request|
|data.error.status|`1` if there is an error `0` if there isn't.|
|data.error.message|The error message sent from the backend|
|data.error.code|The error code sent from the backend|
|data.response.data|Data sent back from the backend (return of function)|
|data.response.headers|Headers of the request|
|data.response.status|Response status of the request (200, 404, 500, etc)|
|data.response.text|String of the response status (success, error)|
