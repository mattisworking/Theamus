# Theamus Command Line Interface

You can run Theamus features from a command line!

## Flow
In order to run a Theamus feature from the command line, you need to have registered the command with Theamus first. Theamus doesn't save this registration in any database or anything, it belongs in your feature's configuration file.

The flow of creating a registered command is this:
1. Create your feature
1. Create the function or method you would like to run from the command line
1. Register that function or method and it's information in the feature's `config.php` file
1. Run the command from your server's command line

## Making Theamus Executable
This is important, otherwise Theamus cannot run from the command line.
```
sudo chmod +x /path/to/theamus/index.php
```

## Creating the Theamus Alias
This is an optional step, if you would like to make Theamus easier to run, you can create this alias to save the time of typing.
```
sudo vim ~/.bashrc
```
Then add this line at the bottom: (replace /path/to/theamus/ with your actual path)
```
alias theamus='php /path/to/theamus/index.php'
```

## Registering a Command
Anywhere in your feature's `config.php` file you would add:

__For Class Methods__
```
<?php
$Theamus->CLI->register_command("command_name", array("file", "Class", "method"));
```

__For Functions__
```
<?php
$Theamus->CLI->register_command("command_name", array("file", "function"));
```

What "registering" a command will do is tell Theamus that you have a command, that goes by a certain name, which could be found in this file, as this function/class method.

> The file name is from the __root of the feature__.

## Running a Command
Right from your command line:
```
theamus feature command_name
```

> Replace "theamus" with `php /path/to/theamus/index.php` if you did not set an alias

## Passing Arguments
You can define arguments through the command line as well. You'd run it the same way but after the command name add key=value as many times as necessary.
```
theamus test say_hello name=john
```

> Replace "theamus" with `php /path/to/theamus/index.php` if you did not set an alias

Then, in the function you would receive the arguments as a key=>value array.
```
<?php
function say_hello($args) // array([name] => "john")
```

## Getting Theamus for Functions
If you need to make a function for the CLI, you can still get access to the Theamus object. The function will accept two parameters. First, the Theamus object, then the arguments from the command line.

```
<?php
function say_hello($Theamus, $args) {
  $Theamus->CLI->out($args['name']);
}
```

## Printing out to the Command Line
For those special moments when you want to say something to the user.
```
<?php
$Theamus->CLI->out($message, $tab); // tab is line padding default is \t
```

## Getting Information from the Command Line
When you want to get some input from a user.
```
<?php
$name = $Theamus->CLI->in("What is your name?");
```
## Logging
Since Theamus is always given, you have access to the logging functions as well. For CLI specific logs, you can use:
```
<?php
$Theamus->Log->CLI($message);
```
