
creasty.create( "creasty.Form", Class.create({
	initialize:function(form){
		this.create(form);
	},
	create:function(form){
		if(!form)return;
		this.form=$(form);
	},
	get:function(){
		var a={},
			_field=this._field;
		
		this.form.find("input,textarea,select").each(function(){
			var n=this.name,
				v=_field(this);
			
			if(!n)return;
			
			if(n.endsWith("[]")){
				n=n.slice(0,-2); 
				if(!isArray(a[n]))a[n]=new Array;
			}
			
			if(!isNull(v) && !isUndefined(v)){
				if(/^[0-9]+$/.test(v))v=parseFloat(v);
				
				if(isArray(a[n])){
					a[n].push(v);
				}else{
					a[n]=v;
				}
			}
		});
		
		return a;
	},
	_field:function(el){
		var n=el.name,
			t=el.type,
			tag=el.tagName.toLowerCase();
		
		if(
			!n || el.disabled || t=="reset" || t=="button" ||
			(t=="checkbox" || t=="radio") && !el.checked ||
			(t=="submit" || t=="image") && el.form && el.form.clk!=el ||
			tag=="select" && el.selectedIndex==-1
		){
			return null;
		}
		
		if(tag=="select") {
			var index=el.selectedIndex;
			if(index<0)return null;
			
			var a=[],
				ops=el.options,
				one=(t=="select-one")?true:false,
				max=(one?index+1:ops.length);
			
			for(var i=(one?index:0);i<max;i++){
				var op=ops[i];
				
				if(op.selected){
					var v=op.value||op.text;
					if(one)return v;
					a.push(v);
				}
			}
			return a;
		}
		return $(el).val();
	}
}) );

creasty.create( "creasty.Form.util", (function(){
	var c0nstruct0r={
		selectAction:function(){
			// TODO
		},
		InputTitleOverlay:function(selector){
			$(selector||".autofill").each(function(){
				var $this=$(this),
					d=$this.attr("title");
				
				document.activeElement!=$this[0]&&$this.val(d);
				
				$this.focus(function(){
					$this.val()==d&&$this.val("");
				}).blur(function(){
					$this.val()==""&&$this.val(d);
				});
			});
		}
	};
	return c0nstruct0r;
})() );
