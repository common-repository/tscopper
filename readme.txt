=== tscopper ===
Contributors: topham
Donate link:
Tags: gallery, coppermine, shadowbox
Requires at least: 3.0
Tested up to: 3.0.1
Stable tag: 0.9.4

tscopper allows inclusion of images from Coppermine gallery via shortcode.

== Description ==

Allows you to select images from Coppermine via 'Coppermine' button beside 'Upload/Insert' on Edit Post form. Select the Album via the dropdown, then check each image you want to include. A generic [tscopper from="image" id=""] will be created with an id for each image.

tscopper creates a shortcode '[tscopper]' which can be configured to  images from a Coppermine gallery. Images can be included by Album, Category, Image Id. Images within an Album can be filtered via Keywords. Images within meta albums can be displayed, and subsequently filtered via keywords. 

`[tscopper from=album id=15 keywords="Aug-2010"]`

would display all images from album where the keyword field contains 'Aug-2010'. Multiple keywords can be included by separating with a semi-colon.



== Installation ==

Manual installation
1. Upload files from tscopper to '/wp-content/plugins/tscopper' directory
2. Activate the plugin through the 'Plugins' menu in wordpress
3. Set up the database parameters for Coppermine in tscopper via the Settings menu.

== Changelog ==

Modified Category/Album query to include unassociated Albums.
Modified Album query to include Albums with restricted visibility.

