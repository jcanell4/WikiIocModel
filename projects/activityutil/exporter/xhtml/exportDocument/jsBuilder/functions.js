define (["render"],function(render){
	var disableshortcuts = false;
	var ltoc = parseInt($("#toc").css('left'),10);
	var lbridge = parseInt($("#bridge").css('left'),10);
	var lfavcount = parseInt($("#favcounter").css('left'),10);
	var lfavorites = parseInt($("#favorites").css('left'),10);
	var topheader = parseInt($("#header").css('height'),10);
	var paddingarticle = parseInt($("article").css('padding-bottom'),10);
	var defaultsettings = '{"menu":[{"mvisible":1}],"toc":[{"tvisible":0}],"settings":[{"fontsize":2,"contrast":0,"alignment":0,"hyphen":0,"width":2,"mimages":1,"scontent":1}]}';
	var defaultbookmarks = '{"fav":[{"urls":"0"}]}';
	var defaultbookquizzes = '{"quiz":[{"urls":"0"}]}';
	var showtooltips = false;
	var cookiesufix = '@IOCCOOKIENAME@';
	var cookiegeneral = 'ioc_settings';//Always same settings for all materials
	var cookiefavorites = 'ioc_'+cookiesufix+'_bookmarks';
	var cookiequizzes = 'ioc_'+cookiesufix+'_quizzes';
        var isBoostIoc = $("link[href='css/boostioc.css']").length;

	
	var setFontsize = (function (info){
		var options=new Array("text-tiny","text-small","text-normal","text-big","text-huge");
		$("article").addClass(options[info]);
		for (i=0;i<options.length;i++){
			if (i==info){
				continue;
			}
			$("article").removeClass(options[i]);
		}
		if (info === options.length-1){
			$("article").css("padding-bottom","8em");
		}else{
			$("article").css("padding-bottom",paddingarticle);
		}
		render.infoTable();
		render.infoFigure();
		setCookieProperty(cookiegeneral,'fontsize', info);
	});

	var setAlignment = (function (info){
		var options=new Array("text-left","text-justify");
		other = (info==0)?1:0;
   	 	$("article").addClass(options[info]);
    	$("article").removeClass(options[other]);
	});

	var setHyphenation = (function (info){
		var state = (info==0)?false:true;
   	 	Hyphenator.doHyphenation = state;
   	 	Hyphenator.toggleHyphenation();
	});
	
	var setMainFig = (function (show){
		if (show == 1){
			$("article .iocfigure").removeClass("hidden");
		}else{
			$("article .iocfigure").addClass("hidden");
		}
	});
	
	var setSecContent = (function (show){
		var elements = new Array("iocfigurec", "iocnote", "ioctext", "iocreference", " ioccopytoclipboard");
		for (i=0;i<elements.length;i++){
			if (show == 1){
				$("article ."+elements[i]).removeClass("hidden");
			}else{
				$("article ."+elements[i]).addClass("hidden");
			}
		}
	});

	var setArticleWidth = (function (info){
		var options=new Array("x-narrow","narrow","medium","wide","x-wide","x-extra-wide");
		$("article").addClass(options[info]);
		for (i=0;i<options.length;i++){
			if (i==info){
				continue;
			}
			$("article").removeClass(options[i]);
		}
		var width = parseInt($("article").outerWidth(true)) + 20;
//		$(document).ready(setpnpage());
		render.infoTable();
		render.infoFigure();
		setCookieProperty(cookiegeneral,'width', info);
	});

	var setCookieProperty = (function (name, n, value){
		var info=getcookie(name);
		if (info){
			if (typeof value == 'number'){
				var patt = new RegExp("\""+n+"\":\\d+", 'g');
				info=info.replace(patt, "\""+n+"\":"+value);
			}else{
				var patt = new RegExp("\""+n+"\":\".*?\"", 'g');
				info=info.replace(patt, "\""+n+"\":\""+value+"\"");
			}
			setcookie(name,info);
		}
	});

	var settings = (function (info){
            //setContrast(info.settings[0]['contrast']);
            setFontsize(info.settings[0]['fontsize']);
            //setFontSlider(info.settings[0]['fontsize']);
            setAlignment(info.settings[0]['alignment']);
            setHyphenation(info.settings[0]['hyphen']);
            setArticleWidth(info.settings[0]['width']);
            //setWidthSlider(info.settings[0]['width']);
            setMainFig(info.settings[0]['mimages']);
            setSecContent(info.settings[0]['scontent']);
            //setCheckboxes(info);
            render.thTable();
            postohashword();
	});

	var hidetooltips = (function (){
		$("#help-tooltips > div").each(function(){
			$(this).addClass("hidden");
		});
	});

	
	var showhelp = (function (obj, show, header){
		var type = (header)?'header':$(obj).attr("name");
		var tooltip = $('#help-'+type);
		if(show){
			tooltip.removeClass('hidden');
			tooltip.fadeTo("fast", 0.8);
		}else{
			tooltip.fadeTo("fast", 0, function(){tooltip.addClass('hidden');});
		}

		if (header && show){
			var item_pos = $(obj).offset();
			tooltip.css({top:item_pos.top - (tooltip.outerHeight()/2) + 15,
						left:item_pos.left + $(obj).width() + 40
			});
		}
	});
	
	var setbackground = (function(show){
		if(!show){
			$("body").css("background-position","-60px 0");
		}else{
			$("body").css("background-position","0 0");
		}
	});

	var islocalChrome = (function (){
		return (/Chrome/.test(navigator.userAgent) && /file/.test(document.location.protocol));
	});
	
	var isChrome = (function (){
		return (/Chrome/.test(navigator.userAgent));
	});
	
	var isIE = (function (){
		return (/MSIE/.test(navigator.userAgent));
	});
	
	var postohashword = (function (){
		var url = document.location.hash;
		if (url){
			url = url.replace(/#/,'');
			var offset = $("a[id='"+url+"']").offset();
			if (offset !== null){
				$(window).scrollTop(offset.top-110);
			}
		}
	});
	
	var postosearchword = (function (){
		var url = document.location.search;
		if (/highlight/.test(url)){
				var offset = $(".highlight:first").offset();
				if (offset !== null){
					$(window).scrollTop(offset.top-80);
				}
		}
	});
	
	var setCheckExercises = (function (info){
		if (info){
			var url = document.location.pathname;
			var patt;
			$("h2").each(function(i){
				patt = new RegExp(";;"+url+"\\|"+$(this).children("a").attr("id"), 'g');
				if(patt.test(info.quiz[0]['urls'])){
					$(this).children("span[name='check']").addClass("check").css('display','inline-block');
				}
			});
		}
	});

	var editCheckExercise = (function(url,idheader){
		var info = getcookie(cookiequizzes);
		if (info){
			var object = $.parseJSON(info);
			var urls = [];
			var patt = new RegExp(";;"+url+"\\|"+idheader, 'g');
			if(!patt.test(object.quiz[0]['urls'])){
				url = object.quiz[0]['urls'] + ";;" + url + "|" + idheader;
				$("h2 > a[id='"+idheader+"']").siblings("span[name='check']").addClass("check").css('display','inline-block');
				setCookieProperty(cookiequizzes,'urls', url);
			}
		}
	});

        var setNumFigs = function(){
            var footerType = $("article").data("figureFooterType");
            $("article .iocfigure > a").each(function(i){
                if(footerType=="toTitle"){
                    var $footerNode = $(this).parent().find(".footfigure");
                    if($footerNode.text()){
                        $(this).parent().find("figcaption").append(". <span class=\"footfigure\">"+$footerNode.text()+"<\span>");
                    }
                    $footerNode.remove();
                }
                $(this).parent().find("figcaption > .figuretitle").append(" "+(i+1)+" ");
                $(".figref > a[href=\"#"+$(this).attr("name")+"\"]").append(" "+(i+1)+" ");
            });            
        };
	
        var setNumTables = function(){
            var footerType = $("article").data("tableFooterType");
            $("article .ioctable .titletable > a, article .iocaccounting .titletable > a").each(function(i){
                if(footerType=="toTitle"){
                    var $footNode = $(this).parent().parent().find(".foottable");
                    if($footNode.text()){
                        $(this).parent().append(". <span class=\"foottable\">"+$footNode.text()+"<\span>");
                    }
                    $footNode.remove();
                }
                $(this).find("span").first().append(" "+(i+1)+" ");
                $(".tabref > a[href=\"#"+$(this).attr("name")+"\"]").append(" "+(i+1));
            });            
        };
        
        var setReferences = function(){
             $(".iocquote .ioccontent p:last-of-type").each(function(){
                 if(isBoostIoc){
                    var $this = $(this);
                    if($this.siblings().length>0 && $this.find("em").length){
                        $this.addClass("reference");
                        $this.appendTo($this.parent().parent());
                    }
                }
             });
             $(".iocquote .ioccontent p:first-of-type").each(function(){
                var $this = $(this);           
                var text = $this.text();
                $this.text(text.replace(/^\s*[“\"](.*)/s, "$1"));
             });
             $(".iocquote .ioccontent p:last-of-type").each(function(){
                var $this = $(this);           
                var text = $this.text();
                $this.text(text.replace(/(.*)[”\"]\s*$/s, "$1"));
             });
             
        }; 
	
	//Set params into our cookie
	var setcookie = (function(name, value){
		document.cookie = name+'=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
		document.cookie = name+"=" + escape(value) + "; path=/;";
	});

	//get params from our cookie    	
	var getcookie = (function(name){
		var i,x,y,ARRcookies=document.cookie.split(";");
		for (i=0;i<ARRcookies.length;i++)
		{
		  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		  x=x.replace(/^\s+|\s+$/g,"");
		  if (x==name){
			return unescape(y);
		  }
		}
	});

	var get_params = (function(reset){
		var info = getcookie(cookiegeneral);
		if (info!=null && info!="" && !reset){
			//Get and apply stored options
			var object = $.parseJSON(info);
			settings(object);
                        info = getcookie(cookiequizzes);
                        if (info!=null && info!=""){
                                object = $.parseJSON(info);
                                setCheckExercises(object);
                        }else{
                                setcookie(cookiequizzes,defaultbookquizzes);
                        }
		}else{
			//Save default options
			var object = $.parseJSON(defaultsettings);
			settings(object);
			setcookie(cookiegeneral,defaultsettings);
			setcookie(cookiequizzes,defaultbookquizzes);
		}
	});
	
	var cookiesOK = (function(){
		document.cookie = 'ioc_html_test="test";';
		if (/ioc_html_test/.test(document.cookie)){
			document.cookie = 'ioc_html_test=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
			return true;
		}
		return false;
	});
	

	function basename(path) {
		return path.replace(/\\/g,'/').replace( /.*\//, '' );
	}

	//Header shadow
	$(window).scroll(function () {
		if ($(window).scrollTop() > 30){
			$("header").addClass("header-shadow");
			$("#upbutton").show("slow");
		}else{
			$("header").removeClass("header-shadow");
			$("#upbutton").hide("slow");
		}
	});
	
	
	$.expr[':'].parents = function(a,i,m){
	    return $(a).parents(m[3]).children('ul').length < 1;
	};
	
	$("h1 > a").hover( 
		function(){
			if(showtooltips){
				showhelp($(this),true,true);
			}
		},
		function(){
			if(showtooltips){
				showhelp($(this),false,true);
			}
		}
	);
	
	$("h2 > a,h3 > a,h4 > a").hover( 
		function(){
			if(showtooltips){
				showhelp($(this),true,true);
			}
		},
		function(){
			if(showtooltips){
				showhelp($(this),false,true);
			}
		}
	);

	$(document).on("click", "article figure img", function(){
		render.previewImage($(this));
	});

	$(document).on("click", ".closepreview",function(){
		$('#back_preview, #preview').addClass('hidden');
	});
	
	$(document).on("click", "#preview", function(){
		$('#back_preview, #preview').addClass('hidden');
	});
	
	//Initialize menu and settings params
	get_params();
        setNumFigs();
        setNumTables();
        setReferences();
	

	return {"editCheckExercise":editCheckExercise,
			"postosearchword":postosearchword};
});
