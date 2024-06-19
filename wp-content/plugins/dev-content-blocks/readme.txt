=== Dev Content Blocks ===
Contributors: allonsacksgmailcom
Donate link: http://www.digitalcontact.co.il
Tags: content blocks, reusable content, shortcodes, modular, html, code
Requires at least: 4.0.1
Tested up to: 5.0.2
Stable tag: 1.4.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Content blocks for global content, with revisions. Use HTML without formatting being broken. Not only for devs.

== Description ==

Not only for developers.
Create content blocks for reusable global content. Supports revisions. With input fields for raw HTML, JS, and CSS without worrying about auto formatting, with an option to also use the usual formatted Wordpress tinyMCE editor.

This plugin let's you create modular content blocks the right way:

1. Content blocks are a post type.
2. Allows you to add HTML, CSS, and JS in separate blocks using ACE editor with code completion and error notifications.
3. Wordpress WYSIWYG editor optional.
4. Revisions(!) You can change your Wordpress content, HTML, CSS, and JS and then go back to a previous state the same as you can with the out of the box wordpress post revisions.
5. Use a shortcode to add the blocks in posts, pages, CPT's, widgets, and in your theme files.
6. Preview your content.
7. Easily import & export using the default Wordpress XML import/export tool.

Using content blocks you can create blocks of content to display globally. Change the block and any page the content block is embedded on will reflect the changes.

**Use Cases:**

* **The Wordpress WYSIWYG editor and even the text editor is often unpredictable not adding line breaks when wanted, or losing formatting after editing a post:**
  Just create a content block calling it "br" using only the HTML box and put &lt;br&gt; in the box. The shortcode "[dcb name=br]" can then be used whenever you want to make sure that you have a line break.

* **You may have multiple "thank you" pages for different LP's. When you start new campaigns or want to add new conversion code (or delete an old conversion code), you need to go to each ty page and add/delete the conversion code:**
   With modular content you can maintain all your conversion codes in one block and then just put the same shortcode in all ty pages. Also, with the regular Wordpress editor you cannot always safely embed conversion code in the post itself and rely on using a separate template. This plugin solves all these issues.

* **Ads:**
   Use the content blocks to manage your ads in one place. Create a content block per ad, then use a shortcode in your post content/theme files/widgets where you want to display each ad.

* There are many more use cases, these were just a few examples. Really you can use content blocks for so many cases and the power it gives you to manage and maintain modular content is necessary on almost any kind of website.


Content Blocks are an easy way for you to create your own shortcodes within the admin and can be used as a minimal shortcode builder.

Dev Content Blocks is created with developers in mind, so while it is simple enough for any Wordpress user to use, developers will have added benefit of being able to use raw HTML without it being formatted by wptexturize and wpautop. Think arbitrary HTML in the old text widget (new "Custom HTML" widget). Here paragraphs are NOT added automatically.
Also, as CSS and JS can easily be added here without fear of it losing it's formatting, this plugin let's you create and manage your own shortcodes. You can easily create shortcodes (the type that do not accept arguments) and manage them in one place in the admin instead of creating them in the theme or as a separate plugin per shortcode.

You can actually add CSS and JS in the HTML box but, for convenience this plugin has separate optional CSS and JS boxes to allow you to separate your content and code.
The output of the shortcode is:
1. Content from the Wordpress editor if it is enabled. (Note, here wptexturize and wpautop are used).
2. Content from the HTML box.
3. The CSS code wrapped in a style tag.
4. The JS code wrapped in a script tag.
("if(typeof(jQuery) !== 'undefined') {$ = jQuery.noConflict();}" is prepended to the script.)

Please Note. Dev Content Blocks lets you use raw HTML, JS, and CSS therefore be careful if copying and pasting from random web pages as in order to allow you maximum control with the content blocks, you will be able to paste JS that is not entirely validated.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. In the admin visit "Settings" > "Permalinks" in order to activate the CPT preview screen.
4. Create your content blocks by going to "Dev Content Blocks" in the admin side menu.


== Frequently Asked Questions ==

= What if I want to use a page builder to style my global content block? =

Page builders are not recommended as they can clash when being used from within a shortcode or on other pages. It is best to use the Wordpress HTML or WYSIWYG editor or even better just use the HTML block provided with this plugin. If you want to try using a page builder, it still may work in some cases but you may lose compatibility in the future. (You have been warned :) )

If you use a page builder and do not see the option to use it on the content block edit screen, you may need to enable it for the post type: <b>Dev Content Blocks.</b> (Depends if your page builder supports custom post types).

Take into account that some page builders output the style per page and not inline meaning that styling may not be visible in the shortcode output.
Tested with Divi Builder it works if you also enable Divi Builder on the destination page itself. (But Divi Builder default CSS might over ride your custom CSS set in the shortcode)
Tested with Elementor the styling done in Elementor does not come through as it is only generated on the page the element was generated on. (The content block page not the destination page using the shortcode pointing to the content block.)

= How can I preview the content? =

While logged into the admin you can see a preview of the content block by clicking the "view Content Block" link, or the URL below the title, or the "Preview Changes" button in the post publish meta section.
Same as previewing any page or post.

= I am logged into the admin but get a 404 page when previewing the content block.

In the admin visit "Settings" > "Permalinks" in order to activate the CPT preview screen.

= Won't this mean the content blocks are added to the sitemap and visible to search engines? =

No:
The content will not be visible, previewing the content block is only possible if you are logged into the admin. Non logged in visitors will receive a 404 page if they were to for some reason have access to the URL of the content block. Content blocks are only meant to be used as a shortcode to output content within another post type or area of the site.

The post type has "public" set to "false" so it should not be in the sitemap but you should check whatever generates your sitemap and make sure the "Dev Content Blocks" post type does not appear there.

= Where can I get the shortcode? =

The shortcodes are displayed at the top of the content block edit screen below the title.
It is highly advised to use the ID of the shortcode for instance [dcb id=4] as the ID will not change however for convenience you can use the slug in two easy to remember formats eg: [dcb name=tests] or [dcb slug=tests]. You really have no reason to change the slug even if you change the title so it is safe enough but ID is the mot reliable.

= What if I use only one of the boxes for instance I use the Wordpress editor and leave the HTML, CSS, and JS boxes empty? =

No problem! Only the boxes you enter content into will be included in the output

= Do I need to delete the content from the Wordpress editor or the page builder/visual builder if I don't want to use that content in the output? =

No, you can simply toggle the switch in the content editor section to allow or disallow output from the default WP editor. (This section should appear below the "Content Block Code" section)

= I used both the WP editor and the HTML box in the "Content Block Code" section. Why is the HTML appearing below the content from the WP editor? =

The plugin is designed to first output the code from the WP editor and then from the HTML box. If you need to have arbitrary raw HTML within the WP editor then create a separate content block and insert that into the WP editor in the current content block (Yes you can nest different content blocks within each other). You can also nest another content block's shortcode in the HTML editor box.

= My preview screen does not look good. Something is wrong. =

The preview tries to use a blank page with just the header, content area, and the footer but different themes might clash with that. Please email me if you have any issues and I will try to assist. Optionally you could just insert the shortcode into a test page, preview there, and when it is ready copy the shortcode to the actual page you want to use it on.

Also note, the content may look differently on different template pages based on the container width.

= How can I use code completion in the code boxes? =

As you type suggestions should appear, just hit TAB to accept the suggestion or the arrow buttons on your keyboard to select a different suggestion.

= In the admin, when scrolling to the end in one of the boxes, I cannot continue scrolling the page when reaching the end of my code without moving my mouse away from the text box =

This is by design to enable fast scrolling to the end of the text box without worrying about losing focus when you reach the end of the box. If you want to continue scrolling the page, either wait for the scrollbar in the textbox to disappear (after about a second) or move the mouse away from the textbox.

= I use a page builder that already has modular sections, do I have a use for this plugin?

Yes! There are a few reasons:
* With "Dev Content Blocks" you can use the modular content on posts/pages/ CPT's where you did not enable the page builder. For instance, often blog posts are written in the default Wordpress editor and this way there is no need to enable the page builder on those posts just to be able use some modular content like CTA's, conversion code, forms, etc...
* You cannot easily use page builder modules within widgets or theme files.
* "Dev Content Blocks" content & code is organized per content block with clear separation between regular content, HTML, JS, and CSS.
* If you stop using the builder or change builders you will still have your modules.
* Easy to export modules between sites regardless of both sites using the same page builder.
* "Dev Content Blocks" can be used as modules within most page builders as long as they support using shortcodes (which most if not all of them do).
* Unlike "Dev Content Blocks", most page builders do not offer an easy option to add code that does not get reformatted in the output. So you may end up having unwanted line breaks, or broken CSS, or scripts that don't work and cause errors.

== Screenshots ==

1. The Content block edit screen
2. The code blocks with auto complete
3. WP content editor disabled
4. WP content editor enabled
5. Revisions
6. Output

== Changelog ==

= 1.4.1 =
Tested with Wordpress 5.0.2
Fixed issue with global version constant not being defined

= 1.4 =
Fix to resize boxes - HTML and CSS were not working due to wrong classname
WordPress 4.9.7 compatibility testing

= 1.3 =
Readme changes
Tested with WP 4.9
Fix to bug with displaying content from WYSIWYG
Added auto code suggestion.

= 1.2 =
Readme changes
Changed markup in single.php and added CSS to it to support more themes
Fix to post type to ensure it is not in sitemap

= 1.1 =
Update to readme

= 1.0 =
First Version

== Upgrade Notice ==

Tested with WP 4.9, Fix to bug with displaying content from WYSIWYG, and added auto code suggestion.

== Future Versions ==

1. Enable/disable each box in the "Content Block Code" section.
2. More Page builder support
3. Variables to be passed in the shortcode
4. Option to enqueue other CSS & JS files when a block is used
5. Preview in a light box on the admin screen
6. Options for incorporating and using the featured image
7. Add an option to change the order of the HTML box vs. the WP editor
8. Add an option to turn off formatting in the WP editor
9. Add an option to enqueue the CSS and JS instead of outputting it in the HTML
10. Widget
11. Button in tinyMCE to add shortcodes
12. Show shortcodes in main Dev Content Blocks screen
13. Localization and language support
14. When leaving or refreshing the admin page users should receive a prommpt if they have unsaved changes in the code boxes
15. All above are under consideration and may or may not be added. Please feel free to email me or write a comment here in the reviews or support section with bugs and/or suggestions.
