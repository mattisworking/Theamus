# Uploading Files API
Pretty much the same as before. This will work if you have a file element in the form you're submitting, or if you have a FLO (file list object).

> __Note:__ The type of call MUST BE POST.

> __Note:__ You can only upload 1 file at a time.

## Example
```html
<script>
Theamus.Ajax.api({
  type: "post",
  url: Theamus.base_url + "/blog",
  method: ["Blog", "upload_file"],
  data: {
    custom: {
      file: flo[0] // using a file list object
    }
  },
  upload: {
    during: function(data) {
      console.log(data);
    }
  },
  success: function(r) {
    console.log("File Uploaded!");
  }
});
</script>
```

### upload.during() Information
|Item|Description|
|---|---|
|percent_completed|Percentage number of upload completion|
|percentage|Number + % string|
|loaded|Uploaded file size so far in bytes|
|loaded_formatted|Uploaded file size so far, formatted to be human readable|
|total_bytes|Total size of upload|
|total_bytes_formatted|Total size of upload formatted to be human readable|
|time_micro|Microtime at the time of upload event trigger (when the percentage changed)|
|time_formatted|Human readable time of upload event trigger (when the percentage changed)|

## Backend
On the backend of this, in your class function, you'll receive the file information as you normally would. In your `$args` array for the class function argument, it will be set to the variable you defined when defining `data.custom` in the JavaScript.
