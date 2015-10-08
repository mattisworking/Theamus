# User Permissions

Sometimes it's necessary to check a user's permissions. Just to make sure that they aren't doing anything they shouldn't be doing. Nicely enough, Theamus provides a way to check a user's permissions from individual permissions, to the groups that they are in.

For an example, we have a group for a blog called "Blog Moderator." These moderators should be able to archive blog posts, but not delete them.

```
Blog Administrator (group)
|-delete_blog_posts
|-archive_blog_posts

Blog Moderator (group)
|- archive_blog_posts
```

> __Scenario:__ The user wants to delete a blog post, but he/she/it is only a part of the moderator group.

&nbsp;

## Checking a User's Group Permissions

A simple function call, returns `true` or `false`. The jist of this function is to see if a user is in a group. If they are in that group, then they have all of the permissions associated to that group.

```php
$Theamus->User->in_group("blog_administrator"); // if false, don't let the user delete the post
```

## Checking a User's Specific Permissions

Like above, this function returns `true` or `false`. You would use this function if you want to know whether or not a user has a specific permission or not.

```php
$Theamus->User->has_permission("delete_blog_posts"); // if false, don't let the user delete the post
```
