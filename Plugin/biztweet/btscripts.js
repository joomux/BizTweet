/**
 * Website: www.jtips.com.au
 * 
 * @author Jeremy Roberts
 * @copyright Copyright &copy; 2009, EvolutionEngin
 * @license Commercial - See website for details
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
    $('btlinkload').setStyle('visibility', 'visible');
    $('btlinkbtn').setProperty('disabled', true);
    new Ajax(btSitePath+'index2.php?option=com_biztweet&task=trim&Itemid='+btItemid, {
        method: 'POST',
        data: 'url=' + escape(rawUrl),
        onComplete:function(result) {
            var insert = '';
            if ($('tweet').value.length > 0 && $('tweet').value.match(/[^\s]$/)) {
                insert += ' ';
            }
            try { // using tr.im
            	var res = Json.evaluate(result);
		    	if (res.status.result == 'ERROR') {
		    		alert(res.status.message);
		    		return;
		    	}
            	insert += res.url;
			} catch (e) { // using tinyurl
				insert += result;
			}
            $('tweet').value += insert;
            $(dom_id).value = _default;
		    $('btlinkload').setStyle('visibility', 'hidden');
		    $('btlinkbtn').setProperty('disabled', false);
            btCountChars($('tweet'));
        }
    }).request();
}

function btRT(dom_id, username) {
	var val = $(dom_id).getText().trim();
	var insert = '';
	if ($('tweet').value.length > 0 && $('tweet').value.match(/[^\s]$/)) {
		insert += ' ';
	}
	insert += '@' + username + ' ' +val;
	// if there are images in the html, need to extract the src and modify
	var imgs = $$($(dom_id).getElementsByTagName('img'));
	imgs.each(function(img) {
		// reparse the img src
		var tpid = basename(img.getProperty('src'), '.jpg');
		insert += ' http://twitpic.com/' + tpid;
	});
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
	if (_default == undefined || typeof _default != 'string') _default = '';
	$('reply_id').value = '';
	$('tweet').value = _default;
	btCountChars($('tweet'));
}

function btAddTag(tag) {
	var insert = '';
	if ($('tweet').value.length > 0 && $('tweet').value.match(/[^\s]$/)) {
		insert += ' ';
	}
	insert += tag;
	$('tweet').value += insert;
	btCountChars($('tweet'));
}

function btShowLogin() {
//	var slide = new Fx.Slide('btlogin');
	$('btloggedin').setStyle('display', 'none');
	$('btusername').setProperty('disabled', false);
	$('btusername').addClass('required');
	$('btpassword').setProperty('disabled', false);
	$('btpassword').addClass('required');
	$('btlogin').setStyle('display', 'inline');
// slide.slideIn();
}

function btCheckTags(dom_id) {
	if (btRequireTags == undefined || btRequireTags == 0) return true; // validation
																		// not
																		// enabled
	var txt = $(dom_id).value.toLowerCase();
	var tagstr = unescape(btHashTags).clean();
	if (tagstr.length == 0) return true;
	var tags = tagstr.split(' ');
	var retval = true;
	tags.each(function(t) {
		if (!txt.contains('#'+t.toLowerCase())) {
			retval = false;
		}
	});
	if (!retval) {
		//alert('Hashtags are required');
	}
	return retval;
}

function btAddSearch(dom_id, _default) {
	var term = $(dom_id).value.trim().replace(/[^A-Za-z0-9_\#]/g, '+');
	try {
		$(dom_id).blur();
	} catch (e) {}
	if (term == _default || term == '') return;
	// how many searches are there already?
	if (btUserSearches != undefined && btUserSearches.length < 3 && !btUserSearches.contains(term)) {
		btUserSearches.push(term);
		var term_id = escape(term.replace(/[^A-Za-z0-9_\#\+]/g, '__'));
		// now create the basic elements and execute the search
		var wrap = new Element('div', {
			'styles':{
				'float':'left'// ,
				// 'width':width + '%'
			},
			'id':'wrap'+term_id
		});
		var rmlnk = new Element('a', {
			'events': {
				'click':function() {
					btRemoveSearch('bt'+term_id);
				}
			},
			'title':'Remove Search'
		});
		var rmimg = new Element('img', {
			'src':btSitePath+'components/com_biztweet/assets/delete.png',
			'align':'absmiddle',
			'styles':{
				'cursor':'pointer'
			}
		});
		rmlnk.adopt(rmimg);

		var title = new Element('div', {'class':'contentheading'});
		title.adopt(rmlnk);
		title.appendText(' '+term.replace(/\+/g, ' '));
		wrap.adopt(title);

		// the loading wrapper
		var loadw = new Element('div', {
			'styles':{
				'text-align':'center'
			},
			'id':'btload_' + term_id.replace(/\+/g, '__')
		});
		// set the loading image
		var load = new Element('img', {
			'src':btSitePath+'components/com_biztweet/assets/loading.gif',
			'alt':'Searching...'
		});
		loadw.adopt(load);
		wrap.adopt(loadw);
		
		// now the ul shell
		var ul = new Element('ul', {
			'class':'timeline btsearchbox',
			'title':term,
			'id':'bt' + term_id
		});
		wrap.adopt(ul);

		$('btsearchresults').adopt(wrap);
		btAdjustWidths();
		$(dom_id).value = _default;

		// now save the search term
		var saveSearch = new Ajax('index2.php', {
			method:'post',
			data:'option=com_biztweet&task=search&term=' + escape(term)
		}).request();
	} else {
		if (btUserSearches.contains(term)) {
			alert('Search already running...');
		} else if (btUserSearches.length >= 3) {
			alert('Maximum number of searches reached');
		}
	}
}

function btRemoveSearch(dom_id) {
	new Ajax('index2.php', {
		method:'post',
		data:'option=com_biztweet&task=clear&term=' + $(dom_id).getProperty('title'),
		onComplete:function() {
			//delete the term
			btUserSearches.remove($(dom_id).getProperty('title'));
			// delete the div
			$(dom_id).getParent().remove();
			// adjust widths
			btAdjustWidths();
		}
	}).request();

}

function btAdjustWidths() {
	if (btUserSearches != undefined && btUserSearches.length < 4 && btUserSearches.length > 0) {
		var width = Math.floor(100/btUserSearches.length);
		// adjust widths of other elements
		$('btsearchresults').getChildren().each(function(item) {
			item.setStyle('width', width+'%');
		});
	}

}

window.addEvent('domready', function() {
	document.formvalidator.setHandler('hashtags', function(value) {
		return btCheckTags('tweet');
	});
	try {
		$('searchbt').addEvent('keydown', function(event) {
			event = new Event(event);
			if (event.key == 'enter') {
				btAddSearch('searchbt', 'search...');
			}
		});
	} catch (e) {} // not on custom search page
});

var btCurrentDiv = [];
var btLatestPost = [];

function btUpdateSearch(dom_id) {
	var target = $(dom_id);
	var q = escape(target.getProperty('title'));
	var url = 'http://search.twitter.com/search.json?q=' + q + '&callback=btUpdateBox&rpp=' + btSearchLimit;
	if (btLatestPost[q] != undefined) {
		url += '&since_id=' + btLatestPost[q];
	}
	if (btLanguage != undefined && btLanguage != '') {
		url += '&lang=' + btLanguage;
	}
	periodicChecker(url);
}

function btUpdateSearches() {
	var divs = $$('.btsearchbox');
	if (divs.length > 0) {
		for (var i=0; i<divs.length; i++) {
			var term_id = divs[i].getProperty('title').replace(/[^A-Za-z0-9_]/g, '__');
			btCurrentDiv[term_id] = divs[i];
			btUpdateSearch(divs[i]);
		}
	}
	setTimeout("btUpdateSearches();", btUpdateFrequency);
}

function btUpdateBox(json) {
	if (json.error) {
		return;
	}
	var items = json.results;
	var term_id = unescape(json.query.replace(/[^A-Za-z0-9_]/g, '__'));
	var update = btCurrentDiv[term_id];
	// remove the loading image if it exists
	try {
		$('btload_' + term_id).remove();
	} catch (e) {}
	items.reverse();
	items.each(function(item, index) {
		// need to remove anything that makes the list longer than 20
		var li = new Element('li');
		if (btEnableRetweet || btEnableReply) {
			li.addEvent('mouseover', function() { $('btopt_'+item.id).setStyle('visibility', 'visible');});
			li.addEvent('mouseout', function() { $('btopt_'+item.id).setStyle('visibility', 'hidden');});
		}
		var imgspan = new Element('span', {'class':'biztweet_image'});
		var imglink = new Element('a', {
			'href':'http://twitter.com/' + item.from_user,
			'target':'_blank',
			'title':item.from_user
		});
		var img = new Element('img', {
			'src': item.profile_image_url,
			'width':'48px',
			'height':'48px'
		});
		imglink.adopt(img);
		imgspan.adopt(imglink);
		li.adopt(imgspan);


		// now for the text
		var txtspan = new Element('span', {'class':'biztweet_body'});
		
		// username linked
		var namelink = new Element('a', {
			'href':'http://twitter.com/' + item.from_user,
			'target':'_blank',
			'title':item.from_user
		}).setHTML('<strong>' + item.from_user + '</strong>');
		txtspan.adopt(namelink);
		txtspan.appendText(' ');
		var text = new Element('span', {
			'id':'bt_' + item.id
		});
		text.setHTML(item.text.btlinkify().btlinkuser().btlinktag());
		btLinkTwitPic(text);
		if (btLinkNewWindow) {
			var as = $$(text.getElementsByTagName('a'));
			as.each(function(anchor) {
				anchor.setProperty('target', '_blank');
			});
		}
		txtspan.adopt(text);

		var infodiv = new Element('div', {'class':'bttime'});
		var time = btCalculateTime(item.created_at);
		var about = time + ' from ' + unescape(item.source).replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
		infodiv.setHTML(about);
		if (btLinkNewWindow) {
			var as = $$(infodiv.getElementsByTagName('a'));
			as.each(function(anchor) {
				anchor.setProperty('target', '_blank');
			});
		}
		txtspan.adopt(infodiv);
		li.adopt(txtspan);


		// now for the reply and retweet links
		if (btEnableRetweet || btEnableReply) {
			var btdiv = new Element('div', {
				'styles':{
					'visibility':'hidden',
					'margin':'0 10px 0 0px'
				},
				'id':'btopt_' + item.id
			});
			if (btEnableRetweet) {
				var rtlink = new Element('a', {
					'href':"javascript:btRT('bt_" + item.id + "', '" + item.from_user + "');"
				});
				var rtimg = new Element('img', {
					'src':btSitePath+'components/com_biztweet/assets/retweet.png',
					'align':'absmiddle',
					'border':0
				});
				rtlink.adopt(rtimg);
				btdiv.adopt(rtlink);
			}
			
			if (btEnableReply) {
				var rylink = new Element('a', {
					'href':"javascript:btReply('" + item.from_user + "', '" + item.id + "');"
				});
				var ryimg = new Element('img', {
					'src':btSitePath+'components/com_biztweet/assets/reply.png',
					'align':'absmiddle',
					'border':0
				});
				rylink.adopt(ryimg);
				btdiv.adopt(rylink);
			}
			txtspan.adopt(btdiv);
		}

		// do we already have 20 elements here?
		if (update.getChildren().length == btSearchLimit) {
			// get rid of the last one
			update.getLast().remove();
		}
		li.injectTop(update);
		var slide = new Fx.Slide(li, {duration:200});
		slide.hide();
		slide.slideIn();

	});
	btLatestPost[escape(update.getProperty('title'))] = json.max_id;
}

function btCalculateTime(utc) {
	var d = new Date(Date.parse(utc));
	var hours = d.getHours();
	if (hours >= 12) {
		var suffix = 'pm';
		if (hours > 12) hours = hours % 12;
	} else {
		var suffix = 'am';
	}
	var mins = d.getMinutes();
	if (mins < 10) {
		mins = '0' + mins;
	}
	// is it today or not
	var now = new Date();
	var posted_at = d.getFullYear() + '-' + d.getFullMonth() + '-' + d.getFullDate();
	var today = now.getFullYear() + '-' + now.getFullMonth() + '-' + now.getFullDate();
	var prefix = '';
	if (posted_at < today) {
		prefix = d.toDateString() + ' at ';
	}
	return prefix + hours + ':' + mins + suffix;
}

function periodicChecker(url) {
	var old = document.getElementById('uploadScript');
	if (old != null) {
		old.parentNode.removeChild(old);
		delete old;
	}
	var head = document.getElementsByTagName("head")[0];
	var script = document.createElement('script');
	script.id = 'uploadScript';
	script.type = 'text/javascript';
	script.src = url;
	head.appendChild(script);
}

String.prototype.btlinkify=function() {
	return this.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&\?\/.=]+/g, function(m) {
		return m.link(m);
	});
};

String.prototype.btlinkuser=function() {
	return this.replace(/\@([A-Za-z0-9-_]+)/g,function(u) {
		var username=u.replace("@","");
		return '@' + username.link("http://twitter.com/"+username);
// return u.link("http://twitter.com/"+username);
	});
};

String.prototype.btlinktag=function() {
	return this.replace(/\#([A-Za-z0-9-_]+)/g,function(t) {
		//var tag=t.replace("#","%23");
		var tag=t.replace("#","");
		return '#' + tag.link("http://search.twitter.com/search?q="+tag);
	});
};

Date.prototype.getFullMonth=function() {
	var m = this.getMonth();
	if (m < 10) m = '0' + m;
	return m;
}

Date.prototype.getFullDate=function() {
	var d = this.getDate();
	if (d < 10) d = '0' + d;
	return d;
}

btLinkTwitPic=function(content) {
	// get the anchor tags
	var links = $$(content.getElementsByTagName('a'));
	links.each(function(l){
		if (l.href.match(/twitpic\.com/g)) {
			var id = l.href.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_\.]*twitpic\.com\//g, '').replace(/[^A-Za-z0-9]/g, '');
			$(l).setProperty('href', btSitePath + 'index2.php?option=com_biztweet&Itemid=' + btItemid + '&task=twitpic&tmpl=component&image=' + id + '.jpg');
			$(l).addClass('modal');
			var options = {
				handler: 'image',
				url: $(l).getProperty('href')
			}
			l.addEvent('click', function(e) {
				new Event(e).stop();
				SqueezeBox.fromElement(l);
			});
			if (btTwitPicThumbs) {
				// now set the actual content of the link to the thumbnail image!
				var thumb = new Element('img', {
					src: 'http://twitpic.com/show/mini/' + id,
					align:'right',
					width:'75px',
					height:'75px'
				});
				$(l).empty();
				$(l).adopt(thumb);
			}
		}
	});
}

function basename (path, suffix) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Ash Searle (http://hexmen.com/blog/)
    // +   improved by: Lincoln Ramsay
    // +   improved by: djmix
    // *     example 1: basename('/www/site/home.htm', '.htm');
    // *     returns 1: 'home'
 
    var b = path.replace(/^.*[\/\\]/g, '');
    
    if (typeof(suffix) == 'string' && b.substr(b.length-suffix.length) == suffix) {
        b = b.substr(0, b.length-suffix.length);
    }
    
    return b;
}
