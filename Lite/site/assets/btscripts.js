/**
 * Website: www.jtips.com.au
 * @author Jeremy Roberts
 * @copyright Copyright &copy; 2009, EvolutionEngin
 * @license GPLv3
 * 
 * @since 1.0 - 02/06/2009
 * @version 1.0.0
 * @package BizTweet
 */

function btCountChars(obj) {
	if (typeof obj == 'string') {
		obj = $(obj);
	}
    var remaining = 140 - parseInt(obj.value.length);
    $('btcharcount').setHTML(remaining);
    if (remaining < 0) {
        $('btcharcount').setStyle('color', 'red');
        $('btpost').disabled = true;
    } else if (remaining <= 20) {
        $('btcharcount').setStyle('color', 'orange');
        $('btpost').disabled = false;
    } else {
        $('btcharcount').setStyle('color', 'lightgrey');
        $('btpost').disabled = false;
    }
}

function btParseUrl(dom_id, _default) {
    var rawUrl = $(dom_id).value;
    if (rawUrl == _default) return; // do nothing
    if (rawUrl.length == 0) {
            return;
    }
    new Ajax('index2.php?option=com_biztweet&task=trim', {
        method: 'POST',
        data: 'url=' + escape(rawUrl),
        onComplete:function(result) {
            var insert = '';
            var res = Json.evaluate(result);
            if (res.status.result == 'ERROR') {
                alert(res.status.message);
                return;
            }
            if ($('tweet').value.length > 0 && $('tweet').value.match(/[^\s]$/)) {
                insert += ' ';
            }
            insert += res.url;
            $('tweet').value += insert;
            $(dom_id).value = _default;
            btCountChars($('tweet'));
        }
    }).request();
}

function btRT(dom_id, username) {
	var val = $(dom_id).getText();
	var insert = '';
	if ($('tweet').value.length > 0 && $('tweet').value.match(/[^\s]$/)) {
		insert += ' ';
	}
	insert += '@' + username + ' ' +val;
	$('tweet').value += 'RT ' + insert;
	btCountChars($('tweet'));
}

function btReply(username, id) {
	var insert = '';
	if ($('tweet').value.length > 0 && $('tweet').value.match(/[^\s]$/)) {
		insert += ' ';
	}
	insert += '@' + username;
	$('tweet').value += insert;
	$('reply_id').value = id;
	btCountChars($('tweet'));
}

function btCheckUrl(dom_id, _default) {
	if ($(dom_id).value == _default) {
		$(dom_id).value = '';
	} else if ($(dom_id).value == '') {
		$(dom_id).value = _default;
	}
}

function btReset(_default) {
	$('reply_id').value = '';
	$('tweet').value = _default;
	btCountChars($('tweet'));
}