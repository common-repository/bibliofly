=== BiblioFly ===
Contributors: damselfly
Donate link: http://deborahmcdonnell.com/
Tags: bibliography
Requires at least: 2.7
Tested up to: 2.7
Stable tag: 0.34

Create and manage a bibliography through your WordPress install

== Description ==

BiblioFly stores and manages bibliography information through your WordPress install. Originally created to manage an author's list of titles, it has also been put to use storing reviewing information. Biblio entries can be marked as published or unpublished, or marked as not visible so they won't display prematurely. There are functions to embed a list of recent publications in a sidebar, and to print out a full bibliography page, sorted by type (novel, short story, etc). Embed a bibliography entry in a post or page. There's also a notes field (for review highlights, links elsewhere etc).

== Installation ==

1. Upload `bibliofly.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place a function call where you want the biblio entry to appear. For a list of available functions and their variables, see http://www.deborahmcdonnell.com/damselfly/wordpress-plugins/bibliofly

== Frequently Asked Questions ==

= How do I style, or change the appearance of my bibliography? =

The bibliography entries are displayed wrapped in an assortment of CSS classes. For more information on which classes to include in your theme's stylesheet, refer to http://www.deborahmcdonnell.com/damselfly/wordpress-plugins/bibliofly

= I don't have WordPress 2.7 installed; can I use BiblioFly? =

Due to a change in the WordPress architecture, BiblioFly v0.34 can only be used on WordPress 2.7. 

If you don't have WordPress 2.7 installed, I would recommend upgrading to 2.7 for security reasons. 

If you can't or won't upgrade, there's a simple hack: open up bibliofly.php, navigate to lines 280 and 281, and in each line change "tools.php" to "edit.php". 


== Screenshots ==

1. A demo short fiction publication, showing the full entry (title, source, link, excerpt, notes (used in this case to display a publication review)).
2. A demo unordered list of publications, displayed in my sidebar. The unpublished title is displayed unlinked and with the comment `(forthcoming)`.