window.onload = function() {  

	Kinvey.init({
    appKey: 'kid2146',
    appSecret: 'ebfe51e14ef54982bc2051f5081576ab'
    });


	// Create a new user instance, and login using supplied credentials.
	//wrap this around an ajax success php function. 
	//the php function should wrap into the wordpress database and get the user name and password. 
	//should retun the user/pass in json form and use it to plugin for the username and password elements here.  
	//if false will soon be part of the if success after ajax, the rest of this page will be wrapped around that function also. 
	$.ajax({
		type: 'POST',
		dataType: "json",
		url: get_secure_data_url,
		success: function(cq_kinvey_options)
		{

			function findUrl(text) {
			    var urlRegex = /(https?:\/\/[^\s]+)/g;
			    return text.match(urlRegex, function(url) {
			        return url;
			    })
			}

/*
			var current_user = Kinvey.getCurrentUser();
			if(null !== current_user) {
			    curent_user.logout({
			        success: function() {
			            // user is now logged out.
			        },
			        error: function() {
			            // An error occurred.
			        }
			    });
			}
			else
			{
			}
*/  
			var user = new Kinvey.User();


			user.login(cq_kinvey_options.user_name, cq_kinvey_options.password, {
			    success: function(user) {
			        // user is the logged in user instance.
						var currentTime = new Date();
						var month = currentTime.getMonth() + 1;
						var day = currentTime.getDate();
						var year = currentTime.getFullYear();
						var todays_date = month + '/' + day + '/' + year;


						var url_match = /https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w/_\.]*(\?\S+)?)?)?/;

						var twitterSearches = new Kinvey.Collection(cq_kinvey_options.daily_check);
						tsquery = new Kinvey.Query(); 
						tsquery.on('date').equal(todays_date);
						// tsquery.on('site').equal(site_url); 	
						twitterSearches.setQuery(tsquery); 

					    twitterSearches.fetch({
					    success: function(list) {
					    // list is an array of entities.
					    	if(list[0])
					    	{
						    	var db_current_date = list[0].get('date');

						    	console.log(list);
						    }
						    else
						    {
									var date_entity = new Kinvey.Entity({
								    date: todays_date
								     }, cq_kinvey_options.daily_check);
								    date_entity.save({
								    success: function(twitterSearch) {

								    },
								    error: function(error) {
								    // An error occurred.
								    }
								});


								$.getJSON('http://search.twitter.com/search.json?q='+site_url+'&rpp=50&include_entities=true&callback=?', function(data)
					/*				$.getJSON('http://search.twitter.com/search.json?q=http:// quote&rpp=50&include_entities=true&callback=?', function(data)
					*/			    {
							    	console.log(data.results);

							    	if(data.results.length > 0)
							    	{
							    		// if there are results for the search term. 
								        $(data.results).each(function(i,v)
								        {
											if(this.text.indexOf('\"') != -1)
											{

												var parts = this.text.split('\"');
												var tweet_id = this.id;
												var twitter_user_id = this.from_user_id;
												var twitter_user_handle = this.from_user;
												var tweet_text = this.text;


												var long_url = this.entities.urls[0].expanded_url;

												var page_url = long_url;

												$.getJSON('http://api.longurl.org/v2/expand?format=json&url=' +  encodeURIComponent(long_url) + '&callback=?', function(urldata) {
												    // console.log(urldata);
												    page_url = urldata['long-url'];
												});


												if(parts.length > 2)
												{
													var the_quote = parts[1];
													var quote_min_length = 10;


													if(the_quote.length < quote_min_length)	//less than quote_min_length aren't really quotes.  Less than 3 words usually.  Sarcasm sometimes...
													{
														//store in a data labeled something like too small to be real quotes to monitor.  
													}
													else  	//meets the typical length of an actual quote.  :-)  
													{
														console.log(this);

														var collection = new Kinvey.Collection(cq_kinvey_options.collection_name);
														//change quote-tweets to be 


														qtquery = new Kinvey.Query(); 
														qtquery.on('tweet_id').equal(this.id);	
														collection.setQuery(qtquery); 

													    collection.fetch({
													    success: function(list) {
													    // list is an array of entities.
													    	if(list[0])
													    	{
														    }
														    else
														    {

																var qt_entity = new Kinvey.Entity({
																    tweet_id: tweet_id, 
																    twitter_user_id: twitter_user_id,
																    twitter_user_handle: twitter_user_handle,
																    tweet_text: tweet_text,
																    quote: the_quote,
																    site: site_url,
																    page_url: page_url

																     }, cq_kinvey_options.collection_name);
																    qt_entity.save({
																    success: function(tweet) {
																    // book is the entity instance.
																    },
																    error: function(error) {
																    // An error occurred.
																    }
																    });
														    }
													    },
													    error: function(error) {
													    	console.log(error);
														    // An error occurred.
													    }
													    });
													}
												}
												else
												{

												}

											}
								        	//add processing here for each tweet, and then 
								        	// quote-tweets is the name of the kinvey collection 
								        	// if the tweet has quotes in it, then store it along w/ the url from the tweet.  				        	

								        	console.log(i);	//i is the counter starting with 0 
								        	// console.log(v);	//v is the actual twitter data

								        });
							    	}
							    	else
							    	{

							    	}

								});

						    }
					    },
					    error: function(error) {
					    	//run php query that should create a user or at least make sure connection is working.  
						    // An error occurred.
					    }
					    });




						var show_collection = new Kinvey.Collection(cq_kinvey_options.collection_name);
						show_query = new Kinvey.Query(); 

						show_query.on('page_url').equal(window.location['href']);	
						show_collection.setQuery(show_query); 



					    show_collection.fetch({
					    success: function(list) {
					    	console.log(list);
					    // list is an array of entities.
					    	if(list[0])
					    	{
						    	var this_quote = list[0].get('quote');
						    	//add code to find the quote, find after the next </p> and then create a blockquote with the twitter button.  :-)

						    	var contains = '*:contains(50 years)';

						    	var found_it = $(contains+':last');

						    	var tweet_text = '"'+this_quote+'"';

						    	var this_page_url = window.location['href'];


								var tweet_js = '<a href="https://twitter.com/intent/tweet?button_hashtag=Quote&text='+ encodeURIComponent(tweet_text) + ' "';
								tweet_js += 'class="twitter-hashtag-button" data-size="large" data-url="' +this_page_url + '">Tweet #Quote</a>';


								var blockquote_quote = '<blockquote>'+this_quote+'</blockquote>';

						    	found_it.after(blockquote_quote+tweet_js);

						    	//twitter script that will actually transition the link into a tweet button.  
								!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];
									if(!d.getElementById(id)){js=d.createElement(s);
									js.id=id;js.src="//platform.twitter.com/widgets.js";
									fjs.parentNode.insertBefore(js,fjs);
									}}(document,"script","twitter-wjs");

						    }
					    },
					    error: function(error) {
						    // An error occurred.
					    }
					  });

			    },
			    error: function(error) {
			    	ajax()
			    	//ajax call to php file that will make sure user stuff is in jq updates.  
			    	console.log(error);
			        // An error occurred.
			    }
			});

		}
	});



};  //window.onload 

