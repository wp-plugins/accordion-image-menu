/**************************************************************

Vertical Accordion Image Menu

**************************************************************/

var ImageMenu = new Class({
	
	getOptions: function(){
		return {
			onOpen: false,
			onClose: Class.empty,
			openHeight: 200,
			transition: Fx.Transitions.quintOut,
			duration: 500,
			open: null,
			border: 0
		};
	},

	initialize: function(elements, options){
		this.setOptions(this.getOptions(), options);
		
		this.elements = $$(elements);
		
		this.height = {};
		this.height.closed = this.elements[0].getStyle('height').toInt();
		this.height.openSelected = this.options.openHeight;
		this.height.openOthers = Math.round(((this.height.closed*this.elements.length) - (this.height.openSelected+this.options.border)) / (this.elements.length-1))
		
		
		this.fx = new Fx.Elements(this.elements, {wait: false, duration: this.options.duration, transition: this.options.transition});
		
		this.elements.each(function(el,i){
			el.addEvent('mouseenter', function(e){
				new Event(e).stop();
				this.reset(i);
				
			}.bind(this));
			
			el.addEvent('mouseleave', function(e){
				new Event(e).stop();
				this.reset(this.options.open);
				
			}.bind(this));
			
			var obj = this;			
						
		}.bind(this));
		
		if(this.options.open){
			if($type(this.options.open) == 'number'){
				this.reset(this.options.open);
			}else{
				this.elements.each(function(el,i){
					if(el.id == this.options.open){
						this.reset(i);
					}
				},this);
			}
		}
		
	},
	
	reset: function(num){
		if($type(num) == 'number'){
			var height = this.height.openOthers;
			if(num+1 == this.elements.length){
				height += this.options.border;
			}
		}else{
			var height = this.height.closed;
		}
		
		var obj = {};
		this.elements.each(function(el,i){
			var h = height;
			if(i == this.elements.length-1){
				h = height+5
			}
			obj[i] = {'height': h};
		}.bind(this));
		
		if($type(num) == 'number'){
			obj[num] = {'height': this.height.openSelected};
		}
				
		this.fx.start(obj);
	}
	
});

ImageMenu.implement(new Options);
ImageMenu.implement(new Events);


/*************************************************************/