# Theamus Feature Setup

There are two ways to set up Theamus Features. The first way is to let Theamus do the work for you, and the second way is to do it all manually.

## The Easy Way
In order to set up a feature the easy way, you __must__ have Developer Mode turned on. If you don't have Developer Mode turned on, you can see how to do that in `documentation/system/developer-mode.md`.

From there, here's the steps to setting up a feature:
1. Login to Theamus as an administrator
1. Open the navigation menu on the top left, or right of the screen. (three horizontal lines)
1. Click on "Features"  which will open up a window, listing all of the features.
1. On the top right of the "Theamus Features" window hover over the "More" menu item and click on "Create a Feature"

Now you're at the form to create a feature. All this process does is set up the file structure for you, the default configuration, and registers the feature with Theamus.

|Field Name|Field Description|
| --- | --- |
|Feature Name|This is the pretty name of your feature - what you will market it as. For example, "The Greatest Calculator Ever"|
|Feautre Description|This is the folder name of the feature. For example, "great_calc".  The folder name of the feature plays a role in the URL as well. When people go to `theamus/great_calc/` they are going to this feature that you're creating, so keep that in mind!|

Once you hit "Create Feature" the form information will submit, then Theamus will create the folders, files, and database information so you can get started on coding!

## The Manual Way
This way allows you to be more customized when you create your feature, but takes a lot longer than the easy way. Actually, I wouldn't reccomend doing it this way unless you _absolutely_ have to keep Developer Mode off.

Still, this way will teach you about the structure of a feature and what everything means. Let's jump right in then.

There's a process to creating a feature, start with one thing, then go to the next, etc etc.
1. Create the folder structure
1. Create the required files
1. Add your code
1. Register your feature with Theamus

### Creating the Feature Folder Structure
```
great_calc
|-- views
```

That's it. The parent folder and the `views` folder are all that's needed to create a feature.

### Creating the Required Feature Files
```
great_calc
|-- views
    |-- index.php
|-- files.info.php
|-- config.php
```

Three files. One as your "view" file, one as a file that handles the specific request, and one that handles the feature configuration.

### Add your code
Now, in each of the files you should add some stuff. To avoid repetition in the documentation, you can find the code for `files.info.php` and `config.php` below. For the `index.php` file, you can go ahead and put anything you want.

To learn more about the `files.info.php` file, see `documentation/features/90-files-info.md`.
To learn more about the `config.php` file, see `documentation/features/91-config.md`.

### Register your feature with Theamus
There's two ways you can do this. The first way being manually adding the information to your database. The second way would be packaging your feature for installation and then "installing" it to your Theamus setup. The latter way only works if you created your feature outside of your Theamus setup though. If you created it in the `features/` folder, then it will fail out saying that feature already exists.

To learn more about packaging your feature for installation, see `documentation/features/80-installation-package.md`.

#### Manually adding the Feature to the database
1. Log in to your database as a user with rights to your Theamus installation
2. Run a query to install your feature, replace the information as you see fit:
```
INSERT INTO `tm_features` (`alias`, `name`, `groups`, `enabled`, `db_prefix`) VALUES ('great_calc', 'The Greatest Calculator Ever', 'everyone', '1', 'asdf2');
```

The `db_prefix` column is the database prefix to your feature. This can be up to 20 characters long, and should be random. This prefix associates any database tables with the prefix to your feature. __DO NOT SET THE PREFIX TO THIS SYSTEM PREFIX!!__ If you do, and the user deletes your database, it will delete all of the system tables too. Which would be bad.