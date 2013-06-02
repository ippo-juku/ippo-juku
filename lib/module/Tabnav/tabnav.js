/**
 * Tabnav
 * 
 * @author ykiwng
 * 
 */

/*
<div class="dyn-tabnav tabnav-zen">
	<h2>Tab Title 1</h2>
	<h3>Not Tab Title</h3>
	<!-- Tab Content 1 -->
	
	<h2>Tab Title 2</h2>
	<div class="test">
		<h2>Not Tab Title</h2>
		<!-- Tab Content 2 -->
	</div>
</div>

---------------------------------------------------------

<div class="dyn-tabnav tabnav-zen">
	<ol class="tab-menu">
		<li>Tab Title 1</li>
		<li>Tab Title 2</li>
	</ol>
	<h2 class="tab-title">Tab Title 1</h2>
	<div class="tab-content">
		<h3>Not Tab Title</h3>
		<!-- Tab Content 1 -->
	</div>
	<h2 class="tab-title">Tab Title 2</h2>
	<div class="tab-content">
		<div class="test">
			<h2>Not Tab Title</h2>
			<!-- Tab Content 2 -->
		</div>
	</div>
</div>

*/

!function( document, $ ){
	var tabnav = function( $container ){
		var header = 'h5';
		
		// get the highest level headerings
		$container.children( 'h2, h3, h4, h5' ).each(function(){
			var h = this.nodeName.toLowerCase();
			if( h < header )
				header = h;
		});
		
		var $title = $container.children( header ),
			$menu = $( '<ol class="tab-menu" />' );
		
		if( $title.length == 0 )
			return;
		
		$title.addClass( 'tab-title' ).each(function(){
			var $this = $( this );
			
			$this.nextUntil( '.tab-title' ).wrapAll( '<div class="tab-content"/>' );
			$menu.append( $( '<li/>' ).text( $this.text() ) );
		});
		
		$container.prepend( $menu );
		
		this.tab = $menu.children();
		this.content = $container.children( '.tab-content' );
		
		this.totalstate = this.tab.length;
		
		var that = this;
		this.tab.bind( 'click', function( e ){
			e.preventDefault();
			that.setCurrentView( $( this ).prevAll().length );
		});
		
		this.setCurrentView( 0 );
	};
	
	tabnav.prototype = {
		viewstate: -1,
		setCurrentView: function( viewId ){
			if( this.isCurrentView( viewId ) )
				return;
			
			if( this.viewstate != -1 ){
				this.getTabNode().removeClass( 'active' );
				this.getContentNode().removeClass( 'show' );
			}
			
			this.changeState( viewId );
			
			this.getTabNode().addClass( 'active' );
			this.getContentNode().addClass( 'show' );
		},
		fixViewId: function( viewId ){
			if( viewId == null )
				return this.viewstate;
			
			return ( this.totalstate + viewId ) % this.totalstate;
		},
		changeState: function( viewId ){
			this.viewstate = this.fixViewId( viewId );
		},
		isCurrentView: function( viewId ){
			return this.viewstate == this.fixViewId( viewId );
		},
		getTabNode: function( viewId ){
			return this.tab.eq( this.fixViewId( viewId ) );
		},
		getContentNode: function( viewId ){
			return this.content.eq( this.fixViewId( viewId ) );
		}
	};
	
	$( document ).ready(function(){
		$( '.dyn-tabnav' ).each(function(){
			new tabnav( $( this ) );
		});
	});
}( document, jQuery );
