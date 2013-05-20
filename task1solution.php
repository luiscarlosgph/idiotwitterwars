<?php
 
# idio has two offices in the UK, the first in Exeter and the second in London.
# As avid and curious Twitter users we'd like to find out whether the population
# of Exeter or London are better at spelling. By using the Twitter API,
# aggregate and analyse tweets from Exeter and London.
#
# More info: https://gist.github.com/idiomag/991055
# Author: Luis Carlos Garcia-Peraza Herrera (luiscarlos.gph@gmail.com)
#
# Instructions (to run it on Ubuntu):
#
# * Install dependencies:
# > sudo apt-get install libpspell-dev
# > sudo apt-get install php5-pspell
# > sudo apt-get install aspell-en
# > sudo service apache2 restart
#
# * This script is developed using PHP 5 >= 5.2.0, PECL json >= 1.2.0.
#
# * London GPS coordinates: 51.528397,-0.115585
# * Exeter GPS coordinates: 50.861444,-3.495484
 
# Checking the spelling of a tweet.
# Returns the number of words with wrong spelling, what can be used as a sort
# of scoring method.
# It does not pay attention to those words starting with # or @.
function checkStr($str) {
	$pspell_link = pspell_new("en"); # Initialising pspell to English
	$words = preg_split('/[\s,.]+/', $str);
	$counter = 0;
	foreach($words as $word) {
		if(!preg_match('/[@#]./', $word) && !pspell_check($pspell_link, $word))  
			$counter++;
	}
	return $counter;
}
 
# Function to get all tweets from a certain place by using GPS coordinates.
# It uses the variable $radius as a threshold.
# It returns an array. Each index of the array is just a string with the text of
# the tweet.
function getTweetsByGPS($latitude, $longitude, $radius) {
	$page = 1;
	$rpp = 100;
	$query = "http://search.twitter.com/search.json?q=&page=$page&rpp=$rpp&geocode=$latitude,$longitude,$radius" . 'mi';
	$response = file_get_contents($query);
	$tweets = json_decode($response, true);
	$results = array();
	for($i = 0; $i < count($tweets['results']); $i++)
		array_push($results, $tweets['results'][$i]['text']);	
	return $results;
}
 
# -- Main source code --
 
# Constants
$london_lat = 51.528397;
$london_lon = -0.115585;
$exeter_lat = 50.861444;
$exeter_lon = -3.495484;
$rad = 5; # 5 miles radius
 
# Obtaining some tweets from each place
$london_tweets = getTweetsByGPS($london_lat, $london_lon, $rad);
$exeter_tweets = getTweetsByGPS($exeter_lat, $exeter_lon, $rad);
 
# Checking the spelling of London people
$london_errors = 0;
foreach($london_tweets as $tweet)
	$london_errors += checkStr($tweet);
 
# Checking the spelling of Exeter people
$exeter_errors = 0;
foreach($exeter_tweets as $tweet)
	$exeter_errors += checkStr($tweet);
 
# Dislaying the shocking results
echo "Discovered spelling mistakes:<br>\n";
echo "People from London: $london_errors spelling mistakes.<br>\n";
echo "People from Exeter: $exeter_errors spelling mistakes.<br>\n";
 
if($london_errors > $exeter_errors)
	echo 'People from London have more spelling mistakes than people from Exeter.';
else if($exeter_errors > $london_errors)
	echo 'People from Exeter have more spelling mistakes than people from London.';
else
	echo 'People from London have the same amount of spelling mistakes than people from Exeter.';
 
?>
