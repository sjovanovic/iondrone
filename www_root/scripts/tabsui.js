var TabsUI = {
tabContainerSelector:'.tabs',
tabSelector:'.tab',
tabSelectedSelector:'.tabSelected',
init:function(opt){
	var tui = this;
	if(opt){
		for(var i in opt){
			tui[i] = opt[i];
		}	
	}
	// make tabs
	if(tui.tabs && tui.tabs.length > 0 && tui.container){
		tui.container.each(function(){
		 	var con = $(this);
		 	var tcont = $('<div class="'+tui.tabContainerSelector.replace(/\./g, '')+'"></div>');
		 	con.html(tcont);
			for(var i=0;i<tui.tabs.length;i++){
				var otab = $('<span class="'+tui.tabSelector.replace(/\./g, '')+'">'+tui.tabs[i].value+'</span>');
				tcont.append(otab);
				if(tui.tabs[i].action){
					otab.data('selectedClass', tui.tabSelectedSelector);
					otab.bind('click.tuiSelect', function(){
						var selcls = $(this).data('selectedClass').replace('.', '');
      				$(this).parent().find('.'+selcls).removeClass(selcls);
      				$(this).addClass(selcls);
					});
					otab.bind('click.tui', tui.tabs[i].action);	
				}
				if(tui.tabs[i].active){
      			otab.trigger('click.tuiSelect');
      			otab.trigger('click.tui');
      		}
			}
		});
	}
	// make tabs UI
	$(this.tabContainerSelector).each(function(){
		$(this).parent().css({'position':'relative'});
		// add left/right buttons
		arwl = $('<div class="tabMoveLeft"><div class="arrow-left tabArrowLeft"></div></div>');
		arwr = $('<div class="tabMoveRight"><div class="arrow-right tabArrowRight"></div></div>');
		$(this).before(arwl);
		$(this).after(arwr);
		arwl.bind('click.tui', function(){
			tui.scrollin($(this).next(), false);
		});
		arwr.bind('click.tui', function(){
			tui.scrollin($(this).prev(), true);
		});
		if(!parseInt(arwr.get(0).style.width)){arwr.width(20);}
		if(!parseInt(arwl.get(0).style.width)){arwl.width(20);}
		var tmlw = arwl.outerWidth(true);
		var tmrw = arwr.outerWidth(true);
		
		
		// get width for the tabs container
		var cwidth = parseInt($(this).get(0).style.width);
		if(!cwidth){
			cwidth = $(this).parent().width() - tmlw - tmrw;
			$(this).width(cwidth);
		}
		// set the height of the tabs container
		var cheight = parseInt($(this).get(0).style.height);
		if(!cheight){
			cheight = $(this).find(tui.tabSelector+':eq(0)').outerHeight(true);
			$(this).css('height', cheight);
		}
		
		
		// set height of left/right buttons
		if(!parseInt(arwr.get(0).style.height)){arwr.css('height',cheight);}
		if(!parseInt(arwl.get(0).style.height)){arwl.css('height',cheight);}
		
		
		// position three elems (left button, right button, tabs container)
		arwl.css({'visibility':'hidden','position':'absolute','left':0, 'margin-top':0});
		$(this).css({'position':'absolute', 'overflow':'hidden', 'left':tmlw});
		arwr.css({'visibility':'hidden','position':'absolute','left':tmlw+$(this).outerWidth(true), 'margin-top':0});
		
		
		// position the arrows inside buttons
		arwl.find('.arrow-left').css({'position':'absolute', 'top':Math.round(arwl.height()/2-5), 'left':Math.round(arwl.width()/2-3), 'margin-top':0});
		arwr.find('.arrow-right').css({'position':'absolute', 'top':Math.round(arwr.height()/2-5), 'left':Math.round(arwr.width()/2-2), 'margin-top':0});
		
		
		// set css for tabs
		var ctwidth = 0;
		$(this).find(tui.tabSelector).each(function(){
			var twidth = $(this).outerWidth(true);							
			$(this).css({
				'position':'absolute',
				'display':'block',
				'left':ctwidth,
				'height':$(this).get(0).style.height?parseInt($(this).get(0).style.height):$(this).height(),
				'width':$(this).get(0).style.width?parseInt($(this).get(0).style.width):$(this).width()
			});
			ctwidth += twidth;
			
			if(ctwidth > cwidth){
				arwr.css('visibility', 'visible');
			}
		});
		
		// add scroll event
		var el = $(this);
		tui.supressScroll = false;
      var wheeler = function(evt){
        	var orgEvent = evt || window.event, delta = 0;
			orgEvent = $.event.fix(orgEvent);
    		// rudimentary scrollwheel delta
		   if ( orgEvent.wheelDelta ) { delta = orgEvent.wheelDelta/120; }
		   if ( orgEvent.detail ) { delta = -orgEvent.detail/3; }
		   // triggering the click event
		   if(!tui.supressScroll){
		   	if(delta > 0){
	    			// scroll left
	        		tui.scrollin(el, false);
	        	}else{
	        		// scroll right
	        		tui.scrollin(el, true);
	        	}
		   }
    		
        	// supressing the page scroll
    		if (orgEvent.preventDefault){
         	orgEvent.preventDefault();
         }
			orgEvent.returnValue = false;
    	};
    	if (el.get(0).addEventListener && !el.get(0).onmousewheel){
    		el.get(0).removeEventListener('DOMMouseScroll', wheeler, false);
    		el.get(0).addEventListener('DOMMouseScroll', wheeler, false);
    	}
    	el.get(0).onmousewheel = wheeler;
		// end scroll event
	});
	// some helper jquery extensions
	jQuery.fn.reverse = [].reverse;
	$.insertStyle = function(css, id){
		var el = document.getElementById(id);
  		if(el){el.parentNode.removeChild(el);}
  		var csse = document.createElement('style');
		csse.type = 'text/css'; csse.id = id;
		if(csse.styleSheet){
			document.getElementsByTagName("head")[0].appendChild(csse);	
			csse.styleSheet.cssText = css;
		}else{
			var rules = document.createTextNode(css);
			csse.appendChild(rules);
			document.getElementsByTagName("head")[0].appendChild(csse);	
		}
   };
},
scrollin:function(tcont, moveLeft){
	//console.log('Scrollin...'+(moveLeft?'left':'right'));
	var tui = this;
	var arwl = tcont.prev(); // left arrow
	var arwr = tcont.next(); // right arrow
	tui.supressScroll = true;
	var tabs = tcont.find(tui.tabSelector);
	
				
	// amont of pixels to move
	var amount, ftab;
	tabs.each(function(index){
		ftab = $(this);
		if(parseInt(ftab.css('left')) === 0){
			if(!moveLeft){
				ftab = ftab.prev();	
			}
			amount = ftab.outerWidth(true);
			return false;
		}
	});
	if(moveLeft){		
	
		tabs.each(function(index){
			var tab = $(this);
			//var newleft = (parseInt(tab.css('left')) - tab.outerWidth(true));
			var newleft = (parseInt(tab.css('left')) - amount);
			
			if(index == 0){
				
				
				// make sure right arrow button is removed when not needed
				var ltab = $(tabs[tabs.length-1]);
				var lastleft = (ltab.position().left - amount + ltab.prev().outerWidth(true));
				if(lastleft < parseInt(tcont.width()) && arwr.css('visibility') == 'hidden'){
					tui.supressScroll = false;
					return false;
				}
				if(lastleft < parseInt(tcont.width())){
					arwr.css('visibility', 'hidden');
				}else{
					arwr.css('visibility', 'visible');
				}
				
				
				// make sure the left arrow button is removed if not needed
				var firstleft = (ftab.position().left - amount);
				if(firstleft < 0){
					arwl.css('visibility', 'visible');
				}else if(firstleft == 0){
					arwl.css('visibility', 'hidden');
				}
				
			}
			
			tab.animate({'left':newleft}, 400, function(){
				tui.supressScroll = false;
			});
			
		});
	}else{
		tabs.each(function(index){
			var tab = $(this);
			//var newleft = (parseInt(tab.css('left')) + tab.outerWidth(true));
			var newleft = (parseInt(tab.css('left')) + amount);
			
			
			if(index == 0){
				// make sure the left arrow button is removed if not needed
				if(!ftab.position()){arwl.css('visibility', 'hidden'); tui.supressScroll = false;return false;}
				var firstleft = (tab.position().left + amount);
				if(firstleft < 0){
					arwl.css('visibility', 'visible');
				}else{
					arwl.css('visibility', 'hidden');
				}
				
				// make sure right arrow button is removed when not needed
				var ltab = $(tabs[tabs.length-1]);
				var lastleft = (ltab.position().left + amount + ltab.outerWidth(true));
				if(lastleft < parseInt(tcont.width())){
					arwr.css('visibility', 'hidden');
				}else{
					arwr.css('visibility', 'visible');
				}
			}
			
			tab.animate({'left':newleft}, 500, function(){
				tui.supressScroll = false;
			});
			
		});
	}
},
refresh:function(tcont){
	var tui = this;
	var arwl = tcont.prev();
	var arwr = tcont.next();
	// readjust container width
	tcont.width(tcont.parent().width() - arwl.outerWidth(true) - arwr.outerWidth(true));
	// move right arrow
	arwr.css('left', arwl.outerWidth(true) + tcont.outerWidth(true));
	//show/hide arrows
	var tabs = tcont.find(tui.tabSelector);
	
	var step = -parseInt($(tabs[0]).position().left);
	if($(tabs[0]).position().left < 0){
		//animate to reset to zero
		tabs.each(function(){
			$(this).animate({'left':$(this).position().left + step}, 400);
		});
	}
	arwl.css('visibility', 'hidden');
	var mostr = $(tabs[tabs.length-1]);
	if(mostr.position().left + mostr.outerWidth() + step > tcont.width()){
		arwr.css('visibility', 'visible');
	}else{
		arwr.css('visibility', 'hidden');
	}
}
}; // end TabsUI
