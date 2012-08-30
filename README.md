flexxblog
=========

A blog system based mainly on xml and xsl, without relational db

Currently this is a development version. There are many test files which will be removed.

What works:
It works for me :) It is possible to create articles as rss file, convert them to html, insert the artikle into the overview rss, convert that to html, comment the article, and on commenting, placing a notice in a "new comments" rss, which could be subscribed to, f.ex in the dynamic bookmarks in firefox.

What do do if you want to use it:
The rss format is specified and fixed. It is created using PHP. These files do not need alteration. The conversion to html is done by an xsl stylesheet named "rss2html.xsl". To create html pages that fit your needs you have to write your own. The stylesheet here may be used as a template. I'd recommend that you create an xsl stylesheet that produces html compatible to your existing site, so that you can reuse your existing css. Although that is just a recommendation.

Next major step will be pingback.

Other features planned (i might need help): Comment spam protection, The possibility to edit articles, maybe comments too, delete comments, and a category system based upon a knowledge representatin system like owl.

Siegfried Gipp
siegfried@rorkvell.de
http://www.rorkvell.de
