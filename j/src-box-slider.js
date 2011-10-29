var BOX_SLIDER = {
	
	init:function()
	{
		// assign the slider to a variable
		  var slider = $('#slider1').bxSlider({
		    controls: false
		  });

		  // assign a click event to the external thumbnails
		  $('.thumbs a').click(function(){
		   var thumbIndex = $('.thumbs a').index(this);
		    // call the "goToSlide" public function
		    slider.goToSlide(thumbIndex);
		  
		    // remove all active classes
		    $('.thumbs a').removeClass('pager-active');
		    // assisgn "pager-active" to clicked thumb
		    $(this).addClass('pager-active');
		    // very important! you must kill the links default behavior
		    return false;
		  });

		  // assign "pager-active" class to the first thumb
		  $('.thumbs a:first').addClass('pager-active');
	}

	,test:function()
	{
		
	}
		
}