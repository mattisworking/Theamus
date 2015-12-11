# Theamus Cron
You can use Theamus to set up and run Cron jobs. This isn't like modifying the crontab though. You add Theamus to the crontab and then when Theamus runs, it will run all of your Cron jobs.

> I'm assuming you've created an alias for "theamus" on the command line. If you have not, replace "theamus" with `<path/to/theamus>/index.php`.

## Setting up Theamus and Cron
An important part of this whole ordeal is to have Theamus run every x amount of time. In most cases, this will be once every minute to ensure that you can run scripts often, and have control over the recurrence of the script's run.

In a command line, run:
```
crontab -e
```

Then add this line to the bottom:
```
* * * * * php <path/to/theamus>/index.php cron run_cron_jobs
```

This will tell Theamus to run any available cron jobs every minute. Note that some jobs might not run every minute, as you can define their recurrence when you create the job.

## Adding/Creating a Cron Job
Once you can run your command through the command line using Theamus, you can then use it to become a Cron job. If you're fuzzy on how to make command line availability with Theamus, read up on it in `documentation/system/cli.md`.

Now, add the job through the command line:
```
theamus cron add_job feature=default command=say_hello recurrence=2
```

This tells Theamus to add a new job, for the feature "default" using the registered command "say_hello" once every two minutes.

If you want to pass through arguments to your command, just keep adding them on to the `add_job` command.

```
theamus cron add_job feature=default command=say_hello recurrence=2 name=John color=red
```
This will do everything that was done above, plus pass through the arguments `[name] = "John"` and `[color] = "red"`.

## Listing Cron Jobs
It's necessary to see the cron jobs that Theamus is running without having to look in the database for the listing.

To get this listing, run:
```
theamus cron get_jobs
```

You'll get a result like:
```
Job ID: 1 - Feature: default - Command: hello - Arguments: {"name":"John","color":"red"}
```

## Deleting Cron Jobs
If you made a mistake creating a job, or need to just stop running a job, then deleting is the next step.

Firstly, you're going to need to run the command `get_jobs` to get the job's information. The Job ID, specifically.

Then, you just run the delete command and plug in the Job ID to delete the job:
```
theamus cron delete_job id=1
```
