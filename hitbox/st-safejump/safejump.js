(function () {
    var initializing = false, fnTest = /xyz/.test(function () {
        xyz;
    }) ? /\b_super\b/ : /.*/;
    // The base Class implementation (does nothing)
    this.Class = function () {
    };
    // Create a new Class that inherits from this class
    Class.extend = function (prop) {
        var _super = this.prototype;
        // Instantiate a base class (but only create the instance,
        // don't run the init constructor)
        initializing = true;
        var prototype = new this();
        initializing = false;
        // Copy the properties over onto the new prototype
        for (var name in prop) {
            // Check if we're overwriting an existing function
            prototype[name] = typeof prop[name] == "function" &&
                typeof _super[name] == "function" && fnTest.test(prop[name]) ?
                (function (name, fn) {
                    return function () {
                        var tmp = this._super;

                        // Add a new ._super() method that is the same method
                        // but on the super-class
                        this._super = _super[name];

                        // The method only need to be bound temporarily, so we
                        // remove it when we're done executing
                        var ret = fn.apply(this, arguments);
                        this._super = tmp;

                        return ret;
                    };
                })(name, prop[name]) :
                prop[name];
        }
        // The dummy class constructor
        function Class() {
            // All construction is actually done in the init method
            if (!initializing && this.init)
                this.init.apply(this, arguments);
        }

        // Populate our constructed prototype object
        Class.prototype = prototype;
        // Enforce the constructor to be what we expect
        Class.prototype.constructor = Class;
        // And make this class extendable
        Class.extend = arguments.callee;
        return Class;
    };
})();

function imageurl(character, move, idx) {
    idx = sprintf('%04d', idx);
    return character + '/' + move + '/' + idx + '.png';
}

var gImageCount = {"balrog":{"rh":59, "srk":88}, "blanka":{"rh":53, "srk":75}, "cammy":{"rh":61, "srk":87}, "chunli":{"rh":63, "srk":75}, "deejay":{"rh":58, "srk":90}, "dhalsim":{"rh":57, "srk":78}, "ehonda":{"rh":67, "srk":76}, "shoto":{"rh":96, "srk":80}, "feilong":{"rh":60, "srk":91}, "guile":{"rh":72, "srk":75}, "mbison":{"rh":54, "srk":84}, "sagat":{"rh":50, "srk":86}, "thawk":{"rh":58, "srk":87}, "vega":{"rh":48, "srk":96}, "zangief":{"rh":53, "srk":83}};
var gImageCache = [];
function cacheImages(character) {
    function _cacheImages(move) {
        for (var x = 0; x < gImageCount[character][move]; x++) {
            //document.write("<img src='" + url +"'/>");
            var url = imageurl(character, move, x);
            if (!gImageCache[url]) {
                gImageCache[url] = new Image();
                gImageCache[url].src = url;
            }
        }
    }
    _cacheImages('rh');
    _cacheImages('srk');
}
