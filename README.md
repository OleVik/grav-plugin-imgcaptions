# [Grav](http://getgrav.org/) Img Captions Plugin

Wraps images in `<figure>` and captions in `<figcaption>` based on the `title`-attribute of the `img`-element. From this Markdown:

    ![Street view](street.jpg "Street view from the east")

Which outputs this HTML:

    <p><img title="Street view from the east" alt="Street view" src="street.jpg"></p>

To this:

    <figure><img title="Street view from the east" alt="Street view" src="street.jpg"><figcaption>Street view from the east</figcaption></figure>

**Note:** The plugin unwraps images from paragraphs, ie. removes enclosing `<p>`-elements from `<img>`-elements, as `<figure>`-elements are invalid HTML within paragraphs.

## Installation and Configuration

1. Download the zip version of [this repository](https://github.com/OleVik/grav-plugin-imgcaptions) and unzip it under `/your/site/grav/user/plugins`.
2. Rename the folder to `imgcaptions`.

You should now have all the plugin files under

    /your/site/grav/user/plugins/imgcaptions

The plugin is enabled by default, and can be disabled by copying `user/plugins/imgcaptions/imgcaptions.yaml` into `user/config/plugins/imgcaptions.yaml` and setting `enabled: false`.

## New in 3.0.0

The Page-instance, and thus Media-instance, is now available to the `partials/figure.html.twig`-template. This means more interoperability between the figure and the Page, such as getting the URL to the full image with `{{ page.media[filename].url }}`.

## TODO

- [ ] Revise tests and test-data
- [ ] Implement DOM-parser

## Running tests

Run `composer update` to install the testing dependencies. Then run `composer test` in the root folder. Finally, run `composer update --no-dev` to uninstall the testing dependencies.

MIT License 2017-2019 by [Ole Vik](https://github.com/olevik).
