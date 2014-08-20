SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

---
-- Dumping data for table `dflt_home-apps`
--

INSERT IGNORE INTO `dflt_home-apps` (`id`, `name`, `path`, `active`, `position`, `column`) VALUES
(1, 'Page Counter', 'pagecounter', 1, 1, 1),
(2, 'Site Summary', 'summary', 1, 2, 2),
(3, 'Theamus Update Checker', 'updatecheck', 1, 1, 2);

--
-- Dumping data for table `tm_features`
--

INSERT IGNORE INTO `tm_features` (`id`, `alias`, `name`, `groups`, `permanent`, `enabled`, `db_prefix`) VALUES
(1, 'groups', 'Groups', 'administrators', 1, 1, 'tm_'),
(2, 'navigation', 'Navigation', 'administrators', 1, 1, 'tm_'),
(3, 'accounts', 'Accounts', 'everyone', 1, 1, 'tm_'),
(4, 'default', 'Default', 'everyone', 1, 1, 'tm_'),
(5, 'features', 'Features', 'administrators', 1, 1, 'tm_'),
(6, 'pages', 'Pages', 'everyone', 1, 1, 'tm_'),
(7, 'settings', 'Settings', 'administrators', 1, 1, 'tm_'),
(8, 'media', 'Media', 'administrators', 1, 1, 'tm_');

--
-- Dumping data for table `tm_groups`
--

INSERT IGNORE INTO `tm_groups` (`id`, `alias`, `name`, `permissions`, `permanent`, `home_override`) VALUES
(1, 'everyone', 'Everyone', '', 1, 'false'),
(2, 'administrators', 'Administrators', 'create_groups,edit_groups,remove_groups,add_users,edit_users,remove_users,create_links,edit_links,remove_links,install_features,edit_features,remove_features,create_pages,edit_pages,remove_pages,install_themes,edit_themes,remove_themes,add_media,remove_media', 1, 'false'),
(3, 'basic_users', 'Basic Users', '', 1, 'false');

--
-- Dumping data for table `tm_pages`
--

INSERT IGNORE INTO `tm_pages` (`id`, `alias`, `title`, `raw_content`, `views`, `permanent`, `groups`, `theme`, `navigation`) VALUES
(1, 'home-page', 'Hello, World!', 'Welcome to your new website.\n\nGetting started is pretty easy if you ask me. All you have to do is [log in](accounts/login/) then go to the administration panel. That''s where you can do the fun things like:\n\n- Add Themes to change the appearance of your website\n- Add Features to change the functionality of your website\n- Manage users and groups\n- Create Pages for the visitors of your website\n\nUsing the Theamus platform as your content management system will make your life easier and more customizable than ever before. The modularity of the system and the seamless integration of stand-alone applications will give you the freedom you''re looking for. Freedom, that doesn''t look like it was whipped together at the whim of someone looking to make a quick buck.\n\nHold on tight because documentation is coming soon.', 0, 1, 'everyone', 'homepage', '');

--
-- Dumping data for table `tm_permissions`
--

INSERT IGNORE INTO `tm_permissions` (`id`, `feature`, `permission`) VALUES
(1, 'groups', 'create_groups'),
(2, 'groups', 'edit_groups'),
(3, 'groups', 'remove_groups'),
(4, 'accounts', 'add_users'),
(5, 'accounts', 'edit_users'),
(6, 'accounts', 'remove_users'),
(7, 'navigation', 'create_links'),
(8, 'navigation', 'edit_links'),
(9, 'navigation', 'remove_links'),
(10, 'features', 'install_features'),
(11, 'features', 'edit_features'),
(12, 'features', 'remove_features'),
(13, 'pages', 'create_pages'),
(14, 'pages', 'edit_pages'),
(15, 'pages', 'remove_pages'),
(16, 'appearance', 'install_themes'),
(17, 'appearance', 'edit_themes'),
(18, 'appearance', 'remove_themes'),
(19, 'media', 'add_media'),
(20, 'media', 'remove_media');

--
-- Dumping data for table `tm_themes`
--

INSERT IGNORE INTO `tm_themes` (`id`, `alias`, `name`, `active`, `permanent`) VALUES
(1, 'default', 'Default', 1, 1);