=== WP Chameleon ===
Contributors: gstieger 
Donate link: http://three-stores.co.uk/
Tags: rss,article software,widget,post,plugin,page,wordpress
Requires at least: 2.0
Tested up to: 3.0
Stable tag: trunk

WP Chameleon gives full control over unique content creation for widgets, pages or posts. It uses RSS to post to multiple blogs and categories.

== Description ==

<span lang="EN-AU"> <p>
  WP Chameleon has TWO important features:
</p>
<p> (1) It varies the content of your pages, posts or text widgets based on special markup in the text that you publish.
</p>

<p> (2) It allows you to post to multiple blogs with one click. You can limit this feature to posts that belong to specific categories. 
</p>
<p>
The plug-in has multiple uses related to (1), (2) or both. You can write one "morphing" post and automatically publish unique versions to multiple blogs.
You can include a unique shadow copy of an existing post on another page of the same blog with different keywords.
You can use it to test the effectiveness of different ad copy or to create self-rewriting articles that can be copied and pasted from your 
web pages to publish in article directories.
</p>

<p>
  See also: <a href="http://www.jv2win.com/wp-chameleon/">WP Chameleon Plugin</a>
</p>

<p>
  Is submitting the same article to multiple blogs or directories a good idea OR are you missing an important piece of the puzzle?
</p>

<p>
  Duplicate content ranks poorly with Google and could even be considered to be spam.
</p>

<p>
  This plugin can help you, in fact it has several additional features: Have you ever had to do any of the following and found you had to resort to PHP coding to get the job done?
</p>

<p>
  - pass your own or Google's ad tracking ID on to ClickBank from your landing page url?
</p>

<p>
  - vary the content of web pages to find the content that ranks the best with Google?
</p>

<p>
  - generate a random quote of the day or some other random content to make your pages look more dynamic?
</p>

<p>
  - create dynamic articles...
</p>

<p>
  - use blog posts to create content templates for use in other pages.
</p>

<p>
  - enhance the text widgets on your blog with dynamic quotes, banners or text advertisements.
</p></span>

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.
== Frequently Asked Questions ==

<span lang="EN-AU"> <p>
  - Question: How do I add a section of rewritten text when posting to my blog?
</p>

<p>
  - Answer: Example: {rewrite}This is the {most useful|best} plugin ever!{/rewrite}
</p>

<p>
  - Question: How do can I control which variation of the post or page gets displayed?
</p>

<p>
  - Answer: You can add a random number seed value to your url. E.g. http://www.jv2win.com/wp-chameleon/?n=1
</p>

<p>
  - Question: What happens if the "normal" text of my post contains any of the special characters such as { or }? How do I prevent these from being treated as mark-up?
</p>

<p>
  - Answer: You can escape the special characters. For {, }, [, ] and | instead use [lcrl], [rcrl], [lsqb], [rsqb] and [pipe] respectively. This will be converted back to what you wanted when the page is displayed. There is no need to escape any text which is outside the rewrite tags.
</p>

<p>
  - Question: I want to pass other values in the query string of my page - can I do that too?
</p>

<p>
  - Answer: Yes just append whatever variable - e.g. "?<name>=<value>" and you will be able show the value in your page/post by placing [<name>] at the position where it should appear.
</p>

<p>
  - Question: Can I pass the value through by using custom variable when setting up wordpress page?
</p>

<p>
  - Answer: Yes. It works the same as when passing via the query string (which takes precedence).
</p>

<p>
  - Question: I want to place my visitors IP address in my ClickBank sales link. Can I do that?
</p>

<p>
  - Answer: Yes. Just add [cbip] as the tid value for the hoplink. E.g. "....?tid=[cbip]"
</p>

<p>
  - Question: Can I include the content of one post into another?
</p>

<p>
  - Answer: Yes. Use [post(<post id>)]" replacing <post id> with the post id. This allows you to use a post as a template within another.
</p>

<p>
  - Question: Can I pass arguments when including the content of one post into another?
</p>

<p>
  - Answer: Yes. Use the syntax [post(<post id>, @arg1[@var1], ...)]" to pass arguments.
</p>

<p>
  - Question: Can I transform variables and assign the result to other variables?
</p>

<p>
  - Answer: Yes. E.g. use [@new[map(@input,0{zero},1{one})]] to assign 'zero' or 'one' to @new for corresponding values of 0 or 1 in @input.
</p>

<p>
  - Question: Can I show different content based on the page the post appears on?
</p>

<p>
  - Answer: Yes. Add [@n[@url]] just after the {rewrite} tag.
</p>

<p>
  - Question: Can I post my content to multiple blogs?
</p>

<p>
  - Answer: Yes. You can set this up on the settings page.
</p></span>
== Screenshots ==
http://www.jv2win.com/wp-chameleon/

== Changelog ==
= 1.0 =
* Initial version

= 1.01 =
* Minor bug fix

= 1.02 =
* Added functionality for escaping reserved characters.

= 1.03 =
* Enhancements

= 1.04 =
* Changes and enhancements

= 1.05 =
* Features added. See FAQ.

= 1.06 =
* Plugin will now use a different hook to ensure both normal posts and posts via rss feeds are processed the same. Comments and Comment RSS are no longer included for processing.

= 1.07 =
* Code cleaned up and re-organized. Separate files created.

= 1.08 =
* "ucfirst" and "ucwords" functions added. "@url" variable returns permalink.

= 1.09 =
* Setting page added. Can now post to multiple blogs.

= 1.10 =
* Bug Fix

= 1.11 =
* Perform rewrites on remote posts

= 1.12 =
* Changes to README file. 

= 1.13 =
* Added instructions on settings screen

= 1.14 =
* Added 'map()' function and '@single' request variable

= 1.15 =
* Code clean-up

= 1.16 =
* Code clean-up and syntax standardization

= 1.17 =
* "Raw" mode added to options page. This will retain mark-up when posting to blogs that have this plug-in installed.

= 1.18 =
* Added site variables to options page.

= 1.19 =
* Avoid pages being treated as posts in RSS context.

= 1.20 =
* Improved random text generation. Weighting of nodes according to number of nodes within.

= 1.21 =
* Added rewriting of post titles.

= 1.22 =
* Compatible with WordPress 3.0.

== Upgrade Notice ==

= 1.11 =
This version contains major functionality enhancements.