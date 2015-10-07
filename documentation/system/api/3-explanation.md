# Theamus API Example
There's a lot of parts here, you've got the front end and you've got the backend. Those are both pretty easy to explain individually, but it's hard to see how it should work when they are together.

## Front End
```html
<div id="result"></div>
<form id="form">
  <input type="text" name="title">
  <textarea name="content"></textarea>
  <button type="submit">Submit"</button>
</form>

<script>
document.getElementById("form").addEventListener("submit", function(e) {
  e.preventDefault();
  Theamus.Ajax.api({
    type: "post", // posting data
    url: Theamus.base_url + "/blog", // to the blog feature
    method: ["Blog", "create_post"], // using this class and function
    data: {
      form: this // sending this data
    },
    success: function(r) { // and doing this when it's all over with
      if (r.error.status === 1) {
        document.getElementById("result").innerHTML = r.error.message;
      } else {
        document.getElementById("result").innerHTML = "Blog Post Created.";
      }
    }
  })
});
</script>
```

## Backend
```php
<?php
class Blog {
  // let the blog class use Theamus
  public function __construct($t) { $this->Theamus = $t; }

  // create a blog post
  public function create_post($args = array()) {
    // we've recieved data from the frontend form in the form of
    // array[<elementName>] = <elementValue>

    // query to add the information in the database
    $query = $this->Theamus->DB->insert_table_row(
      $this->Theamus->DB->table("posts"),
      array(
        "title" => $args['title'],
        "content" => $args['content']
      )
    );

    // log and error out if the query failed
    if (!$query) {
      $this->Theamus->Log->query($this->Theamus->DB->get_last_error());
      throw new Exception("Failed to create the post.");
    }

    // successful? return true
    return true;
  }
}
```

## Notes
- Do your user validation in any public function, as they could be run if someone manually runs the commands. Permissions are checked by your function on a run-by-run basis. Theamus allows everything. It's up to you to restrict functionality how you want.
- The URL defined in the frontend will point Theamus to the feature that you want to use. If you don't define the URL to your feature, then nothing will happen with the call.
