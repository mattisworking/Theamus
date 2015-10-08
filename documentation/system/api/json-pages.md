##Requesting Pages in Theamus as JSON

It's pretty simple. Simpler than you think. Just throw `/:json` in the URL and you're good to go!

Works with both pages _and_ features.

```
http://localhost/hello-world/ -> produces a page with the theme, and styling.


http://localhost/:json/hello-world/ -> produces JSON of the page's content

http://localhost/hello-world/:json -> same as above

http://localhost/default/:json/index/ -> same as above
```

###Return JSON

```js
{
    "content": "",
    "scripts": []
}
```

>Content is what the page would've generated. It's trimmed down, no formatting or anything. Just straight up HTML.
>The scripts, on the other hand are extracted from the content.

###Example
If you have a page for `http://localhost/hi-mom/`
```html
<div>
    Hi mom!
</div>

<script>
    console.log("Hi dad!");
</script>
```

Instead of the content including the script tag, it's minified and put into it's own variable so that you can add it to the DOM dynamically.

```js
// Going to http://localhost/:json/hi-mom/ returns:
{
    "content": "<div> Hi mom! </div>",
    "scripts": [
        "console.log(\"Hi dad!\");"
    ]
}
```