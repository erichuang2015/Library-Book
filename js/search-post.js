( function( $ ) {
	
	// hooks everything into document ready
	$(document).ready( function() {
		
		// create a function to actually fire the search
		function dosearch(t) {

	        // do the ajax request for job search
			    $.ajax({
				    
				    type: 'post',
				    url: 'search_result.php', // the localized name of your file
				    data: {
					    action: 'myplugin_ajax_search_jobs', // the wp_ajax_ hook name
					      search: t
				    },
				
				    // what happens on success
				    success: function( result ) {

					    // if the ajax call returns no results
					    if( result === 'error' ) {
						    
						    // set the html of the element with the class to no results
						    $( '.ajax-results' ).html( 'No results' );
					    
					    // we have results to display
					    } else {
						    
						    // populate the results markup in the element with the class of ajax-results
						    $( '.ajax-results' ).html( result );
						
					    }

				    }
				
			    });
	        
	    }
	    
	    var thread = null;
	    
	    // when the keyboard press is relased in the input with the class ajax-search
	    $('.ajax-search').keyup(function() {
			  
			  // clear our timeout variable - to start the timer again
			  clearTimeout(thread);
			  
			  // set a variable to reference our current element ajax-search
			  var $this = $(this);
			  
			  // set a timeout to wait for a second before running the dosearch function
			  thread = setTimeout(
				  function() {
				  	dosearch($this.val())
				  },
				  1000
			  );
	    });
		
	});
	
}) ( jQuery );