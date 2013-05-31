<?php
require_once('../autoload.php');

function displayVideo() {
    $yt_id = Video::extractYoutubeIDFromUrl($_REQUEST['yt_id']);
    $video = Video::parseVideo($yt_id);
    $text = Util::htmlescape($video['title']) . "\n\n" . Util::htmlescape($video['content']);

echo <<<EOF
<table><tr><td>
<object width="420" height="315">
    <param name="movie" value="http://www.youtube.com/v/$yt_id&hl=en&fs=1&rel=0"></param>
    <param name="allowFullScreen" value="true"></param>
    <param name="allowscriptaccess" value="always"></param>
    <embed src="http://www.youtube.com/v/$yt_id&hl=en&fs=1&rel=0"
           type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480"
           height="385"></embed>
</object>
</td>
<td>
<textarea cols=60 rows=25>
$text
</textarea>
</td></tr></table>
EOF;
}


?>
<head>

    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"/>
    <link rel="stylesheet" href="../static/jqueryCombo.css"/>

    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="../static/jqueryCombo.js"></script>
    <script src="../static/sprintf-0.7-beta1.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js"></script>
    <style type="text/css">
        body {
            font-family: sans-serif;
        }

        input[type=text] {
            font-family: Verdana, Arial, sans-serif;
            font-size: 1.1em;
        }

        input[name=start] {
            width: 60px;
        }

        input[name=name] {
            width: 290px;
        }

        input.player {
            width: 110px;
        }

        input[type=submit] {
            border: 1px solid #006;
            background: #ffc;
        }

        input {
            color: black;
        }

        .combotd {
            min-width: 205px;
        }

        td, th {
            vertical-align: top;
            text-align: left;
            /*padding: 1px;*/
            /*border: solid 1px lightgrey;*/
        }

        .errmsg {
            color: red;
        }

    </style>
</head>

    <table width=100%><tr><td><strong>Submit video annotation</strong>
    </td><td><a href='?'>Submit another video</a>
    </td><td><a href=../?>Matchup video Index</a>
    </td></tr></table>
<?php displayVideo(); ?>
<form id=addMatchForm action="" method="post">
    <table>
        <tr>
            <th>Time Stamp</th>
            <th>Player 1</th>
            <th>Character 1</th>
            <th>Player 2</th>
            <th>Character 2</th>
            <th>Winner</th>
            <th></th>
        </tr>
        <tr>
            <td><input type=text name="start" placeholder="mm:ss"></td>
            <td><input class=player type=text name="player1" placeholder="Player 1"></td>
            <td class=combotd><?php Html::charDropdown(array('name' => "char1"));?></td>
            <td><input class=player type=text name="player2" placeholder="Player 2"></td>
            <td class=combotd><?php Html::charDropdown(array('name' => "char2"));?></td>
            <td><input class=player type=text name="winner" placeholder="Winner"></td>
            <td><input type=submit value="Add"></td>
        </tr>
    </table>
    <div class=errmsg></div>
</form>
<script type='text/template' id='matchTemplate'>
    <%= mm %>:<%= ss %> <%= player1 %> (<%= char1 %>) vs. <%= player2 %> (<%= char2 %>) [<%= winner %>]
    <button class=delete>Delete</button>
</script>
<ol id='matchesViewOL'></ol>

<form id=submitAnnotationsForm action="" style='display:none'>
    <input type=text value="<?php echo Util::htmlescape($_COOKIE['contributor']); ?>" name="name" placeholder="Your name (for credits)">
    <input type=submit value="Submit Annotations">

    <div class=errmsg></div>
</form>

<script type="text/javascript">
window.App = {
    Models:{},
    Views:{},
    Collections:{},
    Globals:{
        playersCompletions:[
<?php
    $allnames = Player::allNames();
    $allnames = array_map('json_encode', $allnames);
    echo implode(',', $allnames);
?>
        ]
    },
    Utils:{}
};

App.Utils.format = function (s) {
    var i = arguments.length - 1;
    while (i-- > 0) {
        s = s.replace(new RegExp('\\{' + i + '\\}', 'gm'), arguments[i + 1]);
    }
    return s;
};
App.Utils.template = function (id) {
    return _.template($('#' + id).html());
};

App.Utils.confirm = function(config) {
    config = $.extend({
        title:'Yes or No?',
        prompt:'Yes or No?',
        yes:$.noop,
        no:$.noop
    }, config);
    $('<div></div>')
            .appendTo('body')
            .html('<div><h6>' + config.prompt + '</h6></div>')
            .dialog({
                modal:true, title: config.title, zIndex:10000, autoOpen:true,
                width:'auto', resizable:false,
                buttons:{
                    Yes:function () {
                        config.yes();
                        $(this).dialog("close");
                    },
                    No:function () {
                        config.no();
                        $(this).dialog("close");
                    }
                },
                close:function (event, ui) {
                    $(this).remove();
                }
            });
};

App.Models.Match = Backbone.Model.extend({
});

App.Views.Match = Backbone.View.extend({
    tagName:'li',
    template:App.Utils.template('matchTemplate'),
    initialize:function () {
        this.model.on('change', this.render, this);
        this.model.on('destroy', this.remove, this);
        // cannot bind using backbone events change for some reasons, have to use jquery to setup the event
        // $('input[name="my-dropdown"]', this.el).on('change', $.proxy(this.onselect, this));
    },
    events:{
        'click .delete':'destroyMatch'
    },
    destroyMatch:function () {
        this.model.destroy();
    },
    remove:function () {
        this.$el.remove();
    },
    render:function () {
        var props = $.extend({
            mm:sprintf("%02d", Math.floor(this.model.get('start') / 60)),
            ss:sprintf("%02d", this.model.get('start') % 60)
        }, this.model.toJSON());
        var template = this.template(props);
        this.$el.html(template);
        return this;
    }
});

App.Collections.Matches = Backbone.Collection.extend({
    comparator:function (model) {
        return model.get('start');
    },
    model:App.Models.Match
});

App.Views.Matches = Backbone.View.extend({
    el:'#matchesViewOL',
    initialize:function () {
        this.collection.on('add', this.addOne, this);
        this.collection.on('sort', this.render, this);
        this.collection.on('reset', this.render, this);
    },
    render:function () {
        this.$el.html('');
        this.collection.each(this.addOne, this);
        return this;
    },
    addOne:function (Match) {
        //create a child view and append to root element
        var MatchView = new App.Views.Match({model:Match});
        this.$el.append(MatchView.render().el);
        return this;
    }
});

App.Views.AddMatch = Backbone.View.extend({
    el:'#addMatchForm',
    events:{
        'submit':'submit'
    },
    setError:function (errmsg) {
        $('.errmsg', this.$el).html(errmsg);
    },
    clearError:function () {
        this.setError('');
    },
    displayError:function (errmsg) {
        this.setError(errmsg.join('<br>'));
    },
    submit:function (e) {
        e.preventDefault();
        this.clearError();

        var request = function (name) {
            var el = $(e.currentTarget).find(App.Utils.format('input[name="{0}"], select[name="{0}"]', name));
            return $.trim(el.val());
        };
        var fields = [
            "start",
            "player1",
            "char1",
            "player2",
            "char2",
            "winner"
        ];
        var props = {};
        var ok = true;
        var errmsg = [];
        $.each(fields, function (idx, f) {
            props[f] = request(f);
            if (!props[f]) {
                errmsg.push('please provide all the fields');
                ok = false;
                return;
            }
            if (f == 'start') {
                var arr = $.map(props[f].split(':'), function (a) {
                    return parseInt(a, 10);
                });
                if (arr.length != 2) {
                    errmsg.push('invalid time stamp, missing colon :');
                    ok = false;
                } else {
                    if (arr[0] < 0 || arr[0] > 60 || arr[1] < 0 || arr[1] > 60) {
                        errmsg.push('invalid time stamp, numbers too big');
                        ok = false;
                    } else {
                        props[f] = (arr[0] * 60) + arr[1];
                    }
                }
            }
        });
        if (props['winner'] != props['player1'] && props['winner'] != props['player2']) {
            ok = false;
            errmsg.push('invalid winner');
        }
        var exists = this.collection.find(function(model){
            return props['start'] == model.get('start');
        });
        if (exists) {
            ok = false;
            errmsg.push('same timestamp exists');
        }

        if (!ok) {
            this.displayError(errmsg);
            return;
        }
        var match = new App.Models.Match(props);
        this.collection.add(match);
    }
});

App.Views.SubmitAnnotations = Backbone.View.extend({
    el:'#submitAnnotationsForm',
    events:{
        'submit':'submit'
    },
    initialize:function() {
        this.collection.on('add', this.checkEmpty, this);
        this.collection.on('reset', this.checkEmpty, this);
        this.collection.on('remove', this.checkEmpty, this);
    },
    checkEmpty:function() {
        this.$el.toggle(!this.collection.isEmpty());
    },
    setError:function (errmsg) {
        $('.errmsg', this.$el).html(errmsg);
    },
    clearError:function () {
        this.setError('');
    },
    displayError:function (errmsg) {
        this.setError(errmsg);
    },
    ajaxRequest:function(name) {
        $.ajax({
            type:"POST",
            url:'post.php',
            data:{
                contributor:name,
                yt_id:<?php echo json_encode(Video::extractYoutubeIDFromUrl($_REQUEST['yt_id'])); ?>,
                data:JSON.stringify(App.Globals.matchesCollection.toJSON())
            },
            success:function (response) {
                if (response == 'ok') {
                    App.Globals.matchesCollection.reset();
                    alert('Thanks for submitting');
                } else {
                    alert(response);
                }
            },
            error:function () {
                alert('Error submitting');
            }
        });
    },
    submit:function (e) {
        e.preventDefault();
        App.Globals.addMatchView.clearError();
        this.clearError();
        var name = $.trim($(e.currentTarget).find('input[name="name"]').val());
        if (!name) {
            this.displayError('Please provide your name');
            return;
        }
        var count = App.Globals.matchesCollection.length;
        if (count == 0) {
            this.displayError('Please add annotation');
        } else {
            if (App.Utils.confirm({
                prompt:App.Utils.format('Submit {0} annotation(s)?', count),
                yes:$.proxy(this.ajaxRequest, this, name)
            }));

//            if (confirm(App.Utils.format('Submit {0} annotation(s)?', count))) {
//                this.ajaxRequest(name);
//            }
        }
    }
});

$(document).ready(function () {
    App.Globals.matchesCollection = new App.Collections.Matches([]);
    App.Globals.matchesView = new App.Views.Matches({collection:App.Globals.matchesCollection});
    App.Globals.matchesView.render();
    App.Globals.addMatchView = new App.Views.AddMatch({collection:App.Globals.matchesCollection});
    App.Globals.submitAnnotations = new App.Views.SubmitAnnotations({collection:App.Globals.matchesCollection});
    $(".jqueryCombo").combobox();
    $(".custom-combobox-input").on('focus', function () {
        this.select()
    });
    $(".player").autocomplete({
        source:App.Globals.playersCompletions
    });
});


</script>
