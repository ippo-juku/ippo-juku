
/** 
 * .tweet()
 * 
 * @author ykiwng
 *
 * @param {Object} settings
 *   @key {String} username - username of twitter
 *   @key {Number} count - limit number of tweet
 *   @key {Function} template - returns HTML strings for display
 *   @key {Function} done - will be called when it finished display
 */
(function( $ ){
	
	var 
	relative_time = function( time ){
		var values = time.split( ' ' );
		time = values[1] + ' ' + values[2] + ', ' + values[5] + ' ' + values[3];
		
		var now = new Date(), 
			d = parseInt( ( now.getTime() - Date.parse( time ) ) / 1000 ) + now.getTimezoneOffset() * 60;
		
		return(
			d < 60
			? 'ちょっと前'
			: d < 3600
				? parseInt( d / 60 ) + '分前'
				: d < 86400
					? parseInt( d / 3600 ) + '時間前'
					: parseInt( d / 86400 ) + '日前'
		);
	},
	linknize = function( text ){
		return text.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g, function( url ){
			return '<a href="' + url + '">' + url + '</a>';
		}).replace(/\B@([_a-z0-9]+)/ig, function( reply ){
			return  reply.charAt(0) + '<a href="http://twitter.com/' + reply.substring(1) + '">' + reply.substring(1) + '</a>';
		});
	},
	tweeturl = function( user, id ){
		return function( text ){
			return [
				'<a href="http://twitter.com/', user, '/statuses/', id, '" class="tweet-time" target="_blank">', 
				text, 
				'</a>'
			].join( '' );
		};
	},
	template = function( o ){
		return [ '<li class="tweet-data-', o.num, '"><span>', o.text, '</span> ', o.url( o.time_relative ), '</li>' ].join( '' );
	};
	
	$.fn.tweet = function( settings ){
		var defaults = {
			username: '',
			count: 10,
			template: template,
			done: $.nope	
		};
		settings = $.extend( defaults, settings );
		
		if( !settings.username || !$.isFunction( settings.template ) )
			return;
		
		var status = "", $this = $( this );
		
		$.getJSON( 'http://twitter.com/statuses/user_timeline/' + settings.username + '.json?callback=?&count=' + settings.count, function( tweets ){
			$.each( tweets, function( i, tw ){
				var obj = {
					num: i,
					text: linknize( tw.text ),
					text_raw: tw.text,
					time: tw.created_at,
					time_relative: relative_time( tw.created_at ),
					url: tweeturl( tw.user.screen_name, tw.id_str )
				};
				
				status += settings.template( obj );
				
			});
			
			$this.html( status );
			
			$.isFunction( settings.done ) && settings.done();
		});
		
		return $this;
	}
	
})( jQuery );

