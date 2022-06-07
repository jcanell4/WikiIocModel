require(["jquery.min","jquery-ui.min","jquery.imagesloaded","render","functions","doctools","quiz"], function(jQuery,jUi,jIl,render,func,Highlight,quiz){
	$("article").imagesLoaded(function(){
		render.infoTable();
		render.infoFigure();
	});
	Highlight();
});

$(document).ready(function () {
	$(".iocnote, .iocreference, .ioctext, .iocfigurec").toBColumn();
});
