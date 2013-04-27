<?php
define('WP_USE_THEMES', true);
$wp_did_header = true;
require_once( '../../wp-load.php' );
wp();
if ( defined('WP_USE_THEMES') && WP_USE_THEMES ) do_action('template_redirect');

//$template = get_home_template();
//echo $template; exit();

///home/kuropp5/public_html/www.strevival.com/wp-content/themes/Sensei/index.php

get_header();
include '../../wp-content/themes/Sensei/header-bottom.php'; 
    
echo '<div id="content">';

require('safejump.php');
$characters = array('balrog','blanka','cammy','chunli','deejay','dhalsim','ehonda','feilong','guile','mbison','shoto','sagat','thawk','vega','zangief');
$jumpframes = array(
    41 => array('Claw'),
    44 => array('Blanka', 'Old Sagat'),
    46 => array('FeiLong','Sagat','THawk','Zangief', 'Old DeeJay'),
    48 => array('Boxer','DeeJay', 'Old Ken', 'Old Ryu'),
    49 => array('Ken','Ryu'),
    50 => array('Cammy','ChunLi','EHonda'),
    51 => array('Dictator'),
    52 => array('Guile')
    );
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
<script src="sprintf-0.7-beta1.js"></script>
<script src="safejump.js"></script>

<h1>Safe Jump Illustrated Guide</h1>

Select your character to safe jump with <input style="display:none" type="checkbox" id="old-character"><span style="display:none">Old Character</span></input>

<br/>
<?php
$checked = true;
foreach($jumpframes as $sec => $names) {
    $checked = $checked ? 'checked' : '';
    echo "<input type='radio' name='attacker' value='$sec' $checked id='$sec-frames'>";
    echo "<span class='framedisplay'>$sec</span>";
    $label = ' frames ' . implode('&nbsp;', $names);
    echo "<label for=$sec-frames>$label</label>";
    echo "<br/>";
    $checked = '';
}
?>
<p/>
<br/>
Select Opponent / Target: <select id='target-character'>
<?
    foreach($characters as $c)
    echo "<option value=$c>$c</option>";
?>
</select>
<br>
After <input type='radio' name='knockdown' value='srk' checked>Full knowndown (Srk)</input><input type='radio' name='knockdown' value='rh'>Regular knowndown (Crouching HK)</option>

<div id='image-background' style='padding:5px'>
<img id='animation' style="border: 6px white solid">
</div>
<button id='left'>&lt;&lt;</button><button id='click'>Animate</button><button id='jump'>Jump</button><button id='right'>&gt;&gt;</button>

<script>
var character = 'balrog';
$("#target-character").change(function(e) {
    character = $(e.currentTarget).val();
    new Animation();
});

var attackerJumpframe = 41;

$('#old-character').click(function(e) {
    var isOld = $(e.currentTarget).is(':checked');
    if (isOld) {
        attackerJumpframe--;
    } else {
        attackerJumpframe++;
    }
    $.each($('span.framedisplay'), function(index,value){
        var f = parseInt($(value).html(), 10);
        if (isOld) {
            f--;
        } else {
            f++;
        }
        $(value).html(f);
    });
    new Animation();
});


function updateAttacker() {
    var val = $("input[name='attacker']:checked").val();
    attackerJumpframe = parseInt(val, 10);
    if ($('#old-character').is(':checked')) {
        attackerJumpframe--;
    }
    new Animation();
}
 
$('input[name="attacker"]').change(updateAttacker);

var knockdown = 'srk';

function updateKnockdown() {
    var val = $("input[name='knockdown']:checked").val();
    knockdown = val;
    new Animation();
}

$('input[name="knockdown"]').change(updateKnockdown);


var Animation = Class.extend({
  init: function(){
    this.character = character;
    this.knockdown = knockdown;
    this.length = gImageCount[character][knockdown];
    cacheImages(character);
    this.direction = -1;
    this.mousedown = false;
    this.idx = attackerJumpframe;
    var color = "blue";
    $('#animation').attr('src', imageurl(this.character, this.knockdown, this.idx)).css("border", "6px " + color + " solid");


    $("#jump").unbind("click");
    $("#click").unbind("click");
    $("#left").unbind("mousedown");
    $("#left").unbind("mouseup");
    $("#right").unbind("mousedown");
    $("#right").unbind("mouseup");

    var self = this;
    $('#jump').click(function(){
            self.idx = attackerJumpframe;
            self.render();
    });
/*
    $('#left').click(function(){
            if (self.idx + 1 >= self.files.length) {
                return;
            }
            self.idx++;
            self.render();
    });
    $('#right').click(function(){
            if (self.idx - 1 < 0) {
                return;
            }
            self.idx--;
            self.render();
        });
*/
    $('#click').click(function() { 
        $('button').attr("disabled", true);
        self.idx = self.length-1;
        self.animate();
    });

    $('#left').mousedown($.proxy(this.fastBackward, this));
    $('#right').mousedown($.proxy(this.fastForward, this));
    $('#left').mouseup($.proxy(this.mouseup, this));
    $('#right').mouseup($.proxy(this.mouseup, this));
  },
  render: function(){
    if (this.idx < 0 || this.idx >= this.length) {
        return;
    }
    var color = "black";
    if (this.idx==attackerJumpframe) {
        color = "blue";
    }
    $('#animation').attr('src', imageurl(this.character, this.knockdown, this.idx)).css("border", "6px " + color + " solid");
  },
  moveFrames: function() {
    if (this.idx + this.direction < 0 || this.idx + this.direction >= this.length) {
        return;
    }
    this.idx += this.direction;
    this.render();
    if (this.mousedown) {
        setTimeout($.proxy(this.moveFrames,this), 100);
    }
  },
  fastBackward: function() {
    this.direction = 1;
    this.mousedown = true;
    setTimeout($.proxy(this.moveFrames,this), 100);
  },
  fastForward: function() {
    this.direction = -1;
    this.mousedown = true;
    setTimeout($.proxy(this.moveFrames,this), 100);
  },
  mouseup : function() {
    this.mousedown = false;
  },
  animate: function(idx) {
    var self = this;
    this.direction = -1;
    var delay = 100;
    if (this.idx==attackerJumpframe) {
        delay = 1000;
    }
    this.render();
    if (this.idx + this.direction < 0 || this.idx + this.direction >= this.length) {
        $('button').attr("disabled", false);
        return;
    }
    this.idx += this.direction;
    setTimeout($.proxy(this.animate, this), delay);
  }
});

new Animation();

</script>

<?php
echo '</div>';
//echo '<div class="clearboth"></div>';
//get_sidebar();
get_footer();


/*
echo '<div id="primary" class="site-content"><div id="content" role="main">';

<html>
<head>
<title>Super Turbo Safe Jump Illustrated</title>
<style type="text/css">
    body {
        font-family: "Arial";
    }
</style>
</body>
</html>
*/
/*
echo '</div></div>';

get_sidebar();
get_footer();

*/