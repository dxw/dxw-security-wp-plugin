# MongooseWP (WordPress Plugin)

The Mongoose Wordpress plugin lets you know about security vulnerabilities in the plugins installed on your site. See the [readme.txt](readme.txt) for more details and installation instructions.

## Development with Grunt

[Grunt](http://gruntjs.com/) is a task runner which is used for a number of development and build tasks. Install it and the required plugins (specified in [package.json](package.json)) using [npm](https://www.npmjs.org/):

    npm install

Here are the grunt commands you'll need:

`grunt` - compiles the css and minifies the js (creating `main.min.css` and `main.min.js`).

`grunt watch` monitors the assets directory for changes and compile the css and js when changes are detected.

`grunt build` copies the deployable set of files to the build directory (this is what lives in WordPress' svn repo) and produces a zip file which can be used to install the plugin.

## Editing the css

The CSS for this plugin is written in [less](http://lesscss.org/) - the `.less` files can be found in [assets/css](assets/css).

## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request
