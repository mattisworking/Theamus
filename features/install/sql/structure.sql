SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `dflt_home-apps`
--

CREATE TABLE IF NOT EXISTS `dflt_home-apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `path` varchar(100) NOT NULL,
  `active` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `column` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_features`
--

CREATE TABLE IF NOT EXISTS `tm_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(125) NOT NULL,
  `name` varchar(100) NOT NULL,
  `groups` text NOT NULL,
  `permanent` int(11) NOT NULL,
  `enabled` int(11) NOT NULL,
  `db_prefix` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_groups`
--

CREATE TABLE IF NOT EXISTS `tm_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(100) NOT NULL,
  `name` varchar(75) NOT NULL,
  `permissions` text NOT NULL,
  `permanent` int(11) NOT NULL,
  `home_override` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_links`
--

CREATE TABLE IF NOT EXISTS `tm_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(100) NOT NULL,
  `text` varchar(75) NOT NULL,
  `path` text NOT NULL,
  `title` varchar(500) NOT NULL,
  `target` varchar(50) NOT NULL,
  `weight` int(11) NOT NULL,
  `groups` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `location` text NOT NULL,
  `child_of` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_logs`
--

CREATE TABLE IF NOT EXISTS `tm_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `class` varchar(100) NOT NULL,
  `function` varchar(150) NOT NULL,
  `line` int(11) NOT NULL,
  `file` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_media`
--

CREATE TABLE IF NOT EXISTS `tm_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(150) NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `type` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_pages`
--

CREATE TABLE IF NOT EXISTS `tm_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(250) NOT NULL,
  `title` varchar(200) NOT NULL,
  `raw_content` text NOT NULL,
  `views` int(11) NOT NULL,
  `permanent` int(11) NOT NULL,
  `groups` text NOT NULL,
  `theme` varchar(50) NOT NULL,
  `navigation` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_permissions`
--

CREATE TABLE IF NOT EXISTS `tm_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature` varchar(100) NOT NULL,
  `permission` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_settings`
--

CREATE TABLE IF NOT EXISTS `tm_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_errors` int(11) NOT NULL,
  `developer_mode` int(11) NOT NULL,
  `email_host` varchar(100) NOT NULL,
  `email_protocol` varchar(5) NOT NULL,
  `email_port` varchar(10) NOT NULL,
  `email_user` varchar(150) NOT NULL,
  `email_password` varchar(50) NOT NULL,
  `installed` int(11) NOT NULL,
  `home` text NOT NULL,
  `version` varchar(50) NOT NULL,
  `logging` text NOT NULL,
  `favicon_path` varchar(2083) NOT NULL,
  `show_page_information` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_themes`
--

CREATE TABLE IF NOT EXISTS `tm_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(125) NOT NULL,
  `name` varchar(100) NOT NULL,
  `active` int(11) NOT NULL,
  `permanent` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_themes-data`
--

CREATE TABLE IF NOT EXISTS `tm_themes-data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` text NOT NULL,
  `value` text NOT NULL,
  `selector` text NOT NULL,
  `theme` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_user-sessions`
--

CREATE TABLE IF NOT EXISTS `tm_user-sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_key` varchar(32) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `expires` datetime NOT NULL,
  `last_seen` datetime NOT NULL,
  `browser` text NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `tm_users`
--

CREATE TABLE IF NOT EXISTS `tm_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `birthday` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `admin` int(11) NOT NULL,
  `groups` text NOT NULL,
  `permanent` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `picture` varchar(64) NOT NULL,
  `created` datetime NOT NULL,
  `active` int(11) NOT NULL,
  `activation_code` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
