<?php
function charDropdown($attrs) {
    $characters = array('ryu', 'ken', 'ehonda', 'chunli', 'blanka', 'zangief', 'guile', 'dhalsim', 'thawk', 'cammy', 'feilong', 'deejay', 'boxer', 'claw', 'sagat', 'dictator');
    echo "<select class=jqueryCombo";
    foreach ($attrs as $k => $v) {
        echo " $k='$v'";
    }
    echo ">";
    foreach ($characters as $c) {
        echo "<option value='$c'>$c</option>";
        echo "<option value='o.$c'>o.$c</option>";
    }
    echo "<option value='akuma'>akuma</option>";
    echo "</select>";
}

?>
<head>

    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"/>
    <link rel="stylesheet" href="jqueryCombo.css"/>

    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="jqueryCombo.js"></script>
    <!--<link rel="stylesheet" href="http://jqueryui.com/resources/demos/style.css" />-->
    <script src="underscore.js"></script>
    <script src="backbone.js"></script>
    <script src="sprintf-0.7-beta1.js"></script>
    <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>-->
    <!--<script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>-->
    <!--<script src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js"></script>-->
    <!--<script src="jquery-1.10.0.js"></script>-->
    <style type="text/css">
        body {
            font-family: sans-serif;
        }

        input[type=text] {
            font-family: Verdana, Arial, sans-serif;
            font-size: 1.1em;
        }

        input[name=start] {
            width: 90px;
        }

        input[name=name] {
            width: 290px;
        }

        input.player {
            width: 150px;
        }

        .combotd {
            min-width: 255px;
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

<!--<h1>Submit youtube matchup video annotation</h1>-->

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
            <td><input type=text value="1:23" name="start" placeholder="mm:ss"></td>
            <td><input class=player type=text value="abc" name="player1" placeholder="Player 1"></td>
            <td class=combotd><?php charDropdown(array('name' => "char1"));?></td>
            <td><input class=player type=text value="abc" name="player2" placeholder="Player 2"></td>
            <td class=combotd><?php charDropdown(array('name' => "char2"));?></td>
            <td><input class=player type=text value="abc" name="winner" placeholder="Winner"></td>
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
<hr>

<form id=submitAnnotationsForm action="">
    <input type=text value="" name="name" placeholder="Your name (for credits)">
    <input type=submit value="Submit Annotations">

    <div class=errmsg></div>
</form>

<script type="text/javascript">
window.App = {
    Models:{},
    Views:{},
    Collections:{},
    Globals:{
        playersCompletions:[]
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
    setError:function (errmsg) {
        $('.errmsg', this.$el).html(errmsg);
    },
    clearError:function () {
        this.setError('');
    },
    displayError:function (errmsg) {
        this.setError(errmsg);
    },
    submit:function (e) {
        e.preventDefault();
        this.clearError();
        var name = $.trim($(e.currentTarget).find('input[name="name"]').val());
        if (!name) {
            this.displayError('please provide your name');
            return;
        }
        var count = App.Globals.matchesCollection.length;
        if (count == 0) {
            this.displayError('Please add annotation');
        } else {
            if (confirm(App.Utils.format('Submit {0} annotation(s)?', count))) {
                $.ajax({
                    type:"POST",
                    url:'post.php',
                    data:{
                        name:name,
                        yt_id:'yt_id_abckjasdkf',
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
            }
        }
    }
});

$(document).ready(function () {
    App.Globals.matchesCollection = new App.Collections.Matches([]);
    App.Globals.matchesView = new App.Views.Matches({collection:App.Globals.matchesCollection});
    App.Globals.matchesView.render();
    App.Globals.addMatchView = new App.Views.AddMatch({collection:App.Globals.matchesCollection});
    App.Globals.submitAnnotations = new App.Views.SubmitAnnotations();
    $(".jqueryCombo").combobox();
    $(".custom-combobox-input").on('focus', function () {
        this.select()
    });
    $(".player").autocomplete({
        source:App.Globals.playersCompletions
    });
});


</script>
