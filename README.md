# [Grav](http://getgrav.org/) Image Captions Plugin

Wraps images in `<figure>` and captions in `<figcaption>` based on the `title`-attribute of the `img`-element. From this:

	<p><img title="Street view from the east" alt="Street view" src="street.jpg"></p>

To this:

	<figure><img title="Street view from the east" alt="Street view" src="street.jpg"><figcaption>Street view from the east</figcaption></figure>

This is only applied to images with the `title`-attribute set:

	![Image Alt](file.jpg "TITLE")

**Note:** The plugin unwraps images from paragraphs, ie. removes enclosing `<p>`-elements from `<img>`-elements, as `<figure>`-elements are invalid HTML within paragraphs.

# Installation and Configuration

1. Download the zip version of [this repository](https://github.com/OleVik/grav-plugin-imgcaptions) and unzip it under `/your/site/grav/user/plugins`.
2. Rename the folder to `imgcaptions`.

You should now have all the plugin files under

    /your/site/grav/user/plugins/imgcaptions

The plugin is enabled by default, and can be disabled by copying `user/plugins/imgcaptions/imgcaptions.yaml` into `user/config/plugins/imgcaptions.yaml` and setting `enabled: false`.

MIT License 2016 by [Ole Vik](http://github.com/olevik).