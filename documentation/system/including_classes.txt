Hello!

Including classes is a pretty neat thing to do.  Sometimes it can be really handy too!

There's two files that need to be edited for the inclusion of files to work.
<feature>/config.php
<feature>/files.info.php

In the configuration file, you are required to define the folder for where your class files will live.  If you don't feel like defining a folder, leave the value blank and you can assume that the pathing will be from the root of your feature.  There should be something in the config file that defines "custom folders":
"custom_folders"  => array()

In that array value, there will be things like "php", "js" and "css".  Well, if you didn't already know, you can add a "class" folder which will define where your classes live.
"custom_folders" => array(
	...
	"class" => "php"
)

Now Theamus will look in <feature>/<custom_folders['class']>/ for any class files that are going to be loaded in the page call/request handling.

After that's all settled, loading classes are easy.  In the files.info.php file, wherever you want, just add this little line of code:
$Theamus->Call->load_class(<class file>, <class name>, <assignment name>);

The class file defines, obviously, the file that holds the class.  The class name is the name of the class and the assignment name is the variable that will hold the new object.

Basically, the logic is this:
include <class file>
$<assignment name> = new <class name>();

You can do this as many times as you want to include and set up as many classes as you would like during a page call.