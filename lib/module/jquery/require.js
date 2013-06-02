/*!
 * Require v1.2
 * 
 * @author ykiwng
 */

!function( window, document, $ ){
	var $head = $( 'head' );
	
	var globalReady = function( ns, callback ){
		// sanitizing
		ns = ns                                    // ~=]*.123..foo.bar45..(()).890baz.;;
			.replace( /[^\w\$\.]/g, '' )           // .123..foo.bar45...890baz.
			.replace( /(^|\.)([0-9\.]+|$)/g, '' ); // foo.bar45baz
		
		var 
		timer, 
		check = new Function( 'return ( window.' + ns + ' != null )' ), 
		_callback = function(){
			if( check() ){
				clearInterval( timer );
				callback();
			}
		};
		
		timer = setInterval( _callback, 10 );
	};
	
	/*	Private Loader Class
	-----------------------------------------------*/
	var loader = {
		path: '',
		abbr: {},
		assets: {},
		packages: {},
		status: {},
		getStatus: function( name ){
			/*	Status:
				0 = None
				1 = Processing
				2 = Done
				3 = Failed
			*/
			return this.status[ name ] >>> 0;
		},
		setStatus: function( name, status ){
			this.status[ name ] = status;
		},
		setPackageStatus: function( pkg, status ){
			if( this.getStatus( pkg ) >= 2 || status == 2 && this.packages[ pkg ].length > 0 )
				return;
			
			this.setStatus( pkg, status );
		},
		setPackage: function( pkg, files ){
			this.packages[ pkg ] = files;
		},
		_load: function( pkg ){
			var path = this.packages[ pkg ].shift();
			
			if( !path )
				return;
			
			if( $.isFunction( path ) ){
				this.setStatus( pkg, path() ? 2 : 3 );
				return;
			}
			
			var 
			that = this,
			uri = path.replace( /^~(\w*)\//, function( m0, m1 ){
				var dir = that.path;
				
				// Expand path abbreviation
				$.each( m1.split( '' ), function( i, s ){
					dir += that.abbr[ s ] || '';
				});
				
				return dir;
			}),
			ext = ( /(\w+)(\?.+)?$/.exec( uri ) || [ '', '' ] )[1].toLowerCase();
			this.setStatus( path, 1 );
			
			if( ext == 'css' ){
				this.setStatus( path, 2 );
				this.setPackageStatus( pkg, 2 );
				this._load( pkg );
				
				// problem with appending stylesheets into head in IE
				// http://stackoverflow.com/questions/6079702/how-to-append-style-sheets-in-ie-using-jquery
				document.createStyleSheet
				? document.createStyleSheet( uri )
				: $( '<link/>' ).attr({ rel: 'stylesheet', href: uri }).appendTo( $head );
				
				return;
			}
			
			$.ajax({
				url: uri,
				async: true,
				cache: true
			}).done(function( data ){
				that.setStatus( path, 2 );
				that.setPackageStatus( pkg, 2 );
				that._load( pkg );
			}).fail(function(){
				that.setStatus( path, 3 );
				that.setPackageStatus( pkg, 3 );
			});
		},
		load: function( assets ){
			var that = this;
			
			$.each( assets, function( i, pkg ){
				if( that.getStatus( pkg ) >= 1 )
					return;
				
				that.setStatus( pkg, 1 );
				
				if( $.isArray( pkg ) ){
					var _pkg = String( Math.random() );
					that.assets[ _pkg ] = pkg;
					pkg = _pkg;
				}
				
				var 
				files = that.assets[ pkg ],
				fn = function(){
					that.setStatus( pkg, 2 );
				};
				
				switch( true ){
					// package
					case !!files:
						that.setPackage( pkg, files );
						that._load( pkg );
						return;
					// dot notation
					case !!pkg.match( /^[\.\w\$]+$/ ):
						var path = that.path + pkg.replace( /\./g, '/' ) + '.js';
						that.setPackage( pkg, [ path ] );
						that._load( pkg );
						return;
					// normal path
					case ( pkg.indexOf( '/' ) >= 0 ):
						that.setPackage( pkg, [ pkg ] );
						that._load( pkg );
						return;
					// global check
					case ( pkg.indexOf( '@' ) == 0 ):
						globalReady( pkg, fn );
						return;
					// dom ready
					case ( pkg == '!ready' ):
						$( document ).ready( fn );
						return;
					// window onload
					case ( pkg == '!load' ):
						$( window ).load( fn );
						return;
					// fallback
					default:
						that.setStatus( pkg, 2 );
				}
				
			});
		}
	};
	
	/*	Require
	-----------------------------------------------*/
	var require = function(){
		// the init constructor enhanced
		return new require.fn.init( arguments );
	};
	require.fn = require.prototype = {
		init: function( assets ){
			this.assets = assets;
			this.assets_count = assets.length;
			this.done_handler = $.noop;
			this.fail_handler = $.noop;
			this.status = 0;
			
			loader.load( assets );
			
			this._listener();
			
			return this;
		},
		_listener: function(){
			var 
			that = this,
			timer, 
			_callback = function(){
				var status, stat = [ 0, 0, 0, 0 ];
				
				$.each( that.assets, function( i, pkg ){
					stat[ loader.getStatus( pkg ) ]++;
				});
				
				status = that.status = stat[3] > 0 ? 3 : stat[2] == that.assets_count ? 2 : 1;
				
				if( status >= 2 ){
					clearInterval( timer );
					status == 2 ? that.done_handler() : that.fail_handler();
				}
			};
			
			timer = setInterval( _callback, 10 );
		},
		_attach: function( status, callback ){
			if( !$.isFunction( callback ) )
				return;
			
			this[ status == 2 ? 'done_handler' : 'fail_handler' ] = callback;
			
			this.status == status && callback();
		},
		done: function( callback ){
			this._attach( 2, callback );
			return this;
		},
		fail: function( callback ){
			this._attach( 3, callback );
			return this;
		},
		status: function(){
			return this.status;
		}
	};
	
	// give the init function the `require` prototype
	require.fn.init.prototype = require.fn;
	
	$.extend( require, {
		path: function( path ){
			return loader.path + ( path || '' );	
		},
		define: function( assets ){
			$.isPlainObject( assets ) && $.extend( loader.assets, assets );
			
			return loader.assets;
		},
		setPath: function( path, abbr ){
			if( path && typeof path == 'string' )
				loader.path = path;
			
			$.isPlainObject( abbr ) && $.extend( loader.abbr, abbr );
			
			return loader.path;
		},
		status: function(){
			return loader.status;
		},
		ready: function( fn ){
			$( document ).ready( fn );
		},
		load: function( fn ){
			$( window ).load( fn );
		}
	});
	
	/*	Expose the global
	-----------------------------------------------*/
	window.require = require;

}( window, document, jQuery );
