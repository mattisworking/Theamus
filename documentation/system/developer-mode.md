# Developer Mode

As of Theamus v1.5, there is no way to turn on or off Developer Mode through the Administration panel. It has to be manually set through the database.

The time that this would've been turned on was either during the installation, or if you have manually changed it sometime before. Obviously change the table name to match your setup in the case that you have a different table prefix for you system tables. The flag is a 1 or 0 to determine on or off.

Log into your database and run either of these queries to turn on or off Developer Mode for Theamus.

## Turning on Developer Mode
```
UPDATE `tm_settings` SET `developer_mode` = 1;
```

## Turning off Developer Mode
```
UPDATE `tm_settings` SET `developer_mode` = 0;
```