## What is Theamus Cards?
Theamus Cards are a way to bring a sort of standard to styling across all Theamus features and themes.

---
## Things you should know about Cards.
### It's always changing.
Hopefully all for the better, but sometimes it will be for the worse. In most cases though, you will be safe to use Theamus Cards on all of your features.

### The CSS classes.
Yeah, how in the world will the classes be named? Good question, young one. In my experience using Theamus, it's best to segregate every little thing to make sure it's specific to what you're going to be doing. That way you can't step on another feature's styling or anything like that.

Here's how the class naming will be done:
__feature__ > __element__ > __specifics__
#### Examples:
    calculator_input-wrapper
    accounts_user-listing-wrapper
    pages_page-wrapper
    card_expansion-wrapper

### JavaScript, oh JavaScript.
Everything in, around and about Theamus Cards is accessible through JavaScript. You'll learn more about that later, though. For the naming scheme, it will be most recognizable.

__Class__ > __Class__ > __someFunction__<br>
__Class__ > __Class__ > __someGetFunction__ > __property__ > __someFunction__

#### Examples:

```js
Theamus.Style.getAllCards();
Theamus.Style.getCard("card-id").expansion.expand();
```

---
## <a name="html">HTML Contents</a>
- [Cards](#cards)
 - [Expandable Cards](#expandable-cards)
 - [Text Cards](#text-card)
  - [Expandable Text](#expandable-text)
 - [Columns](#columns)
 - [Collapsibles](#collapsibles)
  - [Selectable Collapsibles](#selectable-collapsibles)
- [Inputs & Labels](#inputs)
 - [Labels](#labels)
 - [Input Types](#input-types)
 - [Attachments](#input-attachments)
 - [Helpers](#input-helpers)
- [Buttons](#buttons)

---
## <a name="js">JavaScript Contents</a>
- [Explain this to me. Pls.](#what-is-js)
- [Playing in the Developer Console](#playing-in-dev)
- [JS + Cards](#js-cards)
 - [Get 'em All](#getting-cards)
 - [Dynamically Loading](#dynamic-load)
 - [Expansions](#card-expansions)
 - [Collapsibles](#card-collapsibles)
  - [Selectable Collapsibles](#JS_selectable-collapsibles)
 - [Content Expansions](#js-content-expansions)
 - [Progress Bars](#js-progress)
 - [Inputs](#js-inputs)
  - [Feedback](#js-input-feedback)
 - [Notifications](#js-notify)

---
### <a title="Back to Top" href="#html" name="cards">Cards</a>

```html
<section type="card">
    <header type="card_header">Card Header</header>
    
    // Content, man!
</section>
```

#### <a title="Back to Top" href="#html" name="expandable-cards">Expandable Cards</a>
Theamus Cards will automatically detect whether or not a card can be expanded.

```html
<section type="card">
    <header type="card_header">Card Header</header>

    <section type="card_expansion">
        // Woo! Content!
    </section>
</section>
```

You can add the class `.expansion-only` to the top Card element if the card truly is an expansion only card. This will clean up some of the margins/padding.

#### <a title="Back to Top" href="#html" name="text-card">Text Card</a>
Of course, you can just throw some text in there without the wrapper. With the wrapper, though, it will make your text prettier!

```html
<section type="card">
    <header type="card_header">Card Header</header>

    <div class="card_text-wrapper">
        // Woo! Content!
    </div>
</section>
```

#### <a title="Back to Top" href="#html" name="expandable-text-card">Expandable Text</a>
These are for when you want to show more than one card on a page, but just a snippet of text. They can be opened to show more content. The main thing to do here is to just add a class.

```html
<section type="card" class="card_text-expansion">
    <header type="card_header">Card Header</header>
    
    <div class="card_text-expansion-wrapper">
        Max height of 300px when collapsed, when expanded, will fit the inner content.
    </div>
</section>
```

#### <a title="Back to Top" href="#html" name="columns">Columns</a>
There's a lot of possible column options. The smallest they can go is a quarter of the full width.

```css
.three-quarters
.two-thirds
.half
.third
.quarter
.no-padding
```

Adding the `.no-padding` class will keep the size, but remove any pre-defined padding, as you would expect.

```html
<section type="card">
    <header type="card_header">Card Header</header>

    <section type="card_column" class="three-quarters">
        // Moar content!
    </section>
       
    <section type="card_column" class="quarter">
        // Quarter less content!
    </section>
</section>
```

#### <a title="Back to Top" href="#html" name="collapsibles">Collapsibles</a>
These can be cool, if you're in the mood to be cool.

```html
<section type="card">
    <header type="card_header">Card Header<header>

    <section type="card_collapsible">
        <header type="card_collapsible-header">Collapsible Header</header>
        
        <section type="card_collapsible-expansion">
            // Content!
        </section>
    </section>
</section>
```

There's also the option of showing them as a list. If all you need is a list, then just add the `.card_collapsible-list` class to the card section element. (not the card_collapsible element) That will beautify all of the collapsibles into a reasonable looking list.

#### <a title="Back to Top" href="#html" name="selectable-collapsibles">Selectable Collapsibles</a>
The purpose of doing something like this would be so you can have a list, that acts as a checkbox, but has the option to provide more information.

```html
<section type="card" class="card_collapsible-list">
    <header type="card_header">Collapsible Listing</header>

    <section type="card_collapsible"
             data-selectname="selection"
             data-selectvalue="1">
        <header type="card_collapsible-header">Collapsible Numero Uno</header>
        <section type="card_collapsible-expansion">
            // Extra content!
        </section>
    </section>

    <section type="card_collapsible"
             data-selectname="selection"
             data-selectvalue="2">
        <header type="card_collapsible-header">Collapsible Numero Due</header>
        <section type="card_collapsible-expansion">
            // Extra content!
        </section>
    </section>
</section>
```

This automatically generates a hidden checkbox and gives it the name and value that you defined in the collapsible element attributes. They will be sent with any form submissions, or any Theamus sent AJAX requests. You can access them via JavaScript and give them listeners (when checked/unchecked) as well as modify attributes of the checkbox element.
__Note__: _Theamus won't register this as a selectable class if `data-selectname` is not defined. That's the only required attribute._

&nbsp;

### <a name="inputs" title="Back to Top" href="#html">Inputs and Labels</a>

```html
<section type="card">
    <header type="card_header">Card Header</header>

    <div class="card_input-wrapper">
        // Inputs go here.
    </div>
</section>
```

#### <a  title="Back to Top" href="#html">Labels</a>
Labels are automatically generated. (Yay?) They can do cool things, too. Adding labels is adding `data-`attributes to the input. Forget about the HTML for labels! This is all done for you.

|Attribute Name|Acceptable Value|Definition|
|:-------------|:--------------:|----------|
|label|_anything you want_|This is what will show up as the innerHTML of the label element|
|labelaction|_blank_ OR _undefined_|The default value. Actually you don't even need to define this attribute. It's completely optional.<br><br>Leaving this blank, or not defining it at all will make the label look like a placeholder but then slide out of the way when the user clicks on the input.<br><br>A personal favorite of mine.|
|labelaction|none|A normal label looking thing. Sits somewhere relevant like a normal label thing.|
|labelaction|disappear|Makes the label look like a placeholder and disappears when the input is clicked on|
|labeldirection|_blank_ OR _undefined_|Where will the label sit? If this is undefined, or blank, it will default to the left of the input.|
|labeldirection|left|Label will go to the left of the input, pushing the input over.|
|labeldirection|up|Label will go above the input.|

```html
<section type="card">
    <header type="card_header">Card Header</header>

    <div class="card_input-wrapper">
        <input type="text"
            data-label="This is the label Text"
            data-labelaction="none"
            data-labeldirection="up">
    </div>
</section>
```

#### <a name="input-types" title="Back to Top" href="#html">Input Types</a>
Just about everything is supported. __Note:__ _these inputs are not automatically backwards compatible_.

    text
    password
    search
    email
    url
    tel
    number
    range
    date
    color
    textarea

Also, adding the class `.input-clean` to any input element will make it look more like an editable header element.

#### <a name="input-attachments" title="Back to Top" href="#html">Attachments</a>

Pretty simple, just add another element to the wrapper.
```html
<div class="card_input-wrapper">
    <input type="text"
        data-label="Input Label">
    <span class="input-attachment">Hello, world!</span>
</div>
```
You can do buttons, check boxes and radios, too! For buttons, though, change the `input-attachment` to `input-button-attachment` because they are all different and stuff.

#### <a name="input-helpers" title="Back to Top" href="#html">Helpers</a>
It moves with the input, if there's any animation or anything. It's the shadow of the input. Sometimes helpful.

```html
<div class="card_input-wrapper">
    <input type="text"
        data-label="Input Label">
    <p class="input-help">
        This is some help text! It's smaller, it's grey and it's not that helpful.
    </p>
</div>
```

&nbsp;

### <a name="buttons" title="Back to Top" href="#html">Buttons</a>
Woo! Buttons! Yeah!

```html
<section type="card">
    <header type="card_header">Card Header</header>

    <div class="card_button-wrapper">
        <button type="submit" class="default">Default Looking Button</button>
    </div>
</section>
```
__Note:__ _Having the button wrapper is completely optional._

##### Supported button classes:

```css
.default
.primary
.success
.info
.warn
.danger
.flat-default
.flat-primary
.flat-success
.flat-info
.flat-warn
.flat-danger
```

---
## JavaScript!

### <a title="Back to Top" href="#js" name="what-is-js">Explain this to me. Pls.</a>

Well, you see, when [Brendan Eich](http://en.wikipedia.org/wiki/Brendan_Eich) and his keyboard loved eachother very much...

I see, that's not really relevant. You probably want to know about Theamus Cards + JavaScript. I mean, that's why you're here, isn't it?

I was skeptical about using JavaScript to get Cards going and wanted to do everything in HTML and CSS, but that just didn't seem easy enough. When I want to develop things, I just want it to work with as little of me playing around as possible.

Everything in Cards is accessible via JavaScript. _Everything._

### <a title="Back to Top" href="#js" name="playing-in-dev">Playing in the Developer Console</a>

To start out, there's the `Theamus` JavaScript object. This holds a lot of the Theamus-related client-side code and information such as a user's browser information, the handling of AJAX calls, and now the styling of pages.

To access anything in Cards, open up a developer console and type in `Theamus.Style.getAllCards()`. That will return all of the cards for you to play around with, and we'll jump in to actually figuring out how to/what can be done with them.

### <a title="Back to Top" href="#js" name="js-cards">JS + Cards</a>
This can either be really helpful, or really useless to you. Hopefully the former.

#### <a title="Back to Top" href="#js" name="getting-cards">Get 'em All</a>
Capture all of the cards. Use these for good. RETURNS in an object where the `key` is the ID of the card. Even if you didn't define an ID for the card, there will be one assigned to it. Trust me.
```js
Theamus.Style.getAllCards();
    //>Object {card-id: Theamus.Style.Card}

Theamus.Style.getCard("card-id");
    //><section type="card" id="card-id"></section>
```

### <a title="Back to Top" href="#js" name="dynamic-load">Dynamically Loading Cards</a>
The initialization of cards only loads when the page loads. Dynamically loading cards allows you to create card elements and initialize them after the page has loaded. This would be most useful after an AJAX call. 

```js 
Theamus.Style.loadCard(cardElement);
```

#### Example
```js 
var card = document.createElement("section");
card.setAttribute("type", "card");

var header = document.createElement("header");
header.setAttribute("type", "card_header");

var content = document.createElement("div");
content.classList.add("card_text-wrapper");

document.body.appendChild(card);
Theamus.Style.loadCard(card);
```


#### <a title="Back to Top" href="#js" name="card-expansions">Expansions</a>
Open, close. Open, close. Open, <sup>Open, close.<sup>Open, close.<sup>Open, close.<sup>Open, close</sup></sup></sup></sup>
```js
Theamus.Style.getCard("card-id").isExpandable();
    //>true (or false, depending on the TRUTH!)

Theamus.Style.getCard("card-id").expansion.expand();
    //>undefined

Theamus.Style.getCard("card-id").expansion.collapse();
    //>undefined
```

#### <a title="Back to Top" href="#js" name="card-collapsibles">Collapsibles</a>
Kind of the same thing as when playing with the top level card. Returns an object where the key is the ID of the collapsible element.
```js
Theamus.Style.getCard("card-id").getAllCollapsibles();
    //>Object {collapsible-id: Theamus.Style.Card.Collapsible}

Theamus.Style.getCard("card-id").getCollapsible("collapsible-id");
    //><section type="card_collapsible" id="collapsible-id"></section>

Theamus.Style.getCard("card-id").getCollapsible("collapsible-id").expand();
    //>undefined

Theamus.Style.getCard("card-id").getCollapsible("collapsible-id").collapse();
    //>undefined
```

For on-the-fly collapsibles
```js
var collapsible = document.createElement("section");
collapsible.setAttribute("type", "card_collapsible");

var collapsibleHeader = document.createElement("header");
collapsibleHeader.setAttribute("type", "card_collapsible-header");
collapsibleHeader.innerHTML = "I'm a generated collapsible!";
collapsible.appendChild(collapsibleHeader);

var collapsibleContent = document.createElement("section");
collapsibleContent.setAttribute("type", "card_collapsible-expansion");
collapsibleContent.innerHTML = "<div class='card_text-wrapper'>Hi, I'm new!</div>";
collapsible.appendChild(collapsibleContent);

Theamus.Style.getCard("card-id").registerCollapsible(collapsible);
    //><section type='card_collapsible-header'></section>

Theamus.Style.getCard("card-id").deleteCollapsible(collapsible);
    //>undefined
```


#### <a title="Back to Top" href="#js" name="JS_selectable-collapsibles">Selectable Collapsibles</a>
```js
Theamus.Style.getCard("card-id").getCollapsible("collapsible-id").setSelectableAttribute("attributeName", "attributeValue");
    //>undefined

Theamus.Style.getCard("card-id").getCollapsible("collapsible-id").removeSelectableAttribute("attributeName");
    //>undefined

// Triggers when the row is selected
Theamus.Style.getCard("card-id").getCollapsible("collapsible-id").functions.selected = function() {
    console.log("Selected!");
}

// Triggers when the row is unselected
Theamus.Style.getCard("card-id").getCollapsible("collapsible-id").functions.unselected = function() {
    console.log("Unselected!");
}
```


#### <a title="Back to Top" href="#js" name="js-content-expansions">Content Expansions</a>
Open, close?
```js
Theamus.Style.getCard("card-id").hasContentExpansion();
    //>true (or false, depending on the fax.)

Theamus.Style.getCard("card-id").content.expand();
    //>undefined

Theamus.Style.getCard("card-id").content.collapse();
    //>undefined
```

#### <a title="Back to Top" href="#js" name="js-progress">Progress</a>
Progress is a little bar that grows right under the header. It's simple, yet effective. Adding `finishText` is completely optional. If there isn't any text to show when it's all done and over with at 100%, then the progress bar will just show the percentage like it has since it's creation.

```js
Theamus.Style.getCard("card-id").addProgress();
    //>undefined

/* THIS FUNCTION IS OPTIONAL. If it's not defined, it will just say 100% */
Theamus.Style.getCard("card-id").progress.finishText = "upload complete";
    //>"upload complete"

Theamus.Style.getCard("card-id").progress.updatePercentage(10);
    //>undefined
```

#### <a title="Back to Top" href="#js" name="js-inputs">Inputs</a>
Yeah, play with those inputs! Works like the other `get` functions. Returns an object where the key is the input ID.
```js
Theamus.Style.getCard("card-id").getAllInputs();
    //>Object {input-id: Theamus.Style.Card.Input}

Theamus.Style.getCard("card-id").getInput("input-id");
    //><input type="text" id="input-id">
```


##### <a title="Back to Top" href="#js" name="js-input-feedback">Feedback</a>
Adding feedback is useful, and easy. Messages are optional. (hover over them to see, though.)

```js
/* The second parameter, `message` is optional */
Theamus.Style.getCard("card-id").getInput("input-id").addFeedback("success", "Hi!");
    //>undefined

Theamus.Style.getCard("card-id").getInput("input-id").removeFeedback();
    //>undefined
```

Here's the supported feedback types (replace "success" with any of these):

    success
    info
    warn
    danger

#### <a title="Back to Top" href="#js" name="js-notify">Notifications</a>
Notifications can help send feedback to your users.

```js
Theamus.Style.getCard("card-id").addNotification("success", "Hi mom!");
    //>undefined

Theamus.Style.getCard("card-id").removeNotification();
    //>undefined
```

The first parameter of the `addNotification` function is the type of notification you want to show. The possible ones are:

    success
    info
    warn
    danger

The second parameter is the message you want to show along with it. __BOTH ARE REQUIRED__. :)

## Final Thoughts
Yeah, it's long, but thorough hopefully. As I said before, it might be bound to change. As I'm writing this, it's only supported in Chrome so things may definitely change.

If you have any ideas or requests, submit yourself a pull request or [create a new issue](https://github.com/helllomatt/theamus/issues/new).

Thank you very much!