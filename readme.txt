=== Plugin Name ===
Contributors: 
Donate link: http://ben.balter.com/donate/
Tags: documents, document revisions, version control, custom fields, custom taxonomies, field builder
Requires at least: 3.2
Tested up to: 3.3
Stable tag: 0.1.1

Creates static plugin files to add custom fields and taxonomies to wp_document_revisions documents

== Description ==


**Note: this plugin is no longer under active development. To add taxonomies to documents, you may be interested in the [WordPress Custom Taxonomy Generator](http://themergency.com/generators/wordpress-custom-taxonomy/). To add custom fields, you may be interested in [Edit Flow](http://editflow.org) or [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/).**

Creates static, stand-alone plugin files to add custom fields and taxonomies to [WP Document Revisions](http://wordpress.org/extend/plugins/wp-document-revisions/) documents.

Allows for creation of an unlimited number of:

* Custom Freeform Taxonomies (like tags)
* Custom structured taxonomies (like categories, departments, or issues)
* Exclusive Categories (Custom taxonomy that allows users to select only one from a list like colors or clients)
* Text Field (add any text you want)
* Select a user from a list (such as editor)

Generates static plugin file and attempt to write the file to your WordPress installation. If it cannot, it will give you the source code to copy, and tell you where to create the file. Plugin can safely be removed or uninstalled once fields and taxonomies are created, or left active to edit existing fields.

If you are looking to store metadata along side your documents (rather than group and organize them), you may also want to take a look at the [Edit Flow Plugin](http://wordpress.org/extend/plugins/edit-flow/) which WP Document Revisions is designed to integrate with. 

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.1.1 =
* Fixed bug where exclusive custom taxonomies were associated with revisions, not posts

= 0.1 =
* Initial commit