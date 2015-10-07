# Feature Installation Package Setup

Pretty simple, really. Probably the easiest thing you'll do all day.

You have the feature "The Greatest Calculator Ever" that lives at `theamus/features/great_calc`, and here's the file listing:

```text
theamus/features/great_calc/views
theamus/features/great_calc/views/index.php
theamus/features/great_calc/files.info.php
theamus/features/great_calc/config.php
```

All you need to do to package is zip up the __contents__ of the feature. Your .zip file should look like:
```text
great_calc.zip
|-- views
    |-- index.php
|-- files.info.php
|-- config.php
```

> __Note:__ Make sure the contents of the feature are in the root of the zip file. If you zip up the feature folder, it won't install correctly. Remember `zip -> config.php` NOT `zip -> folder -> config.php`